<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of TwoStepOrderController
 *
 * @author gangadhar
 */
ControllerFactory::includeController('ParentOrderController');

class TwoStepOrderControllerCore extends ParentOrderController {

    public $step;

    public function init() {
        parent::init();


        $this->step = (int) (Tools::getValue('step'));
        if (!$this->nbProducts)
            $this->step = -1;
    }

    public function preProcess() {
        global $isVirtualCart, $orderTotal;

        parent::preProcess();
        /* if ($this->nbProducts)
          {
          //give discount of 20tl to customers on thier first cart valid only for 24hrs
          $number_of_customer_orders = Order::getCustomerNbOrders(self::$cookie->id_customer);
          $show_first_cart_discount = false;
          if($number_of_customer_orders == 0)
          {
          $first_cart_discount_name = strval(Configuration::get('PS_FIRST_CART_DISCOUNT_NAME'));
          $discount = new Discount((int)(Discount::getIdByName($first_cart_discount_name)));
          if(self::$cart->getDiscountsCustomer($discount->id) <= 0)
          {
          self::$cart->addDiscount((int)($discount->id));
          }
          if(!CustomerDiscount::customerDiscountExists((int)(self::$cookie->id_customer), 5))
          {
          $cartDiscount = new CustomerDiscount();
          $cartDiscount->id_discount = (int)($discount->id);
          $cartDiscount->id_discount_type = (int)($discount->id_discount_type);
          $cartDiscount->id_customer = (int)(self::$cookie->id_customer);
          //$cartDiscount->valid_upto = date('Y-m-d H:i:s', $first_cart_discount_validity);
          if(!$cartDiscount->add())
          $errors[] = Tools::displayError('cannot add');
          }
          $show_first_cart_discount = true;
          self::$smarty->assign(array(
          'show_first_cart_discount' => $show_first_cart_discount,
          'first_cart_discount_name' =>  $first_cart_discount_name
          ));
          }
          } */
        /* end of {give discount of 20tl to customers on thier first cart valid only for 24hrs} */

        /* If some products have disappear */
        if (!self::$cart->checkQuantities()) {
            $this->step = 0;
        }

        /* Check minimal amount */
        $currency = Currency::getCurrency((int) self::$cart->id_currency);

        $orderTotal = self::$cart->getOrderTotal();
        $minimalPurchase = Tools::convertPrice((float) Configuration::get('PS_PURCHASE_MINIMUM'), $currency);
        if (self::$cart->getOrderTotal(false, Cart::ONLY_PRODUCTS) < $minimalPurchase && $this->step != -1) {
            $this->step = 0;
            $this->errors[] = Tools::displayError('A minimum purchase total of') . ' ' . Tools::displayPrice($minimalPurchase, $currency) .
                    ' ' . Tools::displayError('is required in order to validate your order.');
        }

        /**
         * If condition is added to allow the customers from the google addwords(if the "show_site" cookie is set) to see the cart
         * page and redirect to the login page on further continuation, for other customers directly redirect to the login page on
         * adding product to the cart.
         */


        if (!isset(self::$cookie->show_site) AND self::$cookie->show_site != 1) {
            if (!self::$cookie->isLogged(true) AND in_array($this->step, array(1, 2, 3))) {
                Tools::redirect('authentication.php?back=' . urlencode('order.php?step=' . $this->step));
            }
        }

        if ($this->nbProducts)
            self::$smarty->assign('virtual_cart', $isVirtualCart);

        if ($this->step == 0)
            self::$smarty->assign('errors', $this->errors);

        if (isset(self::$cookie->show_site) AND self::$cookie->show_site == 1 AND self::$cookie->logged) {
            $customer = new Customer(self::$cookie->id_customer);
            $customer_join_month = substr($customer->date_add, 5, 2);
            $customer_join_year = substr($customer->date_add, 0, 4);
            self::$smarty->assign(array(
                'customer_join_month' => $customer_join_month,
                'customer_join_year' => $customer_join_year
            ));
        }

        $cms7 = new CMS((int) (7), (int) (self::$cookie->id_lang));
        $this->link_conditions = self::$link->getCMSLink($cms7, $cms7->link_rewrite, true);
        self::$smarty->assign('link7', $this->link_conditions);

        $cms8 = new CMS((int) (8), (int) (self::$cookie->id_lang));
        $this->link_conditions = self::$link->getCMSLink($cms8, $cms8->link_rewrite, true);
        self::$smarty->assign('link8', $this->link_conditions);

    }

    public function displayHeader() {
        if (!Tools::getValue('ajax'))
            parent::displayHeader();
    }

    public function process() {
        parent::process();

		/*Sending the cart details to the Sailthru*/
		Module::hookExec('sailThruMailSend', array(
			'sailThruEmailTemplate' => 'Abandoned-Cart'
		));

        /* 4 steps to the order */
        switch ((int) $this->step) {
            case -1;
                self::$smarty->assign('empty', 1);
                break;
            case 1:
                $this->_assignSummaryInformations();
                break;
            case 2:
                /* Assign cart summary info */
                $this->_assignSummaryInformations();
                /* Assign cart summary info */

                /* Check if customer has address */
                $chkout_address = true;


                if (isset(self::$cookie->show_site) AND self::$cookie->show_site == 1 AND !self::$cookie->logged) {

                    $loginAfterBasketDisabled = Configuration::get('PS_LOGIN_AFTER_BASKET_DISABLED');

                    if (!$loginAfterBasketDisabled) {
                        //redirect if only login after basket confiugarion 
                        //value not set
                        Tools::redirect('authentication.php?back=order.php?step=2');
                    }
                }


                if (self::$cookie->logged) {

                    if (!Customer::getAddressesTotalById((int) (self::$cookie->id_customer))) {
                        $chkout_address = false;
                        self::$smarty->assign('no_chkout_address', 1);
                    } else {
                        self::$smarty->assign('no_chkout_address', 0);
                    }
                    /* Check if customer has address */

                    if ($chkout_address == true) {
                        if (Tools::getValue('ajax') && Tools::getValue('processAddress')) {
                            $this->processAddress();
                        } else {
                            $this->_assignAddress();
                            $this->_assignCarrier();

                            $this->processAddress();
                            $this->processCarrier();
                            $this->autoStep();

                            if (self::$cart->getOrderTotal() <= 0) {
                                self::$smarty->assign('free_order', true);
                            } else {
                                $this->_assignPayment();
                            }
                        }
                    }
                }
                break;
            case 3:
                /* Bypass payment step if total is 0 */
                if (($id_order = $this->_checkFreeOrder()) AND $id_order) {
                    require_once(_PS_ROOT_DIR_ . '/modules/pgf/pgf.php');
                    $pgf = new PGF();
                    $customer = new Customer((int) (self::$cookie->id_customer));
                    if (self::$cookie->is_guest) {
                        $email = self::$cookie->email;
                        self::$cookie->logout(); // If guest we clear the cookie for security reason
                        Tools::redirect('guest-tracking.php?id_order=' . (int) $id_order . '&email=' . urlencode($email));
                    }
                    else
                        Tools::redirectLink(Tools::getHttpHost(true, true) . __PS_BASE_URI__ . 'order-confirmation.php?key=' . $customer->secure_key . '&id_cart=' . intval(self::$cart->id) . '&id_module=' . intval($pgf->id) . '&id_order=' . intval($id_order) . '&free_order=' . true);
                }
            default:
                $this->_assignSummaryInformations();
                break;
        }

        $provinces = Country::getProvinces();
        /*Two page check out*/
        if(Tools::getIsset('chkout') && Tools::getValue('chkout') == 2)
        {
            $statesList = '';
            $statesList .= '<option value="">-</option>';
            if (isset($this->_address) AND isset($this->_address->id_state) AND !empty($this->_address->id_state) AND is_numeric($this->_address->id_state))
                $selectedstate =  (int)$this->_address->id_state;
            foreach ($countries AS $country)
            {
                if($country['contains_states'] == 1 && isset($country['states']))
                {

                    foreach($country['states'] as $state) {
                        $statesList .= '<option value="'.(int)($state['id_state']).'" '.(isset($selectedstate) ? ($state['id_state'] == $selectedstate ? 'selected="selected"' : ''): '').'>'.htmlentities($state['name'], ENT_COMPAT, 'UTF-8').'</option>';
                    }
                }
            }

            self::$smarty->assign(array(
                    'two_page_checkout' =>  Tools::getValue('chkout'),
                    'statesList' => $statesList));
        }

        /*Two page check out*/

        // March region code for use in template
        preg_match('/(\d{3})(\d{7})/', $this->_address->phone, $phoneMatches);

        self::$smarty->assign(array(
            'countries_list' => $countriesList,
            'countries' => $countries,
            'errors' => $this->errors,
            'token' => Tools::getToken(false),
            'select_address' => (int)(Tools::getValue('select_address')),
            'states' => $provinces,
            'regioncode' => $phoneMatches[1],
            'phoneNumberWTRegion' => $phoneMatches[2],
        ));
    }

    private function processAddressFormat() {
        $addressDelivery = new Address((int) (self::$cart->id_address_delivery));
        $addressInvoice = new Address((int) (self::$cart->id_address_invoice));
        //echo "<pre>"; print_r($addressInvoice); echo "</pre>";
        $invoiceAddressFields = AddressFormat::getOrderedAddressFields($addressInvoice->id_country, false, true);
        $deliveryAddressFields = AddressFormat::getOrderedAddressFields($addressDelivery->id_country, false, true);
        //echo $deliveryAddressFields[3];exit;
        //echo "<pre>"; print_r($deliveryAddressFields); echo "</pre>";
        self::$smarty->assign(array(
            'inv_adr_fields' => $invoiceAddressFields,
            'dlv_adr_fields' => $deliveryAddressFields));
    }

    public function displayContent() {
        global $currency, $new_checkout_process;

        parent::displayContent();

        self::$smarty->assign(array(
            'currencySign' => $currency->sign,
            'currencyRate' => $currency->conversion_rate,
            'currencyFormat' => $currency->format,
            'currencyBlank' => $currency->blank,
        ));

        if(strpos($_SERVER['REQUEST_URI'],'step=1') !== false) {
            self::$smarty->assign('step1', 1);
        } elseif(strpos($_SERVER['REQUEST_URI'],'step=2') !== false || strpos($_SERVER['REQUEST_URI'],'step=3') !== false) {
            self::$smarty->assign('step2', 2);;
        }
        switch ((int) $this->step) {
            case -1:
                if($new_checkout_process) {
                    self::$smarty->display(_PS_THEME_DIR_ . 'shopping-cart2-new.tpl');
                } else {
                    self::$smarty->display(_PS_THEME_DIR_ . 'shopping-cart2.tpl');
                }
                break;
            case 1:
                if($new_checkout_process) {
                    self::$smarty->display(_PS_THEME_DIR_ . 'shopping-cart2-new.tpl');
                } else {
                    self::$smarty->display(_PS_THEME_DIR_ . 'shopping-cart2.tpl');
                }
                break;
            case 2:
                $this->processAddressFormat();
                if($new_checkout_process ) {
                    self::$smarty->display(_PS_THEME_DIR_ . 'order2-steps-new.tpl');
                } else {
                    self::$smarty->display(_PS_THEME_DIR_ . 'order2-steps.tpl');
                }
                break;
            case 3:
                //self::$smarty->display(_PS_THEME_DIR_.'order-payment.tpl');
                break;
            default:
                if($new_checkout_process) {
                    self::$smarty->display(_PS_THEME_DIR_ . 'shopping-cart2-new.tpl');
                } else {
                    self::$smarty->display(_PS_THEME_DIR_ . 'shopping-cart2.tpl');
                }
                break;
        }
    }

    public function displayFooter() {
        if (!Tools::getValue('ajax'))
            parent::displayFooter();
    }

    /* Order process controller */

    public function autoStep() {
        //global $isVirtualCart;

        if ($this->step >= 2 AND (!self::$cart->id_address_delivery OR !self::$cart->id_address_invoice))
            Tools::redirect('order.php?step=1');
        $delivery = new Address((int) (self::$cart->id_address_delivery));
        $invoice = new Address((int) (self::$cart->id_address_invoice));
        if ($delivery->deleted OR $invoice->deleted OR !$delivery->id OR !$invoice->id) {
            if ($delivery->deleted)
                unset(self::$cart->id_address_delivery);
            if ($invoice->deleted)
                unset(self::$cart->id_address_invoice);
            Tools::redirect('order.php?step=1');
        }
        //elseif ($this->step >= 3 AND !self::$cart->id_carrier AND !$isVirtualCart)
        //Tools::redirect('order.php?step=2');
    }

    /* Order process controller */
    /* public function autoStep()
      {
      global $isVirtualCart;

      if ($this->step >= 2 AND (!self::$cart->id_address_delivery OR !self::$cart->id_address_invoice))
      Tools::redirect('order.php?step=1');
      $delivery = new Address((int)(self::$cart->id_address_delivery));
      $invoice = new Address((int)(self::$cart->id_address_invoice));

      if ($delivery->deleted OR $invoice->deleted)
      {
      if ($delivery->deleted)
      unset(self::$cart->id_address_delivery);
      if ($invoice->deleted)
      unset(self::$cart->id_address_invoice);
      Tools::redirect('order.php?step=1');
      }
      elseif ($this->step >= 3 AND !self::$cart->id_carrier AND !$isVirtualCart)
      Tools::redirect('order.php?step=2');
      } */

    /*
     * Manage address
     */

    /* public function processAddress() {
      $id_address = Address::getFirstCustomerAddressId((int) (self::$cookie->id_customer));
      if (self::$cart->id_address_delivery == 0 || self::$cart->id_address_invoice == 0
      || $id_address != self::$cart->id_address_delivery || $id_address != self::$cart->id_address_invoice) {
      if (!Tools::isSubmit('id_address_delivery') OR !Address::isCountryActiveById((int) Tools::getValue('id_address_delivery')))
      $this->errors[] = Tools::displayError('This address is not in a valid area.');
      else {
      self::$cart->id_address_delivery = (int) (Tools::getValue('id_address_delivery'));
      self::$cart->id_address_invoice =  Tools::isSubmit('same') ? self::$cart->id_address_delivery  : (int)(Tools::getValue('id_address_invoice')) ;
      if (!self::$cart->update())
      $this->errors[] = Tools::displayError('An error occurred while updating your cart.');

      //if (Tools::isSubmit('message'))
      //$this->_updateMessage(Tools::getValue('message'));
      }
      }
      if (sizeof($this->errors)) {
      if (Tools::getValue('ajax'))
      die('{"hasError" : true, "errors" : ["' . implode('\',\'', $this->errors) . '"]}');
      $this->step = 1;
      }
      if (Tools::getValue('ajax'))
      die(true);
      } */

    public function processAddress() {
        if (self::$cart->id_address_delivery == 0 || self::$cart->id_address_invoice == 0) {
            $this->errors[] = Tools::displayError('There is no cart address');
        }
        if (Tools::getValue('ajax')) {
            if (!Tools::isSubmit('id_address_delivery') OR !Address::isCountryActiveById((int) Tools::getValue('id_address_delivery')))
                $this->errors[] = Tools::displayError('This address is not in a valid area.');
            else {
                self::$cart->id_address_delivery = (int) (Tools::getValue('id_address_delivery'));
                self::$cart->id_address_invoice = Tools::isSubmit('same') ? self::$cart->id_address_delivery : (int) (Tools::getValue('id_address_invoice'));
                if (!self::$cart->update())
                    $this->errors[] = Tools::displayError('An error occurred while updating your cart.');

//			if (Tools::isSubmit('message'))
//				$this->_updateMessage(Tools::getValue('message'));
            }
            if (sizeof($this->errors)) {
                if (Tools::getValue('ajax'))
                    die('{"hasError" : true, "errors" : ["' . implode('\',\'', $this->errors) . '"]}');
                $this->step = 1;
            }
            if (Tools::getValue('ajax'))
                die(true);
        }
    }

    /* Carrier step */

    protected function processCarrier() {
        global $orderTotal;

        parent::_processCarrier();

        if (sizeof($this->errors)) {
            self::$smarty->assign('errors', $this->errors);
            $this->_assignCarrier();
            $this->step = 2;
            $this->displayContent();
            include(dirname(__FILE__) . '/../footer.php');
            exit;
        }
        $orderTotal = self::$cart->getOrderTotal();
    }

    /* Address step */

    protected function _assignAddress() {
        parent::_assignAddress();

        self::$smarty->assign('cart', self::$cart);
        if (self::$cookie->is_guest)
            Tools::redirect('order.php?step=1');
    }

    /* Carrier step */

    protected function _assignCarrier() {
        global $defaultCountry;

        if (isset(self::$cookie->id_customer))
            $customer = new Customer((int) (self::$cookie->id_customer));
        else
            die(Tools::displayError('Fatal error: No customer'));
        // Assign carrier
        parent::_assignCarrier();
        // Assign wrapping and TOS
        //$this->_assignWrappingAndTOS();

        self::$smarty->assign('is_guest', (isset(self::$cookie->is_guest) ? self::$cookie->is_guest : 0));
    }

    /* Payment step */

    protected function _assignPayment() {
        global $orderTotal;

        // Redirect instead of displaying payment modules if any module are grefted on
        Hook::backBeforePayment('order.php?step=3');

        require_once(dirname(__FILE__) . '/../modules/mediator/mediator.php');
        $mediator = new mediator();

        $paymentDetails = $mediator->paymentOptions();
        self::$smarty->assign('PaymentMethods', $paymentDetails);


        self::$cookie->checkedTOS = '1';

        parent::_assignPayment();
    }

}

?>
