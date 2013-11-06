<?php
require_once(dirname(__FILE__).'/config/config.inc.php');
require_once(dirname(__FILE__).'/init.php');

$sql = 'SELECT *
		FROM bu_customer_stock_remainder1
		WHERE status = 1';

$results = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql);
$defaultLanguageId = 4;
foreach($results as $result){
	$productAttributeList = $result['product_attribute_ids'];
	$productAttributes = explode('|',$productAttributeList);
	foreach($productAttributes as $productAttribute){
		$shoeSize = Product::getShoeSizeByProductAttributeId($defaultLanguageId,$productAttribute);
		$productId = getProductByProductAttributeId($productAttribute);
		if($shoeSize && $productId){
			if(!CustomerStockRemainder::isCustomerAlreadyChoosen($result['id_customer'], $productAttribute)){	
				$customerStockRemainder = new CustomerStockRemainder();
				$customerStockRemainder->id_customer = $result['id_customer'];
				$customerStockRemainder->id_product = $productId;
				$customerStockRemainder->id_product_attribute = $productAttribute;
				$customerStockRemainder->shoe_size = $shoeSize;
				$customerStockRemainder->status = 1;
				$customerStockRemainder->add();
			}
		}
	}
}

function getProductByProductAttributeId($productAttributeId){
	$sql = 'SELECT id_product
		FROM bu_product_attribute
		WHERE id_product_attribute = '.$productAttributeId;

	$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
	
	return $result['id_product'];
}
?>
