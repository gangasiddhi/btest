<?php

//
include_once(dirname(__FILE__).'/../../config/config.inc.php');
include_once(dirname(__FILE__).'/../../init.php');
		
/*To get the produts related to the category*/
if($_GET['action'] == 'getproducts'){
	$catId = $_GET['catId'];
	$langId = $_GET['langId'];
	if($catId) {
		$category = new Category($catId);
		$products = $category->getproducts($langId,0,1000, 'position', NULL, false, true, false, 1, true, true);
	}	
	
	/*Filter the products, which has quantity 0*/
	$newProducts = array();		
	for($i = 0; $i < count($products); $i++){
		if($products[$i]['quantity'] <= 0){
		}else{
			$newProducts[] = $products[$i];
		}
	}
	
	die(json_encode($newProducts));
}else if($_POST['action'] == 'saveRecommendations'){
	/*To save the product recommend list*/
	$recommendationsArray['recommend'] = $_POST['recommend'];
	$recommendationsArray['recommend_type'] = $_POST['recommendType'];
	$recommendationsArray['category_id'] = $_POST['categoryId'];
	
	$recommendedProducts = explode('|', $_POST['productIdList']);
	$recommendedProductsList = '';
	foreach($recommendedProducts as $recommendedProduct){
		if($recommendedProduct){
			$recommend = explode('_',$recommendedProduct);
			/*$recommend[0] productID, $recommend[1] productAttributeId, $recommend[3] position*/
			$recommendedProductsList .= $recommend[0].'_'.$recommend[1].'_'.$recommend[3].'|';
		}		
	}
	$recommendedProductsList = rtrim($recommendedProductsList, '|');
	$recommendationsArray['product_id_list'] = $recommendedProductsList;
	$product = new Product($_POST['productId']);
	$product->recommendations = json_encode($recommendationsArray);
	
	if($product->update()){
		die(json_encode(array('Saved Sucessfully')));
	}else{
		die(json_encode(array('Error While Saving')));
	}
}

?>