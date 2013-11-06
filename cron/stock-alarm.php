<?php

require_once(dirname(__FILE__) . "/../config/config.inc.php");
require_once(dirname(__FILE__) . "/../init.php");

$cron_user = Tools::getValue('crnu');
$cron_pass = Tools::getValue('crnp');
if (!$cron_user)
	$cron_user = $argv[1];
if (!$cron_pass)
	$cron_pass = $argv[2];
$cron_pass = Tools::encrypt($cron_pass);

if ($cron_user == _CRON_USER_ && $cron_pass == _CRON_PASSWD_) {
	$customerSelectedProducts = CustomerStockRemainder::getCustomersNotRemainded();
	$products = array();
	$i = 0;
	foreach ($customerSelectedProducts as $customerSelectedProduct) {
		$products[$i]['id_product'] = $customerSelectedProduct['id_product'];
		$products[$i]['id_product_attribute'] = $customerSelectedProduct['id_product_attribute'];
		$products[$i]['shoe_size'] = $customerSelectedProduct['shoe_size'];
		$i++;
	}

	$products = array_unique($products, $products['id_product_attribute']);

	/* Sending the Stock-Remainder Email through the SailThru */
	Module::hookExec('sailThruMailSend', array(
		'sailThruEmailTemplate' => 'Stock-Alarm',
		'products' => $products
	));
} else {
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");

	header("Location: ../");
	exit;
}
?>
