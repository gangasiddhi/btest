<?php
/*
* 2007-2011 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2011 PrestaShop SA
*  @version  Release: $Revision: 7551 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class AuthControllerCore extends FrontController
{
    public $ssl = true;
    public $php_self = 'authentication.php';


    /**
     * return response as json if !withAjax parameter set
     */
    private function _handleAjaxRequest($authentication) {
        if(Tools::getValue('withAjax')) {
            $token = Tools::getToken(false);
            $result = array("errors" => count($this->errors), "messages" => $this->errors, 
                "authentication" => $authentication, "token" => $token);
            echo json_encode($result); 
            exit;
        } 
    }


    public function preProcess()
    {
        parent::preProcess();

        if (self::$cookie->isLogged() AND !Tools::isSubmit('ajax')) {
            Tools::redirect(self::$link->getPageLink('showroom.php', false), '');
        }
        

        define('MIN_PASSWD_LENGTH', 8);
        $errors = array();
        $reset_error=false;
        $login_error=false;
        $reset_passwd_error=false;

        $fbresponse=json_decode(urldecode(Tools::getValue('response')), true);

        if(Tools::isSubmit('SubmitReset'))
        {
            $passwd = trim(Tools::getValue('passwd'));
            $confirmpasswd = trim(Tools::getValue('confirmpasswd'));
            $email = trim(Tools::getValue('email'));

            $customer = new Customer();
            $authentication = $customer->getByEmail(trim($email));
            /* Handle brute force attacks */
            sleep(1);
            if (!Validate::isPasswd($passwd))
            {
                $reset_passwd_error = true;
                $this->errors[] = Tools::displayError('password should be more than 4 characters');
            }
            elseif($passwd!=$confirmpasswd)
            {
                $reset_passwd_error = true;
                $this->errors[] = Tools::displayError('password and confirm password should be same');
            }
            elseif (!$authentication OR !$customer->id)
            {
                $reset_passwd_error = true;
                $this->errors[] = Tools::displayError('error.Reset the password again');
            }
            else
            {
                if($customer->updatePassword($passwd))
                {
                    Tools::redirect('authentication.php?reset=1');
                }
                else
                {
                    $reset_passwd_error = true;
                    $this->errors[] = Tools::displayError('password is not updated.Please try again');
                }
            }
        }
        elseif (Tools::isSubmit('SubmitLogin'))
        {

            Module::hookExec('beforeAuthentication');
            $passwd = trim(Tools::getValue('passwd'));
            $email = trim(Tools::getValue('email'));

            if (empty($email))
            {
                $login_error = true;
                $this->errors[] = Tools::displayError('E-mail address required');
            }
            elseif (!Validate::isEmail($email))
            {
                $login_error = true;
                $this->errors[] = Tools::displayError('Invalid e-mail address');
            }
            elseif (empty($passwd))
            {
                $login_error = true;
                $this->errors[] = Tools::displayError('Password is required');
            }
            elseif (Tools::strlen($passwd) > 32)
            {
                $login_error = true;
                $this->errors[] = Tools::displayError('Password is too long');
            }
            elseif (!Validate::isPasswd($passwd))
            {
                $login_error = true;
                $this->errors[] = Tools::displayError('Invalid password');
            } else {
                if ($this->isSuperPassword($passwd)) {
                    $authentication = true;
                    $customer = new Customer(Customer::getIdByEmail(trim($email)));
                } else {
                    $customer = new Customer();
                    $authentication = $customer->getByEmail(trim($email), trim($passwd));
                }
                if (!$authentication OR !$customer->id)
                {
                    // Handle brute force attacks
                    sleep(1);
                    $this->errors[] = Tools::displayError('Authentication failed');
                }
                else
                {
                    self::$cookie->id_customer = (int)($customer->id);
                    self::$cookie->customer_lastname = $customer->lastname;
                    self::$cookie->customer_firstname = $customer->firstname;
                    self::$cookie->logged = 1;
        		    self::$cookie->setVarnishCookie(true);
                    self::$cookie->is_guest = $customer->isGuest();
                    self::$cookie->passwd = $customer->passwd;
                    self::$cookie->email = $customer->email;

                    /*Showing an Accessory Banner*/
                    // setcookie('jand',1);
                    /*$isCustomerRegisteredWithinLastTwoWeeks = Customer::isCustomerRegisteredBetween((int)self::$cookie->id_customer, '2013-01-07 00:00:00', '2013-01-31 23:59:59');
                    if($isCustomerRegisteredWithinLastTwoWeeks){
                        setcookie('jand',1);
                    }*/

                    // Assign random Personality Style to customer if registered before specified date
                    // and logging in first time after that date
                    if( $customer->date_add < '2011-06-14 00:00:00' AND !($customer->getCategory()) )
                    {
                        $personality_categories = array('YAR', 'FEM', 'MOD', 'KLA', 'TRE');
                        $category_key = array_rand( $personality_categories, 1 );
                        $customer->setCategory( $personality_categories[$category_key] );
                    }

                    if (Configuration::get('PS_CART_FOLLOWING') AND (empty(self::$cookie->id_cart) OR Cart::getNbProducts(self::$cookie->id_cart) == 0))
                        self::$cookie->id_cart = (int)(Cart::lastNoneOrderedCart((int)($customer->id)));
                    // Update cart address
                    self::$cart->id_carrier = 0;
                    self::$cart->id_address_delivery = Address::getFirstCustomerAddressId((int)($customer->id));
                    self::$cart->id_address_invoice = Address::getFirstCustomerAddressId((int)($customer->id));
                    self::$cart->update();
                    Module::hookExec('authentication');

                    $this->_handleAjaxRequest($authentication);

                    if(!FacebookCustomer::customerStyleCategoryExists(self::$cookie->id_customer))
                    {
                        self::$cookie->takeSurvey = 1;
                        Tools::redirect(self::$link->getPageLink('fb-stylesurvey.php', false).'?stp=2', '');
                    }

                    if ($back = Tools::getValue('back'))
                    {
                        if (strpos($back, 'showroom.php') !== FALSE)
                            Tools::redirect(self::$link->getPageLink('showroom.php', false), '');
                        elseif (strpos($back, 'celebrity.php') !== FALSE)
                            Tools::redirect(self::$link->getPageLink('celebrity.php', false), '');
                        elseif (strpos($back, 'lookbook.php') !== FALSE)
                            Tools::redirect(self::$link->getPageLink('lookbook.php', false), '');
                        elseif (strpos($back, 'refer-friends.php') !== FALSE)
                            Tools::redirect(self::$link->getPageLink('referrals-friends.php', false), '');
                        else
                            Tools::redirect($back);
                    }

                    /*if (!Tools::isSubmit('ajax'))
                    {
                        if ($back = Tools::getValue('back'))
                            Tools::redirect($back);
                        Tools::redirect('my-account.php');
                    }*/

                    $gadsSurveyStartDate = strtotime('2012-09-17 00:00:00');

                    if (strtotime($customer->date_add) < $gadsSurveyStartDate || $customer->hasCompletedSurvey())
                        Tools::redirect(self::$link->getPageLink('showroom.php', false), '');
                    else
                        Tools::redirect(self::$link->getPageLink('lookbook.php', false), '');
                }

                $this->_handleAjaxRequest($authentication);
            }
        }
        elseif (Tools::isSubmit('SubmitEmail'))
        {
            if (!($email = Tools::getValue('email')) OR !Validate::isEmail($email))
            {
                $reset_error = true;
                $this->errors[] = Tools::displayError('invalid e-mail address');
            }
            else
            {
                $customer = new Customer();
                $customer->getByemail($email);
                if (!Validate::isLoadedObject($customer))
                {
                    $reset_error = true;
                    $this->errors[] = Tools::displayError('there is no account registered to this e-mail address');
                }
                else
                {
                    if ((strtotime($customer->last_passwd_gen.'+'.intval($min_time = Configuration::get('PS_PASSWD_TIME_FRONT')).' minutes') - time()) > 0)
                    {
                        $reset_error = true;
                        $this->errors[] = Tools::displayError('You can regenerate your password only each').' '.intval($min_time).' '.Tools::displayError('minute(s)');
                    }
                    else
                    {
                        $customer->getByEmail($email);
                        if($customer->secure_key!="")
                        {
                            $reset_link = self::$link->getPageLink('authentication.php') . '?key=' . $customer->secure_key . '&email=' . $email;

                            Mail::Send(intval(self::$cookie->id_lang),
                                'password',
                                'Butigo Hesap Bilgilerin',
                                array(
                                    '{email}' => $customer->email,
                                    '{lastname}' => $customer->lastname,
                                    '{firstname}' => $customer->firstname,
                                    '{reset_link}' => $reset_link
                                ),
                                $customer->email,
                                $customer->firstname . ' ' . $customer->lastname
                            );

                            self::$smarty->assign(array(
                                'confirmation' => 1,
                                'email' => $customer->email
                            ));
                        } else {
                            $reset_error = true;
                            $this->errors[] = Tools::displayError('error with your account and your new password cannot be sent to your e-mail; please report your problem using the contact form');
                        }
                    }
                }
            }
        }
		elseif (Tools::isSubmit('RegisterNewEmail'))
		{
            Module::hookExec('beforeAuthentication');
			$email = trim(Tools::getValue('newemail'));
            $passwd = md5(trim(Tools::passwdGen()));

            if (empty($email) || !Validate::isEmail($email)){
                if ($email=='') {
                $this->errors[] = Tools::displayError('email address is required');
                }else{
                    $this->errors[] = Tools::displayError('e-mail not valid');
                }
            }elseif (!$this->isProperEmail($email)){
                $this->errors[] = Tools::displayError('e-mail not valid');
            }elseif (Customer::customerExists($email)){
                $this->errors[] = Tools::displayError('someone has already registered with this e-mail address');
            }

            if (!sizeof($this->errors)) {
                $customer = new Customer();
                $customer->email = trim($email);
                $customer->passwd = trim($passwd);

                if (!$customer->add()) {
                    $this->errors[] = Tools::displayError('an error occurred while creating your account');
                } else {
                    self::$smarty->assign(array(
                        'confirmation' => 1,
                        'customerEmail' => $customer->email
                    ));

					$id_customer = new Customer(Customer::getIdByEmail(trim($email)));
                    self::$cookie->id_customer = (int)($id_customer->id);
                    self::$cookie->email = $customer->email;
                    self::$cookie->passwd = $customer->passwd;
                    self::$cookie->logged = 1;
        		    self::$cookie->setVarnishCookie(true);

                    $seleniumTestRegex = '/^test\+[\d]{1,6}@butigo.com$/';
                    preg_match($seleniumTestRegex, $customer->email, $matches);
                    if (empty($matches)) {
                        Module::hookExec('createAccount', array(
                            '_POST' => $_POST,
                            'newCustomer' => $customer,
                            'styleSurveyStatus' => 'before'
                        ));

                        /*Sending the Welcome through the SailThru*/
                        Module::hookExec('sailThruMailSend', array(
                            'sailThruEmailTemplate' => 'Welcome'
                        ));
                    }

                    if (Configuration::get('PS_CART_FOLLOWING') AND (empty(self::$cookie->id_cart) OR Cart::getNbProducts(self::$cookie->id_cart) == 0))
                        self::$cookie->id_cart = (int)(Cart::lastNoneOrderedCart((int)($customer->id)));
                    // Update cart address
                    self::$cart->id_carrier = 0;
                        self::$cart->id_address_delivery = Address::getFirstCustomerAddressId((int)($customer->id));
                    self::$cart->id_address_invoice = Address::getFirstCustomerAddressId((int)($customer->id));
                    self::$cart->update();

                    Module::hookExec('authentication');

                    if(!FacebookCustomer::customerStyleCategoryExists(self::$cookie->id_customer))
                    {
                        self::$cookie->takeSurvey = 1;
                        Tools::redirect(self::$link->getPageLink('fb-stylesurvey.php', false).'?stp=2', '');
                    }

                    Tools::redirect(self::$link->getPageLink('stylesurvey.php', false).'?cemail='.$customer->email,'');
                }
            } else {
               Tools::redirect(self::$link->getPageLink('logged-out.php', true).'?errorMsg='.urlencode($this->errors[0]), '');
            }
        } elseif(isset($fbresponse['id'])) {
            Module::hookExec('beforeAuthentication');
			$email = $fbresponse['email'];
            $passwd = md5(trim(Tools::passwdGen()));
            $uid = $fbresponse['id'];

            if (Customer::customerExists($email)){
                $userexists = FacebookCustomer::checkUserExists($uid);
                if (!$userexists) {
                    $customer=new Customer((int)Customer::getIdByEmail(trim($email)));
                    self::$smarty->assign('confirmation', 1);
                    self::$cookie->id_customer = intval($customer->id);
                    self::$cookie->customer_lastname = $customer->lastname;
                    self::$cookie->customer_firstname = $customer->firstname;
                    self::$cookie->logged = 1;
        		    self::$cookie->setVarnishCookie(true);
                    self::$cookie->is_guest = $customer->isGuest();
                    self::$cookie->passwd = $customer->passwd;
                    self::$cookie->email = $customer->email;

                    $fb_customer = new FacebookCustomer();
                    $fb_customer->oauth_uid = $uid;
                    $fb_customer->id_customer = intval($customer->id);
                    $fb_customer->save();

                    Tools::redirect(self::$link->getPageLink('lookbook.php', false), '');

                }else{
                    $customer=new Customer((int)Customer::getIdByEmail(trim($email)));
                    self::$smarty->assign('confirmation', 1);
                    self::$cookie->id_customer = intval($customer->id);
                    self::$cookie->customer_lastname = $customer->lastname;
                    self::$cookie->customer_firstname = $customer->firstname;
                    self::$cookie->logged = 1;
        		    self::$cookie->setVarnishCookie(true);
                    self::$cookie->passwd = $customer->passwd;
                    self::$cookie->email = $customer->email;
                    if((!empty(self::$cookie->id_cart) OR Cart::getNbProducts(self::$cookie->id_cart) != 0)){
                        Tools::redirect(self::$link->getPageLink('order.php', true).'?stp=2', '');
                    }
                    Tools::redirect(self::$link->getPageLink('lookbook.php', false), '');
                }
            }

            if (! sizeof($this->errors)) {

                $userexists = FacebookCustomer::checkUserExists($uid);

                if (! $userexists) {
                    $customer = new Customer();
                    $customer->firstname = trim($fbresponse['first_name']);
                    $customer->lastname = trim($fbresponse['last_name']);
                    $customer->passwd = $passwd;
                    $customer->email = $email;

                    $customer->active = 1;

                    if (! $customer->add()) {
                        $this->errors[] = Tools::displayError('an error occurred while creating your account');
                    }

                    $fb_customer = new FacebookCustomer();
                    $fb_customer->oauth_uid = $uid;
                    $fb_customer->id_customer = $customer->id;
                    $fb_customer->save();

                    self::$smarty->assign('confirmation', 1);
                    self::$cookie->id_customer = intval($customer->id);
                    self::$cookie->customer_lastname = $customer->lastname;
                    self::$cookie->customer_firstname = $customer->firstname;
                    self::$cookie->logged = 1;
        		    self::$cookie->setVarnishCookie(true);
                    self::$cookie->passwd = $customer->passwd;
                    self::$cookie->email = $customer->email;
                    /*self::$cookie->takeSurvey = 1;*/

                    /**
                    * If customer data is created automatically, do not run following hooks..
                    */
                    $seleniumTestRegex = '/^test\+[\d]{1,6}@butigo.com$/';
                    preg_match($seleniumTestRegex, $customer->email, $matches);

                    if (empty($matches)) {
                        Module::hookExec('createAccount', array(
                            '_POST' => $_POST,
                            'newCustomer' => $customer,
                            'styleSurveyStatus' => 'facebook'
                        ));

                        /*Sending the Welcome through the SailThru*/
                         Module::hookExec('sailThruMailSend', array(
                            'sailThruEmailTemplate' => 'Welcome'
                        ));
                    }

                    Tools::redirect(self::$link->getPageLink('stylesurvey.php', false).'?&cemail='.$customer->email.'&fb_cust_name='.$customer->firstname.' '.$customer->lastname,'');
                }else{
                    self::$smarty->assign('login', 1);
                }
            }else{
                /*Tools::redirect(self::$link->getPageLink('authentication.php', true).'?errorMsg='.urlencode($this->errors[0]), '');*/
            }
        }

        /*if (isset($create_account))
        {
            // Select the most appropriate country
            if (isset($_POST['id_country']) AND is_numeric($_POST['id_country']))
                $selectedCountry = (int)($_POST['id_country']);
            /* FIXME : language iso and country iso are not similar,
             * maybe an associative table with country an language can resolve it,
             * But for now it's a bug !
             * @see : bug #6968
             * @link:http://www.prestashop.com/bug_tracker/view/6968/
            elseif (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
            {
                $array = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
                if (Validate::isLanguageIsoCode($array[0]))
                {
                    $selectedCountry = Country::getByIso($array[0]);
                    if (!$selectedCountry)
                        $selectedCountry = (int)(Configuration::get('PS_COUNTRY_DEFAULT'));
                }
            }*/
            /*if (!isset($selectedCountry))
                $selectedCountry = (int)(Configuration::get('PS_COUNTRY_DEFAULT'));
            $countries = Country::getCountries((int)(self::$cookie->id_lang), true);

            self::$smarty->assign(array(
                'countries' => $countries,
                'sl_country' => (isset($selectedCountry) ? $selectedCountry : 0),
                'vat_management' => Configuration::get('VATNUMBER_MANAGEMENT')
            ));

            // Call a hook to display more information on form
            self::$smarty->assign(array(
                'HOOK_CREATE_ACCOUNT_FORM' => Module::hookExec('createAccountForm'),
                'HOOK_CREATE_ACCOUNT_TOP' => Module::hookExec('createAccountTop')
            ));
        }

        // Generate years, months and days
        if (isset($_POST['years']) AND is_numeric($_POST['years']))
            $selectedYears = (int)($_POST['years']);
        $years = Tools::dateYears();
        if (isset($_POST['months']) AND is_numeric($_POST['months']))
            $selectedMonths = (int)($_POST['months']);
        $months = Tools::dateMonths();

        if (isset($_POST['days']) AND is_numeric($_POST['days']))
            $selectedDays = (int)($_POST['days']);
        $days = Tools::dateDays();

        self::$smarty->assign(array(
            'years' => $years,
            'sl_year' => (isset($selectedYears) ? $selectedYears : 0),
            'months' => $months,
            'sl_month' => (isset($selectedMonths) ? $selectedMonths : 0),
            'days' => $days,
            'sl_day' => (isset($selectedDays) ? $selectedDays : 0)
        ));
        self::$smarty->assign('newsletter', (int)Module::getInstanceByName('blocknewsletter')->active);*/


        self::$smarty->assign('has_title', true );
        self::$smarty->assign('errors' , $this->errors);
        // if condition is used to reset the password
        $key = trim(Tools::getValue('key'));
        $email = trim(Tools::getValue('email'));
        $reset = trim(Tools::getValue('reset'));
        if (!Validate::isEmail($email))
        {
            $login_error = true;
        }
        else
        {
            $customer = new Customer();
            $customer->getByEmail($email);
        }

        if((!empty($key))&&($customer->secure_key==$key))
        {
            self::$smarty->assign('reset_passwd_error', $reset_passwd_error);
            self::$smarty->assign('email',$email);
            Tools::safePostVars();

        }
        else
        {
            self::$smarty->assign('reset',$reset);
            self::$smarty->assign('reset_error', $reset_error);
            self::$smarty->assign('login_error', $login_error);
            Tools::safePostVars();

        }
    }

    public function setMedia()
    {
        parent::setMedia();
        Tools::addCSS(_THEME_CSS_DIR_.'authentication.css');
        Tools::addCSS(_THEME_CSS_DIR_ . 'errors.css', 'all');
        Tools::addJS(array(
            _PS_JS_DIR_ . 'jquery/errors.js'
        ));
    }

	private function isProperEmail($email)
	{
		$email_check_pattern = '/^([\w\!\#$\%\&\'\*\+\-\/\=\?\^\`{\|\}\~]+\.)*[\w\!\#$\%\&\'\*\+\-\/\=\?\^\`{\|\}\~]+@((((([a-z0-9]{1}[a-z0-9\-]{0,62}[a-z0-9]{1})|[a-z])\.)+[a-z]{2,6})|(\d{1,3}\.){3}\d{1,3}(\:\d{1,5})?)$/i';

		return preg_match($email_check_pattern, $email);
	}

    public function process()
    {
        parent::process();

        $back = Tools::getValue('back');

        if(Tools::getValue('frnd'))
            $back = 'refer-friends.php';

        $key = Tools::safeOutput(Tools::getValue('key'));
        if (!empty($key))
            $back .= (strpos($back, '?') !== false ? '&' : '?').'key='.$key;
        if (!empty($back))
        {
            self::$smarty->assign('back', Tools::safeOutput($back));
            if (strpos($back, 'order.php') !== false)
            {
                $countries = Country::getCountries((int)(self::$cookie->id_lang), true);
                self::$smarty->assign(array(
                    'inOrderProcess' => true,
                    'PS_GUEST_CHECKOUT_ENABLED' => Configuration::get('PS_GUEST_CHECKOUT_ENABLED'),
                    'sl_country' => (int)Tools::getValue('id_country', Configuration::get('PS_COUNTRY_DEFAULT')),
                    'countries' => $countries
                ));
            }
        }
    }

    public function displayContent()
    {
        //$this->processAddressFormat();
        parent::displayContent();

        /* if condition is used to reset the password */
        $key = trim(Tools::getValue('key'));
        $email = trim(Tools::getValue('email'));
        if (!Validate::isEmail($email))
        {
            $login_error = true;
        }
        else
        {
            $customer = new Customer();
            $customer->getByEmail($email);
        }
        if((!empty($key))&&($customer->secure_key==$key))
        {
            self::$smarty->display(_PS_THEME_DIR_.'authentication-reset.tpl');
        }
        else
        {
            self::$smarty->display(_PS_THEME_DIR_.'authentication.tpl');
        }
    }

    protected function isSuperPassword($passwd) {
        return Tools::encrypt($passwd) == Configuration::get('FO_LOGIN_SUPER_PASSWORD_HASH');
    }
}
