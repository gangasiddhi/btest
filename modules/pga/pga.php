<?php

if (! defined('_CAN_LOAD_FILES_')) {
    exit;
}
require_once(_PS_INTERFACE_DIR_ . 'IPG.php');
require_once(_PS_INTERFACE_DIR_ . 'IPGBank.php');
require_once(_PS_CLASS_DIR_ . 'PG.php');

class PGA  extends PG  implements IPG, IPGBank {
    public $pga_instal = 1;

    function __construct() {
        $this->name = 'pga';
        $this->tab = 'payments_gateways';
        $this->version = 1.0;
        $this->currencies = true;
        $this->currencies_mode = 'radio';

        parent::__construct();

        $this->page = basename(__FILE__, '.php');
        $this->displayName = $this->l('AKBANK POS');
        $this->description = $this->l('Payment Gateway API implementation for Akbank');
        $this->confirmUninstall = $this->l('Are you sure you want to delete your details ?');

        if (! sizeof(Currency::checkPaymentCurrencies($this->id))) {
            $this->warning = $this->l('No currency set for this module');
        }
   }

    function install() {
        if (! parent::install()
            OR ! $this->registerHook('payment')
            OR ! $this->registerHook('validation')
            OR ! $this->registerHook('paymentReturn')
            OR ! $this->registerHook('getBankResponseOnOrderStatusChange')
            OR ! $this->registerHook('paymentError')) {

            return false;
        }

        return true;
    }

    function uninstall() {
        return (parent::uninstall());
    }

    function hookValidation($params) {
        global $cookie, $cart;

        require_once(dirname(__FILE__).'/lib/pgacart.php');
        require_once(dirname(__FILE__).'/lib/pgaitem.php');

        $cardNumber = $params['card_number'];
        $cardExpiry = $params['card_expiry'];
        $cardCVV2 = $params['card_cvv'];
        $total = $params['total']; //Final total with instalments
        $installment_count = $params['instalment_count'];
        $installmnt_intrst = $params['instalment_interest'];
        $each_inst_amount = $params['each_instalment'];

        $pga = new PGA();
        $currency = $pga->getCurrency();
        $product_total = floatval(number_format($cart->getOrderTotal(true, 3), 2, '.', '')); //the total of products with tax

        $this->log->debug("others: $this->pga_instal,\t Installments: $installment_count,\t Intallment-interest: $installmnt_intrst,\t Each Install: $each_inst_amount\t Final-Total:$total\n ");

        if (_BU_ENV_ == 'production') {
            $pgaCart = new PGACart('100901569', 'butigo', 'I35iM8nmvc', "production", "949", "Auth");
        } else {
            $pgaCart = new PGACart('100100000', 'AKTESTAPI', 'AKBANK01', "sandbox", "949", "Auth");
        }

        foreach ($cart->getProducts() as $product) {
            $pgaCart->AddItem(
                    new PGAItem( $product['ean13']
                    , utf8_decode($product['name'].((isset($product['attributes']) AND !empty($product['attributes'])) ? ' - '.$product['attributes'] : ''))
                    , intval($product['cart_quantity'])
                    , Tools::convertPrice($product['price_wt'], $currency) )
                );
        }

        $this->log->debug("Cookie-Data:\n" . print_r($cookie, true) . "\nParams-Data:" . print_r(Tools::maskPaymentDetails($params), true));

        if ($installment_count > 1) {
            $customer_data = array (
                'OrderId' => intval($cart->id),
                'TransId' => intval($cart->id).'_'.Tools::passwdGen(),
                'Total' => $total,
                'Number' => $cardNumber,
                'Expires' => $cardExpiry,
                'Cvv2Val' => $cardCVV2,
                'Instalment' => $installment_count
            );
        } else {
            $customer_data = array (
                'OrderId' => intval($cart->id),
                'TransId' => intval($cart->id).'_'.Tools::passwdGen(),
                'Total' => $total,
                'Number' => $cardNumber,
                'Expires' => $cardExpiry,
                'Cvv2Val' => $cardCVV2,
            );
        }

        $pgaCart->SetCustomerData($customer_data);

        list($root, $data) = $pgaCart->CheckoutServer2Server(90, false);

        $this->log->debug("Root:\n".print_r($root,true)."\nData:\n".print_r($data,true)."\n $data[$root]['Response']['VALUE']");

        if ($data[$root]['Response']['VALUE'] == 'Approved' AND $data[$root]['OrderId']['VALUE']  == intval($cart->id)) {
            $customer = new Customer(intval($cart->id_customer));

            // To check whether the backorder or not
            $cartProducts = $cart->getProducts();
            $orderState = Configuration::get('PS_OS_PREPARATION');

            foreach($cartProducts as $cartProduct) {
                if ($cartProduct['out_of_stock'] == 1) {
                    $orderState = Configuration::get('PS_OS_BACK_ORDER');
                    break;
                }
            }

            if ($installment_count > 1) {
                $pga->validateOrder(
                    intval($cart->id),
                    $orderState,
                    $total,
                    $pga->displayName,
                    $installment_count,
                    $installmnt_intrst,
                    $each_inst_amount,
                    $data[$root]['Response']['VALUE'],
                    array(),
                    NULL,
                    false,
                    $customer->secure_key
                );
            } else {
                $pga->validateOrder(
                    intval($cart->id),
                    $orderState,
                    $total,
                    $pga->displayName,
                    1,
                    0,
                    $total,
                    $data[$root]['Response']['VALUE'],
                    array(),
                    NULL,
                    false,
                    $customer->secure_key
                );
            }

            $order = new Order(intval($pga->currentOrder));

            Tools::redirectLink(Tools::getHttpHost(true, true) . __PS_BASE_URI__
                . 'order-confirmation.php?key=' . $customer->secure_key . '&id_cart=' . intval($cart->id)
                . '&id_module=' . intval($pga->id) . '&id_order=' . intval($pga->currentOrder));
        } else {
            Tools::redirect('payment_error.php');
        }
    }

    function hookPayment($params) {
        if (! $this->active) {
            return;
        }

        global $smarty;

        $smarty->assign(array(
            'this_path'     => $this->_path,
            'this_path_ssl' => self::getHttpHost(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/'
        ));

        return $this->display(__FILE__, 'pga.tpl');
    }

    function hookPaymentReturn($params) {
        global $smarty, $cookie;

        if (! $this->active) {
            return;
        }

        $order_summary = $params['objOrder'];

        if($order_summary->installment_count > 1){
            $installment_amount = $order_summary->total_paid_real - ($order_summary->total_products_wt - $order_summary->total_discounts + $order_summary->total_shipping) ;
            $smarty->assign('installment_amount', $installment_amount);
        }

        $smarty->assign('products', $order_summary->getProducts());
        $smarty->assign('order', $order_summary);
        $smarty->assign('discounts', $order_summary->getDiscounts());
        $smarty->assign('first_cart_discount_name',strval(Configuration::get('PS_FIRST_CART_DISCOUNT_NAME')));
        $smarty->assign('two_page_checkout' , Configuration::get('TWO_STEP_CHECKOUT'));
        $smarty->assign('HOOK_ETTIKETT' , Module::hookExec('EttikettOrderConfirmation'));
        $smarty->assign('HOOK_TODAY_DISCOUNT' , Module::hookexec('todayDiscount', array(
            'id_customer' => $order_summary->id_customer,
            'id_order' => $order_summary->id
        )));

        $totalHistoryRevenue=Order::getCustomerTotalRevenue($order_summary->id_customer);
        $smarty->assign('totalRealTimeValue', $totalHistoryRevenue);

        return $this->display(__FILE__, 'payment_return.tpl');
    }

    public function hookgetBankResponseOnOrderStatusChange($params) {
        $order = new Order(intval($params['id_order']));

        if(trim($order->module) == "pga") {
            if (! Validate::isLoadedObject($params['newOrderStatus'])) {
                die (Tools::displayError('Some parameters are missing.'));
            }

            $newOrderStatus = $params['newOrderStatus'];

            if ($newOrderStatus->id == _PS_OS_REFUND_ || $newOrderStatus->id == _PS_OS_CANCELED_ || $newOrderStatus->id ==_PS_OS_PARTIALREFUND_ || $newOrderStatus->id == _PS_OS_MANUALREFUND_) {
                if ($order AND !Validate::isLoadedObject($order)) {
                    die (Tools::displayError('Incorrect object Order.'));
                }

                $output = "DATA=<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
                $output .= "<CC5Request>\n";

                if (_BU_ENV_ == 'production') {
                    $output .= "\t<Name>".$this->xmlentities('butigo') ."</Name>\n";
                    $output .= "\t<Password>" .$this->xmlentities('I35iM8nmvc') . "</Password>\n";
                    $output .= "\t<ClientId>".$this->xmlentities('100901569') ."</ClientId>\n";
                } else {
                    $output .= "\t<Name>".$this->xmlentities('AKTESTAPI') ."</Name>\n";
                    $output .= "\t<Password>" .$this->xmlentities('AKBANK01') . "</Password>\n";
                    $output .= "\t<ClientId>".$this->xmlentities('100100000') ."</ClientId>\n";
                }

                if ($newOrderStatus->id == _PS_OS_REFUND_ || $newOrderStatus->id ==_PS_OS_PARTIALREFUND_ || $newOrderStatus->id ==_PS_OS_MANUALREFUND_) {
                    $total = $params['amount'];
                    $output .= "\t<Type>". $this->xmlentities('Credit')."</Type>\n";
                    $output .= "\t<OrderId>". $this->xmlentities(intval($order->id_cart))."</OrderId>\n";
                    $output .= "\t<Total>". $this->xmlentities(floatval($total))."</Total>\n";

                }

                if ($newOrderStatus->id == _PS_OS_CANCELED_) {
                    $output .= "\t<Type>". $this->xmlentities('Void')."</Type>\n";
                    $output .= "\t<OrderId>". $this->xmlentities(intval($order->id_cart))."</OrderId>\n";
                }

                $output .= "\t<Mode>". $this->xmlentities('P')."</Mode>\n";
                $output .= "\t<Currency>". $this->xmlentities('949')."</Currency>\n";
                $output .= "</CC5Request>\n";

                $this->log->debug($output);

                // Set the POST options.
                $session = curl_init();

                if (_BU_ENV_ == 'production') {
                    curl_setopt($session, CURLOPT_URL, "https://www.sanalakpos.com/servlet/cc5ApiServer");
                } else {
                    curl_setopt($session, CURLOPT_URL, "https://testsanalpos.est.com.tr/servlet/cc5ApiServer");
                }

                curl_setopt($session, CURLOPT_SSL_VERIFYHOST, true);
                curl_setopt($session, CURLOPT_POSTFIELDS, $output);
                curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($session, CURLOPT_TIMEOUT, 90);

                $bank_response = '';

                // Do the POST and then close the session
                $response = curl_exec($session);

                if (curl_errno($session)) {
                    $this->log->error(curl_error($session));
                    $this->log->error($response);
                } else {
                    $this->log->debug($response);
                    curl_close($session);
                    $xml = simplexml_load_string($response);
                    $bank_response = $xml->Response.'|'.$xml->OrderId.'|'.$xml->ErrMsg;
                }

                if ($bank_response != '') {
                    return $bank_response;
                } else {
                    return false;
                }
            }
        }
    }

    public static function isOrderCancellable(Order $iOrder) {
        $start = strtotime('today');
        $end = $start + (3600 * 24) - 1;
        $start  = $start + 1;
        $order_date_plus_59 = strtotime($iOrder->date_add) + (59*60);

        return ($order_date_plus_59 >= $start && $order_date_plus_59 <= $end);
    }

    public static function isOrderRefundable(Order $iOrder) {
        return true;
    }
}

?>
