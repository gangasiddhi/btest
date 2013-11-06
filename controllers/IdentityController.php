<?php

class IdentityControllerCore extends FrontController
{
	protected $birthdayConfirmation = 0;

	public function __construct()
	{
		$this->auth = true;
		$this->php_self = 'identity.php';
		$this->authRedirection = 'identity.php';
		$this->ssl = true;

		parent::__construct();

	}

	public function preProcess()
	{
		parent::preProcess();

		$customer = new Customer((int)(self::$cookie->id_customer));

		if (Module::isInstalled('sailthru') AND
			Tools::getValue('utm_source') == 'Sailthru' AND
			substr(Tools::getValue('utm_campaign'),0,8) == 'Birthday') {
			setcookie('bfd',Tools::getValue('utm_content'),time() + 3600 * 24 * 1, '/', Configuration::get('PS_SHOP_DOMAIN'));
				if($customer->birthday != '' || $customer->birthday != null){
						setcookie('bfd','', time()-3600, '/', Configuration::get('PS_SHOP_DOMAIN'));
						Tools::redirect(Tools::getValue('utm_content'));
				}
		}

		if (Tools::isSubmit('submitIdentity')) {
			$bday_day = Tools::getValue('birthDate');
			$bday_month = Tools::getValue('birthMonth');
			$bday_year = 2000;

			if (sizeof($_POST))	{
				$exclusion = array('id_gender', 'secure_key', 'old_passwd', 'passwd', 'active', 'date_add', 'date_upd', 'last_passwd_gen', 'birthday', 'note', 'category_name', 'age', 'dress_size', 'ip_registration_newsletter', 'newsletter_date_add', 'showroom_seen', 'id_default_group');
				$fields = $customer->getFields();

				foreach ($fields AS $key => $value) {
					if (! in_array($key, $exclusion)) {
						$customer->{$key} = key_exists($key, $_POST) ? trim($_POST[$key]) : 0;
					}
				}

				if (! empty($bday_day) AND ! empty($bday_month)) {
					$customer->birthday = sprintf('%s-%s-%s', $bday_year, $bday_month, $bday_day);
				}
			}

			if ($_POST['passwd'] != $_POST['confirmation']) {
				$this->errors[] = Tools::displayError('Password and confirmation do not match');
			} else {
				$prev_id_default_group = $customer->id_default_group;
				$this->errors = $customer->validateControler();
			}

			if (! sizeof($this->errors)) {
				$customer->id_default_group = (int)($prev_id_default_group);

				if (Tools::getValue('passwd')) {
					self::$cookie->passwd = $customer->passwd;
				}

				if ($customer->update()) {
					self::$cookie->customer_lastname = $customer->lastname;
					self::$cookie->customer_firstname = $customer->firstname;
					self::$smarty->assign('confirmation', 1);
				} else {
					$this->errors[] = Tools::displayError('Cannot update information');
				}
			}
		} else if (Tools::isSubmit('submitBirthday')) {
			$birthDate =  Tools::getValue('birthDate');
			$birthMonth =  Tools::getValue('birthMonth');
			$birthday = '2000'.'-'.$birthMonth.'-'.$birthDate;
			$customer->birthday = $birthday;

			if (! $customer->update()) {
				$this->errors[] = Tools::displayError('Cannot update information');
			} else {
				/* Sending the cart details to the Sailthru */
				Module::hookExec('updateCustomerDetails');
				$this->birthdayConfirmation = 1;

				self::$smarty->assign(array(
					'birthdayRedirectUrl' => isset($_COOKIE['bfd']) && $_COOKIE['bfd'] ? $_COOKIE['bfd'] : 'showroom.php',
					'birthdayConfirmation' => 1,
					'bitrhdayCookieDomain' => Configuration::get('PS_SHOP_DOMAIN')
				));
			}
		}
		else
			$_POST = array_map('stripslashes', $customer->getFields());

		$discounts = Discount::getDiscountIdsByType((int)(self::$cookie->id_lang),(int)(self::$cookie->id_customer), _PS_OS_CREDIT_ID_TYPE_ , true, false ,true);
		$credits = count($discounts);
		$current_lang = Language::getLanguage(self::$cookie->id_lang);

		if ($current_lang['iso_code'] == 'tr')
			self::$smarty->assign('months', array('Ocak','Şubat','Mart','Nisan','Mayıs','Haziran','Temmuz','Ağustos','Eylül','Ekim','Kasım','Aralık'));
		else
			self::$smarty->assign('months', Tools::dateMonths());

		/* Generate years, months and days */
		self::$smarty->assign(array(
			'customer' => $customer,
			'birthday' => (! empty($customer->birthday) ? getdate(strtotime($customer->birthday)) : null),
			'years' => Tools::dateYears(),
			'days' => Tools::dateDays(),
			'credits' => $credits,
			'errors' => $this->errors
		));
	}

	public function setMedia()
	{
		parent::setMedia();

		if ((Module::isInstalled('sailthru') AND
			Tools::getValue('utm_source') == 'Sailthru' AND
			substr(Tools::getValue('utm_campaign'),0,8) == 'Birthday') OR $this->birthdayConfirmation == 1 ) {
				Tools::addCSS(_THEME_CSS_DIR_.'birthday-campaign.css');
		} else {
			Tools::addCSS(_THEME_CSS_DIR_.'identity.css');
            Tools::addCSS(_THEME_CSS_DIR_.'my-acc-sidebar.css');
		}

		Tools::addJS(_THEME_JS_DIR_ . 'utils.js');
	}

	public function displayContent()
	{
		parent::displayContent();

		if ((Module::isInstalled('sailthru') AND
			Tools::getValue('utm_source') == 'Sailthru' AND
				substr(Tools::getValue('utm_campaign'),0,8) == 'Birthday') OR $this->birthdayConfirmation == 1){
				if($this->birthdayConfirmation == 1){
					$this->birthdayConfirmation = 0;
					setcookie('bfd','', time()-3600, '/', Configuration::get('PS_SHOP_DOMAIN'));
				}
				self::$smarty->display(_PS_THEME_DIR_.'birthday-campaign.tpl');
		}
        else{
			self::$smarty->display(_PS_THEME_DIR_.'identity.tpl');
		}

	}
}
