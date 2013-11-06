<?php

//To insert each discount for each category for old discounts.

require_once(dirname(__FILE__) . '/config/config.inc.php');
require_once(dirname(__FILE__) . '/init.php');

$startDate = Tools::getValue('from');
$toDate = Tools::getValue('to');

$sql = 'SELECT c.id_category
	    FROM `' . _DB_PREFIX_ . 'category` c';
$categories = DB::getInstance()->ExecuteS($sql);

$discountSql = 'SELECT d.id_discount
	            FROM `' . _DB_PREFIX_ . 'discount` d
				WHERE d.active = 1 AND (d.date_add BETWEEN '.$startDate.' AND '.$toDate.') AND (d.date_upd BETWEEN '.$startDate.' AND '.$toDate.')';

$discounts = DB::getInstance()->ExecuteS($discountSql);

$insertSql = 'INSERT INTO `' . _DB_PREFIX_ . 'discount_category` (id_category,id_discount) VALUES';

foreach ($discounts AS $discount) {
	foreach ($categories AS $category) {
		$sql = 'SELECT *
	            FROM `' . _DB_PREFIX_ . 'discount_category` cd
				WHERE cd.id_category = ' . $category['id_category'] . ' AND cd.id_discount = ' . $discount['id_discount'];

		$recordExists = DB::getInstance()->getRow($sql);

		if (empty($recordExists)) {
			$insertSql .= '(' . $category['id_category'] . ',' . $discount['id_discount'] . ')' . ",";
		}
	}
}
$insertSql = rtrim($insertSql, ',');

if (DB::getInstance()->Execute($insertSql)) {
	echo "The category discount table is updated.";
} else {
	echo "Error while inserting.";
}


?>
