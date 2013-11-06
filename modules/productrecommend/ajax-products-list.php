<?php

include_once(dirname(__FILE__).'/../../config/config.inc.php');
include_once(dirname(__FILE__).'/../../init.php');

$idLang = Tools::getValue('idLang');

$query = Tools::getValue('q', false);
if (!$query OR $query == '' OR strlen($query) < 1)
	die();

$sql = '
	SELECT p.`id_product`, p.`reference`, pl.name, pa.`id_product_attribute`
	FROM `'._DB_PREFIX_.'product` p
	LEFT JOIN  `'._DB_PREFIX_.'product_attribute` pa on (p.id_product = pa.id_product)
	LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (pl.id_product = p.id_product)
	WHERE p.`active` = 1 
	AND (pl.name LIKE \'%'.pSQL($query).'%\' OR p.reference LIKE \'%'.pSQL($query).'%\')
	AND pl.id_lang = '.intval($idLang);

$items =  Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql);

if ($items)
	foreach ($items as $item)
		echo $item['name']./*(!empty($item['id_product_attribute']) ? ' ('.$item['id_product_attribute'].')' : '').*/'|'.intval($item['id_product']).'|'.intval($item['id_product_attribute'])."\n";