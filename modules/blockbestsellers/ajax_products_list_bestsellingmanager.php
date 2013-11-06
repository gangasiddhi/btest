<?php

include_once(dirname(__FILE__).'/../../config/config.inc.php');

$idLang = Tools::getValue('idLang', false);

//echo $idLang;

$query = Tools::getValue('q', false);
if (!$query OR $query == '' OR strlen($query) < 1)
	die();

$items = Db::s('
	SELECT p.`id_product`, `reference`, pl.name
	FROM `'._DB_PREFIX_.'product` p
	LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (pl.id_product = p.id_product)
	WHERE p.`active` = 1
	AND (pl.name LIKE \'%'.pSQL($query).'%\' OR p.reference LIKE \'%'.pSQL($query).'%\')
	AND pl.id_lang = '.intval($idLang).'
');

if ($items)
	foreach ($items as $item)
		echo $item['name']." - ".$item['id_product'].(!empty($item['reference']) ? ' ('.$item['reference'].')' : '').'|'.intval($item['id_product']).'|'.intval($idLang)."\n";