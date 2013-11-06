<?php

if (! defined('_CAN_LOAD_FILES_')) {
    exit;
}

require_once(_PS_INTERFACE_DIR_ . 'IPG.php');
require_once(_PS_INTERFACE_DIR_ . 'IPGBank.php');
require_once(_PS_CLASS_DIR_ . 'PG.php');

class PGY extends PG implements IPG, IPGBank  {
    public $name = 'pgy';
    public $tab = 'payments_gateways';
    public $version = 1.0;
    public $currencies = true;
    public $currencies_mode = 'radio';

    private $mid = '6700955807';
    private $tid = '67247822';
    private $currencyCode = 'YT';
    private $salesType = 'sale';
    private $host = 'https://www.posnet.ykb.com/PosnetWebService/XML';
    private $koiCode;

    function __construct() {
        require_once(_PS_MODULE_DIR_ . $this->name . '/lib/pgycart.php');
        require_once(_PS_MODULE_DIR_ . $this->name . '/lib/pgyitem.php');

        parent::__construct();

        $this->page = basename(__FILE__, '.php');
        $this->displayName = $this->l('YAPIKREDI POS');
        $this->description = $this->l('Payment Gateway API implementation for YapiKredi');
        $this->confirmUninstall = $this->l('Are you sure you want to delete your details ?');

        if (! sizeof(Currency::checkPaymentCurrencies($this->id))) {
            $this->warning = $this->l('No currency set for this module');
        }

        // set test credentials
        if (_BU_ENV_ != 'production') {
            $this->log->info('Test environment variables are being set!');

            $this->host = 'http://setmpos.ykb.com/PosnetWebService/XML';
            $this->mid = '6700000067';
            $this->tid = '67100020';
        }
   }

    function install() {
        if (! parent::install() OR
            ! $this->registerHook('payment') OR
            ! $this->registerHook('validation') OR
            ! $this->registerHook('paymentReturn') OR
            ! $this->registerHook('getBankResponseOnOrderStatusChange') OR
            ! $this->registerHook('paymentError')) {
            return false;
        }

        return true;
    }

    function uninstall() {
        return parent::uninstall();
    }

    private function getCart($params) {
        global $cookie, $cart;

        $cardNumber = $params['card_number'];
        $cardExpiry = $params['card_expiry'];
        $cardCVV2 = $params['card_cvv'];
        $installment = $params['instalment_count'];
        $installmnt_intrst = $params['instalment_interest'];
        $each_inst_amount = $params['each_instalment'];
        $total = $params['total']; // Final total with instalments

        if ($installment < 0) {
            $total = floatval(number_format($cart->getOrderTotal(true, 3), 2, '.', ''));
        }

        $currency = $this->getCurrency();
        $total = $total * 100;

        $this->log->debug("Installments: $installment \n Intallment-interest: $installmnt_intrst \n "
            . "Each Install: $each_inst_amount \n Final-Total: $total\n");

        $pgyCart = new PGYCart($this->mid, $this->tid, $this->host, '', '', $this->currencyCode, $this->koiCode);

        foreach ($cart->getProducts() as $product) {
            $pgyCart->AddItem(
                new PGYItem(
                    $product['ean13'],
                    utf8_decode($product['name'] . (
                        (isset($product['attributes']) AND ! empty($product['attributes']))
                            ? ' - ' . $product['attributes'] : '')),
                    intval($product['cart_quantity']),
                    Tools::convertPrice($product['price_wt'], $currency)
                )
            );
        }

        // completing order id to 24 digits as PGY requires it
        $orderId = intval($cart->id);
        $cart_add_date = str_replace('-', "", substr($cart->date_add, 0, 10));
        $orderId = $orderId . $cart_add_date;
        $length = strlen($orderId);
        $length = 24 - $length;
        $temp = '%0' . $length . 's';
        $format = $orderId . $temp;
        $orderId = sprintf($format, '');

        $customer_data = array(
            'OrderId' => $orderId,
            'TransId' => intval($cart->id) . '_' . Tools::passwdGen(),
            'Total' => $total,
            'Number' => $cardNumber,
            'Expires' => $cardExpiry,
            'Cvv2Val' => $cardCVV2,
            'Instalment' => $installment
        );

        $pgyCart->SetCustomerData($customer_data);

        return $pgyCart;
    }

    public function setKOICode($koiCode) {
        $this->log->info('Setting KOICode to: ' . $koiCode);
        $this->koiCode = $koiCode;
    }

    function hookValidation($params) {
        global $cart;

        $pgyCart = $this->getCart($params);

        $total = $params['total'];
        $installment_count = $params['instalment_count'];
        $installmnt_intrst = $params['instalment_interest'];
        $each_inst_amount = $params['each_instalment'];

        $this->log->debug('[' . $id_cart . '] params: ' . print_r(Tools::maskPaymentDetails($params), true));

        $bankResponse = $pgyCart->CheckoutServer2Server(90, false);

        list($root, $data) = $bankResponse;

        if ($this->isBankResponseApprovedOnPayment($bankResponse)) {
            $this->log->debug('[' . $id_cart . '] Querying for transaction successfull!');

            $this->saveOrderDetails($cart->id, $data[$root]['hostlogkey']['VALUE'], $data[$root]['authCode']['VALUE']);
            $customer = new Customer(intval($cart->id_customer));

			/*To check whether the backorder or not*/
			$cartProducts = $cart->getProducts();
			$orderState = Configuration::get('PS_OS_PREPARATION');
			foreach($cartProducts as $cartProduct){
				if($cartProduct['out_of_stock'] == 1){
					$orderState = Configuration::get('PS_OS_BACK_ORDER');
					break;
				}
			}

            if ($installment_count > 1) {
                $this->log->info('[' . $id_cart . '] More than 1 installment is selected!');

                if ($this->koiCode === 2) {
                    /**
                     * workaround to show 8 installments for
                     * 3+5 Installment & 3 Months Deferment
                     */
                    $installment_count = 8;
                }

                $this->validateOrder(
                    intval($cart->id),
                    $orderState,
                    $total,
                    $this->displayName,
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
                $this->log->info('[' . $id_cart . '] No installment is selected!');

                $this->validateOrder(
                    intval($cart->id),
                    $orderState,
                    $total,
                    $this->displayName,
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

            $order = new Order(intval($this->currentOrder));

            Tools::redirectLink(Tools::getHttpHost(true, true) . __PS_BASE_URI__
                . 'order-confirmation.php?key=' . $customer->secure_key . '&id_cart='
                . intval($cart->id) . '&id_module=' . intval($this->id) . '&id_order='
                . intval($this->currentOrder));
        } else {
            $this->log->error('[' . $cart->id . '] Bank response validation has failed due to error: ' . print_r($bankResponse, true));

            Tools::redirect('payment_error.php');
        }
    }

    function hookPayment($params) {
        global $smarty;

        if (! $this->active) {
            return;
        }

        $smarty->assign(array(
            'this_path' => $this->_path,
            'this_path_ssl' => self::getHttpHost(true, true) . __PS_BASE_URI__ . 'modules/' . $this->name . '/'
        ));

        return $this->display(__FILE__, 'pgy.tpl');
    }

    function hookPaymentReturn($params) {
        global $smarty, $cookie;

        if (! $this->active) {
            return;
        }

        $order_summary = $params['objOrder'];

        if ($order_summary->installment_count > 1) {
            $installment_amount = $order_summary->total_paid_real -
                ($order_summary->total_products_wt - $order_summary->total_discounts + $order_summary->total_shipping);

            $smarty->assign('installment_amount', $installment_amount);
        }

        $smarty->assign('products', $order_summary->getProducts());
        $smarty->assign('order', $order_summary);
        $smarty->assign('discounts', $order_summary->getDiscounts());
        $smarty->assign('first_cart_discount_name', strval(Configuration::get('PS_FIRST_CART_DISCOUNT_NAME')));
        $smarty->assign('two_page_checkout', Configuration::get('TWO_STEP_CHECKOUT'));
        $smarty->assign('HOOK_ETTIKETT', Module::hookExec('EttikettOrderConfirmation'));
        $smarty->assign('HOOK_TODAY_DISCOUNT' , Module::hookexec('todayDiscount', array('id_customer' => $order_summary->id_customer, 'id_order' => $order_summary->id)));

        $totalHistoryRevenue=Order::getCustomerTotalRevenue($order_summary->id_customer);
        $smarty->assign('totalRealTimeValue', $totalHistoryRevenue);

        return $this->display(__FILE__, 'payment_return.tpl');
    }

    public function hookgetBankResponseOnOrderStatusChange($params) {
        $order = new Order(intval($params['id_order']));
        $res = $this->getOrderDetails($order->id);
        $host_log_key = $res['host_log_key'];
        $auth_code = $res['auth_code'];

        if (trim($order->module) == "pgy") {
            if (! Validate::isLoadedObject($params['newOrderStatus'])) {
                die (Tools::displayError('Some parameters are missing.'));
            }

            $newOrderStatus = $params['newOrderStatus'];

            if ($newOrderStatus->id == _PS_OS_REFUND_ OR
                $newOrderStatus->id == _PS_OS_CANCELED_ OR
                $newOrderStatus->id ==_PS_OS_PARTIALREFUND_ OR
                $newOrderStatus->id == _PS_OS_MANUALREFUND_) {

                if ($order AND ! Validate::isLoadedObject($order)) {
                    die (Tools::displayError('Incorrect object Order.'));
                }

                $output = "xmldata=<?xml version=\"1.0\" encoding=\"ISO-8859-9\"?>\n";
                $output .= "<posnetRequest>\n";
                $output .= "\t<mid>" . $this->xmlentities($this->mid) . "</mid>\n";
                $output .= "\t<tid>" . $this->xmlentities($this->tid) . "</tid>\n";
                $output .= "\t<username></username>\n";
                $output .= "\t<password></password>\n";

                if ($newOrderStatus->id == _PS_OS_REFUND_ OR
                    $newOrderStatus->id == _PS_OS_PARTIALREFUND_ OR
                    $newOrderStatus->id ==_PS_OS_MANUALREFUND_) {

                    $output .= "\t<return>\n";
                    $output .= "\t\t<hostLogKey>" . $this->xmlentities($host_log_key) . "</hostLogKey>\n";
                    $total = $params['amount'] * 100;

                    $output .= "\t\t<amount>" . $this->xmlentities($total) . "</amount>\n";
                    $output .= "\t\t<currencyCode>" . $this->xmlentities($this->currencyCode) . "</currencyCode>\n";
                    $output .= "\t</return>\n";
                }

                if ($newOrderStatus->id == _PS_OS_CANCELED_) {
                    $output .= "\t<reverse>\n";
                    $output .= "\t\t<transaction>" . $this->xmlentities($this->salesType) . "</transaction>\n";
                    $output .= "\t\t<hostLogKey>" . $this->xmlentities($host_log_key) . "</hostLogKey>\n";
                    $output .= "\t\t<authCode>" . $this->xmlentities($auth_code) . "</authCode>\n";
                    $output .= "\t</reverse>\n";
                }

                $output .= "</posnetRequest>\n";

                $this->log->debug("The request is being sent with the following data:\n\n" . $output);

                // Set the POST options.
                $session = curl_init();
                curl_setopt($session, CURLOPT_URL, $this->host);
                curl_setopt($session, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($session, CURLOPT_POST, true);
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
                    $this->log->debug("Retrieved response:\n\n" . $response);
                    curl_close($session);
                    $xml = simplexml_load_string($response);
                    $bank_response = $xml->approved . '|' . " " . '|' . $xml->respText;
                }

                if ($bank_response != '') {
                    return $bank_response;
                } else {
                    return false;
                }
            }
        }
    }

    public function getOrderDetails($order_id) {
        $query = 'SELECT ybk.`host_log_key`, ybk.`auth_code`
            FROM `' . _DB_PREFIX_ . 'order_details_ybk` ybk
            LEFT JOIN `' . _DB_PREFIX_ . 'orders` o ON (o.id_cart = ybk.id_cart)
            WHERE o.`id_order` = ' . (int)$order_id;

        $res = Db::getInstance()->getRow($query);

        return $res;
    }

    public function saveOrderDetails($cart_id, $host_log_key, $auth_code) {
        $query = 'INSERT INTO `' . _DB_PREFIX_ . 'order_details_ybk`
            (`id_cart`, `host_log_key`, `auth_code`)
            VALUES ("' . $cart_id . '", "' . $host_log_key . '", "' . $auth_code . '")';

        Db::getInstance()->Execute($query);

        return;
    }

     public function isBankResponseApprovedOnPayment($response) {
        list($root, $data) = $response;
        return $data[$root]['approved']['VALUE'] == 1;
    }

    public static function isOrderCancellable(Order $iOrder) {
        $start = strtotime('today'); /*Represents 00:00:00, i.e mid-night 12 '0 clock */
        /* End of days */
        $end1 = $start + (3600 * 22); /*Represents 22:00:00, i.e Today night 10'0 clock */
        $end2 = $start + (3600 * 24)-(1*60); /*Represents 23:59:00, i.e Today night 11:59 clock */
        $start  = $start-(1*60); /*Represents 23:59:00, i.e Previous day night 11:59 clock */

        $order_date = strtotime($iOrder->date_add);

        return (($order_date > $start && $order_date < $end1) || ($order_date > $end1 && $order_date < $end2));
    }

    public static function isOrderRefundable(Order $iOrder) {
        $order_date = strtotime($iOrder->date_add);
        $end = $order_date + (3600 * 24)-(1*60); // (3600 * 24)-(1*60) => +23:59 minute

        // PGY not accept refund which have installment on same day.
        if ($iOrder->installment_count > 0) {
            return time() > $end;
        }

        // If order not have installment, refundable.
        return true;
    }

}

?>
