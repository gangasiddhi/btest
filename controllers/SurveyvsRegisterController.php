<?php

class SurveyvsRegisterControllerCore extends FrontController {

    public $php_self = 'surveyvsregister.php';
	protected $group_customers = 1;

	public function preProcess()
        {
            parent::preProcess();

            $errors = 0;
            if (self::$cookie->isLogged())
            {
                Tools::redirect(self::$link->getPageLink('lookbook.php', false), '');
            }

            if (Tools::isSubmit('submitAccount'))
            {
                $email = trim(Tools::getValue('email'));
                $passwd = Tools::getValue('passwd');
                //$passwd_confirm   = Tools::getValue('passwd_confirm');

                if (empty($email) || !Validate::isEmail($email))
                    $this->errors[] = Tools::displayError('e-mail not valid');
                elseif (!$this->isProperEmail($email))
                    $this->errors[] = Tools::displayError('e-mail not valid');
                elseif (Customer::customerExists($email))
                    $this->errors[] = Tools::displayError('someone has already registered with this e-mail address');

                if (empty($passwd) || !Validate::isPasswd($passwd) /*|| empty($passwd_confirm) || !Validate::isPasswd($passwd_confirm)*/)
                    $this->errors[] = Tools::displayError('invalid password');
//                if ($passwd != $passwd_confirm)
//                        $this->errors[] = Tools::displayError('password mismatch');

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

                        /* BEGIN - grouping of customers */
                        if($this->group_customers)
                        {
                            $group_names = array('OldCheckout','NewCheckout'/*'PreOrderProductPage', 'RegularProductPage'*/);
                            $groups = Group::getGroups(self::$cookie->id_lang);
                            $last_group_id = Configuration::get('PS_LAST_CUSTOMER_GROUP');
                            $group_ids = array();
                            foreach($groups AS $group)
                                if(in_array($group['name'], $group_names))
                                    $group_ids[] = $group['id_group'];
                            $count = sizeof($group_ids);
                            if(!$last_group_id)
                            {
                                $key = 0;
                            }
                            else
                            {
                                foreach($group_ids as $index => $group_id)
                                {
                                    if($last_group_id == $group_id)
                                    {
                                        if($index == $count-1)
                                            $key = 0;
                                        else
                                            $key = $index+1;
                                    }
                                }
                            }
                            $group_list = array($group_ids[$key]);
                            Configuration::updateValue('PS_LAST_CUSTOMER_GROUP', $group_ids[$key]);
                        }
                        /* END - grouping of customers */

                        if (!$customer->add())
                        {
                            $this->errors[] = Tools::displayError('an error occurred while creating your account');
                            $errors = 1;
                        }
                        else
                        {
                            /* BEGIN - grouping of customers */
                            if($this->group_customers)
                            {
                                if(!$customer->addGroups($group_list))
                                    $this->errors[] = Tools::displayError('Cannot add to group');
                            }
                            /* END - grouping of customers */

                          /*  if( _BU_ENV_ == 'production')
                            {
                                $emarsys_url = "https://mailinfo.butigo.com/u/register.php?CID=119092141&f=6453&p=2&a=r&SID=&el=&llid=&counted=&c=&interest[]=[Interessen]&inp_1=".$customer->firstname."&inp_2=".$customer->lastname."&inp_3=".$customer->email."&inp_10186=yes&inp_10763=yes";
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
                                    'newCustomer' => $customer
                                ));
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

                           if ($customer->hasCompletedSurvey())
                                Tools::redirect(self::$link->getPageLink('showroom.php', false), '');
                            else
                                Tools::redirect(self::$link->getPageLink('lookbook.php', false), '');
                        }
                    }
                    else
                        $errors = 1;
                }
                else
                    $errors = 1;
            }

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

            self::$smarty->assign('has_title', 1);

        }

	public function process()
        {
            parent::process();
            //self::$smarty->assign('HOOK_LANDING', Module::hookExec('landing'));
	}

        public function setMedia()
	{
            parent::setMedia();
            Tools::addCSS(_THEME_CSS_DIR_.'join.css');
	}

	public function displayContent()
        {
            parent::displayContent();
            self::$smarty->display(_PS_THEME_DIR_ . 'surveyvsregister.tpl');
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
