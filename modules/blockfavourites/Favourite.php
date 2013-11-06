<?php
/*
* 2007-2011 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you fav to upgrade PrestaShop to newer
* versions in the future. If you fav to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2011 PrestaShop SA
*  @version  Release: $Revision: 7541 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_CAN_LOAD_FILES_'))
	exit;

class	Favourite extends ObjectModel
{
	/** @var integer Favlist ID */
	public		$id;

	/** @var integer Customer ID */
	public 		$id_customer;

	/** @var integer Token */
	public 		$token;

	/** @var integer Name */
	public 		$name;

	/** @var string Object creation date */
	public 		$date_add;

	/** @var string Object last modification date */
	public 		$date_upd;

	protected	$fieldsSize = array('name' => 64, 'token' => 64);
	protected	$fieldsRequired = array('id_customer', 'name', 'token');
	protected	$fieldsValidate = array('id_customer' => 'isUnsignedId', 'name' => 'isMessage',
		'token' => 'isMessage');
	protected 	$table = 'favlist';
	protected 	$identifier = 'id_favlist';

	public function getFields()
	{
		parent::validateFields();
		$fields['id_customer'] = (int)($this->id_customer);
		$fields['token'] = pSQL($this->token);
		$fields['name'] = pSQL($this->name);
		$fields['date_add'] = pSQL($this->date_add);
		$fields['date_upd'] = pSQL($this->date_upd);
		return ($fields);
	}

	public function delete()
	{
		global $cookie;

		Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'favlist_email` WHERE `id_favlist` = '.(int)($this->id));
		Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'favlist_product` WHERE `id_favlist` = '.(int)($this->id));
		if (isset($cookie->id_favlist))
			unset($cookie->id_favlist);

		return (parent::delete());
	}

	/**
	 * Increment counter
	 *
	 * @return boolean succeed
	 */
	public static function incCounter($id_favlist)
	{
		if (!Validate::isUnsignedId($id_favlist))
			die (Tools::displayError());
		$result = Db::getInstance()->getRow('
		SELECT `counter`
		  FROM `'._DB_PREFIX_.'favlist`
		WHERE `id_favlist` = '.(int)($id_favlist));
		if ($result == false OR !sizeof($result) OR empty($result) === true)
			return (false);
		return (Db::getInstance()->Execute('
		UPDATE `'._DB_PREFIX_.'favlist` SET
		`counter` = '.(int)($result['counter'] + 1).'
		WHERE `id_favlist` = '.(int)($id_favlist)));
	}


	public static function isExistsByNameForUser($name)
	{
		global $cookie;

		return Db::getInstance()->getValue('
		SELECT COUNT(*) AS total
		FROM `'._DB_PREFIX_.'favlist`
		WHERE `name` = \''.pSQL($name).'\'
		AND `id_customer` = '.(int)($cookie->id_customer)
		);
	}

	/**
	 * Return true if favlist exists else false
	 *
	 *  @return boolean exists
	 */
	public static function exists($id_favlist, $id_customer, $return = false)
	{
		if (!Validate::isUnsignedId($id_favlist) OR
			!Validate::isUnsignedId($id_customer))
			die (Tools::displayError());
		$result = Db::getInstance()->getRow('
		SELECT `id_favlist`, `name`, `token`
		  FROM `'._DB_PREFIX_.'favlist`
		WHERE `id_favlist` = '.(int)($id_favlist).'
		AND `id_customer` = '.(int)($id_customer));
		if (empty($result) === false AND $result != false AND sizeof($result))
		{
			if ($return === false)
				return (true);
			else
				return ($result);
		}
		return (false);
	}

	public static function productExistsInCustomerFavlist($id_product, $id_product_attribute = 0, $id_customer)
	{
		if (!Validate::isUnsignedId($id_customer))
			die (Tools::displayError());
		$result = Db::getInstance()->getRow('
		SELECT *
		FROM `'._DB_PREFIX_.'favlist_product` fp
		LEFT JOIN `'._DB_PREFIX_.'favlist` f ON f.`id_favlist` = fp.`id_favlist`
		WHERE `id_product` = '.(int)($id_product).'
		'.($id_product_attribute > 0 ? 'AND `id_product_attribute` = '.(int)($id_product_attribute) : '').'
		AND f.`id_customer` = '.(int)($id_customer));
		
		if (empty($result) === false AND $result != false AND sizeof($result))
		{
			//if ($return === false)
				return (true);
			//else
				//return ($result);
		}
		return (false);
	}

	/**
	 * Get ID favlist by Token
	 *
	 * @return array Results
	 */
	public static function getByToken($token)
	{
		if (!Validate::isMessage($token))
			die (Tools::displayError());
		return (Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
		SELECT f.`id_favlist`, f.`name`, f.`id_customer`, c.`firstname`, c.`lastname`
		  FROM `'._DB_PREFIX_.'favlist` f
		INNER JOIN `'._DB_PREFIX_.'customer` c ON c.`id_customer` = f.`id_customer`
		WHERE `token` = \''.pSQL($token).'\''));
	}

	/**
	 * Get Favlists by Customer ID
	 *
	 * @return array Results
	 */
	public static function getByIdCustomer($id_customer)
	{
		if (!Validate::isUnsignedId($id_customer))
			die (Tools::displayError());
		return (Db::getInstance()->ExecuteS('
		SELECT f.`id_favlist`, f.`name`, f.`token`, f.`date_add`, f.`date_upd`, f.`counter`
		FROM `'._DB_PREFIX_.'favlist` f
		WHERE `id_customer` = '.(int)($id_customer).'
		ORDER BY f.`name` ASC'));
	}

	public static function refreshFavList($id_favlist)
	{
		$old_carts = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT fp.id_product, fp.id_product_attribute, fpc.id_cart, UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(fpc.date_add) AS timecart
		FROM `'._DB_PREFIX_.'favlist_product_cart` fpc
		JOIN `'._DB_PREFIX_.'favlist_product` fp ON (fp.id_favlist_product = fpc.id_favlist_product)
		JOIN `'._DB_PREFIX_.'cart` c ON  (c.id_cart = fpc.id_cart)
		JOIN `'._DB_PREFIX_.'cart_product` cp ON (fpc.id_cart = cp.id_cart)
		LEFT JOIN `'._DB_PREFIX_.'orders` o ON (o.id_cart = c.id_cart)
		WHERE (fp.id_favlist='.(int)($id_favlist).' AND o.id_cart IS NULL)
		HAVING timecart  >= 3600*6');

		if (isset($old_carts) AND $old_carts != false)
			foreach ($old_carts AS $old_cart)
				Db::getInstance()->Execute('
					DELETE FROM `'._DB_PREFIX_.'cart_product`
					WHERE id_cart='.(int)($old_cart['id_cart']).' AND id_product='.(int)($old_cart['id_product']).' AND id_product_attribute='.(int)($old_cart['id_product_attribute'])
				);

		$freshfav = Db::getInstance()->ExecuteS('
			SELECT  fpc.id_cart, fpc.id_favlist_product
			FROM `'._DB_PREFIX_.'favlist_product_cart` fpc
			JOIN `'._DB_PREFIX_.'favlist_product` fp ON (fpc.id_favlist_product = fp.id_favlist_product)
			JOIN `'._DB_PREFIX_.'cart` c ON (c.id_cart = fpc.id_cart)
			LEFT JOIN `'._DB_PREFIX_.'cart_product` cp ON (cp.id_cart = fpc.id_cart AND cp.id_product = fp.id_product AND cp.id_product_attribute = fp.id_product_attribute)
			WHERE (fp.id_favlist = '.(int)($id_favlist).' AND ((cp.id_product IS NULL AND cp.id_product_attribute IS NULL)))
			');
		$res = Db::getInstance()->ExecuteS('
			SELECT fp.id_favlist_product, cp.quantity AS cart_quantity, fpc.quantity AS fav_quantity, fpc.id_cart
			FROM `'._DB_PREFIX_.'favlist_product_cart` fpc
			JOIN `'._DB_PREFIX_.'favlist_product` fp ON (fp.id_favlist_product = fpc.id_favlist_product)
			JOIN `'._DB_PREFIX_.'cart` c ON (c.id_cart = fpc.id_cart)
			JOIN `'._DB_PREFIX_.'cart_product` cp ON (cp.id_cart = fpc.id_cart AND cp.id_product = fp.id_product AND cp.id_product_attribute = fp.id_product_attribute)
			WHERE fp.id_favlist='.(int)($id_favlist)
		);

		if (isset($res) AND $res != false)
			foreach ($res AS $refresh)
				if ($refresh['fav_quantity'] > $refresh['cart_quantity'])
				{
					Db::getInstance()->Execute('
						UPDATE `'._DB_PREFIX_.'favlist_product`
						SET `quantity`= `quantity` + '.((int)($refresh['fav_quantity']) - (int)($refresh['cart_quantity'])).'
						WHERE id_favlist_product='.(int)($refresh['id_favlist_product'])
					);
					Db::getInstance()->Execute('
						UPDATE `'._DB_PREFIX_.'favlist_product_cart`
						SET `quantity`='.(int)($refresh['cart_quantity']).'
						WHERE id_favlist_product='.(int)($refresh['id_favlist_product']).' AND id_cart='.(int)($refresh['id_cart'])
					);
				}
		if (isset($freshfav) AND $freshfav != false)
			foreach ($freshfav AS $prodcustomer)
			{
				Db::getInstance()->Execute('
					UPDATE `'._DB_PREFIX_.'favlist_product` SET `quantity`=`quantity` +
					(
						SELECT `quantity` FROM `'._DB_PREFIX_.'favlist_product_cart`
						WHERE `id_favlist_product`='.(int)($prodcustomer['id_favlist_product']).' AND `id_cart`='.(int)($prodcustomer['id_cart']).'
					)
					WHERE `id_favlist_product`='.(int)($prodcustomer['id_favlist_product']).' AND `id_favlist`='.(int)($id_favlist)
					);
				Db::getInstance()->Execute('
					DELETE FROM `'._DB_PREFIX_.'favlist_product_cart`
					WHERE `id_favlist_product`='.(int)($prodcustomer['id_favlist_product']).' AND `id_cart`='.(int)($prodcustomer['id_cart'])
					);
			}
	}

	/**
	 * Get Favlist products by Customer ID
	 *
	 * @return array Results
	 */
	public static function getProductByIdCustomer($id_favlist, $id_customer, $id_lang, $id_product = null, $quantity = false)
	{
		if (!Validate::isUnsignedId($id_customer) OR
			!Validate::isUnsignedId($id_lang) OR
			!Validate::isUnsignedId($id_favlist))
			die (Tools::displayError());
		
		$products = Db::getInstance()->ExecuteS('
		SELECT fp.`id_product`, fp.`quantity`, p.`quantity` AS product_quantity, pl.`name`, fp.`id_product_attribute`, fp.`priority`, pl.link_rewrite, cl.link_rewrite AS category_rewrite
		FROM `'._DB_PREFIX_.'favlist_product` fp
		JOIN `'._DB_PREFIX_.'product` p ON p.`id_product` = fp.`id_product`
		JOIN `'._DB_PREFIX_.'product_lang` pl ON pl.`id_product` = fp.`id_product`
		JOIN `'._DB_PREFIX_.'favlist` f ON f.`id_favlist` = fp.`id_favlist`
		LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON cl.`id_category` = p.`id_category_default` AND cl.id_lang='.(int)$id_lang.'
		WHERE f.`id_customer` = '.(int)($id_customer).'
		AND pl.`id_lang` = '.(int)($id_lang).'
		AND fp.`id_favlist` = '.(int)($id_favlist).
		(empty($id_product) === false ? ' AND fp.`id_product` = '.(int)($id_product) : '').
		($quantity == true ? ' AND fp.`quantity` != 0': ''));
		if (empty($products) === true OR !sizeof($products))
			return array();
		for ($i = 0; $i < sizeof($products); ++$i)
		{
			if (isset($products[$i]['id_product_attribute']) AND
				Validate::isUnsignedInt($products[$i]['id_product_attribute']))
			{
				$result = Db::getInstance()->ExecuteS('
				SELECT al.`name` AS attribute_name, pa.`quantity` AS "attribute_quantity"
				  FROM `'._DB_PREFIX_.'product_attribute_combination` pac
				LEFT JOIN `'._DB_PREFIX_.'attribute` a ON (a.`id_attribute` = pac.`id_attribute`)
				LEFT JOIN `'._DB_PREFIX_.'attribute_group` ag ON (ag.`id_attribute_group` = a.`id_attribute_group`)
				LEFT JOIN `'._DB_PREFIX_.'attribute_lang` al ON (a.`id_attribute` = al.`id_attribute` AND al.`id_lang` = '.(int)($id_lang).')
				LEFT JOIN `'._DB_PREFIX_.'attribute_group_lang` agl ON (ag.`id_attribute_group` = agl.`id_attribute_group` AND agl.`id_lang` = '.(int)($id_lang).')
				LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa ON (pac.`id_product_attribute` = pa.`id_product_attribute`)
				WHERE pac.`id_product_attribute` = '.(int)($products[$i]['id_product_attribute']));
				$products[$i]['attributes_small'] = '';
				if ($result)
					foreach ($result AS $k => $row)
						$products[$i]['attributes_small'] .= $row['attribute_name'].', ';
				$products[$i]['attributes_small'] = rtrim($products[$i]['attributes_small'], ', ');
				if (isset($result[0]))
					$products[$i]['attribute_quantity'] = $result[0]['attribute_quantity'];
			}
			else
				$products[$i]['attribute_quantity'] = $products[$i]['product_quantity'];
			$products[$i]['price'] = Product::getPriceStatic((int)$products[$i]['id_product'], true, ((isset($products[$i]['id_product_attribute']) AND !empty($products[$i]['id_product_attribute'])) ? (int)($products[$i]['id_product_attribute']) : 	NULL), 6);
		}
		return ($products);
	}

	/**
	 * Get Favlists number products by Customer ID
	 *
	 * @return array Results
	 */
	public static function getInfosByIdCustomer($id_customer)
	{
		if (!Validate::isUnsignedId($id_customer))
			die (Tools::displayError());
		return (Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT SUM(fp.`quantity`) AS nbProducts, fp.`id_favlist`,fp.`id_product`, fp.`id_product_attribute`
		  FROM `'._DB_PREFIX_.'favlist_product` fp
		INNER JOIN `'._DB_PREFIX_.'favlist` f ON (f.`id_favlist` = fp.`id_favlist`)
		WHERE f.`id_customer` = '.(int)($id_customer).'
		GROUP BY f.`id_favlist`
		ORDER BY f.`name` ASC'));
	}

	/**
	 * Add product to ID favlist
	 *
	 * @return boolean succeed
	 */
	public static function addProduct($id_favlist, $id_customer, $id_product, $id_product_attribute, $quantity)
	{
		if (!Validate::isUnsignedId($id_favlist) OR
			!Validate::isUnsignedId($id_customer) OR
			!Validate::isUnsignedId($id_product) OR
			!Validate::isUnsignedId($quantity))
			die (Tools::displayError());
		$result = Db::getInstance()->getRow('
		SELECT fp.`quantity`
		  FROM `'._DB_PREFIX_.'favlist_product` fp
		JOIN `'._DB_PREFIX_.'favlist` f ON (f.`id_favlist` = fp.`id_favlist`)
		WHERE fp.`id_favlist` = '.(int)($id_favlist).'
		AND f.`id_customer` = '.(int)($id_customer).'
		AND fp.`id_product` = '.(int)($id_product).'
		AND fp.`id_product_attribute` = '.(int)($id_product_attribute));
		if (empty($result) === false AND sizeof($result))
		{
			if (($result['quantity'] + $quantity) <= 0)
				return (Favourite::removeProduct($id_favlist, $id_customer, $id_product, $id_product_attribute));
			else
				return (Db::getInstance()->Execute('
				UPDATE `'._DB_PREFIX_.'favlist_product` SET
				`quantity` = '.(int)($quantity + $result['quantity']).'
				WHERE `id_favlist` = '.(int)($id_favlist).'
				AND `id_product` = '.(int)($id_product).'
				AND `id_product_attribute` = '.(int)($id_product_attribute)));
		}
		else
		{
			return (Db::getInstance()->Execute('
			INSERT INTO `'._DB_PREFIX_.'favlist_product` (`id_favlist`, `id_product`, `id_product_attribute`, `quantity`, `priority`) VALUES(
			'.(int)($id_favlist).',
			'.(int)($id_product).',
			'.(int)($id_product_attribute).',
			'.(int)($quantity).', 1)'));
		}
	}

	/**
	 * Update product to favlist
	 *
	 * @return boolean succeed
	 */
	public static function updateProduct($id_favlist, $id_product, $id_product_attribute, $priority, $quantity)
	{
		if (!Validate::isUnsignedId($id_favlist) OR
			!Validate::isUnsignedId($id_product) OR
			!Validate::isUnsignedId($quantity) OR
			$priority < 0 OR $priority > 2)
			die (Tools::displayError());
		return (Db::getInstance()->Execute('
		UPDATE `'._DB_PREFIX_.'favlist_product` SET
		`priority` = '.(int)($priority).',
		`quantity` = '.(int)($quantity).'
		WHERE `id_favlist` = '.(int)($id_favlist).'
		AND `id_product` = '.(int)($id_product).'
		AND `id_product_attribute` = '.(int)($id_product_attribute)));
	}

	/**
	 * Remove product from favlist
	 *
	 * @return boolean succeed
	 */
	public static function removeProduct($id_favlist, $id_customer, $id_product, $id_product_attribute)
	{
		if (!Validate::isUnsignedId($id_favlist) OR
			!Validate::isUnsignedId($id_customer) OR
			!Validate::isUnsignedId($id_product))
			die (Tools::displayError());
		$result = Db::getInstance()->getRow('
		SELECT f.`id_favlist`, fp.`id_favlist_product`
		FROM `'._DB_PREFIX_.'favlist` f
		LEFT JOIN `'._DB_PREFIX_.'favlist_product` fp ON (fp.`id_favlist` = f.`id_favlist`)
		WHERE `id_customer` = '.(int)($id_customer).'
		AND f.`id_favlist` = '.(int)($id_favlist));
		if (empty($result) === true OR
			$result === false OR
			!sizeof($result) OR
			$result['id_favlist'] != $id_favlist)
			return (false);
		// Delete product in favlist_product_cart
		Db::getInstance()->Execute('
		DELETE FROM `'._DB_PREFIX_.'favlist_product_cart`
		WHERE `id_favlist_product` = '.(int)($result['id_favlist_product'])
		);
		return Db::getInstance()->Execute('
		DELETE FROM `'._DB_PREFIX_.'favlist_product`
		WHERE `id_favlist` = '.(int)($id_favlist).'
		AND `id_product` = '.(int)($id_product).'
		AND `id_product_attribute` = '.(int)($id_product_attribute)
		);
	}

	/**
	 * Return bought product by ID favlist
	 *
	 * @return Array results
	 */
	public static function getBoughtProduct($id_favlist)
	{

		if (!Validate::isUnsignedId($id_favlist))
			die (Tools::displayError());
		return (Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT fp.`id_product`, fp.`id_product_attribute`, fpc.`quantity`, fpc.`date_add`, cu.`lastname`, cu.`firstname`
		FROM `'._DB_PREFIX_.'favlist_product_cart` fpc
		JOIN `'._DB_PREFIX_.'favlist_product` fp ON (fp.id_favlist_product = fpc.id_favlist_product)
		JOIN `'._DB_PREFIX_.'cart` ca ON (ca.id_cart = fpc.id_cart)
		JOIN `'._DB_PREFIX_.'customer` cu ON (cu.`id_customer` = ca.`id_customer`)
		WHERE fp.`id_favlist` = '.(int)($id_favlist)));
	}

	/**
	 * Add bought product
	 *
	 * @return boolean succeed
	 */
	public static function addBoughtProduct($id_favlist, $id_product, $id_product_attribute, $id_cart, $quantity)
	{
		if (!Validate::isUnsignedId($id_favlist) OR
			!Validate::isUnsignedId($id_product) OR
			!Validate::isUnsignedId($quantity))
			die (Tools::displayError());
		$result = Db::getInstance()->getRow('
			SELECT `quantity`, `id_favlist_product`
		  FROM `'._DB_PREFIX_.'favlist_product` fp
			WHERE `id_favlist` = '.(int)($id_favlist).'
			AND `id_product` = '.(int)($id_product).'
			AND `id_product_attribute` = '.(int)($id_product_attribute));

		if (!sizeof($result) OR
			($result['quantity'] - $quantity) < 0 OR
			$quantity > $result['quantity'])
			return (false);

			Db::getInstance()->ExecuteS('
			SELECT *
			FROM `'._DB_PREFIX_.'favlist_product_cart`
			WHERE `id_favlist_product`='.(int)($result['id_favlist_product']).' AND `id_cart`='.(int)($id_cart)
			);

		if (Db::getInstance()->NumRows() > 0)
			$result2= Db::getInstance()->Execute('
				UPDATE `'._DB_PREFIX_.'favlist_product_cart`
				SET `quantity`=`quantity` + '.(int)($quantity).'
				WHERE `id_favlist_product`='.(int)($result['id_favlist_product']).' AND `id_cart`='.(int)($id_cart)
				);

		else
			$result2 = Db::getInstance()->Execute('
				INSERT INTO `'._DB_PREFIX_.'favlist_product_cart`
				(`id_favlist_product`, `id_cart`, `quantity`, `date_add`) VALUES(
				'.(int)($result['id_favlist_product']).',
				'.(int)($id_cart).',
				'.(int)($quantity).',
				\''.pSQL(date('Y-m-d H:i:s')).'\')');

		if ($result2 === false)
			return (false);
		return (Db::getInstance()->Execute('
			UPDATE `'._DB_PREFIX_.'favlist_product` SET
			`quantity` = '.(int)($result['quantity'] - $quantity).'
			WHERE `id_favlist` = '.(int)($id_favlist).'
			AND `id_product` = '.(int)($id_product).'
			AND `id_product_attribute` = '.(int)($id_product_attribute)));
	}

	/**
	 * Add email to favlist
	 *
	 * @return boolean succeed
	 */
	public static function addEmail($id_favlist, $email)
	{
		if (!Validate::isUnsignedId($id_favlist) OR empty($email) OR !Validate::isEmail($email))
			return false;
		return (Db::getInstance()->Execute('
		INSERT INTO `'._DB_PREFIX_.'favlist_email` (`id_favlist`, `email`, `date_add`) VALUES(
		'.(int)($id_favlist).',
		\''.pSQL($email).'\',
		\''.pSQL(date('Y-m-d H:i:s')).'\')'));
	}

	/**
	 * Get email from favlist
	 *
	 * @return Array results
	 */
	public static function getEmail($id_favlist, $id_customer)
	{
		if (!Validate::isUnsignedId($id_favlist) OR
			!Validate::isUnsignedId($id_customer))
			die (Tools::displayError());
		return (Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT fe.`email`, fe.`date_add`
		  FROM `'._DB_PREFIX_.'favlist_email` fe
		INNER JOIN `'._DB_PREFIX_.'favlist` f ON f.`id_favlist` = fe.`id_favlist`
		WHERE fe.`id_favlist` = '.(int)($id_favlist).'
		AND f.`id_customer` = '.(int)($id_customer)));
	}
};
