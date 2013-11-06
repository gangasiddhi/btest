<?php

/* Script for sending the customer details to the SailThru */

require_once(dirname(__FILE__) . '/config/config.inc.php');
require_once(dirname(__FILE__) . '/init.php');
require_once(_PS_MODULE_DIR_ . 'sailthru/lib/Sailthru_Client.php');
$api_key = "06d90a997a726e587cc1a4dff7ae0155";
$api_secret = '8f566ebc91cccdcbc03f15b35c6c4f58';
$id_lang = (int) Configuration::get('PS_LANG_DEFAULT');
$sailthruClient = new Sailthru_Client($api_key, $api_secret);
$option = Tools::getValue('opt');

if ($option == 'best_seller') {
	global $protocol_content, $link;

	$start = date("Y-m-d 21:00:00", strtotime("-2 day"));
	$end = date("Y-m-d 21:00:00", strtotime("-1 day"));
	$lastsevendays = date("Y-m-d H:i:s", strtotime("today") - (3600 * 24 * 7));

	$bestsellerProducts = ProductSale::getProductBestSales($id_lang, $start, $end, 0, 100);

	//Filtering the lowstock products(if product_quantity < 3)
	$bestsellers = array();
	foreach ($bestsellerProducts as $bestseller) {
		if (isLowStock($bestseller['id_product']) < 3) {
			continue;
		} else {
			$bestsellers[] = $bestseller;
		}
	}

	if (sizeof($bestsellers) < 5) {
		$start = $lastsevendays;
		$bestsellers = ProductSale::getProductBestSales($id_lang, $start, $end, 0, 100);
	}
	
	/* TODO: please replace NULL with $row['id_product_attribute'], when we are using the multiple color combination. */
	foreach ($bestsellers AS &$row) {
		$row['link'] = $link->getProductLink($row['id_product'], NULL , $row['link_rewrite'], $row['category'], $row['ean13']);
		$row['id_image'] = Product::defineProductImage($row, $id_lang);
	}

	$bestSellProducts = array();
	$i = 0;
	foreach ($bestsellers as $bestseller) {
		$bestSellProducts[$i]['id_product'] = $bestseller['id_product'];
		$bestSellProducts[$i]['title'] = $bestseller['name'];
		$bestSellProducts[$i]['url'] = $bestseller['link'];

		$thumbImagePath = __PS_BASE_URI__ . $bestseller['id_image'] . '-prodsmall/' . $bestseller['link_rewrite'] . '.jpg';
		$mediumImagePath = __PS_BASE_URI__ . $bestseller['id_image'] . '-medium/' . $bestseller['link_rewrite'] . '.jpg';
		$bestSellProducts[$i]['images'] = array(
			'full' => array('url' => $protocol_content . Tools::getMediaServer($mediumImagePath) . $mediumImagePath),
			'thumb' => array('url' => $protocol_content . Tools::getMediaServer($thumbImagePath) . $thumbImagePath)
		);

		$i++;
	}

	$feedName = array('name' => 'Best-Seller-Products');
	$jsonData = array('feed' => $feedName,
		'content' => $bestSellProducts);

	header('Content-type: text/json');
	header('Content-type: application/json');
	print_r(Tools::jsonEncode($jsonData));
	exit;
} else if ($option == 'cart') { /* Getting the details of Carts, Full Carts, Orders, per date in to the CSV File. */
	$days = Tools::getValue('days');
	$i = $days;

	$fileName = _PS_DOWNLOAD_DIR_ . "cart_fullcart_order.csv";

	$fp = fopen($fileName, 'w') or die('cant oen the file');
	fwrite($fp, "Date;Carts;Full-Carts;Orders\n");

	for ($i; $i > 0; $i--) {
		$todayDate = date('Y-m-d', time() - 24 * $i * 60 * 60);
		$startDate = date('Y-m-d 00:00:00', time() - 24 * $i * 60 * 60) . "<br>";
		$endDate = date('Y-m-d 23:59:59', time() - 24 * $i * 3600) . "<br>";

		$dateBetween = '"' . $startDate . '"' . ' AND ' . '"' . $endDate . '"';

		$carts = Db::getInstance()->getValue('SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'cart WHERE id_cart IN (SELECT id_cart FROM ' . _DB_PREFIX_ . 'cart_product) AND (date_add BETWEEN ' . $dateBetween . ' OR date_upd BETWEEN ' . $dateBetween . ')');
		$fullcarts = Db::getInstance()->getValue('SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'cart WHERE id_cart IN (SELECT id_cart FROM ' . _DB_PREFIX_ . 'cart_product) AND id_address_invoice != 0 AND (date_add BETWEEN ' . $dateBetween . ' OR date_upd BETWEEN ' . $dateBetween . ')');
		$orders = Db::getInstance()->getValue('SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'orders WHERE valid = 1 AND date_add BETWEEN ' . $dateBetween);

		fwrite($fp, "$todayDate;$carts;$fullcarts;$orders\n");
	}

	fclose($fp);
} else if ($option == 'lowstock') {/* get all the Lowstock products based on their attributes(Shoe sizes & Colors) */
	/* Get all the product attributes */
	$productAttributes = Attribute::getAttributes($id_lang);

	/* get all the Lowstock products based on their attributes(Shoe sizes & Colors) */
	$lowStockProductDetails = getLowStockProductAttributes($id_lang, 0, 100);
	/* TODO: please replace NULL with $lowStockProduct['id_product_attribute'], when we are using the multiple color combination. */
	foreach ($productAttributes as $productAttribute) {
		foreach ($lowStockProductDetails[$productAttribute['name']] as &$lowStockProduct) {
			$lowStockProduct['link'] = $link->getProductLink($lowStockProduct['id_product'], NULL, $lowStockProduct['link_rewrite'], $lowStockProduct['category'], $lowStockProduct['ean13']);
			$lowStockProduct['id_image'] = Product::defineProductImage($lowStockProduct, $id_lang);
		}
	}

	$lowStockProducts = array();

	foreach ($productAttributes as $productAttribute) {
		$i = 0;
		foreach ($lowStockProductDetails[$productAttribute['name']] as $lowStockProductDetail) {
			$lowStockProducts[$productAttribute['name']][$i]['id_product'] = $lowStockProductDetail['id_product'];
			$lowStockProducts[$productAttribute['name']][$i]['title'] = $lowStockProductDetail['name'];
			$lowStockProducts[$productAttribute['name']][$i]['price'] = Tools::ps_round($lowStockProductDetail['orderprice'], 2);
			$lowStockProducts[$productAttribute['name']][$i]['quantity'] = $lowStockProductDetail['quantity'];
			$lowStockProducts[$productAttribute['name']][$i]['url'] = $lowStockProductDetail['link'];
			$thumbImagePath = __PS_BASE_URI__ . $lowStockProductDetail['id_image'] . '-prodsmall/' . $lowStockProductDetail['link_rewrite'] . '.jpg';
			$mediumImagePath = __PS_BASE_URI__ . $lowStockProductDetail['id_image'] . '-medium/' . $lowStockProductDetail['link_rewrite'] . '.jpg';
			$lowStockProducts[$productAttribute['name']][$i]['images'] = array('full' => array('url' => $protocol_content . Tools::getMediaServer($mediumImagePath) . $mediumImagePath),
				'thumb' => array('url' => $protocol_content . Tools::getMediaServer($thumbImagePath) . $thumbImagePath));

			$i++;
		}
	}

	$feedName = array('name' => 'Low-Stock-Product-List');
	$jsonData = array('feed' => $feedName,
		'content' => $lowStockProducts);

	header('Content-type: text/json');
	header('Content-type: application/json');
	print_r(Tools::jsonEncode($jsonData));
	exit;
} else if ($option == 'is_lowstock') {
	$productIds = Tools::getValue('pid');
	$productIds = explode('|', $productIds);
	$productIdList = '';

	foreach ($productIds as $productId) {
		$productIdList .= $productId . ',';
	}

	$productIdList = rtrim($productIdList, ',');

	/* $isLowStock = Db::getInstance()->ExecuteS('SELECT p.id_product, p.quantity
	  FROM `' . _DB_PREFIX_ . 'product` p
	  WHERE p.id_product IN (' . $productIds . ')');

	  $lowStockList = array();
	  foreach ($isLowStock as $lowstock) {
	  if ($lowstock <= 2 AND $isLowStock != 0) {
	  $lowStockList[$lowstock['id_product']] = array('lowstock' => 1);
	  } else {
	  $lowStockList[$lowstock['id_product']] = array('lowstock' => 0);
	  }
	  }

	  header('Content-type: text/json');
	  header('Content-type: application/json');
	  print_r(Tools::jsonEncode($lowStockList));
	  exit; */

	$lowStockProductDetails = getLowStockProducts($id_lang, 0, 100, $productIdList);

	/* TODO: please replace NULL with $lowStockProduct['id_product_attribute'], when we are using the multiple color combination. */
	foreach ($lowStockProductDetails AS &$lowStockProduct) {
		$lowStockProduct['link'] = $link->getProductLink($lowStockProduct['id_product'], NULL, $lowStockProduct['link_rewrite'], $lowStockProduct['category'], $lowStockProduct['ean13']);
		$lowStockProduct['id_image'] = Product::defineProductImage($lowStockProduct, $id_lang);
	}

	$lowStockProducts = array();
	$i = 0;

	foreach ($lowStockProductDetails as $lowStockProductDetail) {
		$lowStockProducts[$i]['id_product'] = $lowStockProductDetail['id_product'];
		$lowStockProducts[$i]['title'] = $lowStockProductDetail['name'];
		$lowStockProducts[$i]['price'] = Tools::ps_round($lowStockProductDetail['orderprice'], 2);
		$lowStockProducts[$i]['quantity'] = $lowStockProductDetail['quantity'];
		$lowStockProducts[$i]['url'] = $lowStockProductDetail['link'];
		$thumbImagePath = __PS_BASE_URI__ . $lowStockProductDetail['id_image'] . '-prodsmall/' . $lowStockProductDetail['link_rewrite'] . '.jpg';
		$mediumImagePath = __PS_BASE_URI__ . $lowStockProductDetail['id_image'] . '-medium/' . $lowStockProductDetail['link_rewrite'] . '.jpg';
		$lowStockProducts[$i]['images'] = array('full' => array('url' => $protocol_content . Tools::getMediaServer($mediumImagePath) . $mediumImagePath),
			'thumb' => array('url' => $protocol_content . Tools::getMediaServer($thumbImagePath) . $thumbImagePath));

		$i++;
	}

	$feedName = array('name' => 'Low-Stock-Products');
	$jsonData = array('feed' => $feedName,
		'content' => $lowStockProducts);

	header('Content-type: text/json');
	header('Content-type: application/json');
	print_r(Tools::jsonEncode($jsonData));
	exit;
} else if ($option == 'weekly_digest') {
	$lastWeekMonday = date('Y-m-d 00:00:00', strtotime('last week'));
	$lastWeekFriday = date('Y-m-d 21:00:00', strtotime('last week') + 4 * 86400);

	//Get the best sales
	$bestsellers = ProductSale::getProductBestSales($id_lang, $lastWeekMonday, $lastWeekFriday, 0, 200);

	//Separating the shoes and handbags
	$bestsellers = filterHandbagsAndShoes($id_lang, $bestsellers);

	//Best Shoe Sales of this week
	$bestSaleShoes = array();
	$bestSaleShoes = $bestsellers['shoes'];
	/* TODO: please replace NULL with $lowStockProduct['id_product_attribute'], when we are using the multiple color combination. */
	foreach ($bestSaleShoes AS &$bestSaleShoe) {
		$bestSaleShoe['link'] = $link->getProductLink($bestSaleShoe['id_product'], NULL, $bestSaleShoe['link_rewrite'], $bestSaleShoe['category'], $bestSaleShoe['ean13']);
		$bestSaleShoe['id_image'] = Product::defineProductImage($bestSaleShoe, $id_lang);
	}
	$thisWeekBestsaleShoes = array();
	$i = 0;
	foreach ($bestSaleShoes as $shoe) {
		$thisWeekBestsaleShoes[$i]['id_product'] = $shoe['id_product'];
		$thisWeekBestsaleShoes[$i]['title'] = $shoe['name'];
		$thisWeekBestsaleShoes[$i]['url'] = $shoe['link'];
		$thisWeekBestsaleShoes[$i]['description'] = strlen($shoe['description']) < 140 ? $shoe['description'] : substr($shoe['description'], 0, 140) . "...";
		$thumbImagePath = __PS_BASE_URI__ . $shoe['id_image'] . '-prodsmall/' . $shoe['link_rewrite'] . '.jpg';
		$mediumImagePath = __PS_BASE_URI__ . $shoe['id_image'] . '-medium/' . $shoe['link_rewrite'] . '.jpg';
		$prodThumbImagePath = __PS_BASE_URI__ . $shoe['id_image'] . '-prodthumb/' . $shoe['link_rewrite'] . '.jpg';
		$thisWeekBestsaleShoes[$i]['images'] = array(
			'full' => array('url' => $protocol_content . Tools::getMediaServer($mediumImagePath) . $mediumImagePath),
			'thumb' => array('url' => $protocol_content . Tools::getMediaServer($thumbImagePath) . $thumbImagePath),
			'prodthumb' => array('url' => $protocol_content . Tools::getMediaServer($prodThumbImagePath) . $prodThumbImagePath)
		);

		$i++;
	}

	//Best handbags sales of this week	
	$bestSaleHandbags = $bestsellers['handbags'];
	/* TODO: please replace NULL with $bestSaleHandbag['id_product_attribute'], when we are using the multiple color combination. */
	foreach ($bestSaleHandbags AS &$bestSaleHandbag) {
		$bestSaleHandbag['link'] = $link->getProductLink($bestSaleHandbag['id_product'], NULL, $bestSaleHandbag['link_rewrite'], $bestSaleHandbag['category'], $bestSaleHandbag['ean13']);
		$bestSaleHandbag['id_image'] = Product::defineProductImage($bestSaleHandbag, $id_lang);
	}

	$thisWeekBestsaleHandbags = array();
	$i = 0;
	foreach ($bestSaleHandbags as $handbag) {
		$thisWeekBestsaleHandbags[$i]['id_product'] = $handbag['id_product'];
		$thisWeekBestsaleHandbags[$i]['title'] = $handbag['name'];
		$thisWeekBestsaleHandbags[$i]['url'] = $handbag['link'];
		$thisWeekBestsaleHandbags[$i]['description'] = strlen($handbag['description']) < 140 ? $handbag['description'] : substr($handbag['description'], 0, 140) . "...";
		$thumbImagePath = __PS_BASE_URI__ . $handbag['id_image'] . '-prodsmall/' . $handbag['link_rewrite'] . '.jpg';
		$mediumImagePath = __PS_BASE_URI__ . $handbag['id_image'] . '-medium/' . $handbag['link_rewrite'] . '.jpg';
		$prodThumbImagePath = __PS_BASE_URI__ . $handbag['id_image'] . '-prodthumb/' . $handbag['link_rewrite'] . '.jpg';
		$thisWeekBestsaleHandbags[$i]['images'] = array(
			'full' => array('url' => $protocol_content . Tools::getMediaServer($mediumImagePath) . $mediumImagePath),
			'thumb' => array('url' => $protocol_content . Tools::getMediaServer($thumbImagePath) . $thumbImagePath),
			'prodthumb' => array('url' => $protocol_content . Tools::getMediaServer($prodThumbImagePath) . $prodThumbImagePath)
		);

		$i++;
	}

	/* Exporting the data to JSON format */
	$feedName = array('name' => 'Weekly_Digest');
	$jsonData = array('feed' => $feedName,
		'content' => array('shoes' => $thisWeekBestsaleShoes,
			'handbags' => $thisWeekBestsaleHandbags
			));

	header('Content-type: text/json');
	header('Content-type: application/json');
	print_r(Tools::jsonEncode($jsonData));
	exit;
} else if ($option == 'birthday') {
    $log = true;
    $logFilePath = _PS_LOG_DIR_ . "/sailthru_birthday_email.txt";
    if (!file_exists($logFilePath)) {
        $logFile = @fopen($logFilePath, "w");
    } else {
        $logFile = @fopen($logFilePath, "a");
    }
    if (!$logFile) {
        error_log("Log file is not writable : $logFilePath");
    }

    if ($log) {
        $logFile = @fopen($logFilePath, "a");
        fwrite($logFile, 'START----' . date("D M j G:i:s T Y") . "\n");
        fclose($logFile);
    }
	$customerBirthdayGroup = date('l') . 'Birthday' . '15';
	$memberBirthdayGroup = date('l') . 'Birthday' . '10';
	$birthdayFreeShippingGroup = 'BirthdayFreeShipping';
	
	$customerDiscountName = 'bu' . strtolower(date('D')) . 'bir15';
	$memberDiscountName = 'bu' . strtolower(date('D')) . 'bir10';
	$birthdayFreeShippingDiscountName = 'bubirfreeship';
			
	$customerGroups = Group::getGroups($id_lang);
	$customerBirthdayGroupId = 0;
	$memberBirthdayGroupId = 0;
	$birthdayFreeShippingGroupId = 0;
	
	foreach ($customerGroups as $customerGroup) {
		$group = new Group($customerGroup['id_group']);
		if ($group->name[$id_lang] === $customerBirthdayGroup) {
			$customerBirthdayGroupId = $group->id;
			//Remove all the existing customers in the $todayGroup
			$group->deleteAllCustomersFromGroup($group->id);
		} else if ($group->name[$id_lang] === $memberBirthdayGroup) {
			$memberBirthdayGroupId = $group->id;
			//Remove all the existing customers in the $todayGroup
			$group->deleteAllCustomersFromGroup($group->id);
		} else if ($group->name[$id_lang] === $birthdayFreeShippingGroup) {
			$birthdayFreeShippingGroupId = $group->id;
			//Remove all the existing customers in the "BirthdayFreeShipping"
			$group->deleteAllCustomersFromGroup($group->id);
		}
	}

	// Get all the customers, who has birthday 7days prior today
	
	$day = date('d')+7;
	$month = date('m',time()+7*24*3600);
	$todayBirthdayCustomers = Customer::getTodayBirthdayCustomerDetails($month, $day);
	$startDate = date('Y-m-d 00:00:00', time() - (365 * 3600 * 24));
	$endDate = date('Y-m-d 00:00:00', time());
	$customersCount = 0;
	$membersCount = 0;

    if ($log) {
        $logFile = @fopen($logFilePath, "a");
        fwrite($logFile, "Today Birthday Customer lists: \n" . print_r($todayBirthdayCustomers, TRUE) . "\n");
        fclose($logFile);
    }
	//Add all the customers who have birthday today to $todayGroup
	foreach ($todayBirthdayCustomers as $todayBirthdayCustomer) {
		$customer = new Customer($todayBirthdayCustomer['id_customer']);
		$orders = Customer::getCustomerOrdersInTheLastYearFromToday($id_lang, $todayBirthdayCustomer['id_customer'], $startDate, $endDate, $showHiddenStatus = false);
		if (count($orders) >= 1) {
			$customer->addGroups(array($customerBirthdayGroupId));
			$sailthruClient->send('Birthday-15', $customer->email, array('coupon_code' => $customerDiscountName), array(), time());
			$customersCount++;
		} else {
			$customer->addGroups(array($memberBirthdayGroupId));
			$sailthruClient->send('Birthday-10', $customer->email, array('coupon_code' => $memberDiscountName), array(), time());
			$membersCount++;
		}
	}

	// Get all the customers, who has birthday today
	
	$todayDay = date('d');
	$todayMonth = date('m',time()+24*3600);
	$todayBirthdayFreeShippingCustomers = Customer::getTodayBirthdayCustomerDetails($todayMonth, $todayDay);
	$todayBirthdayCustomersCount = 0;

	//Add all the customers who have birthday today to $todayGroup
	foreach ($todayBirthdayFreeShippingCustomers as $todayBirthdayCustomer) {
		$customer = new Customer($todayBirthdayCustomer['id_customer']);	
			$customer->addGroups(array($birthdayFreeShippingGroupId));
            try {
			$response=$sailthruClient->send('Birthday-Shipping', $customer->email, array('coupon_code' => $birthdayFreeShippingDiscountName), array(), time());
            }catch (Exception $e) {
                $error_msg=$e->getMessage();
                $errorData = $e->getTraceAsString();
                if ($log) {
                    $logFile = @fopen($logFilePath, "a");
                    fwrite($logFile, "Following exceptional error occured during execution:\n ".$error_msg."\n".print_r($errorData, TRUE).'\n Sailthrue response =='.print_r($response, true));
                    fclose($logFile);
                }
            }
			$todayBirthdayCustomersCount++;
	}
	
	//Create or updating the vouchers	
	$customerDiscount = Discount::getDiscountIdByname($customerDiscountName);
	$memberDiscount = Discount::getDiscountIdByname($memberDiscountName);
	$todayBirthdayDiscount = Discount::getDiscountIdByname($birthdayFreeShippingDiscountName);
	
	//Customer Discount
	if (!empty($customerDiscount)) {
		$discount = new Discount($customerDiscount['id_discount']);
		$discount->id_group = $customerBirthdayGroupId;
		$discount->quantity = $customersCount;
		$discount->date_from = date('Y-m-d H:i:s', time());
		$discount->date_to = date('Y-m-d H:i:s', time() + (3600 * 24 * 7)); // 7 days validity
		$discount->date_add = date('Y-m-d H:i:s', time());
		$discount->date_upd = date('Y-m-d H:i:s', time());
		$discount->update();
	} else {
		createDiscount($id_lang, 15, 1, $customersCount, $customerDiscountName, 'Birthday Coupon', $customerBirthdayGroupId);
	}

	//Member Discount
	if (!empty($memberDiscount)) {
		$discount = new Discount($memberDiscount['id_discount']);
		$discount->id_group = $memberBirthdayGroupId;
		$discount->quantity = $membersCount;
		$discount->date_from = date('Y-m-d H:i:s', time());
		$discount->date_to = date('Y-m-d H:i:s', time() + (3600 * 24 * 7)); // 7 days validity
		$discount->date_add = date('Y-m-d H:i:s', time());
		$discount->date_upd = date('Y-m-d H:i:s', time());
		$discount->update();
	} else {
		createDiscount($id_lang, 10, 1, $membersCount, $memberDiscountName, 'Birthday Coupon', $memberBirthdayGroupId);
	}
	
	//Today Birthday discount (Birthday Free shipping).
	if (!empty($todayBirthdayDiscount)) {
		
        try{
            $discount = new Discount($todayBirthdayDiscount['id_discount']);
            $discount->id_group = $birthdayFreeShippingGroupId;
            $discount->quantity = $todayBirthdayCustomersCount;
            $discount->date_from = date('Y-m-d H:i:s', time());
            $discount->date_to = date('Y-m-d H:i:s', time() + (3600 * 24 * 1)); // 1 days validity
            $discount->date_add = date('Y-m-d H:i:s', time());
            $discount->date_upd = date('Y-m-d H:i:s', time());
            $discount->update();
         }catch (Exception $e) {
            $error_msg=$e->getMessage();
            $errorData = $e->getTraceAsString();
            if ($log) {
                $logFile = @fopen($logFilePath, "a");
                fwrite($logFile, "\nFollowing exceptional error occured during updating the discount table query execution:\n ".$error_msg."\n".print_r($errorData, TRUE));
                fclose($logFile);
            }
        }
        
	} else {
		createDiscount($id_lang, 0, 3 , $todayBirthdayCustomersCount, $birthdayFreeShippingDiscountName, 'Birthday Coupon', $birthdayFreeShippingGroupId);
	}
	
} else if ($option == 'customer') {
	$sql = 'SELECT cs.* 
			FROM ' . _DB_PREFIX_ . 'customer_stylesurvey cs
			LEFT JOIN ' . _DB_PREFIX_ . 'customer c ON (c.`id_customer` = cs.`id_customer`)
			WHERE c.`category_name` = "" ';

	$results = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql);

	$styleSurveyResults = array();

	foreach ($results as $result) {
		$i = 1;
		foreach ($result as $key => $value) {
			if (substr($key, 0, 8) == 'question') {
				$styleSurveyResults[$result['id_customer']][$i] = substr($value, 2);
				$i++;
			}
		}
	}

	$log = true;
	$myFile = _PS_LOG_DIR_."/Customer_Style_Survey.txt";
	if ($log)
	{
		$fh = fopen($myFile, 'w') or die("can't open file");		
	}
	foreach ($styleSurveyResults as $customerId => $styleSurveyResult) {
		for ($index = 1; $index < 10; $index++) {
			$style_answers[] = $styleSurveyResult[$index];
		}
		$styles_prioritized = array_count_values($style_answers);
		arsort($styles_prioritized, SORT_NUMERIC);
		$final_style = key($styles_prioritized);
		$customer = new Customer($customerId);
		if($log)
			fwrite($fh, print_r($customer ,true));
		$customer->category_name = $final_style;
		$customer->update();
	}
	
	if($log)
		fclose($fh);
}

function createDiscount($id_lang, $discount_value, $discount_type, $discount_quantity, $discount_name, $discount_desc, $id_group)
{
	$languages = Language::getLanguages(false);
	// create discount
	$discount = new Discount();
	$discount->id_discount_type = $discount_type;
	$discount->behavior_not_exhausted = 0;
	foreach ($languages as $language)
		$discount->description[$language['id_lang']] = strval($discount_desc);
	$discount->value = floatval($discount_value);
	$discount->name = $discount_name;
	$discount->id_customer = 0;
	$discount->id_group = $id_group;
	$discount->id_currency = $id_lang;
	$discount->quantity = $discount_quantity;
	$discount->quantity_per_user = 1;
	$discount->cumulable = 0;
	$discount->cumulable_reduction = 0;
	$discount->minimal = 0.00;
	$discount->active = 1;
	$discount->date_from = date('Y-m-d H:i:s', time());
	$discount->date_to = date('Y-m-d H:i:s', time() + (3600 * 24 * 7)); // 7 days validity
	if (!$discount->validateFieldsLang(false) OR !$discount->add()) {
		return false;
	}
	else
		return $discount;
}

function getLowStockProducts($id_lang, $pageNumber = 0, $nbProducts = 5, $productIds = null)
{
	$query = 'SELECT p.id_product, pl.name, (p.`price` * IF(t.`rate`,((100 + (t.`rate`))/100),1)) AS orderprice, IF(pa.default_image, pa.`default_image` ,i.`id_image`) as id_image , pl.`link_rewrite`, pl.`name`, il.`legend`, cl.`link_rewrite` AS category
                    FROM ' . _DB_PREFIX_ . 'product p
                    LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute` pa ON (pa.`id_product` = p.`id_product` AND pa.`default_on` = 1 AND p.`id_color_default` = 2)
                    LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (p.`id_product` = pl.`id_product` AND pl.`id_lang` = ' . (int) $id_lang . ')
                    LEFT JOIN `' . _DB_PREFIX_ . 'image` i ON (i.`id_product` = p.`id_product` AND IF(pa.default_image ,\'  \', i.`cover` = 1))
                    LEFT JOIN `' . _DB_PREFIX_ . 'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = ' . (int) $id_lang . ')
                    LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl ON (cl.`id_category` = p.`id_category_default` AND cl.`id_lang` = ' . (int) $id_lang . ')
                    LEFT JOIN `' . _DB_PREFIX_ . 'tax_rule` tr ON (p.`id_tax_rules_group` = tr.`id_tax_rules_group`
		                                           AND tr.`id_country` = ' . (int) Country::getDefaultCountryId() . '
	                                           	   AND tr.`id_state` = 0)
					LEFT JOIN `' . _DB_PREFIX_ . 'tax` t ON (t.`id_tax` = tr.`id_tax`)
					LEFT JOIN `' . _DB_PREFIX_ . 'tax_lang` tl ON (t.`id_tax` = tl.`id_tax` AND tl.`id_lang` = ' . (int) ($id_lang) . ')
					WHERE p.`active` = 1 ' . ($productIds == null ? '' : 'AND p.id_product IN (' . $productIds . ')') . '
                    ORDER BY p.quantity ASC
                    LIMIT ' . (int) ($pageNumber * $nbProducts) . ', ' . (int) ($nbProducts);


	$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($query);

	return $result;
}

function getLowStockProductAttributes($id_lang, $pageNumber = 0, $nbProducts = 5, $productIds = null)
{
	$productAttributes = Attribute::getAttributes($id_lang);
	$lowStockProducts = array();
	foreach ($productAttributes as $productAttribute) {
		$sql = 'SELECT pa.id_product, pa.`id_product_attribute`, pa.quantity, pa.reference
			FROM `' . _DB_PREFIX_ . 'product_attribute` pa
			LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute_combination` pac ON pac.id_product_attribute = pa.id_product_attribute
			LEFT JOIN `' . _DB_PREFIX_ . 'attribute` a ON pac.id_attribute = a.id_attribute	
			WHERE pa.quantity <=3 AND pa.quantity != 0 AND pa.active = 1 AND a.id_attribute = ' . $productAttribute['id_attribute'] . '
			ORDER BY pa.`quantity` ASC';
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql);
		$lowStockProductCombinations[$productAttribute['name']] = $result;
	}

	$lowStockProductIdList = array();
	foreach ($productAttributes as $productAttribute) {
		foreach ($lowStockProductCombinations[$productAttribute['name']] as $lowStockProductCombination) {
			$lowStockProductIdList[$productAttribute['name']] .= $lowStockProductCombination['id_product'] . ",";
		}
	}

	$lowStockProducts = array();
	foreach ($productAttributes as $productAttribute) {
		$lowStockProductIdList[$productAttribute['name']] = rtrim($lowStockProductIdList[$productAttribute['name']], ',');
		$lowStockProducts[$productAttribute['name']] = getLowStockProducts($id_lang, $pageNumber, $nbProducts, $lowStockProductIdList[$productAttribute['name']]);
	}

	return $lowStockProducts;
}

/* To get the best sales between the start and end days
 * Param $id_lang is the Language.
 * Param $start is Date (From date).
 * Param $end is Date (end date).
 * Param $pageNumber is the lower limit for the selected products
 * Param $nbProducts is the Upper limit of the selected products.
 * Param $shoeSizeList is the lsit of all shoe sizes.
 * Param $shoe is 1 for shoes, 0 for handbags
 */

function filterHandbagsAndShoes($id_lang, $bestSales)
{
	/* Get the Shoe sizes and Available colors of the products */
	$shoeSizes = array();
	$productAttributes = Attribute::getAttributes($id_lang);
	foreach ($productAttributes as $attribure) {
		if (intval($attribure['id_attribute_group']) === 4 && intval($attribure['is_color_group']) === 0)
			$shoeSizes[] = $attribure['name'];
	}
	$shoeSizeList = '';
	foreach ($shoeSizes as $shoeSize) {
		$shoeSizeList .= $shoeSize . ",";
	}

	$shoeSizeList = rtrim($shoeSizeList, ',');

	//Filtering the Handbags and Shoes
	$shoes = array();
	$handbags = array();

	foreach ($bestSales as $bestSale) {
		$query1 = 'SELECT p.`id_product`
					FROM `' . _DB_PREFIX_ . 'product` p
					LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute` pa ON (p.`id_product` = pa.`id_product` AND pa.active = 1 AND pa.`quantity` >= 1 )
					LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute_combination` pac on (pac.`id_product_attribute` = pa.`id_product_attribute`)
					LEFT JOIN `' . _DB_PREFIX_ . 'attribute` a on (pac.`id_attribute` = a.`id_attribute`)
					LEFT JOIN `' . _DB_PREFIX_ . 'attribute_lang` al ON (al.`id_attribute` = a.`id_attribute` AND al.`id_lang` = ' . (int) $id_lang . ')
					WHERE p.`id_product` = ' . $bestSale['id_product'] . ' AND al.name IN (' . $shoeSizeList . ')';

		$row = Db::getInstance()->getRow($query1);

		//To filter the lowstock products
		if (isLowStock($bestSale['id_product']) < 3) {
			continue;
		}

		//To filter the Accessories products
		$category = new Category((int) $bestSale['id_category_default']);
		$categoryName = $category->getName($id_lang);
		if ($categoryName === "ProductAccessories") {
			continue;
		}

		if (!empty($row)) {
			$shoes[] = $bestSale;
		} else {
			$handbags[] = $bestSale;
		}

		if (count($shoes) > 10 && count($handbags) > 10) {
			return array('shoes' => $shoes, 'handbags' => $handbags);
		}
	}

	return array('shoes' => $shoes, 'handbags' => $handbags);
}

function isLowStock($productId)
{
	$isLowStockQuery = 'SELECT p.`quantity` 
					   FROM `' . _DB_PREFIX_ . 'product` p 
					   WHERE  p.`id_product` = ' . $productId;

	$isLowStock = Db::getInstance()->getRow($isLowStockQuery);

	return $isLowStock['quantity'];
}

?>
