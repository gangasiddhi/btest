<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of category
 *
 * @author gangadhar
 */
class Category extends CategoryCore
{
	public function getCustomerInterestProducts($id_lang, $productIdsList, $p, $n, $orderBy = NULL, $orderWay = NULL, $getTotal = false, $active = true, $random = false, $randomNumberProducts = 1, $checkAccess = true, $diffColorQty = false)
	{
		if ($p < 1) $p = 1;

		if (empty($orderBy))
			$orderBy = 'position';
		else
			/* Fix for all modules which are now using lowercase values for 'orderBy' parameter */
			$orderBy = strtolower($orderBy);

		if (empty($orderWay))
			$orderWay = 'ASC';
		if ($orderBy == 'id_product' OR	$orderBy == 'date_add')
			$orderByPrefix = 'p';
		elseif ($orderBy == 'name')
			$orderByPrefix = 'pl';
		elseif ($orderBy == 'manufacturer')
		{
			$orderByPrefix = 'm';
			$orderBy = 'name';
		}
		elseif ($orderBy == 'position')
			$orderByPrefix = 'cp';

		if ($orderBy == 'price')
			$orderBy = 'orderprice';

		if (!Validate::isBool($active) OR !Validate::isOrderBy($orderBy) OR !Validate::isOrderWay($orderWay))
			die (Tools::displayError('Error: 201306051535'));

		$id_supplier = (int)(Tools::getValue('id_supplier'));

			$sql = '
		SELECT DISTINCT pa.`id_product_attribute`,IF(pa.default_image, p.`id_product`,\'  \')  AS product_combination,  p.*, pl.`description`, pl.`description_short`,pl.collection_name, pl.`available_now`, pl.`available_later`, pl.`link_rewrite`, pl.`meta_description`, pl.`meta_keywords`, pl.`meta_title`, pl.`name`,IF(pa.`default_on`, pa.`id_product_attribute` ,\'  \') as default_combination, IF(pa.default_image, pa.`default_image` ,i.`id_image`) as id_image , il.`legend`, m.`name` AS manufacturer_name, tl.`name` AS tax_name, t.`rate`, cl.`name` AS category_default, DATEDIFF(p.`date_add`, DATE_SUB(NOW(), INTERVAL '.(Validate::isUnsignedInt(Configuration::get('PS_NB_DAYS_NEW_PRODUCT')) ? Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20).' DAY)) > 0 AS new,
			(p.`price` * IF(t.`rate`,((100 + (t.`rate`))/100),1)) AS orderprice
		FROM `'._DB_PREFIX_.'product` p
		LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa ON (p.`id_product` = pa.`id_product` AND pa.active = 1 AND IF(pa.default_image ,1 , pa.`default_on` = 1))
		LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON (p.`id_category_default` = cl.`id_category` AND cl.`id_lang` = '.(int)($id_lang).')
		LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product` AND pl.`id_lang` = '.(int)($id_lang).')
		LEFT JOIN `'._DB_PREFIX_.'image` i ON (i.`id_product` = p.`id_product` AND IF(pa.default_image ,\'  \', i.`cover` = 1))
		LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = '.(int)($id_lang).')
		LEFT JOIN `'._DB_PREFIX_.'tax_rule` tr ON (p.`id_tax_rules_group` = tr.`id_tax_rules_group`
		                                           AND tr.`id_country` = '.(int)Country::getDefaultCountryId().'
	                                           	   AND tr.`id_state` = 0)
	    LEFT JOIN `'._DB_PREFIX_.'tax` t ON (t.`id_tax` = tr.`id_tax`)
		LEFT JOIN `'._DB_PREFIX_.'tax_lang` tl ON (t.`id_tax` = tl.`id_tax` AND tl.`id_lang` = '.(int)($id_lang).')
		LEFT JOIN `'._DB_PREFIX_.'manufacturer` m ON m.`id_manufacturer` = p.`id_manufacturer`
		WHERE p.quantity > 1 ' . (! empty($productIdsList) ? ' AND p.id_product IN (' . $productIdsList . ')' : '') . ($active ? ' AND p.`active` = 1' : '').'
		'.($id_supplier ? 'AND p.id_supplier = '.(int)$id_supplier : '');


		if ($random === true)
		{
			$sql .= ' ORDER BY RAND()';
			$sql .= ' LIMIT 0, '.(int)($randomNumberProducts);
		}
		else
		{
			$sql .= ' ORDER BY '.(isset($orderByPrefix) ? $orderByPrefix.'.' : '').'`'.pSQL($orderBy).'` '.pSQL($orderWay).'
			LIMIT '.(((int)($p) - 1) * (int)($n)).','.(int)($n);
		}

		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql);

		if ($orderBy == 'orderprice')
			Tools::orderbyPrice($result, $orderWay);

		if (!$result)
			return false;

		return Product::getProductsProperties($id_lang, $result, $diffColorQty);
	}

	/**
	 *
	 * @param type $productId
	 * @param type $parentId
	 * @return string of subcategories the product belongs, to display in metatag keyword.
	 */
	public function getProductSubcategory($productId, $parentId)
	{
		$subcategory_sql = 'SELECT c.id_category as subcategory_id,cl.name as subcategory_name FROM '._DB_PREFIX_.'category_product cp
			LEFT JOIN '._DB_PREFIX_.'category c ON cp.id_category = c.id_category
            LEFT JOIN '._DB_PREFIX_.'category_lang cl ON c.id_category = cl.id_category WHERE c.id_parent='.$parentId.' AND  cp.id_product='.$productId.' AND cl.id_lang = 4';

		$subcategory_result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($subcategory_sql);
		foreach($subcategory_result as $res)
		{
			$subcategories[] = $res[subcategory_name];
		}
		$subcat_string = implode(",", $subcategories);

		return $subcat_string;
	}


}

?>
