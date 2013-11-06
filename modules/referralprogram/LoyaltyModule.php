<?php

if (!defined('_CAN_LOAD_FILES_'))
	exit;

class LoyaltyModule extends ObjectModel
{
	public $id_loyalty_state;
	public $id_customer;
	public $id_order;
	public $id_discount;
	public $points;
	public $id_referralprogram;
	public $date_add;
	public $date_upd;

	protected $fieldsRequired = array('id_customer', 'points');
	protected $fieldsValidate = array('id_loyalty_state' => 'isInt', 'id_customer' => 'isInt', 'id_discount' => 'isInt', 'id_order' => 'isInt', 'points' => 'isInt','id_referralprogram'=>'isInt');

	protected $table = 'loyalty';
	protected $identifier = 'id_loyalty';

	public function getFields()
	{
		parent::validateFields();
		$fields['id_loyalty_state'] = (int)$this->id_loyalty_state;
		$fields['id_customer'] = (int)$this->id_customer;
		$fields['id_order'] = (int)$this->id_order;
		$fields['id_discount'] = (int)$this->id_discount;
		$fields['points'] = (int)$this->points;
		$fields['id_referralprogram'] = (int)$this->id_referralprogram;
		$fields['date_add'] = pSQL($this->date_add);
		$fields['date_upd'] = pSQL($this->date_upd);
		return $fields;
	}

	public function save($nullValues = false, $autodate = true)
	{
		parent::save($nullValues, $autodate);
		$this->historize();
	}

	public static function getByOrderId($id_order)
	{
		if (!Validate::isUnsignedId($id_order))
			return false;

		$result = Db::getInstance()->getRow('
		SELECT f.id_loyalty
		FROM `'._DB_PREFIX_.'loyalty` f
		WHERE f.id_order = '.(int)($id_order).'
		AND	f.points > 0');

		return isset($result['id_loyalty']) ? $result['id_loyalty'] : false;
	}

	public static function getByreferralId($id_refer)
	{
		if (!Validate::isUnsignedId($id_refer))
			return false;

		$result = Db::getInstance()->getRow('
		SELECT f.id_loyalty
		FROM `'._DB_PREFIX_.'loyalty` f
		WHERE f.id_referralprogram = '.(int)($id_refer));

		return isset($result['id_loyalty']) ? $result['id_loyalty'] : false;
	}


	/*custom written*/
	static public function getOrderPurchasePoints($cart,$order)
	{
		if (!Validate::isLoadedObject($order))
			return false;

		$total = 0;
		$return = Db::getInstance()->ExecuteS('
		SELECT  product_quantity AS quantity
		FROM '._DB_PREFIX_.'order_detail
		WHERE id_order = '.intval($order->id));

		foreach ($return AS $line)
			for ($i = 0 ; $i < $line['quantity'] ; $i++)
				$total += intval(Configuration::get('PS_PRODUCT_PURCHASE_POINTS'));
		return $total;
	}

	public static function getOrderNbPoints($order)
	{
		if (!Validate::isLoadedObject($order))
			return false;
		return self::getCartNbPoints(new Cart((int)$order->id_cart));
	}

	public static function getCartNbPoints($cart, $newProduct = NULL)
	{
		$total = 0;
		if (Validate::isLoadedObject($cart))
		{
			$cartProducts = $cart->getProducts();
			$taxesEnabled = Product::getTaxCalculationMethod();
			if (isset($newProduct) AND !empty($newProduct))
			{
				$cartProductsNew['id_product'] = (int)$newProduct->id;
				if ($taxesEnabled == PS_TAX_EXC)
					$cartProductsNew['price'] = number_format($newProduct->getPrice(false, (int)($newProduct->getIdProductAttributeMostExpensive())), 2, '.', '');
				else
					$cartProductsNew['price_wt'] = number_format($newProduct->getPrice(true, (int)($newProduct->getIdProductAttributeMostExpensive())), 2, '.', '');
				$cartProductsNew['cart_quantity'] = 1;
				$cartProducts[] = $cartProductsNew;
			}
			foreach ($cartProducts AS $product)
			{
				if (!(int)(Configuration::get('PS_LOYALTY_NONE_AWARD')) AND Product::isDiscounted((int)$product['id_product']))
				{
					global $smarty;
					if (isset($smarty) AND is_object($newProduct) AND $product['id_product'] == $newProduct->id)
						$smarty->assign('no_pts_discounted', 1);
					continue;
				}
				$total += ($taxesEnabled == PS_TAX_EXC ? $product['price'] : $product['price_wt'])* (int)($product['cart_quantity']);
			}
			foreach ($cart->getDiscounts(false) AS $discount)
				$total -= $discount['value_real'];
		}

		return self::getNbPointsByPrice($total);
	}

	/*custom written*/
	static public function getCreditValue($id_customer, $id_currency)
	{
		$is_member = 1;//Customer::memberOfGroup(intval($id_customer));
		if($is_member == 1)
			return floatval(floatval(Tools::convertPrice(Configuration::get('PS_LOYALTY_CREDIT_VALUE'), $id_currency)));
		elseif($is_member == 2)
		{
			$member_credit_value = floatval(Configuration::get('PS_LOYALTY_CREDIT_VALUE')) -  floatval( Group::getReduction(((isset($id_customer) AND $id_customer) ? $id_customer : 0)));
			return floatval(floatval(Tools::convertPrice($member_credit_value , $id_currency)));
		}

	}

	public static function getVoucherValue($nbPoints, $id_currency = NULL)
	{
		global $cookie;

		if (empty($id_currency))
			$id_currency = (int)$cookie->id_currency;

		return (int)$nbPoints * (float)Tools::convertPrice(Configuration::get('PS_LOYALTY_POINT_VALUE'), new Currency((int)$id_currency));
	}

	public static function getNbPointsByPrice($price)
	{
		global $cookie;

		if (Configuration::get('PS_CURRENCY_DEFAULT') != $cookie->id_currency)
		{
			$currency = new Currency((int)($cookie->id_currency));
			if ($currency->conversion_rate)
				$price = $price / $currency->conversion_rate;
		}

		/* Prevent division by zero */
		$points = 0;
		if ($pointRate = (float)(Configuration::get('PS_LOYALTY_POINT_RATE')))
			$points = floor(number_format($price, 2, '.', '') / $pointRate);

		return (int)$points;
	}

	public static function getPointsByCustomer($id_customer)
	{
		return
		Db::getInstance()->getValue('
		SELECT SUM(f.points) points
		FROM `'._DB_PREFIX_.'loyalty` f
		WHERE f.id_customer = '.(int)($id_customer).'
		AND f.id_loyalty_state IN ('.(int)(LoyaltyStateModule::getValidationId()).', '.(int)(LoyaltyStateModule::getNoneAwardId()).')')
		+
		Db::getInstance()->getValue('
		SELECT SUM(f.points) points
		FROM `'._DB_PREFIX_.'loyalty` f
		WHERE f.id_customer = '.(int)($id_customer).'
		AND f.id_loyalty_state = '.(int)LoyaltyStateModule::getCancelId().' AND points < 0');
	}

	public static function getValidPointsByCustomer($id_customer)
	{
		return
		Db::getInstance()->getValue('
		SELECT SUM(f.points) points
		FROM `'._DB_PREFIX_.'loyalty` f
		WHERE f.id_customer = '.(int)($id_customer).'
		AND f.id_loyalty_state IN ('.(int)(LoyaltyStateModule::getValidationId()).', '.(int)(LoyaltyStateModule::getNoneAwardId()).')');
	}

//	static public function getTotalPointsByCustomer($id_customer)
//	{
//		$return = Db::getInstance()->getRow('
//		SELECT SUM(f.points) points
//		FROM `'._DB_PREFIX_.'loyalty` f
//		WHERE f.id_customer = '.(int)($id_customer).'
//		AND f.id_loyalty_state IN ('.(int)(LoyaltyStateModule::getValidationId()).', '.(int)(LoyaltyStateModule::getNoneAwardId()).')');
//
//		return (int)($return['points']);
//	}

	/*custom written*/
	static public function getPendingPointsByCustomer($id_customer)
	{
		$return = Db::getInstance()->getRow('
		SELECT SUM(f.points) points
		FROM `'._DB_PREFIX_.'loyalty` f
		WHERE f.id_customer = '.intval($id_customer).'
		AND f.id_loyalty_state IN ('.intval(LoyaltyStateModule::getDefaultId()).', '.intval(LoyaltyStateModule::getNoneAwardId()).')');

		return intval($return['points']);
	}

	public static function getAllByIdCustomer($id_customer, $id_lang, $onlyValidate = false, $pagination = false, $nb = 10, $page = 1) {
		$query = '
		SELECT '.($pagination ? 'SQL_CALC_FOUND_ROWS ' : '').'f.id_order AS id, f.date_add AS date, (o.total_paid - o.total_shipping) total_without_shipping, f.points, f.id_loyalty, f.id_loyalty_state,f.id_loyalty_state AS id_loyalty_state, f.id_referralprogram AS id_refer, fsl.name state
		FROM `'._DB_PREFIX_.'loyalty` f
		LEFT JOIN `'._DB_PREFIX_.'orders` o ON (f.id_order = o.id_order)
		LEFT JOIN `'._DB_PREFIX_.'loyalty_state_lang` fsl ON (f.id_loyalty_state = fsl.id_loyalty_state AND fsl.id_lang = '.(int)($id_lang).')
		WHERE f.id_customer = '.(int)($id_customer);
		if ($onlyValidate === true)
			$query .= ' AND f.id_loyalty_state = '.(int)LoyaltyStateModule::getValidationId();
		$query .= ' GROUP BY f.id_loyalty ORDER BY f.date_add DESC '.
		($pagination ? ' LIMIT '.(((int)($page) - 1) * (int)($nb)).', '.(int)($nb) : '');
		$result = Db::getInstance()->ExecuteS($query);

		if ($pagination) {
			$result[totalItem] = (int) Db::getInstance()->getValue('SELECT FOUND_ROWS() as rowCount');
		}

		return  $result;
	}


	public static function getDiscountByIdCustomer($id_customer, $last=false)
	{
		$query = '
		SELECT f.id_discount AS id_discount, f.date_upd AS date_add
		FROM `'._DB_PREFIX_.'loyalty` f
		LEFT JOIN `'._DB_PREFIX_.'orders` o ON (f.`id_order` = o.`id_order`)
		WHERE f.`id_customer` = '.(int)($id_customer).'
		AND f.`id_discount` > 0
		AND o.`valid` = 1';
		if ($last === true)
			$query.= ' ORDER BY f.id_loyalty DESC LIMIT 0,1';
		$query.= ' GROUP BY f.id_discount';

		return Db::getInstance()->ExecuteS($query);
	}

	public static function registerDiscount($discount)
	{
		if (!Validate::isLoadedObject($discount))
			die(Tools::displayError('Incorrect object Discount.'));
		$items = self::getAllByIdCustomer((int)$discount->id_customer, NULL, true);
		foreach ($items AS $item)
		{
			$f = new LoyaltyModule((int)$item['id_loyalty']);

			/* Check for negative points for this order */
			$negativePoints = (int)Db::getInstance()->getValue('SELECT SUM(points) points FROM '._DB_PREFIX_.'loyalty WHERE id_order = '.(int)$f->id_order.' AND id_loyalty_state = '.(int)LoyaltyStateModule::getCancelId().' AND points < 0');

			if ($f->points + $negativePoints <= 0)
				continue;

			$f->id_discount = (int)$discount->id;
			$f->id_loyalty_state = (int)LoyaltyStateModule::getConvertId();
			$f->save();
		}
	}

	public static function registerCredits($discount , $id_customer)
	{
		if (!Validate::isLoadedObject($discount))
			die(Tools::displayError('Incorrect object Discount.'));
		$items = self::getAllByIdCustomer((int)$id_customer, NULL, true);
		foreach ($items AS $item)
		{
			$f = new LoyaltyModule((int)$item['id_loyalty']);

			/* Check for negative points for this order */
			$negativePoints = (int)Db::getInstance()->getValue('SELECT SUM(points) points FROM '._DB_PREFIX_.'loyalty WHERE id_order = '.(int)$f->id_order.' AND id_loyalty_state = '.(int)LoyaltyStateModule::getCancelId().' AND points < 0');

			if ($f->points + $negativePoints <= 0)
				continue;

			$f->id_discount = (int)$discount->id;
			$f->id_loyalty_state = (int)LoyaltyStateModule::getConvertId();
			$f->save();
		}
	}

	public static function getOrdersByIdDiscount($id_discount)
	{
		$items = Db::getInstance()->ExecuteS('
		SELECT f.id_order AS id_order, f.points AS points, f.date_upd AS date
		FROM `'._DB_PREFIX_.'loyalty` f
		WHERE f.id_discount = '.(int)($id_discount).' AND f.id_loyalty_state = '.(int)(LoyaltyStateModule::getConvertId()));

		if (!empty($items) AND is_array($items))
		{
			foreach ($items AS $key => $item)
			{
				$order = new Order((int)$item['id_order']);
				$items[$key]['id_currency'] = (int)$order->id_currency;
				$items[$key]['id_lang'] = (int)$order->id_lang;
				$items[$key]['total_paid'] = $order->total_paid;
				$items[$key]['total_shipping'] = $order->total_shipping;
			}
			return $items;
		}

		return false;
	}

	public static function restoreConvertedLoyaltiesByDiscountId($id_discount) {
		$sql = sprintf("UPDATE `%sloyalty` SET `id_loyalty_state` = %d WHERE `id_discount` = %d AND `id_loyalty_state` = %d",
			_DB_PREFIX_,
			LoyaltyStateModule::getValidationId(),
			pSQL($id_discount),
			LoyaltyStateModule::getConvertId()
		);

		return Db::getInstance()->ExecuteS($sql);
	}

	/*custom written*/
	static public function referralIdExists($id_customer,$id_referralprogram)
	{
        $result = Db::getInstance()->getRow('
		SELECT l.id_referralprogram
		FROM `'._DB_PREFIX_.'loyalty` l
	    WHERE l.id_customer = '.intval($id_customer).' AND l.id_referralprogram = '.intval($id_referralprogram));
		return intval($result['id_referralprogram']);
	}

	/* Register all transaction in a specific history table */
	private function historize()
	{
		Db::getInstance()->Execute('
		INSERT INTO `'._DB_PREFIX_.'loyalty_history` (`id_loyalty`, `id_loyalty_state`, `points`, `date_add`)
		VALUES ('.(int)($this->id).', '.(int)($this->id_loyalty_state).', '.(int)($this->points).', NOW())');
	}

	static public function creditDiscountExists($orderId)
	{
		$result = Db::getInstance()->ExecuteS('
		SELECT od.`id_discount`
		FROM `'._DB_PREFIX_.'order_discount` od
		LEFT JOIN `'._DB_PREFIX_.'discount` d  ON (d.`id_discount` = od.`id_discount`)
		WHERE od.id_order = '.(int)($orderId).'
		AND d.id_discount_type 	 = '._PS_OS_CREDIT_ID_TYPE_.'
		');

		if($result)
			return true;
		else
			return false;
	}

}
