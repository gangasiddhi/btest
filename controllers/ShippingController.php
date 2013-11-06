<?php


class ShippingControllerCore extends FrontController
{
	public $auth = true;
	public $guestAllowed = true;
	public $php_self = 'shipping.php';
	public $authRedirection = 'addresses.php';
	public $ssl = true;

	protected $_address;

	public function preProcess()
	{
		parent::preProcess();

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
				/*if (Tools::isSubmit('delete'))
				{
					if (self::$cart->id_address_invoice == $this->_address->id)
						unset(self::$cart->id_address_invoice);
					if (self::$cart->id_address_delivery == $this->_address->id)
						unset(self::$cart->id_address_delivery);
					if ($this->_address->delete())
						Tools::redirect('addresses.php');
					$this->errors[] = Tools::displayError('This address cannot be deleted.');
				}*/
				self::$smarty->assign(array('address' => $this->_address, 'id_address' => (int)$id_address));
			}
			elseif (Tools::isSubmit('ajax'))
				exit;
			else
				Tools::redirect('addresses.php');
		}
		if (Tools::isSubmit('submitInfo'))
		{
			$address = new Address();
			$this->errors = $address->validateControler();
			//print_r($this->errors);exit;
			$address->id_customer = (int)(self::$cookie->id_customer);

			if (!Tools::getValue('phone') AND !Tools::getValue('phone_mobile'))
				$this->errors[] = Tools::displayError('You must register at least one phone number');
			elseif (!Validate::isPhoneNumber( Tools::getValue('phone')))
				$this->errors[] = Tools::displayError('Invalid Phone Number');
			if (!$country = new Country((int)$address->id_country) OR !Validate::isLoadedObject($country))
				die(Tools::displayError('Error: 201306051534'));
                                 if (strlen(Tools::getValue('phone')) <= 7){
                                   $address->phone = Tools::getValue('regioncode').Tools::getValue('phone');
                                }
                         else
                         {
                             $address->phone = Tools::getValue('phone');
                         }
			/* US customer: normalize the address */
			/*if ($address->id_country == Country::getByIso('US'))
			{
				include_once(_PS_TAASC_PATH_.'AddressStandardizationSolution.php');
				$normalize = new AddressStandardizationSolution;
				$address->address1 = $normalize->AddressLineStandardization($address->address1);
				$address->address2 = $normalize->AddressLineStandardization($address->address2);
			}*/

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
					if ((bool)(Tools::getValue('select_address', false)) == true OR (int)Tools::getValue('id_carrier') OR (Tools::isSubmit('ajax') AND Tools::getValue('type') == 'invoice'))
					{
						/* This new adress is for invoice_adress, select it */
						self::$cart->id_address_delivery = (int)($address->id);
						self::$cart->id_address_invoice = (int)($address->id);
						self::$cart->id_carrier = (int)Tools::getValue('id_carrier');
						self::$cart->update();
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
                                        if(Configuration::get('TWO_STEP_CHECKOUT'))
                                        {
                                             Tools::redirect('order.php?step=2');
                                        }
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
		Tools::addCSS(_THEME_CSS_DIR_.'shipping.css');
		Tools::addCSS(_THEME_CSS_DIR_.'order-steps.css');
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

                $statesList = '';
                if (isset($this->_address) AND isset($this->_address->id_state) AND !empty($this->_address->id_state) AND is_numeric($this->_address->id_state))
                     $selectedstate =  (int)$this->_address->id_state;
//                else
//                    $selectedstate =  1;
                 /*Two page check out*/
                 if(Configuration::get('TWO_STEP_CHECKOUT'))
                 {
                    self::$smarty->assign('two_page_checkout',2);
                    $statesList .= '<option value="">-</option>';
                    foreach ($countries AS $country)
                    {
                        if($country['contains_states'] == 1 && isset($country['states']))
                        {
                            foreach($country['states'] as $state)
                                $statesList .= '<option value="'.(int)($state['id_state']).'" '.(isset($selectedstate) ? ($state['id_state'] == $selectedstate ? 'selected="selected"' : ''): '').'>'.htmlentities($state['name'], ENT_COMPAT, 'UTF-8').'</option>';
                        }

                        self::$smarty->assign(array(
                                // 'two_page_checkout' =>  Tools::getValue('chkout'),
                                    'statesList' => $statesList));
                    }
                 }
//                  if (Validate::isPhoneNumber( Tools::getValue('phone') )){
//                       $phone_num = Tools::getValue('phone');
                         $phone_number = Tools::getValue('regioncode');
                       $phnum_regioncode = array(212,216,222,224,226,228,232,236,242,246,248,252,256,258,262,264,266,272,274,
                         276,282,284,286,288,312,318,322,324,326,328,332,338,342,344,346,348,352,354,356,358,362,364,366,368,370,372,374,376,378,380,382,384,386,388,412,414,416,422,424,426,428,432,434, 436,438,442,446,452,454,456,458,462,464,472,
                        474,476,478,482,484,486,488,501,505,506,507,530,531,532,533,534,535,536,537,538,539,540,541,542,543,544,545,546,547,548,549,551,552,553,554,555,559);
//                                      }
                   self::$smarty->assign(array(
                                'phnum_regioncode' => $phnum_regioncode));
                /*Two page check out*/
		//$provinces = Country::getProvinces();

		if ((Configuration::get('VATNUMBER_MANAGEMENT') AND file_exists(_PS_MODULE_DIR_.'vatnumber/vatnumber.php')) && VatNumber::isApplicable(Configuration::get('PS_COUNTRY_DEFAULT')))
			self::$smarty->assign('vat_display', 2);
		elseif (Configuration::get('VATNUMBER_MANAGEMENT'))
			self::$smarty->assign('vat_display', 1);
		else
			self::$smarty->assign('vat_display', 0);

		self::$smarty->assign('ajaxurl', _MODULE_DIR_);

		self::$smarty->assign('vatnumber_ajax_call', (int)file_exists(_PS_MODULE_DIR_.'vatnumber/ajax.php'));

		$customer = new Customer((int)(self::$cookie->id_customer));
		$id_zone = Country::getIdZone((int)Configuration::get('PS_COUNTRY_DEFAULT'));
//		echo 'vib'.Country::getIdZone((int)Configuration::get('PS_COUNTRY_DEFAULT'));
//		echo (int)Configuration::get('PS_COUNTRY_DEFAULT');
//		exit;
		$carriers = Carrier::getCarriersForOrder($id_zone, $customer->getGroups());
//		print_r($carriers);
//		echo 'vib'.$this->_setDefaultCarrierSelection($carriers);	exit;
//		self::$smarty->assign('checked' , $this->_setDefaultCarrierSelection($carriers));
		self::$smarty->assign(array(
			'checked' => $this->_setDefaultCarrierSelection($carriers),
			'carriers' => $carriers,
			'default_carrier' => (int)(Configuration::get('PS_CARRIER_DEFAULT'))
		));

		self::$smarty->assign(array(
			'countries_list' => $countriesList,
			'countries' => $countries,
			'errors' => $this->errors,
			'token' => Tools::getToken(false),
			'select_address' => 1//(int)(Tools::getValue('select_address'))
			//'states' => $provinces
		));
	}

	protected function _setDefaultCarrierSelection($carriers)
	{
		if (sizeof($carriers))
		{
			$defaultCarrierIsPresent = false;
			if ((int)self::$cart->id_carrier != 0)
				foreach ($carriers AS $carrier)
					if ($carrier['id_carrier'] == (int)self::$cart->id_carrier){
						$id_carrier = (int)$carrier['id_carrier'];
						$defaultCarrierIsPresent = true;
					}
			if (!$defaultCarrierIsPresent)
				foreach ($carriers AS $carrier)
					if ($carrier['id_carrier'] == (int)Configuration::get('PS_CARRIER_DEFAULT'))
					{
						$defaultCarrierIsPresent = true;
						$id_carrier = (int)$carrier['id_carrier'];
					}
			if (!$defaultCarrierIsPresent)
				$id_carrier = (int)$carriers[0]['id_carrier'];
		}
		else
			$id_carrier = 0;
		//if (self::$cart->update())
			return $id_carrier;
		return 0;
	}


	protected function _processAddressFormat()
	{

		$id_country = is_null($this->_address)? 0 : (int)$this->_address->id_country;

		$dlv_adr_fields = AddressFormat::getOrderedAddressFields($id_country, true, true);
		self::$smarty->assign('ordered_adr_fields', $dlv_adr_fields);
	}

	public function displayHeader()
	{
               if(!Configuration::get('TWO_STEP_CHECKOUT'))
		if (Tools::getValue('ajax') != 'true')
			parent::displayHeader();
	}

	public function displayContent()
	{
		parent::displayContent();

		$this->_processAddressFormat();
		self::$smarty->display(_PS_THEME_DIR_.'shipping.tpl');
	}

	public function displayFooter()
	{
               if(!Configuration::get('TWO_STEP_CHECKOUT'))
		if (Tools::getValue('ajax') != 'true')
			parent::displayFooter();
	}
}

