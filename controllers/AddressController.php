<?php

if (Configuration::get('VATNUMBER_MANAGEMENT') AND file_exists(_PS_MODULE_DIR_.'vatnumber/vatnumber.php'))
	include_once(_PS_MODULE_DIR_.'vatnumber/vatnumber.php');

class AddressControllerCore extends FrontController
{
	public $auth = true;
	public $guestAllowed = true;
	public $php_self = 'address.php';
	public $authRedirection = 'addresses.php';
	public $ssl = true;

	protected $_address;

    private $_smartyValues = array();

    public function init()
    {
        $loginAfterBasketDisabled = Configuration::get('PS_LOGIN_AFTER_BASKET_DISABLED');

        //prevent login redirection if PS_LOG_AFTER_BASKET_DISABLED SET
        if ($loginAfterBasketDisabled) {
            $chkout = Tools::getValue('chkout');
            $selectAddress = Tools::getValue('select_address');
            $isSubmitted = Tools::isSubmit('submitAddress');

            if ($selectAddress == "1" && !$isLogged && ($chkout == "2" || $isSubmitted)) {
                if (!$isSubmitted) {
                    $this->auth = false;
                } else {
                    Configuration::set('PS_TOKEN_ENABLE', 0);
                }
            }

            //smarty object not initialized on init
            //so I've set it on a private varaible
            //and add these values to smarty on preProcess
            $this->_smartyValues['loginAfterBasketDisabled'] = $loginAfterBasketDisabled;
        }

        parent::init();
    }



	public function preProcess()
	{
        parent::preProcess();

        //assign smarty values which are defined on init()
        foreach($this->_smartyValues as $k => $v) {
            self::$smarty->assign($k, $v);
        }

        $isLogged = (self::$cookie && self::$cookie->logged);
	self::$smarty->assign("_userLogged", $isLogged);

		if ($back = Tools::getValue('back'))
			self::$smarty->assign('back', Tools::safeOutput($back));
		if ($mod = Tools::getValue('mod'))
			self::$smarty->assign('mod', Tools::safeOutput($mod));

		if (Tools::isSubmit('ajax') AND Tools::isSubmit('type'))
		{
			if (Tools::getValue('type') == 'delivery')
				$id_address = isset(self::$cart->id_address_delivery) ? (int)self::$cart->id_address_delivery : 0;
			elseif (Tools::getValue('type') == 'invoice')
				$id_address = (isset(self::$cart->id_address_invoice) AND self::$cart->id_address_invoice != self::$cart->id_address_delivery) ? (int)self::$cart->id_address_invoice : 0;
			else
				exit;
		}
		else
			$id_address = (int)Tools::getValue('id_address', 0);

		if ($id_address)
		{
			$this->_address = new Address((int)$id_address);
			if (Validate::isLoadedObject($this->_address) AND Customer::customerHasAddress((int)(self::$cookie->id_customer), (int)($id_address)))
			{
				if (Tools::isSubmit('delete'))
				{
					if (self::$cart->id_address_invoice == $this->_address->id)
						unset(self::$cart->id_address_invoice);
					if (self::$cart->id_address_delivery == $this->_address->id)
						unset(self::$cart->id_address_delivery);
					if ($this->_address->delete())
					{
                                            if(Tools::getIsset('chkout') && Tools::getValue('chkout') == 2)
                                                Tools::redirect('order.php?step=2');
                                            Tools::redirect('addresses.php');
                                        }
					$this->errors[] = Tools::displayError('This address cannot be deleted.');
				}
				self::$smarty->assign(array('address' => $this->_address, 'id_address' => (int)$id_address));
			}
			elseif (Tools::isSubmit('ajax'))
				exit;
			else
				Tools::redirect('addresses.php');
		}

		if (Tools::isSubmit('cancelAddress'))
		{
                    if(Tools::getIsset('backchkout2') && Tools::getValue('backchkout2'))
                            Tools::redirect('order.php?step=2');
			Tools::redirect($back ? ($mod ? $back.'&back='.$mod : $back) : 'addresses.php');
		}
		if (Tools::isSubmit('submitAddress'))
		{
			$address = new Address();
                            $this->errors = $address->validateControler();
			$address->id_customer = (int)(self::$cookie->id_customer);

			if (!Tools::getValue('phone') AND !Tools::getValue('phone_mobile'))
				$this->errors[] = Tools::displayError('You must register at least one phone number');
			elseif (!Validate::isPhoneNumber( Tools::getValue('phone') ))
				$this->errors[] = Tools::displayError('Invalid Phone Number');
			if (!$country = new Country((int)$address->id_country) OR !Validate::isLoadedObject($country))
				die(Tools::displayError('Error: 201306051529'));
                                if (strlen(Tools::getValue('phone')) <= 7){
                                   $address->phone = Tools::getValue('regioncode').Tools::getValue('phone');
                                }
                         else
                         {
                             $address->phone = Tools::getValue('phone');
                         }
			/* US customer: normalize the address */
			if ($address->id_country == Country::getByIso('US'))
			{
				include_once(_PS_TAASC_PATH_.'AddressStandardizationSolution.php');
				$normalize = new AddressStandardizationSolution;
				$address->address1 = $normalize->AddressLineStandardization($address->address1);
				$address->address2 = $normalize->AddressLineStandardization($address->address2);
			}

			$zip_code_format = $country->zip_code_format;
			if ($country->need_zip_code)
			{
				if (($postcode = Tools::getValue('postcode')) AND $zip_code_format)
				{
					$zip_regexp = '/^'.$zip_code_format.'$/ui';
					$zip_regexp = str_replace(' ', '( |)', $zip_regexp);
					$zip_regexp = str_replace('-', '(-|)', $zip_regexp);
					$zip_regexp = str_replace('N', '[0-9]', $zip_regexp);
					$zip_regexp = str_replace('L', '[a-zA-Z]', $zip_regexp);
					$zip_regexp = str_replace('C', $country->iso_code, $zip_regexp);
					if (!preg_match($zip_regexp, $postcode))
						$this->errors[] = '<strong>'.Tools::displayError('Zip/ Postal code').'</strong> '.Tools::displayError('is invalid.').'<br />'.Tools::displayError('Must be typed as follows:').' '.str_replace('C', $country->iso_code, str_replace('N', '0', str_replace('L', 'A', $zip_code_format)));
				}
				elseif ($zip_code_format)
					$this->errors[] = '<strong>'.Tools::displayError('Zip/ Postal code').'</strong> '.Tools::displayError('is required.');
				elseif ($postcode AND !preg_match('/^[0-9a-zA-Z -]{4,9}$/ui', $postcode))
						$this->errors[] = '<strong>'.Tools::displayError('Zip/ Postal code').'</strong> '.Tools::displayError('is invalid.').'<br />'.Tools::displayError('Must be typed as follows:').' '.str_replace('C', $country->iso_code, str_replace('N', '0', str_replace('L', 'A', $zip_code_format)));
			}
			if ($country->isNeedDni() AND (!Tools::getValue('dni') OR !Validate::isDniLite(Tools::getValue('dni'))))
				$this->errors[] = Tools::displayError('Identification number is incorrect or has already been used.');
			elseif (!$country->isNeedDni())
				$address->dni = NULL;
			if (Configuration::get('PS_TOKEN_ENABLE') == 1 AND
				strcmp(Tools::getToken(false), Tools::getValue('token')) AND
				self::$cookie->isLogged(true) === true)
				$this->errors[] = Tools::displayError('Invalid token');

			if ((int)($country->contains_states) AND !(int)($address->id_state))
				$this->errors[] = Tools::displayError('This country requires a state selection.');

			if (!sizeof($this->errors))
			{
				if (isset($id_address))
				{
					$country = new Country((int)($address->id_country));
					if (Validate::isLoadedObject($country) AND !$country->contains_states)
						$address->id_state = 0;
					$address_old = new Address((int)$id_address);
					if (Validate::isLoadedObject($address_old) AND Customer::customerHasAddress((int)self::$cookie->id_customer, (int)$address_old->id))
					{
						if ($address_old->isUsed())
						{
							$address_old->delete();
							if (!Tools::isSubmit('ajax'))
							{
								$to_update = false;
								if (self::$cart->id_address_invoice == $address_old->id)
								{
									$to_update = true;
									self::$cart->id_address_invoice = 0;
								}
								if (self::$cart->id_address_delivery == $address_old->id)
								{
									$to_update = true;
									self::$cart->id_address_delivery = 0;
								}
								if ($to_update)
									self::$cart->update();
							}
						}
						else
						{
							$address->id = (int)($address_old->id);
							$address->date_add = $address_old->date_add;
						}
					}
				}
				elseif (self::$cookie->is_guest)
					Tools::redirect('addresses.php');

				if ($result = $address->save())
				{
					/* In order to select this new address : order-address.tpl */
					if (/*(bool)(Tools::getValue('select_address', false)) == true*/ (Tools::getIsset('select_address') AND Tools::getValue('select_address')) OR (Tools::isSubmit('ajax') AND Tools::getValue('type') == 'invoice'))
					{
                                                if((Tools::getValue('select_address')) == 1)
                                                {
                                                    self::$cart->id_address_delivery = (int)($address->id);
                                                    self::$cart->id_address_invoice = (int)($address->id);
                                                    self::$cart->update();
                                                }
                                                elseif((Tools::getValue('select_address')) == 2)
                                                {
                                                   /* This new adress is for invoice_adress, select it */
                                                   self::$cart->id_address_invoice = (int)($address->id);
                                                   self::$cart->update();
                                                }
					}
					if (Tools::isSubmit('ajax'))
					{
						$return = array(
							'hasError' => !empty($this->errors),
							'errors' => $this->errors,
							'id_address_delivery' => self::$cart->id_address_delivery,
							'id_address_invoice' => self::$cart->id_address_invoice
						);
						die(Tools::jsonEncode($return));
					}

                                        if(Tools::getIsset('backchkout2') && Tools::getValue('backchkout2'))
                                             Tools::redirect('order.php?step=2');
					Tools::redirect($back ? ($mod ? $back.'&back='.$mod : $back) : 'addresses.php');
				}
				$this->errors[] = Tools::displayError('An error occurred while updating your address/cannot redirect.');
			}
			else
				self::$smarty->assign(
				'errors', $this->errors);
		}
		elseif (!$id_address)
		{
			$customer = new Customer((int)(self::$cookie->id_customer));
			if (Validate::isLoadedObject($customer))
			{
				$_POST['firstname'] = $customer->firstname;
				$_POST['lastname'] = $customer->lastname;
			}
		}
		if (Tools::isSubmit('ajax') AND sizeof($this->errors))
		{
			$return = array(
				'hasError' => !empty($this->errors),
				'errors' => $this->errors
			);
			die(Tools::jsonEncode($return));
		}
	}

	public function setMedia()
	{
		parent::setMedia();
		Tools::addCSS(_THEME_CSS_DIR_.'addresses.css');
		Tools::addCSS(_THEME_CSS_DIR_.'my-acc-sidebar.css');
		Tools::addJS(_THEME_JS_DIR_.'tools/statesManagement.js');
	}

	public function process()
	{
		parent::process();

		/* Secure restriction for guest */
		if (self::$cookie->is_guest)
			Tools::redirect('addresses.php');

		if (Tools::isSubmit('id_country') AND Tools::getValue('id_country') != NULL AND is_numeric(Tools::getValue('id_country')))
			$selectedCountry = (int)Tools::getValue('id_country');
		elseif (isset($this->_address) AND isset($this->_address->id_country) AND !empty($this->_address->id_country) AND is_numeric($this->_address->id_country))
			$selectedCountry = (int)$this->_address->id_country;
		elseif (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
		{
			$array = preg_split('/,|-/', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
			if (!Validate::isLanguageIsoCode($array[0]) OR !($selectedCountry = Country::getByIso($array[0])))
				$selectedCountry = (int)Configuration::get('PS_COUNTRY_DEFAULT');
		}
		else
			$selectedCountry = (int)Configuration::get('PS_COUNTRY_DEFAULT');

		$countries = Country::getCountries((int)self::$cookie->id_lang, true);
		$countriesList = '';
		foreach ($countries AS $country)
			$countriesList .= '<option value="'.(int)($country['id_country']).'" '.($country['id_country'] == $selectedCountry ? 'selected="selected"' : '').'>'.htmlentities($country['name'], ENT_COMPAT, 'UTF-8').'</option>';

		$provinces = Country::getProvinces();

		if ((Configuration::get('VATNUMBER_MANAGEMENT') AND file_exists(_PS_MODULE_DIR_.'vatnumber/vatnumber.php')) && VatNumber::isApplicable(Configuration::get('PS_COUNTRY_DEFAULT')))
			self::$smarty->assign('vat_display', 2);
		elseif (Configuration::get('VATNUMBER_MANAGEMENT'))
			self::$smarty->assign('vat_display', 1);
		else
			self::$smarty->assign('vat_display', 0);

		self::$smarty->assign('ajaxurl', _MODULE_DIR_);

		self::$smarty->assign('vatnumber_ajax_call', (int)file_exists(_PS_MODULE_DIR_.'vatnumber/ajax.php'));

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
                                'two_page_chkout_no_add' =>  Tools::getValue('no_add'),
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

	protected function _processAddressFormat()
	{

		$id_country = is_null($this->_address)? 0 : (int)$this->_address->id_country;

		$dlv_adr_fields = AddressFormat::getOrderedAddressFields($id_country, true, true);
		self::$smarty->assign('ordered_adr_fields', $dlv_adr_fields);
	}

	public function displayHeader()
	{
            if(!Tools::getIsset('chkout'))
		if (Tools::getValue('ajax') != 'true')
			parent::displayHeader();
	}

	public function displayContent()
	{
		parent::displayContent();

		$this->_processAddressFormat();
		self::$smarty->display(_PS_THEME_DIR_.'address.tpl');
	}

	public function displayFooter()
	{
            if(!Tools::getIsset('chkout'))
		if (Tools::getValue('ajax') != 'true')
			parent::displayFooter();
	}
}

