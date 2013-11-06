<?php

/**
 * Description of discountmanagement
 *
 * @author gangadhar
 */
if (!defined('_CAN_LOAD_FILES_'))
	exit;

class DiscountManagement extends Module
{

	public function __construct()
	{
		$this->name = 'discountmanagement';
		$this->tab = 'front office features';
		$this->version = 1.4;
		$this->author = 'Gangadhar K.M';

		parent::__construct();

		$this->displayName = $this->l('Discount Generation On Orders');
		$this->description = $this->l('This module must be enabled if you want to generate discount for an order.');
	}

	public function install()
	{
		if (!parent::install() OR
				!$this->registerHook('createDiscountForOrder') OR
				!$this->registerHook('updateOrderStatus')) {
			return FALSE;
		} else {
			return TRUE;
		}
	}

	public function uninstall()
	{
		parent::uninstall();
	}

	public function hookCreateDiscountForOrder($params)
	{
		global $smarty;

		if (!$this->discountExistsForOrder($params['orderDetails']->id)) {

			if ($this->checkAnVoucherIsAlreadyCreatedToday($params['cookie']->id_customer)) {

				//Get all the order products.
				$orderProducts = $params['orderDetails']->getProducts();

				//Get the number of Shoes, Handbags and Accessories in the order. 
				$orderShoesHandbagsAccessoriesCount = $this->numberOfShoesHandbagsAccessoriesInTheOrder($params['cookie']->id_lang, $orderProducts);

				$couponCodeCategory = '';
				$couponCodeCategoryId = 0;
				$category_details = array();

				if ($orderShoesHandbagsAccessoriesCount['shoes'] AND $orderShoesHandbagsAccessoriesCount['handbags']) {
					$couponCodeCategory = 1;
					$params['cookie']->voucherCategory = 3;
				} else if ($orderShoesHandbagsAccessoriesCount['shoes']) {
					$couponCodeCategory = 0;
					$params['cookie']->voucherCategory = 1;
				} else if ($orderShoesHandbagsAccessoriesCount['handbags']) {
					$couponCodeCategory = 1;
					$params['cookie']->voucherCategory = 2;
				} else {
					$couponCodeCategory = 1;
					$params['cookie']->voucherCategory = 3;
				}

				if ($couponCodeCategory) {
					$category_details = Category::searchByNameAndParentCategoryId((int) ($params['cookie']->id_lang), 'Shoes', 1);
					$couponCodeCategoryId = $category_details['id_category'];
					$smarty->assign(array('couponCodeCategory' => 'Shoes'));
				} else {
					$category_details = Category::searchByNameAndParentCategoryId((int) ($params['cookie']->id_lang), 'Handbags', 1);
					$couponCodeCategoryId = $category_details['id_category'];
					$smarty->assign(array('couponCodeCategory' => 'Handbags'));
				}

				$data = array('customerId' => $params['cookie']->id_customer,
					'langId' => $params['cookie']->id_lang,
					'currencyId' => $params['cookie']->id_currency,
					'discountCode' => Tools::voucherGen(),
					'discountValue' => 15.00,
					'discountType' => 1,
					'discountQuantity' => 1,
					'discountDescription' => $this->l('Special Discount'),
					'discountQuantityPerUser' => 1,
					'discountCumulable' => 0,
					'discountCumulableReduction' => 1,
					'discountValidityInDays' => 1,
					'discountCategory' => array($couponCodeCategoryId),
					'discountForOrderId' => $params['orderDetails']->id
				);

				$discountGenerated = $this->createDiscount($data);
				if ($discountGenerated) {
					$params['cookie']->voucherCode = $discountGenerated;
					$smarty->assign(array('voucherCode' => $discountGenerated));
				}
			}
		}
	}

	public function hookUpdateOrderStatus($params)
    {
        $newOS = $params['newOrderStatus'];
		
        if($newOS->id == Configuration::get('PS_OS_CANCELED') || $newOS->id == Configuration::get('PS_OS_REFUND') || $newOS->id == Configuration::get('PS_OS_PARTIALREFUND') || $newOS->id == Configuration::get('PS_OS_MANUALREFUND') || $newOS->id == Configuration::get('PS_OS_EXCHANGE') || $newOS->id == Configuration::get('PS_OS_PARTIALEXCHANGE')) {
            if($this->discountExistsForOrder($params['id_order'])) {

				$status = Db::getInstance()->Execute('
                         UPDATE `'._DB_PREFIX_.'discount`
                         SET `active`= 0
                         WHERE `id_order` = '.(int)($params['id_order']).'');

                if($status)
                    return true;
                return false;
            }
        }
    }
	
	public function createDiscount($data)
	{
		$languages = Language::getLanguages(false);
		// create discount
		$discount = new Discount();
		$discount->id_discount_type = $data['discountType'];
		$discount->behavior_not_exhausted = 0;

		foreach ($languages as $language) {
			$discount->description[$language['id_lang']] = $data['discountDescription'];
		}

		$discount->value = $data['discountValue'];
		$discount->name = $data['discountCode'];
		$discount->id_customer = $data['customerId'];
		$discount->id_currency = $data['currencyId'];
		$discount->quantity = $data['discountQuantity'];
		$discount->quantity_per_user = $data['discountQuantityPerUser'];
		$discount->cumulable = $data['discountCumulable'];
		$discount->cumulable_reduction = $data['discountCumulableReduction'];
		$discount->minimal = 0.00;
		$discount->active = 1;
		$now = time();
		$discount->date_from = date('Y-m-d H:i:s', $now);
		$discount->date_to = date('Y-m-d H:i:s', $now + ($data['discountValidityInDays'] * 24 * 60 * 60));
		$discount->cart_display = 1;

		if (!$discount->validateFieldsLang(false) OR !$discount->add(true, false, $data['discountCategory'])) {
			return FALSE;
		}
		if ($this->insertOrderID($discount->id, $data['discountForOrderId'])) {
			return $discount->name;
		}

		return TRUE;
	}

	public function insertOrderID($id_discount, $id_order)
	{
		return Db::getInstance()->Execute('
            UPDATE `' . _DB_PREFIX_ . 'discount`
            SET `id_order`= ' . ($id_order) . '
            WHERE `id_discount` = ' . $id_discount . '');
	}

	public function discountExistsForOrder($id_order)
	{
		$result = Db::getInstance()->getValue('
		SELECT `name`
		FROM `' . _DB_PREFIX_ . 'discount`
		WHERE `id_order` = ' . (int) ($id_order) . '
		');
		if ($result)
			return $result;
		return false;
	}

	public function numberOfShoesHandbagsAccessoriesInTheOrder($id_lang, $bestSales)
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
		$shoesCount = 0;
		$handbagsCount = 0;
		$accessoriesCount = 0;

		foreach ($bestSales as $bestSale) {
			$query1 = 'SELECT p.`id_product`
					FROM `' . _DB_PREFIX_ . 'product` p
					LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute` pa ON (p.`id_product` = pa.`id_product` AND pa.active = 1 AND pa.`quantity` >= 1 )
					LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute_combination` pac on (pac.`id_product_attribute` = pa.`id_product_attribute`)
					LEFT JOIN `' . _DB_PREFIX_ . 'attribute` a on (pac.`id_attribute` = a.`id_attribute`)
					LEFT JOIN `' . _DB_PREFIX_ . 'attribute_lang` al ON (al.`id_attribute` = a.`id_attribute` AND al.`id_lang` = ' . (int) $id_lang . ')
					WHERE p.`id_product` = ' . $bestSale['product_id'] . ' AND al.name IN (' . $shoeSizeList . ')';

			$row = Db::getInstance()->getRow($query1);

			//To filter the Accessories products
			$category = new Category((int) $bestSale['id_category_default']);
			$categoryName = $category->getName($id_lang);
			if ($categoryName === "ProductAccessories") {
				$accessoriesCount++;
				continue;
			}

			if (!empty($row)) {
				$shoesCount++;
			} else {
				$handbagsCount++;
			}
		}

		return array('shoes' => $shoesCount,
			'handbags' => $handbagsCount,
			'accessories' => $accessoriesCount);
	}

	public function checkAnVoucherIsAlreadyCreatedToday($customerId)
	{

		$startDate = date('Y-m-d H:i:s', time() - (24 * 60 * 60));
		$todate = date('Y-m-d H:i:s', time());
		$sql = 'SELECT o.id_order
						FROM `' . _DB_PREFIX_ . 'orders` o
						WHERE o.id_customer = ' . $customerId . ' AND (o.date_add BETWEEN "' . $startDate . '" AND "' . $todate . '")';

		$todayOrders = Db::getInstance()->ExecuteS($sql);

		$todayDiscounts = array();

		foreach ($todayOrders AS $todayOrder) {
			$sql = 'SELECT id_discount 
					FROM `' . _DB_PREFIX_ . 'discount` d
					WHERE d.id_order = ' . $todayOrder['id_order'];

			$todayDiscount = Db::getInstance()->getRow($sql);

			if (!empty($todayDiscount)) {
				$todayDiscounts[] = $todayDiscount;
			}
		}

		if (empty($todayDiscounts)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

}

?>
