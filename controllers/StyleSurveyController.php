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

class StyleSurveyControllerCore extends FrontController
{
	//public $ssl = true;
	public $php_self = 'stylesurvey.php';
	//public $hypothesis_design  = 'showroom';
	protected $step_count = 0;
	protected $group_customers = 1;

	public function preProcess()
	{
		parent::preProcess();

        $cemail = Tools::getValue('cemail', '');
        $fb_cust_name=Tools::getValue('fb_cust_name', '');

        if($cemail !== '')
            self::$smarty->assign('cemail', $cemail);

        if($fb_cust_name !=''){
             self::$smarty->assign('fb_cust_name', $fb_cust_name);
        }

		$errors = array();
		$this->step_count = Tools::getIsset('stp') ? Tools::getValue('stp') : 0;

        if (self::$cookie->isLogged() && $this->step_count != 3 && !isset(self::$cookie->show_site)) {
            if(!isset($cemail))
                Tools::redirect(self::$link->getPageLink('showroom.php', false), '');
        } elseif (!self::$cookie->isLogged() && isset(self::$cookie->show_site) && self::$cookie->show_site == 1 && Tools::getValue('stylesurvey') != 1) {
            Tools::redirect(self::$link->getPageLink('customer-registration.php', false), '');
        }

		if (Tools::isSubmit('submitAccount'))
		{
			$email = trim(Tools::getValue('email'));
			$passwd = Tools::getValue('passwd');
			$passwd_confirm   = Tools::getValue('passwd_confirm');

			if (empty($email) || !Validate::isEmail($email))
				$this->errors[] = Tools::displayError('e-mail not valid');
			elseif (!$this->isProperEmail($email))
				$this->errors[] = Tools::displayError('e-mail not valid');
			elseif (Customer::customerExists($email))
				$this->errors[] = Tools::displayError('someone has already registered with this e-mail address');

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
				$ques_ans = array(
					0 => array('YAR', 'FEM', 'MOD'),
					1 => array('KLA', 'TRE', 'YAR'),
					2 => array('KLA', 'TRE', 'MOD'),
					3 => array('KLA', 'TRE', 'YAR'),
					4 => array('FEM', 'KLA', 'MOD'),
					5 => array('KLA', 'YAR', 'FEM'),
					6 => array('MOD', 'FEM', 'YAR'),
					7 => array('MOD', 'FEM', 'TRE'),
					8 => array('KLA', 'MOD', 'TRE'),
					9 => array('bej', 'siyah', 'kahve', 'mavi', 'gri', 'yesiller', 'turuncu', 'pembe', 'mor', 'kirmizi', 'sari', 'beyaz'),
					/*9 => array('kisabot', 'babet', 'yuksek', 'dolgu', 'alcak', 'orta', 'uzuncizme', 'sivriburun', 'acikburun', 'platform', 'kareburun', 'bantli', 'parmakarasi', 'maryjane', 'arkasiacik', 'bilektebagli'),*/
					10 => array('18-23', '24-29', '30-35', '36-45', '46+'),
					11 => array('35', '36', '37', '38', '39', '40', '41', '42'),
					12 => array('0-4', '6-8', '10-12', '14-16', '18+')
				);

				$customer = new Customer();
				$customer->firstname = trim($customerName[0]);
				$customer->lastname = trim($customerName[1]);
				$customer->age = $ques_ans[10][Tools::getValue('qqa_1010')-1];
				$customer->shoe_size = $ques_ans[11][Tools::getValue('qqa_1011')-1];
				$customer->dress_size = $ques_ans[12][Tools::getValue('qqa_1012')-1];
				$this->errors = $customer->validateControler();
				if (!sizeof($this->errors))
				{
					$customer->active = 1;

					$style_answers = array();
					$index = 0;
					for(; $index < 9; $index++)
					{
						$style_answers[] = $ques_ans[$index][(Tools::getValue('qqa_100'.$index))-1];
					}
					$styles_prioritized = array_count_values($style_answers);
					arsort($styles_prioritized, SORT_NUMERIC);
					$final_style = key( $styles_prioritized );
					$customer->category_name = trim($final_style);
					//$customer->setCategory($final_style);

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
						$this->step_count = 2;
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

						self::$smarty->assign('confirmation', 1);
						self::$cookie->id_customer = intval($customer->id);
						self::$cookie->customer_lastname = $customer->lastname;
						self::$cookie->customer_firstname = $customer->firstname;
						self::$cookie->logged = 1;
                        self::$cookie->is_guest = $customer->isGuest();
						self::$cookie->passwd = $customer->passwd;
						self::$cookie->email = $customer->email;

						/*Style Survey Details*/
						$styleSurvey = new CustomerStyleSurvey();
						$styleSurvey->id_customer = $customer->id;
						for($i=1; $i<=9; $i++)
						{
							$question = 'question'.$i;
							$question_number = Tools::getValue('qqa_100'.($i-1));
							$styleSurvey->$question = $question_number."-".$ques_ans[$i-1][$question_number-1];
						}

						$question10 = '';
						for($i=1; $i<=12; $i++)
							if(Tools::getValue('qqa_1009_'.$i) == 1)
								$question10 .= $i."-".$ques_ans[9][$i-1].",";

						$styleSurvey->question10 = rtrim($question10,',');
						$styleSurvey->add(true, true);

						//$hypothesis_name  = Hypothesis::splitHypoKeys(self::$cookie->hypo_keys, 'showroom', 'cookie');
                        //Concatenating space so that the key becomes a string.
						//Values in the input array with numeric keys will be renumbered with incrementing keys starting from zero in the result array.
						//$customer_keys = array(self::$cookie->id_customer.' ' => $hypothesis_name[1]);
						//Hypothesis::writeHypothesisString('showroom', $customer_keys);

						/*$inserted = 0;
						//splitting the global $cookie value to extract the hypothesis key
						$customer_key = Hypothesis::splitHypoKeys(self::$cookie->hypo_keys, 'showroom', 'cookie');
						//Insert the key of the hypothesis in table bu_hypothesis_values
						if(Hypothesis::insertCustomerIdAndKey('showroom', self::$cookie->id_customer, $customer_key))
							$inserted = 1;

						if( _BU_ENV_ == 'production')
						{
							////writing information into file for tracking purpose.
							$errorLogFile = @fopen(_PS_ROOT_DIR_.'/log/hypothesis_track.txt', "a");
							fwrite($errorLogFile, sprintf("\n%s:- %s,%s,%s,%s\n",date("D M j G:i:s T Y"),'Customer Id:-'.self::$cookie->id_customer,'Inserted in table hypothesis_values:-'.$inserted,'Design Name:-'.'showroom','Cookie:-'.self::$cookie->hypo_keys ."\n"));
							fclose($errorLogFile);
						}*/

						$color_answer = array();
						for($i=1; $i<=12; $i++)
							if(Tools::getValue('qqa_1008_'.$i) == 1)
								$color_answer[] = $ques_ans[9][$i-1];
						$color_tags = array();
						foreach($color_answer as $color)
						{
							$color_tag = new Tag(NULL, $color, $cookie->id_lang);
							$color_tags[] = $color_tag->id;
						}
						$customer->addTags(intval($customer->id), $color_tags, 'color', 0, false);

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

						/*$this->displayHeader();

						self::$smarty->assign('style_result', $final_style);
						self::$smarty->display(_PS_THEME_DIR_.'stylesurvey_complete.tpl');

						$this->displayFooter();
						exit;*/
						Tools::redirect(self::$link->getPageLink('stylesurvey.php', false).'?stp=3&res='.$final_style, '');
					}
				}
				else
					$this->step_count = 2;
			}
			else
				$this->step_count = 2;
		}
        elseif (Tools::isSubmit('updateAccount'))
		{
			$email = trim(Tools::getValue('email'));
			$passwd = Tools::getValue('passwd');
			$passwd_confirm = Tools::getValue('passwd_confirm');

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
			}else{
				$this->errors[] = Tools::displayError('please enter your full name');
            }

            if (!sizeof($this->errors))
            {
				$ques_ans = array(
					0 => array('YAR', 'FEM', 'MOD'),
					1 => array('KLA', 'TRE', 'YAR'),
					2 => array('KLA', 'TRE', 'MOD'),
					3 => array('KLA', 'TRE', 'YAR'),
					4 => array('FEM', 'KLA', 'MOD'),
					5 => array('KLA', 'YAR', 'FEM'),
					6 => array('MOD', 'FEM', 'YAR'),
					7 => array('MOD', 'FEM', 'TRE'),
					8 => array('KLA', 'MOD', 'TRE'),
					9 => array('bej', 'siyah', 'kahve', 'mavi', 'gri', 'yesiller', 'turuncu', 'pembe', 'mor', 'kirmizi', 'sari', 'beyaz'),
					10 => array('18-23', '24-29', '30-35', '36-45', '46+'),
					11 => array('35', '36', '37', '38', '39', '40', '41', '42'),
					12 => array('0-4', '6-8', '10-12', '14-16', '18+')
				);

                $customerName = explode(" ", Tools::getValue('customer_name'));
                $firstname = trim($customerName[0]);
                $lastname = trim($customerName[1]);
                $id_customer = new Customer(Customer::getIdByEmail(trim($email)));

                $customer = new Customer((int)$id_customer->id);
                $customer->firstname = $firstname;
				$customer->lastname = $lastname;
                $customer->age = $ques_ans[10][Tools::getValue('qqa_1010')-1];
                $customer->shoe_size = $ques_ans[11][Tools::getValue('qqa_1011')-1];
                $customer->dress_size = $ques_ans[12][Tools::getValue('qqa_1012')-1];

				$this->errors = $customer->validateControler();
                if (!sizeof($this->errors))
                {
                    $customer->active = 1;
                    $style_answers = array();
                    $index = 0;
                    for(; $index < 9; $index++)
                    {
                        $style_answers[] = $ques_ans[$index][(Tools::getValue('qqa_100'.$index))-1];
                    }
                    $styles_prioritized = array_count_values($style_answers);
                    arsort($styles_prioritized, SORT_NUMERIC);
                    $final_style = key( $styles_prioritized );
                    $customer->category_name = trim($final_style);

                    /*Style Survey Details*/
                    $styleSurvey = new CustomerStyleSurvey();
                    $styleSurvey->id_customer = $id_customer->id;

                    for($i=1; $i<=9; $i++)
                    {
                        $question = 'question'.$i;
                        $question_number = Tools::getValue('qqa_100'.($i-1));
                        $styleSurvey->$question = $question_number."-".$ques_ans[$i-1][$question_number-1];
                    }
                    $question10 = '';
                    for($i=1; $i<=12; $i++){
                        if(Tools::getValue('qqa_1009_'.$i) == 1)
                            $question10 .= $i."-".$ques_ans[9][$i-1].",";
                    }

                    $styleSurvey->question10 = rtrim($question10,',');
                    $styleSurvey->add(true, true);

                    $color_answer = array();
                    for($i=1; $i<=12; $i++){
                        if(Tools::getValue('qqa_1008_'.$i) == 1)
                            $color_answer[] = $ques_ans[9][$i-1];
                    }
                    $color_tags = array();
                    foreach($color_answer as $color)
                    {
                        $color_tag = new Tag(NULL, $color, $cookie->id_lang);
                        $color_tags[] = $color_tag->id;
                    }

                    $customer->addTags($id_customer->id, $color_tags, 'color', 0, false);

                    if($customer->update() && $customer->updatePassword($passwd)) {
                        self::$cookie->id_customer = $customer->id;
                        self::$cookie->customer_lastname = $customer->lastname;
                        self::$cookie->customer_firstname = $customer->firstname;
                        self::$cookie->logged = 1;
                        self::$cookie->is_guest = $customer->isGuest();
                        self::$cookie->passwd = $customer->passwd;
                        self::$cookie->email = $customer->email;

                        Tools::redirect(self::$link->getPageLink('stylesurvey.php', false).'?stp=3&res='.$final_style, '');
                    } else {
                        $this->errors[] = Tools::displayError('There was an error in acount updation. Please try again');
                        $this->step_count = 2;
                    }
                } else
                    $this->step_count = 2;
            } else
                 $this->step_count = 2;
        }

		elseif ($this->step_count == 0)
		{
			/* tracking part - */
			if($ref_by = Tools::getValue('referred_by'))
			{
				$cookie->ref_by = $ref_by;
				$referrer = isset($_SERVER['HTTP_REFERER']) ? strtolower($_SERVER['HTTP_REFERER']) : '';

				$refer_type = 0;
				if((strpos($referrer, 'facebook.com') !== false) || (strpos($referrer, 't.co') !== false))
				{
					if(strpos($referrer, 'facebook.com') !== false)
						$refer_type = 1;
					else if(strpos($referrer, 't.co') !== false)
						$refer_type = 2;
				}
				else
				{
					if(isset($cookie->refer_type))
						$refer_type = $cookie->refer_type;
					else
						$refer_type = 3;
				}
				$cookie->refer_type = $refer_type;
			}

			$this->step_count = 1;
		}
		elseif ($this->step_count == 1)
		{
			/*if(Tools::getIsset('ref_by'))
				$_POST['ref_by'] = Tools::getValue('ref_by');*/
			$ref_by = self::$cookie->ref_by;
			if($ref_by)
				$_POST['ref_by'] = $ref_by;

			$this->step_count = 2;
		}

		if ($this->step_count == 2)
		{
			/* Call a hook to display more information on form */
			self::$smarty->assign(array(
				'HOOK_CREATE_ACCOUNT_FORM' => Module::hookExec('createAccountForm'),
				'HOOK_CREATE_ACCOUNT_TOP' => Module::hookExec('createAccountTop')
			));
		}

		if($this->step_count != 3)
		{
			self::$smarty->assign(array(
				'errors' => $this->errors,
				'has_title' => 1,
				'step_count' => $this->step_count
			));

			Tools::safePostVars();
		}
		else
		{
			$customer = new Customer(self::$cookie->id_customer);
			$customer_join_month = substr($customer->date_add, 5, 2);
			$customer_join_year = substr($customer->date_add, 0, 4);
			self::$smarty->assign(array(
				'customer_join_month' => $customer_join_month,
				'customer_join_year' => $customer_join_year
			));

            $styleSurveyResult = Tools::getValue('res');
            $styleHeadline = '';
            if(strtolower($styleSurveyResult) == 'kla'){
                $styleHeadline = 'Klasik Yalın Özgüvenli';
            }elseif(strtolower($styleSurveyResult) == 'mod'){
                $styleHeadline = 'Modern Rafine Minimalist';
            }elseif(strtolower($styleSurveyResult) == 'tre'){
                $styleHeadline = 'Trendy Casual Yaratıcı';
            }elseif(strtolower($styleSurveyResult) == 'yar'){
                $styleHeadline = 'Romantik Elegan Unique';
            }elseif(strtolower($styleSurveyResult) == 'fem'){
                $styleHeadline = 'Feminen Cesur Kozmopolit';
            }
			self::$smarty->assign(array(
                'style_result' => $styleSurveyResult,
                'styleHeadline' => $styleHeadline
            ));
		}
	}

	public function setMedia()
	{
		parent::setMedia();
		Tools::addCSS(_THEME_CSS_DIR_.'stylesurvey.css');
		if($this->step_count != 3)
			Tools::addJS(array(_THEME_JS_DIR_.'survey.js', _PS_JS_DIR_.'jquery/coda-slider.pack.js',  _PS_JS_DIR_.'main.js',  _PS_JS_DIR_.'jquery/jquery.easing.compatibility.1.2.pack.js', _PS_JS_DIR_.'jquery/jquery.easing.1.2.pack.js' ));
	}

	public function displayContent()
	{
		parent::displayContent();

		if ($this->step_count != 3) {
			self::$smarty->display(_PS_THEME_DIR_.'stylesurvey.tpl');
		} else {
			if (self::$cookie->open_to_ab_test) {
				self::$smarty->assign('open_to_ab_test',true);
				unset(self::$cookie->open_to_ab_test);
			}

			self::$smarty->display(_PS_THEME_DIR_.'stylesurvey_complete.tpl');
		}
	}

	private function isProperEmail($email)
	{
		$email_check_pattern = '/^([\w\!\#$\%\&\'\*\+\-\/\=\?\^\`{\|\}\~]+\.)*[\w\!\#$\%\&\'\*\+\-\/\=\?\^\`{\|\}\~]+@((((([a-z0-9]{1}[a-z0-9\-]{0,62}[a-z0-9]{1})|[a-z])\.)+[a-z]{2,6})|(\d{1,3}\.){3}\d{1,3}(\:\d{1,5})?)$/i';

		return preg_match($email_check_pattern, $email);
	}
}

?>
