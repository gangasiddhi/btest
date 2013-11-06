<?php

/**
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
class FacebookStyleSurveyControllerCore extends FrontController {
	public $php_self = 'fb-stylesurvey.php';

	public function init() {
        $this->step_count = Tools::getValue('stp', 0);

        parent::init();
    }

	public function preProcess() {
		parent::preProcess();

		$this->step_count = Tools::getIsset('stp') ? Tools::getValue('stp') : 0;

		if ($this->step_count == 0) {
			Tools::redirect(self::$link->getPageLink('index.php', false), '');
		}

		/*-----------------facebook register----------------------*/
		if ($this->step_count == 1) {
			if (_BU_ENV_ == 'production') {
				define('FACEBOOK_APP_ID', '220196491340962');
				define('FACEBOOK_SECRET', '2ab34084d354843225e67031b86a8357');
			} elseif (_BU_ENV_ == 'development') {
				define('FACEBOOK_APP_ID', '312142095529326');
				define('FACEBOOK_SECRET', 'c5525b01c0482e51af7aa1eceeaa0aca');
			}

			function parse_signed_request($signed_request, $secret) {
				list($encoded_sig, $payload) = explode('.', $signed_request, 2);

				// decode the data
				$sig = base64_url_decode($encoded_sig);
				$data = json_decode(base64_url_decode($payload), true);

				if (strtoupper($data['algorithm']) !== 'HMAC-SHA256') {
					// error_log('Unknown algorithm. Expected HMAC-SHA256');
					return null;
				}

				// check sig
				$expected_sig = hash_hmac('sha256', $payload, $secret, $raw = true);

				if ($sig !== $expected_sig) {
					// error_log('Bad Signed JSON signature!');
					return null;
				}

				return $data;
			}

			function base64_url_decode($input) {
				return base64_decode(strtr($input, '-_', '+/'));
			}

			if ($_REQUEST) {
				$facebook_customer = false;
				$customer_exists = false;
				$response = parse_signed_request($_REQUEST['signed_request'], FACEBOOK_SECRET);

				if ($response) {
					if (isset($response['user_id'])) {
						$facebook_customer = true;
						$uid = $response['user_id'];
					}

					$name = $response["registration"]["name"];
					$email = $response["registration"]["email"];
					$password = $response["registration"]["password"];
					$customerName = explode(" ", $name);

					if ($facebook_customer == true) {
						$userexists = FacebookCustomer::checkUserExists($uid);
					}

					if (Customer::customerExists($email)) {
						self::$smarty->assign('login', 1);
					} elseif ((! $userexists && $facebook_customer == true) || $facebook_customer == false) {
						$customer = new Customer();
						$customer->firstname = trim($customerName[0]);
						$customer->lastname = trim($customerName[1]);
						$customer->passwd = md5(pSQL(_COOKIE_KEY_ . $password));
						$customer->email = $email;

						$this->errors = $customer->validateControler();

						if (! sizeof($this->errors)) {
							$customer->active = 1;

							if (! $customer->add()) {
								$this->errors[] = Tools::displayError('an error occurred while creating your account');
							}

							if (! sizeof($this->errors)) {
								if ($facebook_customer == true) {
									$fb_customer = new FacebookCustomer();
									$fb_customer->oauth_uid = $uid;
									$fb_customer->id_customer = $customer->id;
									$fb_customer->save();
								}

								self::$smarty->assign('confirmation', 1);
								self::$cookie->id_customer = intval($customer->id);
								self::$cookie->customer_lastname = $customer->lastname;
								self::$cookie->customer_firstname = $customer->firstname;
								self::$cookie->logged = 1;
								self::$cookie->passwd = $customer->passwd;
								self::$cookie->email = $customer->email;
								self::$cookie->takeSurvey = 1;

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

									/*Sending the Welcome through the SailThru*/
									Module::hookExec('sailThruMailSend', array(
										'sailThruEmailTemplate' => 'Welcome'
									));
								}

								Tools::redirect(self::$link->getPageLink('fb-stylesurvey.php', false) . '?stp=2', '');
							}
						}
					} else {
						self::$smarty->assign('login', 1);
					}
				} else {
					Tools::redirect(self::$link->getPageLink('authentication.php', false), '');
				}
			} else {
				echo '$_REQUEST is empty';
			}
		}

		if (! self::$cookie->isLogged() && $this->step_count == 2) {
			Tools::redirect(self::$link->getPageLink('authentication.php', false), '');
		}

		if ($this->step_count == 3) {
			if (! self::$cookie->isLogged()) {
				Tools::redirect(self::$link->getPageLink('authentication.php', false), '');
			}

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

			$customer = new Customer(self::$cookie->id_customer);
			$customer->age = $ques_ans[10][Tools::getValue('qqa_1010') - 1];
			$customer->shoe_size = $ques_ans[11][Tools::getValue('qqa_1011') - 1];
			$customer->dress_size = $ques_ans[12][Tools::getValue('qqa_1012') - 1];

			$this->errors = $customer->validateControler();

			if (! sizeof($this->errors)) {
				$style_answers = array();
				$index = 0;

				for(; $index < 9; $index++) {
					$style_answers[] = $ques_ans[$index][(Tools::getValue('qqa_100' . $index)) - 1];
					echo Tools::getValue('qqa_100' . $index);
				}

				$styles_prioritized = array_count_values($style_answers);

				arsort($styles_prioritized, SORT_NUMERIC);

				$final_style = key($styles_prioritized);
				$customer->category_name = trim($final_style);

				if (! $customer->update()) {
					$this->errors[] = Tools::displayError('an error occurred while updating your style');
				} else {
					/*Style Survey Details*/
					$styleSurvey = new CustomerStyleSurvey();
					$styleSurvey->id_customer = $customer->id;

					for($i = 1; $i <= 9; $i++) {
						$question = 'question' . $i;
						$question_number = Tools::getValue('qqa_100' . ($i - 1));
						$styleSurvey->$question = $question_number . "-" . $ques_ans[$i - 1][$question_number - 1];
					}

					$question10 = '';

					for ($i = 1; $i <= 12; $i++) {
						if (Tools::getValue('qqa_1009_' . $i) == 1) {
							$question10 .= $i . "-" . $ques_ans[9][$i - 1] . ",";
						}
					}

					$styleSurvey->question10 = rtrim($question10, ',');
					$styleSurvey->add(true, true);
					$color_answer = array();

					for ($i = 1; $i <= 12; $i++) {
						if (Tools::getValue('qqa_1008_' . $i) == 1) {
							$color_answer[] = $ques_ans[9][$i - 1];
						}
					}

					$color_tags = array();

					foreach($color_answer as $color) {
						$color_tag = new Tag(NULL, $color, $cookie->id_lang);
						$color_tags[] = $color_tag->id;
					}

					$customer->addTags(intval($customer->id), $color_tags, 'color', 0, false);

					Tools::redirect(self::$link->getPageLink('fb-stylesurvey.php', false) . '?stp=4&res=' . $final_style, '');
				}
			} else {
				$this->step_count = 2;
			}
		}

		if ($this->step_count == 4) {
			if (! self::$cookie->isLogged()) {
				Tools::redirect(self::$link->getPageLink('authentication.php', false), '');
			}

			require_once(_PS_MODULE_DIR_ . 'ettikett/ettikett.php');

			if (self::$cookie->takeSurvey) {
				unset(self::$cookie->takeSurvey);
			}

			$customer = new Customer(self::$cookie->id_customer);

			/* To add a customer to the EttikettReferred group upon register to the site sucessfully from Facebook/Twitter shared link.*/
			$ettikett = new Ettikett();
			$ettikett->hookCreateAccount();

			$customer_join_month = substr($customer->date_add, 5, 2);
			$customer_join_year = substr($customer->date_add, 0, 4);
			self::$smarty->assign(array(
				'customer_join_month' => $customer_join_month,
				'customer_join_year' => $customer_join_year
			));
//			self::$smarty->assign('style_result', Tools::getValue('res'));

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

		self::$smarty->assign('step_count', $this->step_count);
	}

	public function setMedia() {
		parent::setMedia();

		Tools::addCSS(_THEME_CSS_DIR_ . 'stylesurvey.css');

		if ($this->step_count != 3) {
			Tools::addJS(array(
				_THEME_JS_DIR_ . 'survey.js',
				_PS_JS_DIR_ . 'jquery/coda-slider.pack.js',
				_PS_JS_DIR_ . 'main.js',
				_PS_JS_DIR_ . 'jquery/jquery.easing.compatibility.1.2.pack.js',
				_PS_JS_DIR_ . 'jquery/jquery.easing.1.2.pack.js'
			));
		}
	}

	public function displayContent() {
		parent::displayContent();

		if ($this->step_count == 2 || $this->step_count == 1) {
			self::$smarty->display(_PS_THEME_DIR_ . 'fb-stylesurvey.tpl');
		} elseif ($this->step_count == 4) {
			self::$smarty->display(_PS_THEME_DIR_ . 'stylesurvey_complete.tpl');
		}
	}
}

?>
