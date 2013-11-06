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
*  @version  Release: $Revision: 7541 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class DiscountCore extends ObjectModel
{
    public      $id;

    /** @var integer Customer id only if discount is reserved */
    public      $id_customer;

    /** @var integer Group id only if discount is reserved */
    public      $id_group;

    /** @var integer Currency ID only if the discount type is 2 */
    public      $id_currency;

    /** @var integer Discount type ID */
    public      $id_discount_type;

    /** @var string Name (the one which must be entered) */
    public      $name;

    /** @var string A short description for the discount */
    public      $description;

    /** @var string Value in percent as well as in euros */
    public      $value;

    /** @var integer Totale quantity available */
    public      $quantity;

    /** @var integer User quantity available */
    public      $quantity_per_user;

    /** @var boolean Indicate if discount is cumulable with others */
    public      $cumulable;

    /** @var integer Indicate if discount is cumulable with already bargained products */
    public      $cumulable_reduction;

    /** @var integer Date from wich discount become active */
    public      $date_from;

    /** @var integer Date from wich discount is no more active */
    public      $date_to;

    /** @var integer Minimum cart total amount required to use the discount */
    public      $minimal;

    /** @var integer display the discount in the summary */
    public      $cart_display;

    public      $behavior_not_exhausted;

    /** @var boolean Status */
    public      $active = true;

    /** @var string Object creation date */
    public      $date_add;

    /** @var string Object last modification date */
    public      $date_upd;

    /** @var integer Id of source order */
    public      $origin;

    protected   $fieldsRequired = array('id_discount_type', 'name', 'value', 'quantity', 'quantity_per_user', 'date_from', 'date_to');
    protected   $fieldsSize = array('name' => '32', 'date_from' => '32', 'date_to' => '32');
    protected   $fieldsValidate = array('id_customer' => 'isUnsignedId', 'id_group' => 'isUnsignedId',
        'id_discount_type' => 'isUnsignedId', 'id_currency' => 'isUnsignedId',
        'name' => 'isDiscountName', 'value' => 'isPrice', 'quantity' => 'isUnsignedInt', 'quantity_per_user' => 'isUnsignedInt',
        'cumulable' => 'isBool', 'cumulable_reduction' => 'isBool', 'date_from' => 'isDate',
        'date_to' => 'isDate', 'minimal' => 'isUnsignedFloat', 'active' => 'isBool', 'origin' => 'isInt');
    protected   $fieldsRequiredLang = array('description');
    protected   $fieldsSizeLang = array('description' => 128);
    protected   $fieldsValidateLang = array('description' => 'isVoucherDescription');

    protected   $table = 'discount';
    protected   $identifier = 'id_discount';

    protected   $webserviceParameters = array(
            'fields' => array(
            'id_discount_type' => array('sqlId' => 'id_discount_type', 'xlink_resource' => 'discount_types'),
            'id_customer' => array('sqlId' => 'id_customer', 'xlink_resource' => 'customers'),
            'id_group' => array('sqlId' => 'id_group', 'xlink_resource' => 'groups'),
            'id_currency' => array('sqlId' => 'id_currency', 'xlink_resource' => 'currencies'),
            'name' => array('sqlId' => 'name'),
            'value' => array('sqlId' => 'value'),
            'quantity' => array('sqlId' => 'quantity'),
            'quantity_per_user' => array('sqlId' => 'quantity_per_user'),
            'cumulable' => array('sqlId' => 'cumulable'),
            'cumulable_reduction' => array('sqlId' => 'cumulable_reduction'),
            'behavior_not_exhausted' => array('sqlId' => 'behavior_not_exhausted'),
            'date_from' => array('sqlId' => 'date_from'),
            'date_to' => array('sqlId' => 'date_to'),
            'minimal' => array('sqlId' => 'minimal'),
            'active' => array('sqlId' => 'active'),
            'cart_display' => array('sqlId' => 'cart_display'),
            'date_add' => array('sqlId' => 'date_add'),
            'date_upd' => array('sqlId' => 'date_upd'),
            'origin' => array('sqlId' => 'origin')
        )
    );

    public function __construct($id = NULL, $id_lang = NULL) {
        parent::__construct($id, $id_lang);

        $this->log = Logger::getLogger(get_class($this));
    }

    public function getFields() {
        parent::validateFields();

        $fields['id_customer'] = (int)($this->id_customer);
        $fields['id_group'] = (int)($this->id_group);
        $fields['id_currency'] = (int)($this->id_currency);
        $fields['id_discount_type'] = (int)($this->id_discount_type);
        $fields['name'] = pSQL($this->name);
        $fields['value'] = (float)($this->value);
        $fields['quantity'] = (int)($this->quantity);
        $fields['quantity_per_user'] = (int)($this->quantity_per_user);
        $fields['cumulable'] = (int)($this->cumulable);
        $fields['cumulable_reduction'] = (int)($this->cumulable_reduction);
        $fields['date_from'] = pSQL($this->date_from);
        $fields['date_to'] = pSQL($this->date_to);
        $fields['minimal'] = (float)($this->minimal);
        $fields['behavior_not_exhausted'] = (int)$this->behavior_not_exhausted;
        $fields['active'] = (int)($this->active);
        $fields['cart_display'] = (int)($this->cart_display);
        $fields['date_add'] = pSQL($this->date_add);
        $fields['date_upd'] = pSQL($this->date_upd);
        $fields['origin'] = pSQL($this->origin);

        return $fields;
    }

    public function add($autodate = true, $nullValues = false, $categories = null)
    {
        $ret = NULL;
        if (parent::add($autodate, $nullValues))
            $ret = true;
        /**********************TODO-CategoriesDiscount  //CD: Dont delete the below commented code
        * Please uncomment the below code when you are using the Categories
        * discounts**********************/
		
		if(Configuration::get('BU_CATEGORY_DISCOUNT_ENABLE')){
	       $this->updateCategories($categories);
		}
        return $ret;
    }

    /* Categories initialization is different between add() and update() because the addition will set all categories if none are selected (compatibility with old modules) and update won't update categories if none are selected */
    public function update($autodate = true, $nullValues = false, $categories = false)
    {
        $ret = NULL;
        if (parent::update($autodate, $nullValues))
            $ret = true;
        /**********************TODO-CategoriesDiscount  //CD: Dont delete the below commented code
        * Please uncomment the below code when you are using the Categories
        * discounts**********************/
		if(Configuration::get('BU_CATEGORY_DISCOUNT_ENABLE')){
	        $this->updateCategories($categories);
		}
        return $ret;
    }

    public function delete()
    {
        if (!parent::delete())
            return false;
        return (Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'cart_discount WHERE id_discount = '.(int)($this->id))
                                AND Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'discount_category WHERE id_discount = '.(int)($this->id)));
    }

    public function getTranslationsFieldsChild()
    {
        if (!parent::validateFieldsLang())
            return false;
        return parent::getTranslationsFields(array('description'));
    }

    /**
      * Return discount types list
      *
      * @return array Discount types
      */
    public static function getDiscountTypes($id_lang)
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
        SELECT *
        FROM '._DB_PREFIX_.'discount_type dt
        LEFT JOIN `'._DB_PREFIX_.'discount_type_lang` dtl ON (dt.`id_discount_type` = dtl.`id_discount_type` AND dtl.`id_lang` = '.(int)($id_lang).')');
    }

    /**
      * Get discount ID from name
      *
      * @param string $discountName Discount name
      * @return integer Discount ID
      */
    public static function getIdByName($discountName)
    {
        if (!Validate::isDiscountName($discountName))
            die(Tools::displayError('Invalid discount name: ' . $discountName));

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
        SELECT `id_discount`
        FROM `'._DB_PREFIX_.'discount`
        WHERE `name` = \''.pSQL($discountName).'\'');
        return isset($result['id_discount']) ? $result['id_discount'] : false;
    }

    /**
      * Get discount ID's by customer
      * @param string $id_customer
      * @return integer Discount ID's
      */

    static public  function getDiscountIdsByType($id_lang, $id_customer,$id_discount_type, $active = false, $includeGenericOnes = true, $stock = false)
    {

        $res = Db::getInstance()->ExecuteS('
        SELECT d.id_discount, dtl.`name` AS `type`, dl.`description`
        FROM `'._DB_PREFIX_.'discount` d
        LEFT JOIN `'._DB_PREFIX_.'discount_lang` dl ON (d.`id_discount` = dl.`id_discount` AND dl.`id_lang` = '.intval($id_lang).')
        LEFT JOIN `'._DB_PREFIX_.'discount_type` dt ON dt.`id_discount_type` = d.`id_discount_type`
        LEFT JOIN `'._DB_PREFIX_.'discount_type_lang` dtl ON (dt.`id_discount_type` = dtl.`id_discount_type` AND dtl.`id_lang` = '.intval($id_lang).')
        WHERE (`id_customer` = '.intval($id_customer).($includeGenericOnes ? ' OR `id_customer` = 0' : '').') '.(' AND d.`id_discount_type`= '.intval($id_discount_type).'').'
        '.($active ? ' AND d.`active` = 1' : '').'
        '.($stock ? ' AND d.`quantity` != 0' : ''));
        return $res;
    }

    /**
      * Return customer discounts
      *
      * @param integer $id_lang Language ID
      * @param boolean $id_customer Customer ID
      * @return array Discounts
      */
    public static function getCustomerDiscounts($id_lang, $id_customer, $active = false, $includeGenericOnes = true, $stock = false,
          $pagination = false, $limit = 10, $page = 1) {
        global $cart;

        $sql = '
            SELECT '.($pagination ? 'SQL_CALC_FOUND_ROWS ' : '').' d.*, dtl.`name` AS `type`, dl.`description`
            FROM `' . _DB_PREFIX_ . 'discount` d
            LEFT JOIN `' . _DB_PREFIX_ . 'discount_lang` dl ON (d.`id_discount` = dl.`id_discount` AND dl.`id_lang` = ' . (int)($id_lang) . ')
            LEFT JOIN `' . _DB_PREFIX_ . 'discount_type` dt ON dt.`id_discount_type` = d.`id_discount_type`
            LEFT JOIN `' . _DB_PREFIX_ . 'discount_type_lang` dtl ON (dt.`id_discount_type` = dtl.`id_discount_type` AND dtl.`id_lang` = ' . (int)($id_lang) . ')
            WHERE (`id_customer` = ' . (int)($id_customer) . '
            OR `id_group` IN (SELECT `id_group` FROM `' . _DB_PREFIX_ . 'customer_group` WHERE `id_customer` = ' . (int)($id_customer) . ')' .
            ($includeGenericOnes ? ' OR (`id_customer` = 0 AND `id_group` = 0)' : '') . ')
            ' . ($active ? ' AND d.`active` = 1' : '') . '
            ' . ($stock ? ' AND d.`quantity` != 0' : '') . '
            ORDER BY d.`date_add` DESC'
            .($pagination ? ' LIMIT '.(((int)($page) - 1) * (int)($limit)).', '.(int)($limit) : '');

        $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql);

        if ($pagination) {
            $res['totalItem'] = Db::getInstance()->getValue('SELECT FOUND_ROWS() as rowCount');
        }

        foreach ($res as &$discount) {
            if ($discount['quantity_per_user']) {
                $quantity_used = Order::getDiscountsCustomer((int)($id_customer), (int)($discount['id_discount']));

                if (isset($cart) AND isset($cart->id)) {
                    $quantity_used += $cart->getDiscountsCustomer((int)($discount['id_discount']));
                }

                $discount['quantity_for_user'] = $discount['quantity_per_user'] - $quantity_used;
            } else {
                $discount['quantity_for_user'] = 0;
            }
        }

        return $res;
    }

    public function usedByCustomer($id_customer)
    {
        return Db::getInstance()->getValue('
        SELECT COUNT(*)
        FROM `'._DB_PREFIX_.'order_discount` od
        LEFT JOIN `'._DB_PREFIX_.'orders` o ON (od.`id_order` = o.`id_order`)
        WHERE od.`id_discount` = '.(int)($this->id).'
        AND o.`id_customer` = '.(int)($id_customer)
        );
    }

    /**
      * Return discount value
      *
      * @param integer $nb_discounts Number of discount currently in cart
      * @param boolean $order_total_products Total cart products amount
      * @return mixed Return a float value or '!' if reduction is 'Shipping free'
      */
    public function getValue($nb_discounts = 0, $order_total_products = 0, $shipping_fees = 0, $idCart = false, $useTax = true)
    {
        $totalAmount = 0;

        $cart = new Cart((int)($idCart));
        if (!Validate::isLoadedObject($cart))
            return 0;

        if ((!$this->cumulable AND (int)($nb_discounts) > 1  AND $this->id_discount_type != 4)
        OR !$this->active OR (!$this->quantity AND !$cart->OrderExists()))
        {
            return 0;

        }

        if ($this->usedByCustomer((int)($cart->id_customer)) >= $this->quantity_per_user AND !$cart->OrderExists())
            return 0;

        $date_start = strtotime($this->date_from);
        $date_end = strtotime($this->date_to);
        if ((time() < $date_start OR time() > $date_end) AND !$cart->OrderExists()) return 0;

        $products = $cart->getProducts();
        /**********************TODO-CategoriesDiscount  //CD: Dont delete the below commented code
        * Please uncomment the below code when you are using the Categories
        * discounts**********************/
		if(Configuration::get('BU_CATEGORY_DISCOUNT_ENABLE')) {
	        $categories = Discount::getCategories((int)($this->id));
		}

        foreach ($products AS $product){
			if(Configuration::get('BU_CATEGORY_DISCOUNT_ENABLE')){
				if (count($categories) AND Product::idIsOnCategoryId($product['id_product'], $categories)) {
					$totalAmount += $useTax ? $product['price_wt'] * $product['quantity']: $product['price'] * $product['quantity'];
				}
			}else{
				$totalAmount += $useTax ? $product['price_wt'] * $product['quantity']: $product['price'] * $product['quantity'];
			}
		}
        if ($this->minimal > 0 AND $totalAmount < $this->minimal)
            return 0;

        switch ($this->id_discount_type)
        {
            /* Relative value (% of the order total) */
            case 1:
                $amount = 0;
                $percentage = $this->value / 100;
                foreach ($products AS $product) {
					if(Configuration::get('BU_CATEGORY_DISCOUNT_ENABLE')){
						if (Product::idIsOnCategoryId($product['id_product'], $categories)) {
							if ($this->cumulable_reduction OR (!$product['reduction_applies'] AND !$product['on_sale'])){
								$amount += ($useTax ? $product['total_wt'] : $product['total']) * $percentage;
							}
						}
					}else{
						if ($this->cumulable_reduction OR (!$product['reduction_applies'] AND !$product['on_sale'])){
								$amount += ($useTax ? $product['total_wt'] : $product['total']) * $percentage;
							}
					}
				}
                return $amount;

            /* Absolute value */
            case 2:
            case 7:
                // An "absolute" voucher is available in one currency only
                $currency = ((int)$cart->id_currency ? Currency::getCurrencyInstance($cart->id_currency) : Currency::getCurrent());
                if ($this->id_currency != $currency->id)
                    return 0;

                $taxDiscount = Cart::getTaxesAverageUsed((int)($cart->id));
                if (!$useTax AND isset($taxDiscount) AND $taxDiscount != 1)
                    $this->value = abs($this->value / (1 + $taxDiscount * 0.01));

                // Main return
                $value = 0;
                /**********************CategoriesDiscount  //CD: Dont delete the below commented code
                * Please uncomment the below code when you are using the Categories
                * discounts**********************/
                foreach ($products AS $product) {
					if(Configuration::get('BU_CATEGORY_DISCOUNT_ENABLE')){
						if (Product::idIsOnCategoryId($product['id_product'], $categories)) {
							 $value = $this->value;
						}
					}else{
						if (Product::idIsOnCategoryId($product['id_product'], $categories)) {
							 $value = $this->value;
						}
					}
				}
                // Return 0 if there are no applicable categories
                return $value;

            /* Free shipping (does not return a value but a special code) */
            case 3:
                return '!';

                /* Credits absolute value */
            case 4:
                $currency = ((int)$cart->id_currency ? new Currency($cart->id_currency) : Currency::getCurrent());
                if ($this->id_currency != $currency->id)
                    return 0;
                //$taxDiscount = Cart::getTaxesAverageUsed(intval($cart->id));
                //if (!$useTax AND isset($taxDiscount) AND $taxDiscount != 1)
                // Main return
                $value = 0;
                $value= floatval($this->value);
                foreach ($products AS $product)
                    if (Product::idIsOnCategoryId($product['id_product']))
                        // Return 0 if there are no applicable categories
                        return  $value;
            case 5:
                $currency = ((int)$cart->id_currency ? new Currency($cart->id_currency) : Currency::getCurrent());
                if ($this->id_currency != $currency->id)
                    return 0;

                //$taxDiscount = Cart::getTaxesAverageUsed(intval($cart->id));
                //if (!$useTax AND isset($taxDiscount) AND $taxDiscount != 1)
                // Main return
                $value = 0;
                    $value= floatval($this->value);
					if(Configuration::get('BU_CATEGORY_DISCOUNT_ENABLE')){
						foreach ($products AS $product) 
							if (Product::idIsOnCategoryId($product['id_product'], $categories))
							// Return 0 if there are no applicable categories
								return  $value;
					}
//CD                foreach ($products AS $product) 
//CD                    if (Product::idIsOnCategoryId($product['id_product'], $categories))
                        // Return 0 if there are no applicable categories
                        return  $value;
            case 6:
                $currency = ((int)$cart->id_currency ? new Currency($cart->id_currency) : Currency::getCurrent());
                if ($this->id_currency != $currency->id)
                    return 0;

                //$taxDiscount = Cart::getTaxesAverageUsed(intval($cart->id));
                //if (!$useTax AND isset($taxDiscount) AND $taxDiscount != 1)
                // Main return
                $value = 0;
                    $value= floatval($this->value);
                foreach ($products AS $product)
                    if (Product::idIsOnCategoryId($product['id_product']))
                        // Return 0 if there are no applicable categories
                        return  $value;
        }
        return 0;
    }

    /**
     * @param int $id_category_product
     * @param int $id_category_discount
     * @return bool
     * @deprecated
     */
    public static function isParentCategoryProductDiscount($id_category_product, $id_category_discount)
    {
        Tools::displayAsDeprecated();
        $category = new Category((int)($id_category_product));
        $parentCategories = $category->getParentsCategories();
        foreach($parentCategories AS $parentCategory)
            if ($id_category_discount == $parentCategory['id_category'])
                return true;
        return false;
    }

    public static function getCategories($id_discount)
    {
        return Db::getInstance()->ExecuteS('
        SELECT `id_category`
        FROM `'._DB_PREFIX_.'discount_category`
        WHERE `id_discount` = '.(int)($id_discount));
    }

    public function updateCategories($categories)
    {
        /* false value will avoid category update and null value will force all category to be selected */
        if ($categories === false)
            return ;
        if ($categories === null)
        {
            // Compatibility for modules which create discount without setting categories (ex. fidelity, sponsorship)
            $result = Db::getInstance()->ExecuteS('SELECT id_category FROM '._DB_PREFIX_.'category');
            $categories = array();
            foreach ($result as $row)
                $categories[] = $row['id_category'];
        }
        elseif (!is_array($categories) OR !sizeof($categories))
            return false;
        Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'discount_category` WHERE `id_discount`='.(int)($this->id));
        foreach($categories AS $category)
        {
            Db::getInstance()->ExecuteS('
            SELECT `id_discount`
            FROM `'._DB_PREFIX_.'discount_category`
            WHERE `id_discount`='.(int)($this->id).' AND `id_category`='.(int)($category));
            if (Db::getInstance()->NumRows() == 0)
                Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'discount_category` (`id_discount`, `id_category`) VALUES('.(int)($this->id).','.(int)($category).')');
        }
    }

    public static function discountExists($discountName, $id_discount = 0) {
        return Db::getInstance()->getRow('SELECT `id_discount` FROM '._DB_PREFIX_.'discount WHERE `name` LIKE \''.pSQL($discountName).'\' AND `id_discount` != '.(int)($id_discount));
    }

    // To create discount for orders
    static public function createDiscountForOrder($order, $name, $type, $discount_value = 0) {
        $languages = Language::getLanguages($order);
        // create discount
        $credit = new Discount();
        $credit->id_discount_type = $type;

        if (in_array($type, array(2, 7))) {
            $credit->behavior_not_exhausted = 2;
        } else {
            $credit->behavior_not_exhausted = 0;
        }

        foreach ($languages as $language) {
            $credit->description[$language['id_lang']] = strval($name) . ' Sipariş #: ' . intval($order->id);
        }

        $credit->name = Tools::voucherGen();
        $credit->id_customer = intval($order->id_customer);
        $credit->id_currency = intval($order->id_currency);
        $credit->quantity = 1;
        $credit->quantity_per_user = 1;
        $credit->cumulable = 0;
        $credit->cumulable_reduction = 1;
        $credit->value = floatval($discount_value);

        /**
         * Only exchange vouchers can be used cumulatively..
         * We also check for voucher types during cart addition to prevent
         * usage of different types of vouchers..
         */
        if ($type == _EXCHANGE_VOUCHER_TYPE_ID_) {
            $credit->cumulable = 1;
            $credit->cumulable_reduction = 1;
            $credit->origin = $order->id;
        }

		$credit->origin = $order->id;

        $credit->minimal = 0;
        $credit->active = 1;

        $now = time();

        $credit->date_from = date('Y-m-d H:i:s', $now);
        $credit->date_to = date('Y-m-d H:i:s', $now + (3600 * 24 * 365.25));

        if (! $credit->validateFieldsLang(false) OR ! $credit->add()) {
            $this->log->error('Error occurred during voucher creation: ' . mysql_error());
            $this->log->debug('Is voucher valid? ' . ($credit->validateFieldsLang(false) ? 'yes' : 'no'));

            return false;
        }

        if (! $credit->update()) {
            $this->log->error('Error occurred during voucher update: ' . mysql_error());

            return false;
        }

        return $credit;
    }

    public static function display($discountValue, $discountType, $currency = false)
    {
        if ((float)($discountValue) AND (int)($discountType))
        {
            if ($discountType == 1)
                return $discountValue.chr(37); // ASCII #37 --> % (percent)
            elseif ($discountType == 2)
                return Tools::displayPrice($discountValue, $currency);
        }
        return ''; // return a string because it's a display method
    }

    public static function getVouchersToCartDisplay($id_lang, $id_customer)
    {
        return Db::getInstance()->ExecuteS('
        SELECT d.`name`, dl.`description`, d.`id_discount`
        FROM `'._DB_PREFIX_.'discount` d
        LEFT JOIN `'._DB_PREFIX_.'discount_lang` dl ON (d.`id_discount` = dl.`id_discount`)
        WHERE d.`active` = 1
        AND d.`date_from` <= \''.pSQL(date('Y-m-d H:i:s')).'\' AND d.`date_to` >= \''.pSQL(date('Y-m-d H:i:s')).'\'
        AND dl.`id_lang` = '.(int)($id_lang).'
        AND d.`cart_display` = 1 AND d.`quantity` > 0
        AND ((d.`id_customer` = 0 AND d.`id_group` = 0) '.($id_customer ? 'OR (d.`id_customer` = '.$id_customer.'
        OR d.`id_group` IN (SELECT `id_group` FROM `'._DB_PREFIX_.'customer_group` WHERE `id_customer` = '.(int)($id_customer).')))' : 'OR d.`id_group` = 1)'));
    }

    public static function deleteByIdCustomer($id_customer)
    {
        $discounts = Db::getInstance()->ExecuteS('SELECT `id_discount` FROM `'._DB_PREFIX_.'discount` WHERE `id_customer` = '.(int)($id_customer));
        foreach ($discounts as $discount)
        {
            Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'discount` WHERE `id_discount` = '.(int)($discount['id_discount']));
            Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'discount_category` WHERE `id_discount` = '.(int)($discount['id_discount']));
            Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'discount_lang` WHERE `id_discount` = '.(int)($discount['id_discount']));
        }
        return true;
    }

    public static function deleteByIdGroup($id_group)
    {
        $discounts = Db::getInstance()->ExecuteS('SELECT `id_discount` FROM `'._DB_PREFIX_.'discount` WHERE `id_group` = '.(int)($id_group));
        foreach ($discounts as $discount)
        {
            Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'discount` WHERE `id_discount` = '.(int)($discount['id_discount']));
            Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'discount_category` WHERE `id_discount` = '.(int)($discount['id_discount']));
            Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'discount_lang` WHERE `id_discount` = '.(int)($discount['id_discount']));
        }
        return true;
    }

    public static function getDiscount($id_discount)
    {
        return Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.'discount` WHERE `id_discount` = '.(int)$id_discount);
    }

    public static function updateDiscountValue($discount_value ,$id_discount)
    {
        Db::getInstance()->Execute('
            UPDATE `'._DB_PREFIX_.'discount`
            SET  `value` = '.floatval($discount_value).'
            WHERE `id_discount` = '.(int)$id_discount.'
        ');
    }

    public static function createPeriodicalDiscounts($discount_value, $discount_type, $discount_desc , $id_customer)
    {
        $languages = Language::getLanguages(false);
        // create discount
        $discount = new Discount();
        $discount->id_discount_type = $discount_type;
        $discount->behavior_not_exhausted = 0;
        foreach ($languages as $language)
        $discount->description[$language['id_lang']] = strval($discount_desc);
        $discount->value = floatval($discount_value);
        $discount->name = Tools::voucherGen();
        $discount->id_customer = $id_customer;
        $discount->id_currency = 4;
        $discount->quantity = 1;
        $discount->quantity_per_user = 1;
        $discount->cumulable = 0;
        $discount->cumulable_reduction = 1;
        $discount->minimal = 0.00;
        $discount->active = 1;
        $now = time();
        $discount->date_from = date('Y-m-d H:i:s', $now);
        $discount->date_to = date('Y-m-d H:i:s', $now + (3600 * 24 * 365.25));
        if (!$discount->validateFieldsLang(false) OR !$discount->add())
        {
            return false;
        }
        else
            return $discount;
    }

	public static function getExistingDiscountsForOrder($id_order)
    {
        return Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'discount` WHERE `origin` = '.(int)$id_order);
    }
}

