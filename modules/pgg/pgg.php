<?php

if (! defined('_CAN_LOAD_FILES_')) {
    exit;
}

require_once(_PS_INTERFACE_DIR_ . 'IPG.php');
require_once(_PS_INTERFACE_DIR_ . 'IPGBank.php');
require_once(_PS_CLASS_DIR_ . 'PG.php');

class PGG extends PG implements IPG, IPGBank {
    public $name = 'pgg';
    public $tab = 'payments_gateways';
    public $version = 1.0;
    public $currencies = true;
    public $currencies_mode = 'radio';

    private $apiVer = 'v0.01';
    private $mid = '9259353';
    private $tid = '10012307';
    private $salesUserId = 'PROVAUT';
    private $salesUserPassword = 'F0itxvKQ7E';
    private $voidRefundUserId = 'PROVRFN';
    private $voidRefundUserPassword = 't90FT4Y6oS';
    private $currencyCode = '949'; // TRL: 949, USD: 840, EURO: 978, GBP: 826, JPY: 392
    private $salesType = 'sales';
    private $host = 'https://sanalposprov.garanti.com.tr/VPServlet';
    private $mode = 'PROD';

    function __construct() {
        require_once(_PS_MODULE_DIR_ . $this->name . '/lib/pggcart.php');
        require_once(_PS_MODULE_DIR_ . $this->name . '/lib/pggitem.php');

        parent::__construct();

        $this->page = basename(__FILE__, '.php');
        $this->displayName = $this->l('GARANTI POS');
        $this->description = $this->l('Payment Gateway API implementation for Garantibank');
        $this->confirmUninstall = $this->l('Are you sure you want to delete your details ?');

        if (! sizeof(Currency::checkPaymentCurrencies($this->id))) {
            $this->warning = $this->l('No currency set for this module');
        }

        // set test credentials
        if (_BU_ENV_ != 'production') {
            $this->log->info('Test environment variables are being set!');

            $this->mode = 'TEST';
            $this->host = 'https://sanalposprovtest.garanti.com.tr/VPServlet';
            $this->mid = '600218';
            $this->tid = '30690116';
            $this->salesUserPassword = '123qweASD';
            $this->voidRefundUserPassword = '123qweASD';
        }
    }

    function install() {
        if (! parent::install()
            OR ! $this->registerHook('payment')
            OR ! $this->registerHook('validation')
            OR ! $this->registerHook('paymentReturn')
            OR ! $this->registerHook('paymentError')
            OR ! $this->registerHook('getBankResponseOnOrderStatusChange')
            )
            return false;

        return true;
    }

    function uninstall() {
        return (parent::uninstall());
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

        $pggCart = new PGGCart($this->mode, $this->apiVer, $this->tid, $this->salesUserId, $this->mid,
            $this->salesUserPassword, $this->currencyCode, $this->salesType, $this->host);

        foreach ($cart->getProducts() as $product) {
            $pggCart->AddItem(
                new PGGItem(
                    $product['ean13'],
                    utf8_decode($product['name'] . (
                        (isset($product['attributes']) AND ! empty($product['attributes']))
                            ? ' - ' . $product['attributes'] : '')),
                    intval($product['cart_quantity']),
                    Tools::convertPrice($product['price_wt'], $currency)
                )
            );
        }

        $customer_data = array(
            'email' => $cookie->email,
            'ip_address' => Tools::getRemoteAddr(),
            'OrderId' => intval($cart->id),
            'GroupId' => null,
            'TransId' => intval($cart->id) . '_' . Tools::passwdGen(),
            'Total' => $total,
            'Number' => $cardNumber,
            'Expires' => $cardExpiry,
            'Cvv2Val' => $cardCVV2,
            'Instalment' => null
        );

        if ($installment > 1) {
            $customer_data['Instalment'] = $installment;
        }

        $pggCart->SetCustomerData($customer_data);

        return $pggCart;
    }

    function hookValidation($params) {
        global $cart;

        $pggCart = $this->getCart($params);

        $total = $params['total'];
        $installment_count = $params['instalment_count'];
        $installmnt_intrst = $params['instalment_interest'];
        $each_inst_amount = $params['each_instalment'];

        $this->log->debug('params: ' . print_r(Tools::maskPaymentDetails($params), true));

        $bankResponse = $this->getBandResponseForPayment($pggCart);

        if ($this->isBankResponseApprovedOnPayment($bankResponse)) {
            $this->log->info('Querying for transaction successfull!');

            $customer = new Customer(intval($cart->id_customer));
			/*To check whether the backorder or not*/
			$cartProducts = $cart->getProducts();
			$orderState = Configuration::get('PS_OS_PREPARATION');

			foreach($cartProducts as $cartProduct){
				if($cartProduct['out_of_stock'] == 1) {
					$orderState = Configuration::get('PS_OS_BACK_ORDER');
					break;
				}
			}

            if ($installment_count > 1) {
                $this->log->debug('More than 1 installment is selected!');

                $this->validateOrder(
                    intval($cart->id),
                    $orderState,
                    $total,
                    $this->displayName,
                    $installment_count,
                    $installmnt_intrst,
                    $each_inst_amount,
                    null,
                    array(),
                    null,
                    false,
                    $customer->secure_key
                );
            } else {
                $this->log->debug('No installment is selected!');

                $this->validateOrder(
                    intval($cart->id),
                    $orderState,
                    $total,
                    $this->displayName,
                    1,
                    0,
                    $total,
                    null,
                    array(),
                    null,
                    false,
                    $customer->secure_key
                );
            }

            // $order = new Order(intval($this->currentOrder));

            $this->log->info('Success!');

            Tools::redirectLink(Tools::getHttpHost(true, true) . __PS_BASE_URI__
                . 'order-confirmation.php?key=' . $customer->secure_key . '&id_cart='
                . intval($cart->id) . '&id_module=' . intval($this->id) . '&id_order='
                . intval($this->currentOrder));
        } else {
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

        return $this->display(__FILE__, 'pgg.tpl');
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
        $smarty->assign('HOOK_ETTIKETT' , Module::hookExec('EttikettOrderConfirmation'));
        $smarty->assign('HOOK_TODAY_DISCOUNT' , Module::hookexec('todayDiscount', array('id_customer' => $order_summary->id_customer, 'id_order' => $order_summary->id)));

        $totalHistoryRevenue=Order::getCustomerTotalRevenue($order_summary->id_customer);
        $smarty->assign('totalRealTimeValue', $totalHistoryRevenue);

        return $this->display(__FILE__, 'payment_return.tpl');
    }

    public function hookgetBankResponseOnOrderStatusChange($params) {
        $order = new Order(intval($params['id_order']));

        if (trim($order->module) == "pgg") {
            if (! Validate::isLoadedObject($params['newOrderStatus'])) {
                die(Tools::displayError('Some parameters are missing.'));
            }

            $newOrderStatus = $params['newOrderStatus'];

            if ($newOrderStatus->id == _PS_OS_REFUND_ || $newOrderStatus->id == _PS_OS_CANCELED_ || $newOrderStatus->id ==_PS_OS_PARTIALREFUND_ || $newOrderStatus->id == _PS_OS_MANUALREFUND_) {
                if ($order AND ! Validate::isLoadedObject($order)) {
                    die(Tools::displayError('Incorrect object Order.'));
                }

                $customer_name = '';
                $ip_address = Tools::getRemoteAddr(); // real ip address
                $email_address = '';
                $order_id = $order->id_cart; // unique order id generated by us
                $group_id = ''; // only used when orders are grouped (batch order)
                $installment_cnt = $order->installment_count; // number of instalments (default: '')
                $cc_number = ''; // real card number here
                $cc_expireDate = ''; // real concatenated expire date in monthyear format (ex: 0114)
                $cc_cvv2 = ''; // real CVV2 number

                //$amount = $order->total_paid_real * 100; // 1 TL
                $amount = $params['amount'] * 100;

                $type = 'void';
                $currency_code = $this->currencyCode;
                $card_holder_present_code = '0'; // 0 for normal, 13 for 3d secure transactions
                $moto_ind = 'N'; // Y for mailorder
                $originalRetrefNum = ''; // reference number of transaction retrieved from sales response

                if ($newOrderStatus->id == _PS_OS_REFUND_ || $newOrderStatus->id == _PS_OS_PARTIALREFUND_ || $newOrderStatus->id == _PS_OS_MANUALREFUND_) {
                    $type = 'refund';
                }

                $this->log->info('Issuing a ' . $type . ' request for orderId: ' . $order_id);

                $terminal_id_padded = sprintf('0%s', $this->tid);
                $security_data = strtoupper(sha1($this->voidRefundUserPassword . $terminal_id_padded));
                $hash_data = strtoupper(sha1($order_id . $this->tid . $cc_number . $amount . $security_data));

                $output = "data=<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
                $output .= "<GVPSRequest>\n";

                $output .= "\t<Mode>" . $this->xmlentities($this->mode) . "</Mode>\n";
                $output .= "\t<Version>" . $this->xmlentities($this->apiVer) . "</Version>\n";

                $output .= "\t<Terminal>\n";
                $output .= "\t\t<ProvUserID>" . $this->xmlentities($this->voidRefundUserId) . "</ProvUserID>\n";
                $output .= "\t\t<HashData>" . $this->xmlentities($hash_data) . "</HashData>\n";
                $output .= "\t\t<UserID>" . $this->xmlentities($this->voidRefundUserId) . "</UserID>\n";
                $output .= "\t\t<ID>" . $this->xmlentities($this->tid) . "</ID>\n";
                $output .= "\t\t<MerchantID>" . $this->xmlentities($this->mid) . "</MerchantID>\n";
                $output .= "\t</Terminal>\n";

                $output .= "\t<Customer>\n";
                $output .= "\t\t<IPAddress>" . $this->xmlentities($ip_address) . "</IPAddress>\n";
                $output .= "\t\t<EmailAddress>" . $this->xmlentities($email_address) . "</EmailAddress>\n";
                $output .= "\t</Customer>\n";

                $output .= "\t<Card>\n";
                $output .= "\t\t<Number>" . $this->xmlentities($cc_number) . "</Number>\n";
                $output .= "\t\t<ExpireDate>" . $this->xmlentities($cc_expireDate) . "</ExpireDate>\n";
                $output .= "\t\t<CVV2>" . $this->xmlentities($cc_cvv2) . "</CVV2>\n";
                $output .= "\t</Card>\n";

                $output .= "\t<Order>\n";
                $output .= "\t\t<OrderID>" . $this->xmlentities($order_id) . "</OrderID>\n";
                $output .= "\t\t<GroupID>" . $this->xmlentities($group_id) . "</GroupID>\n";
                $output .= "\t</Order>\n";

                $output .= "\t<Transaction>\n";
                $output .= "\t\t<Type>". $this->xmlentities($type)."</Type>\n";
                $output .= "\t\t<Amount>". $this->xmlentities(floatval($amount))."</Amount>\n";
                $output .= "\t\t<InstallmentCnt>" . $this->xmlentities($installment_cnt) . "</InstallmentCnt>\n";
                $output .= "\t\t<CurrencyCode>" . $this->xmlentities($currency_code) . "</CurrencyCode>\n";
                $output .= "\t\t<CardholderPresentCode>" . $this->xmlentities($card_holder_present_code) . "</CardholderPresentCode>\n";
                $output .= "\t\t<MotoInd>" . $this->xmlentities($moto_ind) . "</MotoInd>\n";
                $output .= "\t\t<OriginalRetrefNum>" . $this->xmlentities($originalRetrefNum) . "</OriginalRetrefNum>\n";
                $output .= "\t</Transaction>\n";
                $output .= "</GVPSRequest>\n";

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
                    $bank_response = $xml->Transaction->Response->Message . '|' . $xml->Order->OrderID . '|' . $xml->Transaction->Response->ErrorMsg;
                }

                if ($bank_response != '') {
                    return $bank_response;
                } else {
                    return false;
                }
            }
        }
    }

    public function isBankResponseApprovedOnPayment($response) {
        global $cart;

        list($root, $data) = $response;
        return ($data[$root]['Transaction']['Response']['Message']['VALUE'] == 'Approved'
                AND $data[$root]['Order']['OrderID']['VALUE']  == intval($cart->id));
    }

    public static function isOrderCancellable(Order $iOrder) {

        /*Garantibank have two End of days 20:00:00 and 23:00:00, That means the orders placed after 23:00:00 should be
         * canceled untill the next day 20:00:00 after that it should be refunded, suppose if the order is placed after 20:00:00,
         * that should be canceled untill the 23:00:00 of the same day and refunded after 23:00:00 of the same day.
         */
        $start = strtotime('today'); /*Represents 00:00:00, i.e mid-night 12 '0 clock */
        /* End of days */
        $end1 = $start + (3600 * 20); /*Represents 20:00:00, i.e night 8'0 clock */
        $end2 = $start + (3600 * 23); /*Represents 23:00:00, i.e night 11'0 clock */
        $start  = ($start - 3600); /*Represents 23:00:00, i.e Previous day night 11'0 clock */
        $order_date = strtotime($iOrder->date_add);

        if(($order_date > $start && $order_date < $end1) || ($order_date > $end1 && $order_date < $end2)) {
            return true;
        }

        return false;
    }

    public static function isOrderRefundable(Order $iOrder) {
        return true;
    }

}

?>
