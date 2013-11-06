<?php

/**
 * Description of sailthru
 *
 * This Module is used to intregrate with SailThru for marketing.
 *
 * @author gangadhar
 */
if (!defined('_CAN_LOAD_FILES_')) {
	exit;
}

class SailThru Extends Module
{
	/* Sailthru Private keys for authentication */

	private $api_key = "06d90a997a726e587cc1a4dff7ae0155";
	private $api_secret = '8f566ebc91cccdcbc03f15b35c6c4f58';
	private $sailthruClient;
	private $list;

	public function __construct()
	{
		$this->name = 'sailthru';
		$this->tab = 'thirdparty';
		$this->version = '1.2';
		$this->author = 'PrestaShop';
		$this->need_instance = 0;

		parent::__construct();

		$this->displayName = $this->l('SailThru Integration');
		$this->description = $this->l('Using this module, we can send campaigns informations etc to the customers.');

		/* To include the SailThru API */
		require_once(_PS_MODULE_DIR_ . $this->name . '/lib/Sailthru_Client.php');
		$this->sailthruClient = new Sailthru_Client($this->api_key, $this->api_secret);

		if (_BU_ENV_ == 'production') {
			$this->list = 'Butigo_Masterlist';
		} else {
			$this->list = 'sailthru_testlist';
		}
	}

	public function install()
	{
		if (parent::install() == false OR
				$this->registerHook('header') == false OR
				$this->registerHook('footerBottom') == false OR
				$this->registerHook('createAccount') == false OR
				$this->registerHook('updateCustomerDetails') == false OR
				$this->registerHook('sailThruMailSend') == false OR
				$this->registerHook('authentication') == false OR
				$this->registerHook('updateOrderStatus') == false OR
				$this->registerHook('sailthruCustomerInterests') == false OR
				$this->registerHook('updateQuantity') == false OR
				$this->registerHook('productOutOfStock') == false OR
				$this->registerHook('deleteProduct') == false) {
			return false;
		}

		return true;
	}
	
	public function uninstall() 
	{
		return (parent::uninstall());
	}

	/* To add the specified meta tags for SailThru */

	public function hookHeader()
	{
		if (strpos($_SERVER['PHP_SELF'], 'product') !== false) {
			return $this->display(__FILE__, 'sailthruheader.tpl');
		} else {
			return '';
		}
	}

	/* This hook is used to include the sailThru Horizon in the footer to collect the data */

	public function hookFooterBottom($params)
	{
		/* Setting the horizon Cookie if the user islogged */
		if (_BU_ENV_ == 'production') {
			global $smarty;
			$smarty->assign('cssPath', $this->_path);

			if ($params['cookie']->logged && !isset($_COOKIE['sailthru_hid'])) {
				$result = $this->sailthruClient->apiPost('user', array(
					'id' => $params['cookie']->email,
					'fields' => array('keys' => 1),
					'login' => array(
						'site' => Tools::getShopDomain(),
						'ip' => $_SERVER['REMOTE_ADDR'],
						'user_agent' => $_SERVER['HTTP_USER_AGENT'])
						));
				setcookie('sailthru_hid', $result['keys']['cookie'], time() + 3600 * 24 * 1, '/', '.butigo.com');
			}

			return $this->display(__FILE__, 'sailthrufooter.tpl');
		}

		return;
	}

	public function hookSailthruCustomerInterests($params)
	{
		if (strpos($_SERVER['PHP_SELF'], 'product') !== false) {
			if(isset($_COOKIE['scout'])){
				$productIds = explode(',', $_COOKIE['scout']);
				$productIdsList = '';
				foreach ($productIds as $productId) {
					if($productId != ''){
						if (!preg_match('([a-zA-Z])', $productId)) {
							$productIdsList .= $productId . ",";
						}
					}
				}

				$productIdsList = rtrim($productIdsList,',');
				return $productIdsList;
			}else{
				return NULL;
			}
		}
	}

	/* To send the customer/user details to the SailThru on new registration/sucribe */

	public function hookCreateAccount($params)
	{
		if (_BU_ENV_ == 'production') {
			if ($params['cookie']->logged AND $params['cookie']->id_customer) {
				$userDetails = $this->setSailThruCustomerProfileData($params['cookie']->id_customer);

				/*Is the customer has taken the style survey before or After the Registration*/
				if($params['styleSurveyStatus']){
					$userDetails['vars']['style_survey_taken'] = $params['styleSurveyStatus'];
				}

				/*If the customer registered through refered link like facebook, Google Ads, send the refered link to the Sailthru*/
				$referedLink = Customer::getCustomerRegisteredSource((int)$params['cookie']->id_customer);
				if($referedLink){
					$userDetails['vars']['source'] = $referedLink;
				}

				return $this->sailthruClient->createNewUser($userDetails);
			}
		}
	}

	/* To send the customer/user details to the SailThru on updating the customer details */

	public function hookUpdateCustomerDetails($params)
	{
		if (_BU_ENV_ == 'production') {
			if ($params['cookie']->id_customer) {
				$id = $userDetails['id'];
				$userDetails = $this->setSailThruCustomerProfileData($params['cookie']->id_customer);

				if (isset($userDetails['id'])) {
					unset($userDetails['id']);
				}

				return $this->sailthruClient->saveUser($id, $userDetails);
			}
		}
	}

	/* Updating the customer/user details in the SailThru on Customer Login */

	public function hookAuthentication($params)
	{
		if (_BU_ENV_ == 'production') {
			if ($params['cookie']->logged AND $params['cookie']->id_customer) {
				$id = $userDetails['id'];
				$userDetails = $this->setSailThruCustomerProfileData($params['cookie']->id_customer);

				if (isset($userDetails['id'])) {
					unset($userDetails['id']);
				}

				return $this->sailthruClient->saveUser($id, $userDetails);
			}
		}
	}

	public function hookSailThruMailSend($params)
	{
		/* Redirect to login page if the customer is not login, upon click the link on Abandonment-Cart
		 *  email template */
		if (Module::isInstalled('sailthru')) {
			if (Tools::getValue('utm_source') == 'Sailthru' AND
					Tools::getValue('utm_campaign') == 'Abandoned-Cart' AND
					!$params['cookie']->logged)
				Tools::redirect('authentication.php?back=order.php?step=1');
		}
		
		global $cookie;

		/*Sending the WELCOME EMAIL on customer registration/subscribe.*/
			if (isset($params['sailThruEmailTemplate']) && $params['sailThruEmailTemplate'] == 'Welcome') {
				/*Add the customer to "RegisterWelcome" group, to give the free shipping voucher to the  newly
				registered customers, only once*/
				$registerWelcomeGroupId = Group::getGroupIdByName('RegisterWelcome', $params['cookie']->id_lang);
				$customer = new Customer($params['cookie']->id_customer);
				$customer->addGroups(array($registerWelcomeGroupId));

				/*Sending the welcome email*/
				$template = 'Welcome';
				$email = $params['cookie']->email;
				$vars = array('coupon_code' => 'MERHABA10');
				$verified = 1;

				$this->sailthruClient->send($template, $email, $vars, $options = array(), $schedule_time = null);

				return;
			}/*Sending the Showroom remainder mail to the customer after the shoowroom is ready.*/
			else if (isset($params['sailThruEmailTemplate']) && $params['sailThruEmailTemplate'] == 'Showroom-Reminder') {
				$customerLists = $params['customerList'];
				$registerWelcomeGroupId = Group::getGroupIdByName('RegisterWelcome', $params['cookie']->id_lang);
				foreach($customerLists as $customerList){
					/*To check whether the customer has already used the register voucher*/
					$discountArray = Discount::getDiscountIdByname('MERHABA10');
					$isCustomerUsedTheDiscount = Discount::checkCustomerAsUsedDiscount($discountArray['id_discount'],$customerList['customerId']);

					/*To check whether the customer belongs to the RegisterWelcome or not*/
					$customer = new Customer($customerList['customerId']);
					$isCustomerBelongs = $customer->isMemberOfGroup($registerWelcomeGroupId);

					$vars = array();
					if (!$isCustomerUsedTheDiscount AND $isCustomerBelongs == 1) {
						$vars = array('coupon_code' => 'MERHABA10');
					}
					$template = 'Showroom-Reminder';
					$email = $customerList['customerEmail'];
					$verified = 1;

					$this->sailthruClient->send($template, $email, $vars, $options = array(), $schedule_time = null);
				}

				return;
			}/*Sending the Showroom ready mail to the customer after the shoowroom is ready.*/
			else if (isset($params['sailThruEmailTemplate']) && $params['sailThruEmailTemplate'] == 'Showroom-Ready') {
				$customerLists = $params['customerList'];
				$log = TRUE;
				$logFilePath = _PS_LOG_DIR_ . "/emarsys.txt";
				if ($log) {
					$logFile = @fopen($logFilePath, "a");
					fwrite($logFile, "Sending showroom-ready via sailthru.". "\n");
					fclose($logFile);
				}
				
				foreach($customerLists as $customerList){
					$discountArray = Discount::getDiscountIdByname('MERHABA10');
					$isCustomerUsedTheDiscount = Discount::checkCustomerAsUsedDiscount($discountArray['id_discount'],$customerList['customerId']);

					$vars = array();
					if (!$isCustomerUsedTheDiscount) {
						$vars = array('coupon_code' => 'MERHABA10');
					}
					$template = 'Showroom-Ready';
					$email = $customerList['customerEmail'];
					$verified = 1;
					//sleep(5);					
					$response = $this->sailthruClient->send($template, $email, $vars, $options = array(), $schedule_time = null);
					
					if ($log) {
						$logFile = @fopen($logFilePath, "a");
						fwrite($logFile, "Sailthru Response for $email :".print_r($response). "\n");
						fclose($logFile);
					}					
					
				}

				return;
			}/*Sending the mail to the customer on Order Approval via Sailthru.*/
			else if (isset($params['sailThruEmailTemplate']) && $params['sailThruEmailTemplate'] == 'Order-Approval') {
				$email = $params['cookie']->email;
				$sailThruPurchaseData = $this->setSailThruCartData($params);
				$items = $sailThruPurchaseData['sailThruItems'];
				$sailThruAdjustments = $sailThruPurchaseData['sailThruAdjustments'];
				$sailThruTenders = $sailThruPurchaseData['sailThruTenders'];
				$templateName = 'Order-Approval-No-Recommendation';
				
				//To unset the voucherCode and voucherCategory for the next order.
				if(isset($cookie->voucherCode)) {
					$cookie->voucherCode = NULL;
				}				
				if(isset($cookie->voucherCategory)) {
					$cookie->voucherCategory = NULL;
				}
				
				/*To check the for backorder*/
				$cart = new Cart($params['cart']->id);
				$cartProducts = $cart->getProducts();
				foreach($cartProducts as $cartProduct){
					if($cartProduct['out_of_stock'] == 1){
						$templateName = 'BackOrder-Approval-No-Recommendation';
						break;
					}
				}

				$options = array('adjustments' => $sailThruAdjustments,
					'tenders' => $sailThruTenders,
					'send_template' => $templateName);

				if (isset($_COOKIE['sailthru_bid'])) {
					$message_id = $_COOKIE['sailthru_bid'];
				} else {
					$message_id = null;
				}

				$response = $this->sailthruClient->purchase($email, $items, null, $message_id, $options);

				foreach ($sailThruPurchaseData['sailThruItems'] as $sailThruItem) {
					/*$logFilePath = _PS_LOG_DIR_ . '/sailthru_respider.log';
					if (!file_exists($logFilePath)) {
						$logFile = @fopen($logFilePath, "w");
					} else {
						$logFile = @fopen($logFilePath, "a");
					}
					if (!$logFile) {
						error_log("Log file is not writable : $logFilePath");
					}
					fwrite($logFile, 'hookUpdateQuantity: START----' . date("D M j G:i:s T Y") . "\n");
					fwrite($logFile, "sailThruItem[title]: ".$sailThruItem['title']."\n
									  sailThruItem[url]: ".$sailThruItem['url']."\n");*/
					if (_BU_ENV_ == 'production') {
						$this->sailthruClient->pushContent($sailThruItem['title'], $sailThruItem['url']);
					}
				}

				return;
			} else if (isset($params['sailThruEmailTemplate']) && $params['sailThruEmailTemplate'] == 'Abandoned-Cart') {
				$email = $params['cookie']->email;
				$sailThruPurchaseData = $this->setSailThruCartData($params);
				$sailThruItems = $sailThruPurchaseData['sailThruItems'];
				$sailThruAdjustments = $sailThruPurchaseData['sailThruAdjustments'];
				$sailThruTenders = $sailThruPurchaseData['sailThruTenders'];
				$items = $sailThruItems;

				if ($items[0]['qty'] < 1) {
					$options = array();
				} else {
					$options = array('adjustments' => $sailThruAdjustments,
						'tenders' => $sailThruTenders,
						'reminder_template' => 'Abandoned-Cart',
						'reminder_time' => time() + 3600 * 2);
				}

				if (isset($_COOKIE['sailthru_bid'])) {
					$message_id = $_COOKIE['sailthru_bid'];
				} else {
					$message_id = null;
				}

				return $response = $this->sailthruClient->purchaseIncomplete($email, $items, $message_id, $options);
			} else if (isset($params['sailThruEmailTemplate']) && $params['sailThruEmailTemplate'] == 'Stock-Alarm') {
				$products = $params['products'];
				$template = 'Stock Alarm';
				$emailSentCustomersList = array();
				foreach($products as $product){
					$shoeSize = $product['shoe_size'];
					$quantity = Product::getProductQuantityOfParticularCombination($product['id_product'],$product['id_product_attribute']);
					$customerIds = CustomerStockRemainder::getRemainderCustomersForThisProduct($product['id_product_attribute']);
					$productDetails = $this->getProductDetailsToSailthru($product['id_product']);
					$vars =  array('stock_alarm_id_product' => $productDetails['id_product'],
									'stock_alarm_title' => $productDetails['title'],
									'stock_alarm_url' => $productDetails['url'],
									'stock_alarm_description' => $productDetails['description'],
									'stock_alarm_images' => $productDetails['images']
								  );

					//Sending the Stock-Alarm Emails
					$i = 0;
					foreach($customerIds as $customerId){
						$email = Customer::getEmailById((int)$customerId['id_customer']);
						$result = array();
						if(($shoeSize == 35 || $shoeSize == 40) AND $quantity >= 1) {
							$result = $this->sailthruClient->send($template, $email, $vars, $options = array(), $schedule_time = null);
						}else if(($shoeSize == 36 || $shoeSize == 39) AND $quantity >= 2) {
							$result = $this->sailthruClient->send($template, $email, $vars, $options = array(), $schedule_time = null);
						}else if(($shoeSize == 37 || $shoeSize == 38) AND $quantity >= 3) {
							$result = $this->sailthruClient->send($template, $email, $vars, $options = array(), $schedule_time = null);
						}
						//Collecting the email sent customerIds
						if(!$result['error'] AND $result AND $result['send_id'] AND $result['email'] == $email){
							$emailSentCustomersList[$product['id_product_attribute']][$i] = $customerId['id_customer'];
							$i++;

						}
					}

				}
				//Updating email sent status in database
				if($emailSentCustomersList){
					CustomerStockRemainder::changeTheStatus($emailSentCustomersList);
				}
			}else if (isset($params['sailThruEmailTemplate']) && $params['sailThruEmailTemplate'] == 'voucher') {
				/*Sending the Cancel/Exchange/Refund voucher details email*/
				$template = 'Voucher';
				$email = $params['voucherDetail']['customerEmail'];
				
				$vars = array('voucher_amt' => $params['voucherDetail']['voucherAmount'],
					          'voucher_num' => $params['voucherDetail']['voucherNumber']);
				$verified = 1;

				$this->sailthruClient->send($template, $email, $vars, $options = array(), $schedule_time = null);

				return;
			}else if (isset($params['sailThruEmailTemplate']) && $params['sailThruEmailTemplate'] == 'Increment-Voucher') {
				/*Sending the Increment-voucher email*/
				$template = 'Increment-Voucher';
				$email = $params['voucherDetail']['customerEmail'];
				
				$vars = array('voucher_amt' => $params['voucherDetail']['voucherAmount'],
					          'voucher_num' => $params['voucherDetail']['voucherNumber'],
							  'orderId' => $params['voucherDetail']['orderId']);					
				
				$this->sailthruClient->send($template, $email, $vars);
				
				return;
			}else if (isset($params['sailThruEmailTemplate']) && $params['sailThruEmailTemplate'] == 'Order-Statuses') {
				/*Sending the All the transactional emails*/
				$template = $params['orderStatusDetail']['emailTemplate'];
				$email = $params['orderStatusDetail']['customerEmail'];
				$vars = $params['orderStatusDetail']['orderData'];
							
				$verified = 1;

				$this->sailthruClient->send($template, $email, $vars, $options = array(), $schedule_time = null);

				return;
			}

		return;
	}

	/* Updating the style points on Exchange/Refund/order Shipped */

	public function hookUpdateOrderStatus($params)
	{
		if ($params['newOrderStatus']->id == Configuration::get('PS_OS_EXCHANGE') || $params['newOrderStatus']->id == Configuration::get('PS_OS_PARTIALEXCHANG') ||
				$params['newOrderStatus']->id == Configuration::get('PS_OS_SHIPPING') || $params['newOrderStatus']->id == Configuration::get('PS_OS_SHIPPING') ||
				$params['newOrderStatus']->id == Configuration::get('PS_OS_REFUND') || $params['newOrderStatus']->id == Configuration::get('PS_OS_PARTIALREFUND')) {

			$order = new Order($params['id_order']);
			$userDetails = $this->setSailThruCustomerProfileData($order->id_customer);
			$id = $userDetails['id'];

			if (isset($userDetails['id'])) {
				unset($userDetails['id']);
			}

			$this->sailthruClient->saveUser($id, $userDetails);

			return;
		}
	}

	/*Update the product details in the Sailthru by Re-spider*/

	public function hookUpdateQuantity($params) {
		if (_BU_ENV_ == 'production') {
			global $link;
			$defaultLanguageId = Configuration::get('PS_LANG_DEFAULT');
			$product = $params['product'];
			$ProductName = $product->name;
			$productLinkRewrite = $product->link_rewrite;
			$productLink = $link->getProductLink((int) $product->id, NULL, $productLinkRewrite, $product->category, NULL, $defaultLanguageId);

			/*$logFilePath = _PS_LOG_DIR_ . '/sailthru_respider.log';
			if (!file_exists($logFilePath)) {
				$logFile = @fopen($logFilePath, "w");
			} else {
				$logFile = @fopen($logFilePath, "a");
			}
			if (!$logFile) {
				error_log("Log file is not writable : $logFilePath");
			}
			fwrite($logFile, 'hookUpdateQuantity: START----' . date("D M j G:i:s T Y") . "\n");
			fwrite($logFile, "product->id: ".$product->id." \n
							  ProductName: ".print_r($ProductName, true)."\n
							  productLinkRewrite: ".print_r($productLinkRewrite, true)." \n
							  defaultLanguageId: ".$defaultLanguageId." \n
							  ProductName[defaultLanguageId]: ".$ProductName[$defaultLanguageId]."\n
							  productLinkRewrite[defaultLanguageId]: ".$productLinkRewrite[$defaultLanguageId]."\n
							  productLink: ".$productLink."\n
							  product->category: ".$product->category." \n
							  product->ean13: ".$product->ean13." \n \n");*/

			if($product->active){
				$this->sailthruClient->pushContent($ProductName, $productLink);
			}
		}
	}

	/*Update the product details in the Sailthru by Re-spider*/

	public function hookproductOutOfStock($params){
		/*if (_BU_ENV_ == 'production') {
			global $link;
			$defaultLanguageId = Configuration::get('PS_LANG_DEFAULT');
			$product = $params['product'];
			$ProductName = $product->name;
			$productLinkRewrite = $product->link_rewrite;
			$productLink = $link->getProductLink((int) $product->id, NULL, $productLinkRewrite[$defaultLanguageId], $product->category, NULL, $defaultLanguageId);

			if($product->active){
				$this->sailthruClient->pushContent($ProductName[$defaultLanguageId], $productLink);
			}
		}*/
	}

	/*Update the product details in the Sailthru by Re-spider*/

	public function hookDeleteProduct($params){
		/*if (_BU_ENV_ == 'production') {
			global $link;
			$defaultLanguageId = Configuration::get('PS_LANG_DEFAULT');
			$product = $params['product'];
			$ProductName = $product->name;
			$productLinkRewrite = $product->link_rewrite;
			$productLink = $link->getProductLink((int) $product->id, NULL, $productLinkRewrite[$defaultLanguageId], $product->category, NULL, $defaultLanguageId);

			if($product->active){
				$this->sailthruClient->pushContent($ProductName[$defaultLanguageId], $productLink);
			}
		}*/
	}

	public function setSailThruCustomerProfileData($customerId)
	{
		$customerDetails = Customer::getCustomerDetails($customerId);

		//Get the customer favourite color
		$customerColors = explode(',', $customerDetails['color']);
		$customerColorsStr = "";

		foreach ($customerColors as $color) {
			$customerColorsStr .= substr($color, 2) . ",";
		}

		/*Customer Group Details*/
		$customer = new Customer((int)$customerId);
		$customerGroups = $customer->getGroups();
		$customerGroupIdsList = '';
		foreach($customerGroups as $customerGroup){
			$customerGroupIdsList .= $customerGroup.',';
		}
		$customerGroupIdsList = rtrim($customerGroupIdsList, ',');

		$customerColorsStr = rtrim(str_replace("-", "", $customerColorsStr), ",");

		$userDetails = array(
			'id' => $customerDetails['email'],
			'key' => 'email',
			'fields' => array('keys' => 1,
				'vars' => 1),
			'keys' => array('email' => $customerDetails['email']),
			'vars' => array('customer_id' => $customerDetails['id_customer'],
				'default_group_id' => $customerDetails['id_default_group'],
				'customer_group_ids' => $customerGroupIdsList,
				'first_name' => $customerDetails['first_name'],
				'last_name' => $customerDetails['last_name'],
				'name' => $customerDetails['name'],
				'shoe_size' => (int) $customerDetails['shoe_size'],
				'age' => $customerDetails['age'],
				'birthday' => substr($customerDetails['birthday'],5),
				'color' => $customerColorsStr,
				'dress_size' => $customerDetails['dress_size'],
				'style_points' => (int) $customerDetails['style_points'] ? $customerDetails['style_points'] : 0,
				'style_survey_result' => $customerDetails['style_survey_result'],
				'registration_date' => $customerDetails['registration_date'],
				'last_visit_date' => $customerDetails['last_visit_date']),
			'lists' => array($this->list => 1),
			'optout_email' => 'none',
			'login' => array('site' => Tools::getShopDomain(),
				'ip' => $_SERVER['REMOTE_ADDR'],
				'user_agent' => $_SERVER['HTTP_USER_AGENT'])
		);

		return $userDetails;
	}

	public function setSailThruCartData($data)
	{
		global $link,$cookie;

		$defaultLanguageId = Configuration::get('PS_LANG_DEFAULT');
		$cartAbandonment = $data['sailThruEmailTemplate'] == 'Abandoned-Cart' ? 1 : 0;
		$cart = new Cart($data['cart']->id);
		$cartProducts = $cart->getProducts();
		$orderName = $data['sailThruData']['order_name'] ? $data['sailThruData']['order_name'] : '';
		$orderDate = $data['sailThruData']['order_date'] ? $data['sailThruData']['order_date'] : '';
		$payment = $data['sailThruData']['payment'] ? $data['sailThruData']['payment'] : '';
		$total = $data['sailThruData']['total_paid'] ? $data['sailThruData']['total_paid'] : $cart->getOrderTotal(true, Cart::BOTH);
		$totalProducts = $data['sailThruData']['total_products'] ? $data['sailThruData']['total_products'] : $cart->getOrderTotal(true, Cart::ONLY_PRODUCTS);
		$discounts = $data['sailThruData']['total_discounts'] ? $data['sailThruData']['total_discounts'] : $cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS);
		$shipping = $data['sailThruData']['total_shipping'] ? $data['sailThruData']['total_shipping'] : $cart->getOrderTotal(true, Cart::ONLY_SHIPPING);
		$installments = $data['sailThruData']['installments'] ? $data['sailThruData']['installments'] : 0;
		$installmentInterest = $data['sailThruData']['installment_interest'] ? $data['sailThruData']['installment_interest'] : 0;
		$interestAmount = $data['sailThruData']['interest_amount'] ? $data['sailThruData']['interest_amount'] : 0;
		$eachInstallmentAmount = $data['sailThruData']['each_installment_amount'] ? $data['sailThruData']['each_installment_amount'] : 0;
		$cashOnDelivery = $data['sailThruData']['cash_on_delivery'] ? $data['sailThruData']['cash_on_delivery'] : 0;
		$preSalesAgreementLink = $link->getPageLink('agreements-general.php') . '?id_cms=20&id_order=' . ltrim($orderName, '#') . '&s_key=' . md5(ltrim($orderName, '#') . _COOKIE_KEY_);
		$nonMembersAgreementLink = $link->getPageLink('agreements-general.php') . '?id_cms=21&id_order=' . ltrim($orderName, '#') . '&s_key=' . md5(ltrim($orderName, '#') . _COOKIE_KEY_);

		if ($cashOnDelivery) {
			$shipping += $cashOnDelivery;
		}

		$sailThruItems = array();
		$sailThruAdjustments = array();
		$sailThruTenders = array();

		if ($cartProducts) {
			foreach ($cartProducts as $cartProduct) {
				//Product Tags
				$tagsList = Tag::getProductTags((int) $cartProduct['id_product']);
				$productTags = 'pid'.$cartProduct['id_product'].','.trim($cartProduct['name']).',';
				foreach ($tagsList[$data['cookie']->id_lang] as $tag) {
						$productTags .= $tag . ",";
				}

				if($cartProduct['stock_quantity'] < 3)
					$productTags .= 'low-stock'.',';

				$productTags = rtrim($productTags, ',');

				if ($cartAbandonment)
					$thumbImagePath = $link->getImageLink($cartProduct['link_rewrite'], $cartProduct['id_image'], 'prodsmall');
				else
					$thumbImagePath = $link->getImageLink($cartProduct['link_rewrite'], $cartProduct['id_image'], 'prodthumb');

				//Original Price of the product  without reduction
				$product = new Product($cartProduct['id_product']);
				$original_Price = Tools::ps_round($product->getPriceWithoutReduct(), 2);

				/*LOG Part Start */
				/*$logFilePath = _PS_LOG_DIR_ . '/sailthru_respider.log';
				if (!file_exists($logFilePath)) {
					$logFile = @fopen($logFilePath, "w");
				} else {
					$logFile = @fopen($logFilePath, "a");
				}
				if (!$logFile) {
					error_log("Log file is not writable : $logFilePath");
				}
				if($data['sailThruData']['order_name']){
					fwrite($logFile, 'purchase: START----' . date("D M j G:i:s T Y") . "\n");
					fwrite($logFile, "Order Items\n");
					fwrite($logFile, "cartProduct['id_product']: ".$cartProduct['id_product']."\n
									cartProduct['link_rewrite']: ".$cartProduct['link_rewrite']."\n
									cartProduct['category']: ".$cartProduct['category']."\n
									cartProduct['ean13']: ".$cartProduct['ean13']."\n
									defaultLanguageId: ".$defaultLanguageId."\n");
				}*/

				/*LOG Part END */
				//UNCOMMENT This, when multiple color combination are used in a single product_id
				/*if ($cartProduct['id_color_default'] != 0)
					$productLink = $link->getProductLink((int) $cartProduct['id_product'], $cartProduct['id_product_attribute'] , $cartProduct['link_rewrite'], $cartProduct['category'], NULL);
				else*/
					$productLink = $link->getProductLink((int) $cartProduct['id_product'], NULL, $cartProduct['link_rewrite'], $cartProduct['category'], NULL, $defaultLanguageId);

				//Items
				$sailThruItems[] = array("qty" => $cartProduct['cart_quantity'],
					"title" => rtrim($cartProduct['name'], ' '),
					"price" => $original_Price * 100,
					"id" => $cartProduct['reference'],
					"url" => $productLink,
					"tags" => $productTags,
					"vars" => array('image_url' => $thumbImagePath,
						'order_name' => $orderName,
						'payment' => $payment,
						'total_paid' => $total,
						'total_products' => $totalProducts,
						'total_discounts' => $discounts,
						'total_shipping' => $shipping,
						'installments' => $installments,
						'installment_interest' => $installmentInterest,
						'interest_amount' => $interestAmount,
						'each_installment_amount' => $eachInstallmentAmount,
						'cash_on_delivery' => $cashOnDelivery,
						'shipping_charge_for_cash_on_delivery'=> Delivery::getShippingChargeForCashOnDelivery($data['cookie']->id_customer, $data['cookie']->id_lang),
						'order_date' => $orderDate,
						'pre_sales_agreement_link' => $preSalesAgreementLink,
						'non_members_agreement_link' => $nonMembersAgreementLink,
						'order_voucher_code' => isset($cookie->voucherCode) ? $cookie->voucherCode : NULL,
						'order_voucher_category' => isset($cookie->voucherCategory) ? $cookie->voucherCategory : NULL
					)
				);
								
				//Discounted products price adjustments in the sailthru revenue
				if ($cartProduct['reduction_applies'] AND $cartProduct['reduction_applies'] == 1) {
					$sailThruAdjustments[] = array('price' => ($original_Price - $cartProduct['price_wt']) * (-100),
						'title' => 'sp_discount');
				}
			}

			//Voucher or Discount , Shipping, Interest Amount Adjustments in the sailthru revenue
			if ($discounts > 0.00) {
				$sailThruAdjustments[] = array('price' => $discounts * (-100),
					'title' => 'discount');
			}
			if ($shipping > 0.00) {
				$sailThruAdjustments[] = array('price' => $shipping * (100),
					'title' => 'shipping');
			}
			if ($interestAmount > 0.00) {
				$sailThruAdjustments[] = array('price' => $interestAmount * (100),
					'title' => 'interest');
			}

			//Tenders
			$sailThruTenders[] = array('price' => $total * 100,
				'title' => $payment);
		} else {
			$sailThruItems[] = array("qty" => '',
				"title" => '',
				"price" => '',
				"id" => '',
				"url" => '',
				"tags" => '',
				"vars" => array()
			);
		}

		$sailThruPurchaseData = array('sailThruItems' => $sailThruItems,
			'sailThruAdjustments' => $sailThruAdjustments,
			'sailThruTenders' => $sailThruTenders);
		return $sailThruPurchaseData;
	}



	function getProductDetailsToSailthru($productId)
	{
		global $link;
		$id_lang = Configuration::get('PS_LANG_DEFAULT');
		$productDetails = $this->getProductDetails($id_lang, $productId);

		/* TODO: please replace NULL with $lowStockProduct['id_product_attribute'], when we are using the multiple color combination. */
		foreach ($productDetails AS &$productDetail) {
			$productDetail['link'] = $link->getProductLink($productDetail['id_product'], NULL, $productDetail['link_rewrite'], $productDetail['category'], $productDetail['ean13']);
			$productDetail['id_image'] = Product::defineProductImage($productDetail, $id_lang);
		}
		$productDetailsToSailthru = array();
		$i = 0;
		foreach ($productDetails as $productDetail) {
			$productDetailsToSailthru[$i]['id_product'] = $productDetail['id_product'];
			$productDetailsToSailthru[$i]['title'] = $productDetail['name'];
			$productDetailsToSailthru[$i]['url'] = $productDetail['link'];
			$productDetailsToSailthru[$i]['description'] = strlen($productDetail['description']) < 140 ? $productDetail['description'] : substr($productDetail['description'], 0, 140) . "...";
			$productDetailsToSailthru[$i]['images'] = array('full' => array('url' =>  $link->getImageLink($productDetail['link_rewrite'], $productDetail['id_image'], 'large')),
															'thumb' => array('url' =>  $link->getImageLink($productDetail['link_rewrite'], $productDetail['id_image'], 'prodthumb')),
															'prodthumb' => array('url' => $link->getImageLink($productDetail['link_rewrite'], $productDetail['id_image'], 'prodsmall'))
														);

			$i++;
		}

		return $productDetailsToSailthru[0];
	}

	function getProductDetails($id_lang, $productId)
	{
		$query = 'SELECT p.id_product, pl.name, p.`id_category_default`, pa.`id_product_attribute` , IF(pa.default_image, pa.`default_image` ,i.`id_image`) as id_image , pl.`link_rewrite`, pl.`name`, pl.`description`, pl.`description_short`,  il.`legend`, p.`ean13`, p.`upc`, cl.`link_rewrite` AS category
				FROM ' . _DB_PREFIX_ . 'product p
				LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute` pa ON (pa.`id_product` = p.`id_product` AND pa.`default_on` = 1 AND p.`id_color_default` = 2)
				LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (p.`id_product` = pl.`id_product` AND pl.`id_lang` = ' . (int) $id_lang . ')
				LEFT JOIN `' . _DB_PREFIX_ . 'image` i ON (i.`id_product` = p.`id_product` AND IF(pa.default_image ,\'  \', i.`cover` = 1))
				LEFT JOIN `' . _DB_PREFIX_ . 'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = ' . (int) $id_lang . ')
				LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl ON (cl.`id_category` = p.`id_category_default` AND cl.`id_lang` = ' . (int) $id_lang . ')
				WHERE p.`active` = 1 AND p.id_product = ' . $productId;


		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($query);

		return $result;
	}
}

?>
