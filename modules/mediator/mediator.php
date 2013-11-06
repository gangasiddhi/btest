<?php

if (!defined('_CAN_LOAD_FILES_'))
    exit;

class mediator extends PaymentModule {

    public function __construct() {
        $this->name = 'mediator';
        $this->tab = 'payments_gateways';
        $this->version = '1.2';

        $this->currencies = true;
        $this->currencies_mode = 'radio';

        parent::__construct();
        $this->page = basename(__FILE__, '.php');
        $this->displayName = $this->l('Payment Gateway Mediator');
        $this->description = $this->l('Payment Gateway(Mediator) API implementation for banks');
        $this->confirmUninstall = $this->l('Are you sure you want to delete your details ?');

        if (!sizeof(Currency::checkPaymentCurrencies($this->id)))
            $this->warning = $this->l('No currency set for this module');
    }

    function install() {
        if (!parent::install()
            OR !$this->registerHook('header')
            OR !$this->registerHook('paymentError')
            OR !$this->registerHook('updateOrderStatus')
        ) {
            return false;
        }

        return true;
    }

    /* For displaying payment options to customers */

    function paymentOptions() {
        if (!$this->active)
            return;

        global $cookie, $smarty, $cart, $new_checkout_process;

        $carriers = Carrier::getCarriers($cookie->id_lang, true, false);
        $cod_carrier = 0;
        foreach ($carriers as $carrier) {
            if ($carrier['name'] == 'MNG') {
                $cod_carrier = $carrier['id_carrier'];
            }
        }

        $summary = $cart->getSummaryDetails();

        if (!intval($cart->id_currency))
            $currency = new Currency(intval($cookie->id_currency));
        else
            $currency = new Currency(intval($cart->id_currency));

        if (!Validate::isLoadedObject($currency))
            $currency = new Currency(intval(Configuration::get('PS_CURRENCY_DEFAULT')));

        $sign = $currency->getSign();

        $smarty->assign(array(
            'this_path' => $this->_path,
            'two_page_checkout' => Configuration::get('TWO_STEP_CHECKOUT'),
            'this_path_ssl' => self::getHttpHost(true, true) . __PS_BASE_URI__ . 'modules/' . $this->name . '/',
            'default_id_carrier' => (int) Configuration::get('PS_CARRIER_DEFAULT'),
            'cod_carrier' => $cod_carrier,
            'total_price' => number_format($summary['total_price'], 2, '.', ''),
            'currency_type' => $sign,
        ));

        if(!$new_checkout_process) {
            return $this->display(__FILE__, 'mediator.tpl');
        } else {
            return $this->display(__FILE__, 'mediator-new.tpl');
        }
    }

    /* Display the payment List */

    public function displayPaymentList($order_steps = true) {
        return $this->display(__FILE__, 'payment_list.tpl');
    }

    /*
     * This function will check display card details form using payment_execution.tpl
     * Once submit button on the form has been pressed, card details are submitted to the server
     */

    /* display of payment page */

    public function displayPayment($order_steps = true, $bankId = 0) {
        global $cookie, $smarty, $cart, $new_checkout_process;

        if (!$this->active)
            return;

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

        for ($i = 0; $i <= 15; $i++) {
            $years[] = $year++;
        }

        $century = substr($full_year, 0, 2);

        // Starting Installments Calculations..
        $paymentModules = Tools::filterPaymentModules(Module::getPaymentModules());
        $installmentsOptions = array();
        $installmentsAmount = array();
        foreach ($paymentModules as $p) {
            $installmentsOptions[$p] = array(
                1 => 0,
                3 => Configuration::get(strtoupper($p) . '_3_INSTALLMENT_INTEREST_RATE'),
                6 => Configuration::get(strtoupper($p) . '_6_INSTALLMENT_INTEREST_RATE'),
                12 => Configuration::get(strtoupper($p) . '_12_INSTALLMENT_INTEREST_RATE')
            );


            foreach ($installmentsOptions[$p] as $installment => $interest) {
                $totalAmountForInstallments = $summary['total_price'] + ($summary['total_price'] * ($interest / 100));
                $interestAmountForInstallments = $summary['total_price'] * ($interest / 100);

                $installmentsAmount[$p]['totalAmountFor' . $installment . 'Installments'] = number_format($totalAmountForInstallments, 2, '.', '');
                $installmentsAmount[$p]['eachInstallmentAmountFor' . $installment . 'Installments'] = number_format($totalAmountForInstallments / $installment, 2, '.', '');
                $installmentsAmount[$p]['interestAmountFor' . $installment . 'Installments'] = number_format($interestAmountForInstallments, 2, '.', '');
            }
        }

        if (! intval($cart->id_currency)) {
            $currency = new Currency(intval($cookie->id_currency));
        } else {
            $currency = new Currency(intval($cart->id_currency));
        }

        if (! Validate::isLoadedObject($currency)) {
            $currency = new Currency(intval(Configuration::get('PS_CURRENCY_DEFAULT')));
        }

        $sign = $currency->getSign();
        $smarty->assign('order_steps', $order_steps);
        $smarty->assign(array(
            'totalPrice' => number_format($summary['total_price'], 2, '.', ''),
            'years' => $years,
            'century' => $century,
            'installmentsOptions' => $installmentsOptions,
            'installmentsAmount' => $installmentsAmount,
            'currencyType' => $sign,
            'bank_id' => $bankId
        ));

        if (! $new_checkout_process) {
            return $this->display(__FILE__, 'payment_execution.tpl');
        } else {
            return $this->display(__FILE__, 'payment_execution_new.tpl');
        }
    }

    static private function getHttpHost($http = false, $entities = false) {
        $host = (isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : $_SERVER['HTTP_HOST']);
        if ($entities)
            $host = htmlspecialchars($host, ENT_COMPAT, 'UTF-8');
        if ($http)
            $host = (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://') . $host;
        return $host;
    }

    function hookPaymentError($params) {
        if (!$this->active)
            return;
        return $this->display(__FILE__, 'payment_error.tpl');
    }

    function cartProductQuantityZero() {
        return $this->display(__FILE__, 'cartProductNotAvailable.tpl');
    }

    function hookHeader($params) {
        global $new_checkout_process;

        if (strpos($_SERVER['PHP_SELF'], 'order') !== false ||
            strpos($_SERVER['PHP_SELF'], 'order-confirmation') !== false ||
            strpos($_SERVER['PHP_SELF'], 'payment_error') !== false) {
                if (! Configuration::get('TWO_STEP_CHECKOUT')) {
                    if (! $new_checkout_process) {
                        Tools::addCSS(($this->_path) . 'payment.css', 'all');
                    } else {
                        Tools::addCSS(($this->_path) . 'payment-new.css', 'all');
                    }
                } else {
                    if (! $new_checkout_process) {
                        Tools::addCSS(($this->_path) . 'spc-payment.css', 'all');
                    } else {
                        Tools::addCSS(($this->_path) . 'spc-payment-new.css', 'all');
                    }

                    Tools::addCSS(_THEME_CSS_DIR_ . 'agreements.css', 'all');
                }

                Tools::addCSS(_THEME_CSS_DIR_ . 'order-steps.css', 'all');
        }

        if (strpos($_SERVER['PHP_SELF'], 'order') !== false) {
            Tools::addJS($this->_path . 'scripts/cc.js');

            if (! $new_checkout_process) {
                Tools::addJS($this->_path . 'scripts/bank-ajax.js');
            } else {
                Tools::addJS($this->_path . 'scripts/bank-ajax-new.js');
            }

            Tools::addJs(_PS_JS_DIR_ . 'jquery.fancybox.pack.js');
        }
    }

    public function getCreditCardBankCode($ccNo) {
        return Tools::getBankViaCCNo($ccNo);
    }

    public function hookUpdateOrderStatus($params) {
        // Update customer placed_order when user shipped an order
        $iOrder = new Order($params['id_order']);
        $newStateId = $params['newOrderStatus']->id;
        $currentStateId = $iOrder->getCurrentState();

        error_log('20130627 - newStateId: ' . $newStateId . ', currentStateId: ' . $currentStateId);

        if (_PS_OS_SHIPPING_ == $newStateId) {
            error_log('20130627 - State has changed to shipped!');

            $iCustomer = new Customer(Order::getCustomerIdStatic($params['id_order']));

            if (! $iCustomer->placed_order) {
                $iCustomer->placed_order = true;
                $iCustomer->save();

                error_log('20130627 - Marking customer as "placed order"');

                $orders = Order::getCustomerOrders($iCustomer->id);

                foreach ($orders as $order) {
                    if (Configuration::get('CUSTOMER_FIRST_ORDER_CONTROL') == $order['id_order_state']) {
                        error_log('20130627 - Marking order as PIP!"');
                        $iOrder = new Order($order['id_order']);
                        $iOrder->setCurrentState(_PS_OS_PREPARATION_);
                    }
                }
            }
        } elseif (Configuration::get('CUSTOMER_FIRST_ORDER_CONTROL') == $newStateId) {
            require_once(_PS_MODULE_DIR_ . 'moderation/FraudModerationDetail.php');

            error_log('20130627 - New state is CUSTOMER_FIRST_ORDER_CONTROL, adding fraud moderation record..');

            $iOrderModerationDet = new FraudModerationDetail();
            $iOrderModerationDet->id_order = $params['id_order'];
            $iOrderModerationDet->id_customer = Order::getCustomerIdStatic($params['id_order']);

            return $iOrderModerationDet->add();
        } elseif (Configuration::get('CUSTOMER_FIRST_ORDER_CONTROL') == $currentStateId) {
            if ($newStateId != Configuration::get('PS_OS_FRAUD_ORDER')) {
                // Mark as moderated if order state changed to another
                // FIXME: Infinite loop because state of order changing in markCustomerAsVerified function.
                require_once(_PS_MODULE_DIR_ . 'moderation/FraudModerationDetail.php');

                error_log('20130627 - Current state was CUSTOMER_FIRST_ORDER_CONTROL, marking customer as verified!');

                return FraudModerationDetail::markCustomerAsVerified(Order::getCustomerIdStatic($params['id_order']));
            }
        }
    }
}

?>
