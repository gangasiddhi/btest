<?php

if (!defined('_CAN_LOAD_FILES_')) {
    exit;
}

/**
 * Turkcell Wallet (aka Turkcell Cüzdan) is a payment gateway
 * proxy just like PayPal. Turkcell is the biggest mobile operator
 * in Turkey and they invest this wallet technology all over Turkey.
 *
 * Currently it's in beta hence not in production. In order to test
 * we should use following hosts:
 *
 * 194.29.209.226 sanalposprovtest.garanti.com.tr
 *
 * Test Information:
 *
 * MID: 3424113
 * TID: 30690133
 * Provision User: PROVAUT
 * Provision Password: 123qweASD
 * Securekey: 12345678
 *
 * Supported Cards For Now:
 *  - GarantiParam (prepaid)
 *  - Bonus (credit)
 *  - Miles&Smiles (credit)
 *  - Paracard (credit)
 *  - CepT Paracard (prepaid)
 *
 * Installments options will be same as other gateways.
 *
 * Test Phone 1: 5309400351
 * Test Phone 2: 5309400362
 *
 * After launch, above information will be replaced with our Garanti
 * production values.
 */

require_once(_PS_INTERFACE_DIR_ . 'IPG.php');
require_once(_PS_INTERFACE_DIR_ . 'IPGBank.php');
require_once(_PS_CLASS_DIR_ . 'PG.php');

class PGTW extends PG  implements IPG, IPGBank {
    const SUCCESS = '00';
    const FAILURE = '01';
    const RETRY = '04';

    public $name = 'pgtw';
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
    private $salesType = 'walletsales';
    private $inqueryType = 'walletorderinq';
    private $host = 'https://sanalposprov.garanti.com.tr/VPServlet';
    private $mode = 'PROD';

    function __construct() {
        require_once(_PS_MODULE_DIR_ . $this->name . '/lib/pgtwcart.php');
        require_once(_PS_MODULE_DIR_ . $this->name . '/lib/pgtwitem.php');

        parent::__construct();

        $this->page = basename(__FILE__, '.php');
        $this->displayName = $this->l('Payment Gateway Turkcell Wallet');
        $this->description = $this->l('Payment Gateway API implementation for Turkcell Wallet');
        $this->confirmUninstall = $this->l('Are you sure you want to delete your details ?');

        if (! sizeof(Currency::checkPaymentCurrencies($this->id))) {
            $this->warning = $this->l('No currency set for this module');
        }

        // set test credentials
        if (_BU_ENV_ != 'production') {
            $this->log->info('Test environment variables are being set!');

            $this->mode = 'TEST';
            $this->host = 'https://sanalposprovtest.garanti.com.tr/VPServlet';
            $this->mid = '3424113';
            $this->tid = '30690133';
            $this->salesUserPassword = '123qweASD';
            $this->voidRefundUserPassword = '123qweASD';
        }
    }

    function install() {
        if (! parent::install() OR
            ! $this->registerHook('payment') OR
            ! $this->registerHook('validation') OR
            ! $this->registerHook('paymentReturn') OR
            ! $this->registerHook('getBankResponseOnOrderStatusChange')) {

            return false;
        }

        return true;
    }

    function uninstall() {
        return (parent::uninstall());
    }

    private function getCart($params) {
        global $cookie, $cart;

        $cellPhone = $params['cellPhone'];
        $cardChoice = $params['cardChoice'];
        $installment = $params['installment']; // number of installments
        $installmnt_intrst = 0.00;
        $bonus = $params['bonus'];
        $final_total = $params['finalTotal']; // amount with interest depending on the installment type
        $total = floatval(number_format($final_total, 2, '.', ''));
        $type = isset($params['type']) ? $params['type'] : $this->salesType;

        if ($installment < 0) {
            $total = floatval(number_format($cart->getOrderTotal(true, 3), 2, '.', ''));
        }

        $currency = $this->getCurrency();
        $total = $final_total * 100;

        if ($installment > 1) {
            if ($installment == 6) {
                $installmnt_intrst = Configuration::get('PS_SIX_INSTALL_INTEREST_RATE');
            } else if ($installment == 12) {
                $installmnt_intrst = Configuration::get('PS_TWELVE_INSTALL_INTEREST_RATE');
            }

            $each_inst_amount = $total / $installment; // amount to be paid in each installment
        }

        $this->log->debug("Installments: $installment \n Intallment-interest: $installmnt_intrst \n "
            . "Each Install: $each_inst_amount \n Final-Total: $final_total\n");

        $pgtwCart = new PGTWCart($this->mode, $this->apiVer, $this->tid, $this->salesUserId, $this->mid,
            $this->salesUserPassword, $this->currencyCode, $type, $this->host);

        foreach ($cart->getProducts() as $product) {
            $pgtwCart->AddItem(
                new PGTWItem(
                    $product['ean13'],
                    utf8_decode($product['name'] . (
                        (isset($product['attributes']) AND !empty($product['attributes'])) ? ' - ' . $product['attributes'] : '')
                    ),
                    intval($product['cart_quantity']),
                    Tools::convertPrice($product['price_wt'], $currency)
                )
            );
        }

        /* To make the repeated requests, if any of the request(1st or 2nd request) fails */
        if ($params['type'] != $this->inqueryType) {
            $this->log->info('First request is issued, setting orderId..');

            if (! isset($_COOKIE['tcwad'])) {
                setcookie('tcwad', '1000', null);
                $orderId = intval($cart->id) . 'b' . '1000';
                $orderId = $orderId;

                $this->log->info('tcwad cookie is set for the first time, orderId set to: ' . $orderId);
            } else {
                $turk_append_data = $_COOKIE['tcwad'];
                $turk_append_data++;
                setcookie('tcwad', $turk_append_data, null);
                $orderId = intval($cart->id) . 'b' . $turk_append_data;
                $orderId = $orderId;

                $this->log->info('tcwad cookie is set with new orderId: ' . $orderId);
            }
        } else {
            $turk_append_data = $_COOKIE['tcwad'];
            $orderId = intval($cart->id) . 'b' . $turk_append_data;
            $orderId = $orderId;

            $this->log->info('Second request in action! Setting orderId (via cookie): ' . $orderId);
        }

        $customer_data = array(
            'email' => $cookie->email,
            'ip_address' => Tools::getRemoteAddr(),
            'OrderId' => $orderId,
            'TransId' => $orderId . '_' . Tools::passwdGen(),
            'Total' => $total,
            'GSMNumber' => $cellPhone
        );

        if ($installment > 1) {
            $customer_data['Instalment'] = $installment;
        }

        $pgtwCart->SetCustomerData($customer_data);

        return $pgtwCart;
    }

    function hookValidation($params) {
        global $cart;

        $pgtwCart = $this->getCart($params);
        $result = array();

        // $this->log->LogResponse('Root: ' . $root . ', data: ' . print_r($data, true));

        /**
         * This is the first request that takes place in the Turkcell Wallet Scenario which is
         * used to initiate the mobile approval process for the enduser side. Since we show a
         * "waiting for approval" kind of dialog box during this process, it needs to be
         * implemented as an AJAX call. Hence returning TRUE or FALSE to let JS side know about
         * the result of the first request to continue with the verification of user approval.
         */

        $bankResponse = $this->getBandResponseForPayment($pgtwCart);

        if ($this->isBankResponseApprovedOnPayment($bankResponse)) {
            $result['cellPhone'] = $params['cellPhone'];
            $result['cardChoice'] = $params['cardChoice'];
            $result['installment'] = $params['installment']; // number of installments
            $result['bonus'] = $params['bonus'];
            $result['finalTotal'] = $params['finalTotal'];
            $result['transStatus'] = 'success';
        } else {
            $result['transStatus'] = 'error';
        }

        return $result;
    }

    function hookPayment($params) {
        global $smarty;

        if (!$this->active)
            return;

        $smarty->assign(array(
            'this_path' => $this->_path,
            'this_path_ssl' => self::getHttpHost(true, true) . __PS_BASE_URI__ . 'modules/' . $this->name . '/'
        ));

        return $this->display(__FILE__, 'pgtw.tpl');
    }

    function hookPaymentReturn($params) {
        global $smarty, $cookie;

        if (!$this->active) {
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
        $smarty->assign('first_cart_discount_name', strval(Configuration::get('PS_FIRST_CART_DISCOUNT_NAME')));
        $smarty->assign('two_page_checkout' , Configuration::get('TWO_STEP_CHECKOUT'));
        $smarty->assign('HOOK_ETTIKETT' , Module::hookExec('EttikettOrderConfirmation'));
        $smarty->assign('HOOK_TODAY_DISCOUNT' , Module::hookexec('todayDiscount', array('id_customer' => $order_summary->id_customer, 'id_order' => $order_summary->id)));

        $totalHistoryRevenue=Order::getCustomerTotalRevenue($order_summary->id_customer);
        $smarty->assign('totalRealTimeValue', $totalHistoryRevenue);

        return $this->display(__FILE__, 'payment_return.tpl');
    }

    protected function getXML($orderId, $hash_data, $groupId, $installment, $ccNumber, $ccExpireDate, $ccCvv2, $amount, $type, $email, $cardHolderPresentCode, $motoInd, $originalRetrefNum, $gsmNumber, $walletId) {

        $ipAddress = Tools::getRemoteAddr(); // real ip address

        $tmp = "data=<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $tmp .= "<GVPSRequest>\n";
        $tmp .= "\t<Mode>" . $this->xmlentities($this->mode) . "</Mode>\n";
        $tmp .= "\t<Version>" . $this->xmlentities($this->apiVer) . "</Version>\n";
        $tmp .= "\t<Terminal>\n";
        $tmp .= "\t\t<ProvUserID>" . $this->xmlentities($this->voidRefundUserId) . "</ProvUserID>\n";
        $tmp .= "\t\t<HashData>" . $this->xmlentities($hash_data) . "</HashData>\n";
        $tmp .= "\t\t<UserID>" . $this->xmlentities($this->voidRefundUserId) . "</UserID>\n";
        $tmp .= "\t\t<ID>" . $this->xmlentities($this->tid) . "</ID>\n";
        $tmp .= "\t\t<MerchantID>" . $this->xmlentities($this->mid) . "</MerchantID>\n";
        $tmp .= "\t</Terminal>\n";

        $tmp .= "\t<Customer>\n";
        $tmp .= "\t\t<IPAddress>" . $this->xmlentities($ipAddress) . "</IPAddress>\n";
        $tmp .= "\t\t<EmailAddress>" . $this->xmlentities($email) . "</EmailAddress>\n";
        $tmp .= "\t</Customer>\n";

        $tmp .= "\t<Card>\n";
        $tmp .= "\t\t<Number>" . $this->xmlentities($ccNumber) . "</Number>\n";
        $tmp .= "\t\t<ExpireDate>" . $this->xmlentities($ccExpireDate) . "</ExpireDate>\n";
        $tmp .= "\t\t<CVV2>" . $this->xmlentities($ccCvv2) . "</CVV2>\n";
        $tmp .= "\t</Card>\n";

        $tmp .= "\t<Order>\n";
        $tmp .= "\t\t<OrderID>" . $this->xmlentities($orderId) . "</OrderID>\n";
        $tmp .= "\t\t<GroupID>" . $this->xmlentities($groupId) . "</GroupID>\n";
        $tmp .= "\t</Order>\n";

        $tmp .= "\t<Transaction>\n";
        $tmp .= "\t\t<Type>" . $this->xmlentities($type) . "</Type>\n";
        $tmp .= "\t\t<Amount>" . $this->xmlentities(floatval($amount)) . "</Amount>\n";
        $tmp .= "\t\t<InstallmentCnt>" . $this->xmlentities($installment) . "</InstallmentCnt>\n";
        $tmp .= "\t\t<CurrencyCode>" . $this->xmlentities($this->currencyCode) . "</CurrencyCode>\n";
        $tmp .= "\t\t<CardholderPresentCode>" . $this->xmlentities($cardHolderPresentCode) . "</CardholderPresentCode>\n";
        $tmp .= "\t\t<MotoInd>" . $this->xmlentities($motoInd) . "</MotoInd>\n";
        $tmp .= "\t\t<OriginalRetrefNum>" . $this->xmlentities($originalRetrefNum) . "</OriginalRetrefNum>\n";
        $tmp .= "\t</Transaction>\n";
        $tmp .= "</GVPSRequest>\n";

        return $tmp;
    }

    public function hookGetBankResponseOnOrderStatusChange($params) {
        $order = new Order(intval($params['id_order']));

        if (trim($order->module) == "pgtw") {
            if (!Validate::isLoadedObject($params['newOrderStatus'])) {
                die(Tools::displayError('Some parameters are missing.'));
            }

            $newOrderStatus = $params['newOrderStatus'];

            if ($newOrderStatus->id == _PS_OS_REFUND_ || $newOrderStatus->id == _PS_OS_CANCELED_ || $newOrderStatus->id == _PS_OS_PARTIALREFUND_ || $newOrderStatus->id == _PS_OS_MANUALREFUND_) {
                $id_order_detail = $params['id_order_detail'];

                if ($order AND !Validate::isLoadedObject($order)) {
                    die(Tools::displayError('Incorrect object Order.'));
                }

                /* Turkcell incremental number appended to the orderId */
                $turkcell_inc_num = $this->getTurkcellFieldInOrder($order->id_cart);

                $order_id = $order->id_cart . 'b' . $turkcell_inc_num; // unique order id generated by us
                $group_id = ''; // only used when orders are grouped (batch order)
                $installment_cnt = $oder->installment_count; // number of instalments (default: '')
                /* $cc_number = $this->customer_arr['Number']; // real card number here */
                $cc_number = ''; // real card number here
                $cc_expireDate = ''; // real concatenated expire date in monthyear format (ex: 0114)
                $cc_cvv2 = ''; // real CVV2 number
                $amount = $order->total_paid_real * 100; // 1 TL
                $type = 'walletvoid';
                $card_holder_present_code = '0'; // 0 for normal, 13 for 3d secure transactions
                $moto_ind = 'N'; // Y for mailorder
                $originalRetrefNum = ''; // reference number of transaction retrieved from sales response

                if ($newOrderStatus->id == _PS_OS_REFUND_ || $newOrderStatus->id == _PS_OS_PARTIALREFUND_ || $newOrderStatus->id == _PS_OS_MANUALREFUND_) {
                    $total = $params['amount'];
                    $amount = $total * 100;
                    $type = 'walletrefund';
                }

                if ($newOrderStatus->id == _PS_OS_CANCELED_) {
                    $moto_ind = '';
                }

                $terminal_id_padded = sprintf('0%s', $this->tid);
                $security_data = strtoupper(sha1($this->voidRefundUserPassword . $terminal_id_padded));
                $hash_data = strtoupper(sha1($order_id . $this->tid . $cc_number . $amount . $security_data));

                $output = $this->getXML($order_id, $hash_data, $group_id, $installment_cnt, $cc_number, $cc_expireDate, $cc_cvv2, $amount, $type, '', $card_holder_present_code, $moto_ind, $originalRetrefNum, '', '');

                $this->log->debug($this->host);
                $this->log->debug($output);

                // Set the POST options.
                $session = curl_init();
                curl_setopt($session, CURLOPT_URL, $this->host);
                curl_setopt($session, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($session, CURLOPT_POST, true);
                curl_setopt($session, CURLOPT_POSTFIELDS, $output);
                curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($session, CURLOPT_TIMEOUT, 90);

                /* Response from the bank */
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

    /**
     * Queries the status of the request made to Turkcell Wallet and
     * returns one of the status codes below:
     *
     *     00 - SUCCESS
     *     01 - FAILURE
     *     04 - TRY AGAIN
     *
     * Needs to get the following parameters:
     *
     *     array(
     *         'cellPhone' => $cellPhone,
     *         'installment' => $installment,
     *         'bonus' => $bonus,
     *         'finalTotal' => $finalTotal
     *     )
     */
    public function queryOrderStatus($params) {
        global $cart;

        if (Validate::isLoadedObject($cart) AND $cart->OrderExists() === false) {
            // Cart exists but no order created yet.. yay!
            // setting request type
            $params['type'] = $this->inqueryType;
            $pgtwCart = $this->getCart($params);
            $installment = $params['installment']; // number of installments
            $total = $params['finalTotal'];
            $interetRate = $params['interetRate'];
            $eachInstallmentAmount = $params['eachInstallmentAmount'];

            $this->log->debug('params: ' . print_r($params, true));
            $this->log->debug('cart: ' . print_r($pgtwCart, true));

            list($root, $data) = $pgtwCart->CheckoutServer2Server(90, false);

            // $this->log->debug('root: ' . $root . ', data: ' . print_r($data, true));

            if ($data[$root]['Order']['WalletOrderInqResult']['OrderTxnList']['OrderTxn']['Status']['VALUE'] === self::SUCCESS) {
                $this->log->info('Querying for transaction successfull!');

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

                if ($installment > 1) {
                    $this->log->info('More than 1 installment is selected!');

                    $this->validateOrder(intval($cart->id), $orderState, $total, $this->displayName, $installment, $interetRate, $eachInstallmentAmount, $data[$root]['Response']['VALUE'], array(), NULL, false, $customer->secure_key);
                } else {
                    $this->log->info('No installment is selected!');

                    $this->validateOrder(intval($cart->id), $orderState, $total, $this->displayName, 1, 0, $total, $data[$root]['Response']['VALUE'], array(), NULL, false, $customer->secure_key);
                }

                $order = new Order(intval($this->currentOrder));

                $this->updateTurkcellFieldInOrder(intval($cart->id), $_COOKIE['tcwad']);

                setcookie('tcwad', null, time() - 3600 * 24);

                $confirmation_data['trans2Status'] = self::SUCCESS;
                $confirmation_data['customerSecurekey'] = $customer->secure_key;
                $confirmation_data['cartId'] = intval($cart->id);
                $confirmation_data['moduleId'] = intval($this->id);
                $confirmation_data['orderId'] = intval($this->currentOrder);

                $this->log->debug('Returning success with following information: ' . print_r($confirmation_data, true));

                return $confirmation_data;
            } else {
                $error_data['trans2Status'] = $data[$root]['Order']['WalletOrderInqResult']['OrderTxnList']['OrderTxn']['Status']['VALUE'];

                return $error_data;
            }
        } else {
            // No cart exists or order has already created.. Either way
            // we shouldn't be here..
            Tools::display404Error();
        }
    }

    /* display of payment page */

    /**
     * Update the turkcell_append_data after the successfull completion of the 1st and 2nd request of the Turkcell payment
     * $param $cartId is cart id of the order after the second request.
     * $param $turkcell_append_data is the integer data append to the cart id(orderId) while passing to the request.
     */
    public function updateTurkcellFieldInOrder($cartId, $turkcell_append_data) {
        $query = 'UPDATE `' . _DB_PREFIX_ . 'orders`
			SET `turkcell_inc_num` = ' . $turkcell_append_data . '
			WHERE `id_cart`=' . $cartId;

        $res = Db::getInstance()->Execute($query);

        return;
    }

    public function getTurkcellFieldInOrder($cartId) {
        $query = 'SELECT `turkcell_inc_num`
			FROM `' . _DB_PREFIX_ . 'orders`
			WHERE `id_cart`=' . $cartId;

        $res = Db::getInstance()->getRow($query);

        return (int) $res['turkcell_inc_num'];
    }

    public function isBankResponseApprovedOnPayment($response) {
        global $cart;

        list($root, $data) = $response;
        return ($data[$root]['Transaction']['Response']['Message']['VALUE'] === 'Approved') ? true : false;
    }

    public static function isOrderCancellable(Order $iOrder) {
        $start = strtotime('today'); /*Represents 00:00:00, i.e mid-night 12 '0 clock */
        /* End of days */
        $end1 = $start + (3600 * 20); /*Represents 20:00:00, i.e night 8'0 clock */
        $end2 = $start + (3600 * 23); /*Represents 23:00:00, i.e night 11'0 clock */
        $start  = ($start - 3600); /*Represents 23:00:00, i.e Previous day night 11'0 clock */
        $order_date = strtotime($iOrder->date_add);

        return (($order_date > $start && $order_date < $end1) || ($order_date > $end1 && $order_date < $end2));
    }

    public static function isOrderRefundable(Order $iOrder) {
        return !self::isOrderCancellable($iOrder);
    }


}
?>

