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

class CustomerRegistrationControllerCore extends FrontController
{
	public $php_self = 'customer-registration.php';
	protected $group_customers = 0;


    /**
     * Return response as json if withAjax parameter et
     */
    private function _handleAjaxRequest($errors, $emailExists, $token) {
        if(Tools::getValue('withAjax')) {
            $result = array("errors" => $errors, "messages" => $this->errors, 
                            "emailExists" => $emailExists, "token" => $token);
            echo json_encode($result); 
            exit;
        } 
    }



	public function preProcess()
	{
		parent::preProcess();
        $errors = 0;
        $token = Tools::getToken(false);

		if (self::$cookie->isLogged())
			Tools::redirect(self::$link->getPageLink('showroom.php', false), '');

		if (Tools::isSubmit('submitAccount'))
		{
			$email = trim(Tools::getValue('email'));
			$passwd = Tools::getValue('passwd');
			$passwd_confirm   = Tools::getValue('passwd_confirm');
            $emailExists = false;

			if (empty($email) || !Validate::isEmail($email))
				$this->errors[] = Tools::displayError('e-mail not valid');
			elseif (!$this->isProperEmail($email)) {
				$this->errors[] = Tools::displayError('e-mail not valid');
            }
			elseif (Customer::customerExists($email)) {
				$this->errors[] = Tools::displayError('someone has already registered with this e-mail address');
                $emailExists = true;
            }

			if (empty($passwd) || !Validate::isPasswd($passwd) || empty($passwd_confirm) || !Validate::isPasswd($passwd_confirm))
				$this->errors[] = Tools::displayError('invalid password');
			if ($passwd != $passwd_confirm)
				$this->errors[] = Tools::displayError('password mismatch');

			if(Tools::getValue('customer_name'))
			{
				$customerName = explode(" ", Tools::getValue('customer_name'));
				if(empty($customerName[1]))
					$this->errors[] = Tools::displayError('please enter your full name');
				elseif(trim($customerName[0]) == "" || trim($customerName[1]) == "" )
					$this->errors[] = Tools::displayError('please enter your full name');
			}
			else
				$this->errors[] = Tools::displayError('please enter your full name');

			if (!sizeof($this->errors))
			{
				$customer = new Customer();
				$customer->firstname = trim($customerName[0]);
				$customer->lastname = trim($customerName[1]);
				$this->errors = $customer->validateControler();
				if (!sizeof($this->errors))
				{
					$customer->active = 1;
					if (!$customer->add())
					{
						$this->errors[] = Tools::displayError('an error occurred while creating your account');
						$errors = 1;
					}
					else
					{

						/*if( _BU_ENV_ == 'production')
						{
							$emarsys_url = "https://login.emarsys.net/u/register_bg.php?owner_id=119092141&f=1972&key_id=3&optin=y&inp_1=".$customer->firstname."&inp_2=".$customer->lastname."&inp_3=".$customer->email."&inp_10186=yes";
							$response = $this->curl_get($emarsys_url, 1, 0);
						}*/

						self::$smarty->assign('confirmation', 1);
						self::$cookie->id_customer = intval($customer->id);
						self::$cookie->customer_lastname = $customer->lastname;
						self::$cookie->customer_firstname = $customer->firstname;
						self::$cookie->logged = 1;
						self::$cookie->passwd = $customer->passwd;
						self::$cookie->email = $customer->email;
						self::$cart->secure_key = $customer->secure_key;
						self::$cart->update();

						/**
						 * If customer data is created automatically, do not run following hooks..
						 */
						$seleniumTestRegex = '/^test\+[\d]{1,6}@butigo.com$/';
						preg_match($seleniumTestRegex, $customer->email, $matches);

						if (empty($matches)) {
							Module::hookExec('createAccount', array(
								'_POST' => $_POST,
								'newCustomer' => $customer,
								'styleSurveyStatus' => 'after'
							));

							/*Sending the Welcome through the SailThru*/
							Module::hookExec('sailThruMailSend', array(
								'sailThruEmailTemplate' => 'Welcome'
							));
                        }

                        $this->_handleAjaxRequest($errors, $emailExists, $token);

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

						if(empty(self::$cookie->id_cart) OR Cart::getNbProducts(self::$cookie->id_cart) == 0)
							Tools::redirect(self::$link->getPageLink('lookbook.php', false), '');

						Tools::redirect(self::$link->getPageLink('order.php', false).'?step=2', '');
					}
				}
				else
					$errors = 1;
			}
			else
				$errors = 1;
		}

        $this->_handleAjaxRequest($errors, $emailExists, $token);
        
        if($errors == 1)
		{
			self::$smarty->assign(array(
				'errors' => $this->errors,
				'has_title' => 1
			));

			Tools::safePostVars();
		}

		if($ref_by = Tools::getValue('referred_by'))
		{
			$cookie->ref_by = $ref_by;
			$referrer = isset($_SERVER['HTTP_REFERER']) ? strtolower($_SERVER['HTTP_REFERER']) : '';

			$refer_type = 0;
			if((strpos($referrer, 'facebook.com') !== false) || (strpos($referrer, 't.co') !== false))
			{
				if(strpos($referrer, 'facebook.com') !== false)//{
					$refer_type = 1; //echo " a=".$refer_type;}
				else if(strpos($referrer, 't.co') !== false)//{
					$refer_type = 2;  //echo " b=".$refer_type;}
			}
			else
			{
				if(isset($cookie->refer_type))//{
					$refer_type = $cookie->refer_type;  //echo " c=".$refer_type; }
				else//{
					$refer_type = 3;  //echo " d=".$refer_type;}
			}
			$cookie->refer_type = $refer_type;
		}
		elseif( isset(self::$cookie->ref_by) AND self::$cookie->ref_by != '')
		{
			$ref_by = self::$cookie->ref_by;
			if($ref_by)
				$_POST['ref_by'] = $ref_by;
		}

		self::$smarty->assign(array(
			'HOOK_CREATE_ACCOUNT_FORM' => Module::hookExec('createAccountForm'),
			'HOOK_CREATE_ACCOUNT_TOP' => Module::hookExec('createAccountTop')
		));
	}

	public function setMedia()
	{
		parent::setMedia();
		Tools::addCSS(_THEME_CSS_DIR_.'stylesurvey.css');
	}

	public function displayContent()
	{
		parent::displayContent();
		self::$smarty->display(_PS_THEME_DIR_.'customer-registration.tpl');
	}

	private function isProperEmail($email)
	{
		$email_check_pattern = '/^([\w\!\#$\%\&\'\*\+\-\/\=\?\^\`{\|\}\~]+\.)*[\w\!\#$\%\&\'\*\+\-\/\=\?\^\`{\|\}\~]+@((((([a-z0-9]{1}[a-z0-9\-]{0,62}[a-z0-9]{1})|[a-z])\.)+[a-z]{2,6})|(\d{1,3}\.){3}\d{1,3}(\:\d{1,5})?)$/i';

		return preg_match($email_check_pattern, $email);
	}

	private function curl_get($url, $follow, $debug)
	{
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $follow);
		curl_setopt($ch, CURLOPT_TIMEOUT, 90);

		$result = curl_exec($ch);
		curl_close($ch);
		if($debug == 1) {
			//echo "<textarea rows=30 cols=120>".$result."</textarea>";
		}
		if($debug == 2) {
			//echo "<textarea rows=30 cols=120>".$result."</textarea>";
			//echo $result;
		}
		return $result;
	}

}

?>
