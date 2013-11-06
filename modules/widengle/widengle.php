<?php

/**
 * Description of widengle
 * In this module, discounts are given to the customers based on the Widengle Analytics.
 */
if (!defined('_CAN_LOAD_FILES_'))
	exit;

class Widengle extends Module
{
	/* Widengle cookies */

	public $widengle_cookies = array('discount_campaign' => 'biawxs', 'group_specific_price' => 'bwacg', 'surveyvsregister' => 'bsvrt');

	public function __construct()
	{
		$this->name = 'widengle';
		$this->tab = 'analytics_stats';
		$this->version = 1.0;
		$this->author = 'PrestaShop';

		parent::__construct();

		$this->displayName = $this->l('Widengle Analytics');
		$this->description = $this->l('This module must be enabled if you want to use Widengle Analytics.');
	}

	public function install()
	{
		if (!parent::install() OR !$this->registerHook('footer') OR !$this->registerHook('header') OR !$this->registerHook('shoppingCartDiscounts') OR !$this->registerHook('productFooter') OR !$this->registerHook('productFooterBlock') OR !$this->registerHook('addProduct') OR !$this->registerHook('joinNow'))
			return false;

		return true;
	}
	
	public function uninstall()
	{
		return (parent::uninstall());
	}

	public function hookHeader($params)
	{
		global $cookie, $smarty;

		if ($cookie->logged) {
			$customer = new Customer($cookie->id_customer);
			$customerAddress = $customer->getAddresses($cookie->id_lang);
			$customerLanguage = new Language($cookie->id_lang);
			$smarty->assign(array('language' => $customerLanguage->name, 'customerId' => $customer->id,
				'customerFirstname' => $customer->firstname, 'customerLastname' => $customer->lastname,
				'customerBirthdate' => $customer->birthday, 'customerAge' => $customer->age,
				'customerShoeSize' => $customer->shoe_size, 'customerDressSize' => $customer->dress_size));

			$groups = Group::getGroups($cookie->id_lang);
			foreach ($groups AS $group) {
				if ($group['id_group'] == $customer->id_default_group) {
					$customer_grp_name = $group['name'];
				}
			}
			$smarty->assign('customerGroup', $customer_grp_name);

			if ($customerAddress)
				$smarty->assign(array(/* 'customerId'=>$customer_address[0]['id_customer'],'customerFirstname'=>$customer_address[0]['firstname'],
					  'customerLastname'=>$customer_address[0]['lastname'], */
					'customerCountry' => $customerAddress[0]['country'],
					'customerState' => $customerAddress[0]['state'],
					'customerCity' => $customerAddress[0]['cityName'],
					'customerStreet' => $customerAddress[0]['address1'] . " " . $customerAddress[0]['address2'],
					'customerPostalcode' => $customerAddress[0]['postcode']
				));
		}

		return $this->display(__FILE__, 'widengle-header.tpl');
	}

	function hookProductFooter($params)
	{
		global $smarty;
		$id_product = (int) (Tools::getValue('id_product'));
		$smarty->assign(array('id_product' => $id_product));
		return $this->display(__FILE__, 'widengle-product.tpl');
	}

	function hookProductFooterBlock($params)
	{
		return $this->hookProductFooter($params);
	}

	public function hookFooter($params)
	{
		global $smarty;

		if (isset($_COOKIE[$this->widengle_cookies['discount_campaign']])) {
			$discount_cookie = trim($_COOKIE[$this->widengle_cookies['discount_campaign']]);

			//Cookie in the format "image_name:discount_value:shipping_discount_or_not"
			$discount_cookie = explode(':', $discount_cookie);

			if (isset($discount_cookie[0])) //image name. i.e image to be displayed
				$discount_image = $discount_cookie[0];
			if (isset($discount_cookie[1]))  // Discount amount in float.
				$discount = floatval($discount_cookie[1]);
			if (isset($discount_cookie[2])) // 1 for free shipping else 0 for not.
				$free_shipping = intval($discount_cookie[2]);

			$smarty->assign(array('discount_image' => $discount_image));

			if ($params['cookie']->logged) {
				// Include the customers in the campaign, who have not made any purchase in the last 8 days
				//$cus_purchased = Customer::customerBoughtProductslist($params['cookie']->id_customer, 8);
				// Don't create the discounts if the customer already have the IABW discounts
				$customer_have_discounts = $this->getCustomerDiscounts($params['cookie']->id_customer);

				if (empty($customer_have_discounts) /* AND empty($cus_purchased) */) {

					//Discount
					if ($discount > 0.00)
						$this->createDiscount($params['cookie']->id_customer, $params['cookie']->id_currency, $this->l('Butigo Special Discount'), 2, $discount);

					//Free Shipping
					if ($free_shipping == 1)
						$this->createDiscount($params['cookie']->id_customer, $params['cookie']->id_currency, $this->l('Butigo Special Free Shipping'), 3, 0);
				}
			}
		}
		if ($params['cookie']->logged) {
			if (isset($_COOKIE[$this->widengle_cookies['group_specific_price']])) {
				$this->addSpecificPrice($params['cookie']->id_customer, $params['cookie']->id_lang);
			}
		}

		return $this->display(__FILE__, 'widengle.tpl');
	}

	/* To apply the discounts at the cart */

	public function hookShoppingCartDiscounts($params)
	{
		$errors = array();

		if (isset($_COOKIE[$this->widengle_cookies['discount_campaign']])) {
			$discount_cookie = trim($_COOKIE[$this->widengle_cookies['discount_campaign']]);
			$discount_cookie = explode(':', $discount_cookie);

			if (isset($discount_cookie[0])) //image name. i.e image to be displayed
				$image = $discount_cookie[0];
			if (isset($discount_cookie[1]))  // Discount amount in float.
				$discount = floatval($discount_cookie[1]);
			if (isset($discount_cookie[2])) // 1 for free shipping else 0 for not.
				$free_shipping = intval($discount_cookie[2]);

			if ($free_shipping == 1)
				$discount_types = array(2, 3);
			else
				$discount_types = array(2);

			foreach ($discount_types as $discount_type)
				if ($discounts = Discount::getDiscountIdsByType((int) ($params['cookie']->id_lang), (int) ($params['cookie']->id_customer), $discount_type, true, false, true)) {
					foreach ($discounts as $discount) {
						$widengle_discount = new Discount(intval($discount['id_discount']));
						if (is_object($widengle_discount) AND $widengle_discount) {
							//checking the validity of the discount.
							if ($tmpError = $params['cart']->checkDiscountValidity($widengle_discount, $params['cart']->getDiscounts(), $params['cart']->getOrderTotal(), $params['cart']->getProducts(), true))
								$errors[] = $tmpError;
						}


						if (!sizeof($errors)) {
							//Add the discounts to the cart if the cart don't have the carts.
							if ($params['cart']->getDiscountsCustomer($widengle_discount->id) <= 0)
								$params['cart']->addDiscount((int) ($widengle_discount->id));
						}
					}
				}
		}
		return "";
	}

	/* Creating the Discounts(vouchers/credits) as per the cookie set by the Widengle Analyatics
	 *
	 * @param int $id_customer Customer ID
	 * @param int $currency Currency ID
	 * @param String $name Discription of the discount
	 * @param int $discount_type Type of the discount
	 * @param float $discount_value value of the discount
	 */

	public function createDiscount($id_customer, $currency, $name, $discount_type, $discount_value)
	{
		$languages = Language::getLanguages();
		$name = $name;
		$discount = new Discount();
		$discount->name = "BIAWXS" . Tools::passwdGen(2);
		$discount->id_discount_type = $discount_type;
		$discount->behavior_not_exhausted = 2;
		foreach ($languages as $language)
			$discount->description[$language['id_lang']] = strval($name);
		$discount->id_customer = intval($id_customer);
		$discount->id_currency = intval($currency);
		$discount->value = floatval($discount_value);
		$discount->quantity = 1;
		$discount->quantity_per_user = 1;
		$discount->cumulable = 0;
		$discount->cumulable_reduction = 1;
		$discount->date_from = date('Y-m-d H:i:s', time());
		$discount->date_to = date('Y-m-d H:i:s', time() + 1 * 24 * 3600); // 1 day validity.
		$discount->minimal = 0;
		$discount->active = 1;
		$discount->cart_display = 1;
		$discount->add();
		return true;
	}

	/**
	 * Return customer discounts
	 *
	 * @param int $id_customer Customer ID
	 * @return integer Discount ID
	 */
	public static function getCustomerDiscounts($id_customer)
	{
		if (!Validate::isInt($id_customer))
			die(Tools::displayError());

		$today = date('Y-m-d H:i:s');

		$sql = 'SELECT `id_discount`, `date_to`
		FROM `' . _DB_PREFIX_ . 'discount`
		WHERE `name` LIKE \'' . pSQL('BIAWXS') . '%\' AND `id_customer` = ' . $id_customer . ' AND `date_to` > \'' . $today . '\'';

		$res = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql);

		return $res;
	}

	public function addSpecificPrice($id_customer, $id_lang)
	{
		$group_id = array();

		if (isset($_COOKIE[$this->widengle_cookies['group_specific_price']])) {
			$grp_name_cookie = trim($_COOKIE[$this->widengle_cookies['group_specific_price']]);

			if ($grp_name_cookie == 'bwacg0') {
				$grp_id = 1;
			} else {
				$groups = Group::getGroups($id_lang);
				foreach ($groups AS $group) {
					if (strpos($group['name'], $grp_name_cookie) !== false) {
						$grp_id = $group['id_group'];
					}
				}
			}

			$customer = new Customer($id_customer);
			$customer_grps = $customer->getGroups();

			/* Check if customer belongs to the group */
			$default_id = Customer::getDefaultGroupId($customer->id);
			if ($default_id != $grp_id) {
				/* Deleting Customer from the group */
				if ($customer->id_default_group != 1 && in_array($customer->id_default_group, $customer_grps))
					Db::getInstance()->Execute('DELETE FROM `' . _DB_PREFIX_ . 'customer_group` WHERE `id_group` = ' . (int) ($customer->id_default_group) . ' AND `id_customer` = ' . (int) ($customer->id));
				if ($grp_id != 1) {
					$group_id[] = $grp_id;
					/* Adding Customer to the group. */
					$customer->addGroups($group_id);
				}
				$customer->id_default_group = $grp_id;
				if (!$customer->update())
					$errors[] = Tools::displayError('Cannot update customer`s default group');
			}
		}
	}

	public function hookaddProduct($params)
	{
		global $cookie;

		$newProduct = $params['product'];
		$group_names = array();
		$group_names = array('bwacgp5', 'bwacgp10', 'bwacgp15', 'bwacgm5', 'bwacgm10', 'bwacgm15');

		$group_ids = $this->createGroups($group_names, $cookie->id_lang);

		if ($group_ids) {

			$orderBy = 'date_add';
			$orderWay = 'ASC';

			//5th parameter is group id.
			//0 is all groups
			//1 is default group
			foreach ($group_ids as $grp_id => $variation) {
				$spc_price = $newProduct->price + ($newProduct->price * ($variation / 100 ));
				$tax_rate = Tax::getProductTaxRate($newProduct->id);
				$final_product_price = $spc_price * (1 + ($tax_rate / 100));
				$ceil_price = ceil($final_product_price);
				$sp_price = $ceil_price / (1 + ($tax_rate / 100));
				$sp_price = Tools::ps_round($sp_price, 3);
				if ($ceil_price > $newProduct->price)
					$strike_out = 1;
				else
					$strike_out = 0;
			}
		}
	}

	function hookjOinNow($params)
	{
		global $smarty;
		if (isset($_COOKIE[$this->widengle_cookies['surveyvsregister']]) && $_COOKIE[$this->widengle_cookies['surveyvsregister']] == 1) {
			$smarty->assign(array('img_path' => _PS_IMG_));
			return $this->display(__FILE__, 'widengle-join-now.tpl');
		}
		return false;
	}

	function getExistingGroups($id_lang)
	{
		$existing_grps = array();
		$groups = Group::getGroups($id_lang);

		foreach ($groups AS $group) {
			$existing_grps[$group['name']] = $group['id_group'];
			$existing_grps['names'][] = $group['name'];
		}

		return $existing_grps;
	}

	function createGroups($group_names, $id_lang)
	{
		$create_groups = array();
		$grp_ids = array();
		$existing_grps = $this->getExistingGroups($id_lang);

		foreach ($group_names as $grp_name) {
			if (!in_array($grp_name, $existing_grps['names'])) {
				$create_groups[] = $grp_name;
			} else {
				$price_variation = $this->getPriceVariation($grp_name);
				$grp_ids[$existing_grps[$grp_name]] = $price_variation;
			}
		}


		if ($create_groups) {

			$languages = Language::getLanguages(false);

			foreach ($create_groups as $create) {
				$group = new Group();
				$group->reduction = 0.00;
				$group->price_display_method = 0;
				foreach ($languages as $language)
					$group->name[$language['id_lang']] = strval($create);
				$group->add();

				if ($group->id != 0 || $group->id = '') {
					$price_variation = $this->getPriceVariation($create);
					$grp_ids[$group->id] = $price_variation;
				}
			}
		}
		if (!empty($grp_ids))
			return $grp_ids;
		else
			return false;
	}

	function createSpecificPrice($id_product, $id_group, $price, $strike_out)
	{

		$id_shop = 0;
		$id_currency = 0;
		$id_country = 0;
		$from_quantity = 1;
		$reduction = 0.000000;
		$reduction_type = 'amount';
		//$strike_out = 0;
		//$from = '2012-08-01 16:47:44';
		$from = '0000-00-00 00:00:00';
		$to = '0000-00-00 00:00:00';

		$specificPrice = new SpecificPrice();
		$specificPrice->id_product = $id_product;
		$specificPrice->id_shop = (int) ($id_shop);
		$specificPrice->id_currency = (int) ($id_currency);
		$specificPrice->id_country = (int) ($id_country);
		$specificPrice->id_group = (int) ($id_group);
		$specificPrice->price = (float) ($price);
		$specificPrice->from_quantity = (int) ($from_quantity);
		$specificPrice->reduction = (float) ($reduction_type == 'percentage' ? $reduction / 100 : $reduction);
		$specificPrice->reduction_type = $reduction_type;
		$specificPrice->strike_out = $strike_out;
		$specificPrice->from = !$from ? '0000-00-00 00:00:00' : $from;
		$specificPrice->to = !$to ? '0000-00-00 00:00:00' : $to;
		if (!$specificPrice->add())
			return false;
		else
			return true;
	}

	public function getPriceVariation($group_name)
	{
		$sub_str = substr($group_name, 5);
		$symbol = substr($sub_str, 0, 1);
		$price_variation = substr($sub_str, 1);
		if ($symbol == 'm')
			$price_variation = $price_variation * -1;
		return $price_variation;
	}

}

?>
