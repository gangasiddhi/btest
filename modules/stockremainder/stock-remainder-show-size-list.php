<?php

require_once(dirname(__FILE__) . '/../../config/config.inc.php');
require_once(dirname(__FILE__) . '/../../init.php');
include_once(dirname(__FILE__) . '/stockremainder.php');

$action = Tools::getValue('action') ? Tools::getValue('action') : 0;

if ($action == 'display') {
	$productId = Tools::getValue('productId') ? Tools::getValue('productId') : 0;
	if($productId){
		$stockremainder = new stockremainder();
		$stockremainder->getProductSizes($productId);
	}
} else if ($action == 'save') {
	global $cookie;
	$productId = Tools::getValue('productId') ? Tools::getValue('productId') : 0;
	$productAttributeId = Tools::getValue('productAttributeId') ? Tools::getValue('productAttributeId') : 0;
	$shoeSize = Tools::getValue('shoeSize') ? Tools::getValue('shoeSize') : 0;

	if ($cookie->logged AND $productId AND $productAttributeId AND $shoeSize) {
		$customerStockRemainderId = CustomerStockRemainder::isCustomerAlreadyChoosen($cookie->id_customer, $productAttributeId);
		if (!$customerStockRemainderId) {
			$customerStockRemainder = new CustomerStockRemainder();
			$customerStockRemainder->id_customer = $cookie->id_customer;
			$customerStockRemainder->id_product = $productId;
			$customerStockRemainder->id_product_attribute = $productAttributeId;
			$customerStockRemainder->shoe_size = $shoeSize;
			$customerStockRemainder->status = 1;
			$customerStockRemainder->add();
		}
	}
}
?>
