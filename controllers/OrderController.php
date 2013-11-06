<?php

ControllerFactory::includeController('ParentOrderController');

class OrderControllerCore extends ParentOrderController
{
	public $step;

	public function init()
	{
		parent::init();

		$this->step = (int)(Tools::getValue('step'));
		if (!$this->nbProducts)
			$this->step = -1;
	}

	public function preProcess()
	{
		global $isVirtualCart, $orderTotal;

		parent::preProcess();
		/*if ($this->nbProducts)
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
		}*/
		 /* end of {give discount of 20tl to customers on thier first cart valid only for 24hrs}*/

		/* If some products have disappear */
		if (!self::$cart->checkQuantities())
		{
			$this->step = 0;
			$this->errors[] = Tools::displayError('An item in your cart is no longer available for this quantity, you cannot proceed with your order.');
		}

		/* Check minimal amount */
		$currency = Currency::getCurrency((int)self::$cart->id_currency);

		$orderTotal = self::$cart->getOrderTotal();
		$minimalPurchase = Tools::convertPrice((float)Configuration::get('PS_PURCHASE_MINIMUM'), $currency);
		if (self::$cart->getOrderTotal(false, Cart::ONLY_PRODUCTS) < $minimalPurchase && $this->step != -1)
		{
			$this->step = 0;
			$this->errors[] = Tools::displayError('A minimum purchase total of').' '.Tools::displayPrice($minimalPurchase, $currency).
			' '.Tools::displayError('is required in order to validate your order.');
		}

		/**
		 * If condition is added to allow the customers from the google addwords(if the "show_site" cookie is set) to see the cart
		 * page and redirect to the login page on further continuation, for other customers directly redirect to the login page on
		 * adding product to the cart.
		 */
		if (!isset(self::$cookie->show_site) AND self::$cookie->show_site != 1)
		{
			if (!self::$cookie->isLogged(true) AND in_array($this->step, array(1, 2, 3)))
			{
				Tools::redirect('authentication.php?back=' . urlencode('order.php?step=' . $this->step));
			}
		}

                /*Survey Vs Register*/
                $showSite = isset(self::$cookie->show_site) AND self::$cookie->show_site === 1;
                if(self::$cookie->logged)
                {
                    $customer = new Customer((int)(self::$cookie->id_customer));
                    if($customer->date_add >= '2012-09-17 00:00:00')
                    {
                        if (! $customer->hasCompletedSurvey() /*AND strtotime($customer->date_add) > $gadsSurveyStartDate*/ AND !$showSite)
                        {
                                self::$smarty->assign('no_butigim_link' , 1);
                        }
                    }
                }
                /*Survey Vs Register*/

		if ($this->nbProducts)
			self::$smarty->assign('virtual_cart', $isVirtualCart);

		if($this->step == 0)
			self::$smarty->assign('errors', $this->errors);

		if(isset(self::$cookie->show_site) AND self::$cookie->show_site == 1 AND self::$cookie->logged){
			$customer = new Customer(self::$cookie->id_customer);
			$customer_join_month = substr($customer->date_add, 5, 2);
			$customer_join_year = substr($customer->date_add, 0, 4);
			self::$smarty->assign(array(
				'customer_join_month' => $customer_join_month,
				'customer_join_year' => $customer_join_year
			));
		}
	}

	public function displayHeader()
	{
		if (!Tools::getValue('ajax'))
			parent::displayHeader();
	}

	public function process()
	{
		parent::process();

		/* 4 steps to the order */
		switch ((int)$this->step)
		{
			case -1;
				self::$smarty->assign('empty', 1);
				break;
			case 1:
				$this->_assignSummaryInformations();
				//$this->_assignAddress();

				break;
			case 2:
				//if (Tools::isSubmit('processAddress'))
				if(isset(self::$cookie->show_site) AND self::$cookie->show_site == 1 AND !self::$cookie->logged)
					Tools::redirect('authentication.php?back=order.php?step=2');
				$this->_assignAddress();
				$this->_assignCarrier();
				//$this->autoStep();
				//$this->_assignCarrier();
				break;
			case 3:
				//if(Tools::issubmit('processAddress'))
					$this->processAddress();
					$this->processCarrier();
					$this->autoStep();

				//Test that the conditions (so active) were accepted by the customer
				/*$cgv = Tools::getValue('cgv');
				if (Configuration::get('PS_CONDITIONS') AND (!Validate::isBool($cgv)))
					Tools::redirect('order.php?step=2');*/

				//if (Tools::isSubmit('processCarrier'))
					//$this->processCarrier();
				//$this->autoStep();
				/* Bypass payment step if total is 0 */
				if (($id_order = $this->_checkFreeOrder()) AND $id_order)
				{
                                        require_once(_PS_ROOT_DIR_.'/modules/pgf/pgf.php');
                                         $pgf = new PGF();
                                        $customer = new Customer((int)(self::$cookie->id_customer));

					if (self::$cookie->is_guest)
					{
						$email = self::$cookie->email;
						self::$cookie->logout(); // If guest we clear the cookie for security reason
						Tools::redirect('guest-tracking.php?id_order='.(int)$id_order.'&email='.urlencode($email));
					}
					else
						Tools::redirectLink(Tools::getHttpHost(true, true).__PS_BASE_URI__.'order-confirmation.php?key='.$customer->secure_key.'&id_cart='.intval(self::$cart->id).'&id_module='.intval($pgf->id).'&id_order='.intval($id_order).'&free_order='.true);
				}
				$this->_assignPayment();
				break;
			default:
				$this->_assignSummaryInformations();
				break;
		}
	}

	private function processAddressFormat()
	{
		$addressDelivery = new Address((int)(self::$cart->id_address_delivery));
		$addressInvoice = new Address((int)(self::$cart->id_address_invoice));

		$invoiceAddressFields = AddressFormat::getOrderedAddressFields($addressInvoice->id_country, false, true);
		$deliveryAddressFields = AddressFormat::getOrderedAddressFields($addressDelivery->id_country, false, true);

		self::$smarty->assign(array(
			'inv_adr_fields' => $invoiceAddressFields,
			'dlv_adr_fields' => $deliveryAddressFields));
	}

	public function displayContent()
	{
		global $currency;

		parent::displayContent();

		self::$smarty->assign(array(
			'currencySign' => $currency->sign,
			'currencyRate' => $currency->conversion_rate,
			'currencyFormat' => $currency->format,
			'currencyBlank' => $currency->blank,
		));

		switch ((int)$this->step)
		{
			case -1:
				self::$smarty->display(_PS_THEME_DIR_.'shopping-cart.tpl');
				break;
			case 1:
				self::$smarty->display(_PS_THEME_DIR_.'shopping-cart.tpl');
				//$this->processAddressFormat();
				//self::$smarty->display(_PS_THEME_DIR_.'order-address.tpl');
				break;
			case 2:
				$this->processAddressFormat();
				self::$smarty->display(_PS_THEME_DIR_.'order-address.tpl');
				//self::$smarty->display(_PS_THEME_DIR_.'order-carrier.tpl');
				break;
			case 3:
				//self::$smarty->display(_PS_THEME_DIR_.'order-payment.tpl');
				break;
			default:
				self::$smarty->display(_PS_THEME_DIR_.'shopping-cart.tpl');
				break;
		}
	}

	public function displayFooter()
	{
		if (!Tools::getValue('ajax'))
			parent::displayFooter();
	}

	/* Order process controller */
	public function autoStep()
	{
		//global $isVirtualCart;

		if ($this->step >= 2 AND (!self::$cart->id_address_delivery OR !self::$cart->id_address_invoice))
			Tools::redirect('order.php?step=1');
		$delivery = new Address((int)(self::$cart->id_address_delivery));
		$invoice = new Address((int)(self::$cart->id_address_invoice));
		if ($delivery->deleted OR $invoice->deleted OR !$delivery->id  OR !$invoice->id)
		{
			if ($delivery->deleted)
				unset(self::$cart->id_address_delivery);
			if ($invoice->deleted)
				unset(self::$cart->id_address_invoice);
			Tools::redirect('order.php?step=2');
		}
		//elseif ($this->step >= 3 AND !self::$cart->id_carrier AND !$isVirtualCart)
			//Tools::redirect('order.php?step=2');
	}

	/* Order process controller */
	/*public function autoStep()
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
	}*/

	/*
	 * Manage address
	 */
	public function processAddress()
	{
		$id_address = Address::getFirstCustomerAddressId((int)(self::$cookie->id_customer));
		if(self::$cart->id_address_delivery == 0 || self::$cart->id_address_invoice == 0
		|| $id_address != self::$cart->id_address_delivery || $id_address != self::$cart->id_address_invoice)
		{
			if (!Tools::isSubmit('id_address_delivery') OR !Address::isCountryActiveById((int)Tools::getValue('id_address_delivery')))
				$this->errors[] = Tools::displayError('This address is not in a valid area.');
			else
			{
				self::$cart->id_address_delivery = (int)(Tools::getValue('id_address_delivery'));
				self::$cart->id_address_invoice = /*Tools::isSubmit('same') ? */self::$cart->id_address_delivery /*: (int)(Tools::getValue('id_address_invoice'))*/;
				if (!self::$cart->update())
					$this->errors[] = Tools::displayError('An error occurred while updating your cart.');

				//if (Tools::isSubmit('message'))
					//$this->_updateMessage(Tools::getValue('message'));
			}
		}
		if (sizeof($this->errors))
		{
			if (Tools::getValue('ajax'))
				die('{"hasError" : true, "errors" : ["'.implode('\',\'', $this->errors).'"]}');
			$this->step = 1;
		}
		if (Tools::getValue('ajax'))
			die(true);
	}

	/* Carrier step */
	protected function processCarrier()
	{
		global $orderTotal;

		parent::_processCarrier();

		if (sizeof($this->errors))
		{
			self::$smarty->assign('errors', $this->errors);
			$this->_assignCarrier();
			$this->step = 2;
			$this->displayContent();
			include(dirname(__FILE__).'/../footer.php');
			exit;
		}
		$orderTotal = self::$cart->getOrderTotal();
	}

	/* Address step */
	protected function _assignAddress()
	{
		parent::_assignAddress();

		self::$smarty->assign('cart', self::$cart);
		if (self::$cookie->is_guest)
			Tools::redirect('order.php?step=2');
	}

	/* Carrier step */
	protected function _assignCarrier()
	{
		global $defaultCountry;

		if (isset(self::$cookie->id_customer))
			$customer = new Customer((int)(self::$cookie->id_customer));
		else
			die(Tools::displayError('Fatal error: No customer'));
		// Assign carrier
		parent::_assignCarrier();
		// Assign wrapping and TOS
		//$this->_assignWrappingAndTOS();

		self::$smarty->assign('is_guest' ,(isset(self::$cookie->is_guest) ? self::$cookie->is_guest : 0));
	}

	/* Payment step */
	protected function _assignPayment()
	{
		global $orderTotal;

		// Redirect instead of displaying payment modules if any module are grefted on
		Hook::backBeforePayment('order.php?step=3');

		/* We may need to display an order summary */
		//self::$smarty->assign(self::$cart->getSummaryDetails());
		//require_once(dirname(__FILE__).'/../header.php');
		require_once(dirname(__FILE__).'/../modules/mediator/mediator.php');
		$mediator = new mediator();
//		$grp_id = Customer::getDefaultGroupId(self::$cookie->id_customer);

		/*$customer_group_ids = Customer::getGroupsStatic(self::$cookie->id_customer);
		$groups = Group::getGroups(self::$cookie->id_lang);
		foreach($groups AS $group)
		{	if($group['name'] == 'PaymentNew')
				$new_group = $group['id_group'];
			if($group['name'] == 'PaymentOld')
				$old_group = $group['id_group'];
		}

		//print_R($grp_id);exit;
		if(in_array($old_group, $customer_group_ids)) // normal flow for payment for one group of customers for tracking purpose.
			echo $mediator->displayPayment();
		elseif(in_array($new_group, $customer_group_ids) )
			echo $mediator->paymentOptions(); // payment option COD provided to another group of customers.
		elseif(in_array(1, $customer_group_ids) )
			echo $mediator->displayPayment();*/

		echo $mediator->paymentOptions();

		/*self::$smarty->assign(array(
			'total_price' => (float)($orderTotal),
			'taxes_enabled' => (int)(Configuration::get('PS_TAX'))
		));*/
		self::$cookie->checkedTOS = '1';

		parent::_assignPayment();
	}
}

