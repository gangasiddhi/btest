<?php

class AddressesControllerCore extends FrontController
{
	public $auth = true;
	public $php_self = 'addresses.php';
	public $authRedirection = 'addresses.php';
	public $ssl = true;

	public function setMedia()
	{
		parent::setMedia();
		Tools::addCSS(_THEME_CSS_DIR_.'addresses.css');
		Tools::addCSS(_THEME_CSS_DIR_.'my-acc-sidebar.css');
		Tools::addJS(_THEME_JS_DIR_.'tools.js');
	}

	public function process()
	{
		parent::process();

		$multipleAddressesFormated = array();
		$ordered_fields = array();
		$customer = new Customer((int)(self::$cookie->id_customer));

		if (!Validate::isLoadedObject($customer))
			die(Tools::displayError('Customer not found'));

		// Retro Compatibility Theme < 1.4.1
		self::$smarty->assign('addresses', $customer->getAddresses((int)(self::$cookie->id_lang)));

		$customerAddressesDetailed = $customer->getAddresses((int)(self::$cookie->id_lang));

		$total = 0;
		foreach($customerAddressesDetailed as $addressDetailed)
		{
			$address = new Address($addressDetailed['id_address']);

			$multipleAddressesFormated[$total] = AddressFormat::getFormattedLayoutData($address);
			unset($address);
			++$total;

			// Retro theme < 1.4.2
			$ordered_fields = AddressFormat::getOrderedAddressFields($addressDetailed['id_country'], false, true);
		}

		// Retro theme 1.4.2
    if (($key = array_search('Country:name', $ordered_fields)))
       $ordered_fields[$key] = 'country';

		self::$smarty->assign('addresses_style', array(
								'company' => 'address_company'
								,'vat_number' => 'address_company'
								,'firstname' => 'address_name'
								,'lastname' => 'address_name'
								,'address1' => 'address_address1'
								,'address2' => 'address_address2'
								,'city' => 'address_city'
								,'country' => 'address_country'
								,'phone' => 'address_phone'
								,'phone_mobile' => 'address_phone_mobile'
								,'alias' => 'address_title'
							));

		self::$smarty->assign(array(
			'multipleAddresses' => $multipleAddressesFormated,
			'ordered_fields' => $ordered_fields));
		unset($customer);
	}

	public function displayContent()
	{
		parent::displayContent();
		self::$smarty->display(_PS_THEME_DIR_.'addresses.tpl');
	}
}

