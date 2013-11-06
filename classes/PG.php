<?php

abstract class PGCore  extends PaymentModule {
    protected function getBandResponseForPayment(IPGCart $pgCart) {
        return $pgCart->CheckoutServer2Server(90, false);
    }

    public function hookPaymentError($params) {
        if (! $this->active) {
            return;
        }

        return $this->display($this->_path . $this->name,  'payment_error.tpl');
    }

    protected static function getHttpHost($http = false, $entities = false) {
        $host = (isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : $_SERVER['HTTP_HOST']);

        if ($entities) {
            $host = htmlspecialchars($host, ENT_COMPAT, 'UTF-8');
        }

        if ($http) {
            $host = (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').$host;
        }

        return $host;
    }

    /*display of payment page*/
    public function displayPayment($order_steps = true, $bank_id = 0) {
        global $cookie, $smarty, $cart;

        if (! $this->active) {
            return;
        }

        $smarty->assign(array(
            'this_path' => $this->_path,
            'this_path_ssl' => self::getHttpHost(true, true) . __PS_BASE_URI__ . 'modules/' . $this->name . '/'
        ));

        if (isset($cart->id_customer)) {
            $customer = new Customer(intval($cart->id_customer));

            if (Validate::isLoadedObject($customer)) {
                $firstname = strval($customer->firstname);
                $lastname = strval($customer->lastname);
                $is_member = Customer::memberOfGroup(intval($cart->id_customer));
                $smarty->assign('is_member', $is_member);
            }
        }

        $summary = $cart->getSummaryDetails();
        $full_year = Date('Y');
        $year = substr($full_year, 2, 2);
        $years = array();

        for($i = 0; $i <= 15; $i++) {
            $years[] = $year++;
        }

        $century = substr($full_year, 0, 2);
        $three_instlmnt_total_cal = $summary['total_price'] + ($summary['total_price'] * (0.00 / 100));
        $three_each_instlmnt =  number_format($three_instlmnt_total_cal / 3, 2, '.', '');
        $three_instlmnt_total =  number_format($three_instlmnt_total_cal, 2, '.', '');

        $six_instlmnt_total_cal =   $summary['total_price'] + ($summary['total_price'] * (8.35 / 100));
        $six_each_instlmnt = number_format($six_instlmnt_total_cal/6, 2, '.', '');
        $six_instlmnt_total =  number_format($six_instlmnt_total_cal, 2, '.', '');

        $twelve_instlmnt_total_cal =   $summary['total_price'] + ($summary['total_price'] * (14.93 / 100));
        $twelve_each_instlmnt = number_format($twelve_instlmnt_total_cal/12, 2, '.', '');
        $twelve_instlmnt_total =  number_format($twelve_instlmnt_total_cal, 2, '.', '');

        if (! intval($cart->id_currency)) {
            $currency = new Currency(intval($cookie->id_currency));
        } else {
            $currency = new Currency(intval($cart->id_currency));
        }

        if (! Validate::isLoadedObject($currency)) {
            $currency = new Currency(intval(Configuration::get('PS_CURRENCY_DEFAULT')));
        }

        $sign = $currency->getSign();

        $smarty->assign(array(
            'order_steps' => $order_steps,
            'total_price' => number_format($summary['total_price'], 2, '.', ''),
            'years' => $years,
            'century' => $century,
            'three_each_instlmnt' => $three_each_instlmnt,
            'three_instlmnt_total' =>   $three_instlmnt_total,
            'six_each_instlmnt' => $six_each_instlmnt,
            'six_instlmnt_total' => $six_instlmnt_total,
            'twelve_each_instlmnt' => $twelve_each_instlmnt,
            'twelve_instlmnt_total' => $twelve_instlmnt_total,
            'currency_type' => $sign,
            'bank_id' => $bank_id
        ));

        return $this->display($this->_path . $this->name, 'payment-execution.tpl');
    }

    /**
    * @param $id_order
    * @param $amount
    * @return errors as array if there is any error else true
    */
    public static function refundViaBank($id_order, $amount) {
        $myFile = "PaymentRefundExchange/PaymentRefundExchange_".date('Y-m-d',time()).".log";
        $msgLogFile = new LogFile($myFile, 'a');

        $order = new Order($id_order);

        if ($order->module != "cashondelivery" ) {
            // Get the response from the bank on refund
            $bank_response = Hook::getBankResponseOnOrderStatusChange(_PS_OS_PARTIALREFUND_, (int)($id_order), $amount);
            $msgLogFile->addLine("Bank Response:" . print_r($bank_response, true));

            $bankResLogFile = new LogFile($order->module.'/refund-money.log', "a");
            $bankResLogFile->setPrefix('[PG][refundViaBank]');

            if(! $bank_response) {
                $bankResLogFile->addLine("No response from bank,\t id-order:$id_order");
                return array('error' => true, 'errorMessage' => 'There is no response from the bank');
            }

            $bankResLogFile->addLine("State: _PS_OS_PARTIALREFUND_ ,\n Bank response AdminOrders:$bank_response,\n id-order:$id_order,\n refund amount: $amount");

            $response_list = explode('|',$bank_response);
            if($response_list[0] != 'Approved' && $response_list[0] != 1) {
                $err_msg = isset($response_list[2]) && $response_list[2]  ? $response_list[2]: '';
                $bankResLogFile->addLine('The Response from the bank is '.$response_list[0].', '.$err_msg.' Please try again later');
                return array('error' => true, 'errorMessage' => 'The Response from the bank is '.$response_list[0].', '.$err_msg.' Please try again later');
            }
        }

        return true;
    }


    public static function getPGInstanceByName($name) {
        $moduleFile = _PS_MODULE_DIR_.$name.'/'.$name.'.php';
        include_once($moduleFile);
        return new $name();
    }

    static public function xmlentities($string) {
        $string = str_replace("&", "&amp;", $string);
        $string = str_replace("<", "&lt;", $string);
        $string = str_replace(">", "&gt;", $string);
        $string = str_replace("\"", "&quot;", $string);
        $string = str_replace("'", "&apos;", $string);

        return $string;
    }

}

?>
