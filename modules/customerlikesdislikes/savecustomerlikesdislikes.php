<?php

/* To save the customer likes and dislikes */
require_once(dirname(__FILE__) . '/../../config/config.inc.php');
require_once(dirname(__FILE__) . '/../../init.php');
global $cookie;
$action = Tools::getValue('action');
$productId = Tools::getValue('productId');

$customerRecordExists = CustomerLikesAndDislikes::getCustomerRecord((int) $cookie->id_customer);
$productRecordExists = ProductLikesAndDislikes::checkProductRecordExists($productId);

if (!$productRecordExists) {
	$newProductLikeRecord = new ProductLikesAndDislikes();
	$newProductLikeRecord->id_product = $productId;
	$newProductLikeRecord->likes = 0;
	$newProductLikeRecord->dislikes = 0;
	$newProductLikeRecord->add();
	$productRecordExists = new ProductLikesAndDislikes($productId);
} else {
	$productRecordExists = new ProductLikesAndDislikes($productId);
}

switch ($action) {
	//Save the customer like products (product ids), suppose is the like product id in the dislike list, remove from dislike list.
	case 'savelike':
		//Saving the customer likes
		if ($customerRecordExists) {
			$customerRecordExists = new CustomerLikesAndDislikes((int) $cookie->id_customer);
			if ($customerRecordExists->likes[$productId]) {
				unset($customerRecordExists->likes[$productId]);
				$customerRecordExists->update();
				//updating the product record
				if ($productRecordExists->likes > 0) {
					$productRecordExists->likes -= 1;
				}

				$productRecordExists->update(array('id_product' => $productId), $productRecordExists);
			} else {
				$customerRecordExists->likes[$productId] = date('Y-m-d h:i:s', time());
				// To remove from the like list.
				if ($customerRecordExists->dislikes[$productId]) {
					unset($customerRecordExists->dislikes[$productId]);
					$productRecordExists->dislikes -= 1;
				}
				//Ading a new ProductId.
				$customerRecordExists->update();

				//updating the product record.
				$productRecordExists->likes += 1;
				$productRecordExists->update();
			}
		} else {
			$customerRecordExists = new CustomerLikesAndDislikes();
			$customerRecordExists->id_customer = $cookie->id_customer;
			$customerRecordExists->likes = array($productId => date('Y-m-d h:i:s', time()));
			$customerRecordExists->dislikes = array();

			//Adding the new customer record.
			$customerRecordExists->add();

			//updating the product record
			$productRecordExists->likes += 1;
			$productRecordExists->update();
		}

		Module::hookExec('productLiked', array('id_product' => $productId));

		break;

	//Save the customer dislike products (product ids), suppose is the dislike product id in the like list, remove from like list.
	case 'savedislike':
		if ($customerRecordExists) {
			$customerRecordExists = new CustomerLikesAndDislikes((int) $cookie->id_customer);
			if ($customerRecordExists->dislikes[$productId]) {
				unset($customerRecordExists->dislikes[$productId]);
				$customerRecordExists->update();

				//updating the product record
				if ($productRecordExists->dislikes > 0) {
					$productRecordExists->dislikes -= 1;
				}

				$productRecordExists->update();
			} else {
				$customerRecordExists->dislikes[$productId] = date('Y-m-d h:i:s', time());

				//To remove from the like list.
				if ($customerRecordExists->likes[$productId]) {
					unset($customerRecordExists->likes[$productId]);
					$productRecordExists->likes -= 1;
				}

				$customerRecordExists->update();
				//updating the product record
				$productRecordExists->dislikes += 1;
				$productRecordExists->update();
			}
		} else {
			$customerRecordExists = new CustomerLikesAndDislikes();
			$customerRecordExists->id_customer = $cookie->id_customer;
			$customerRecordExists->likes = array();
			$customerRecordExists->dislikes = array($productId => date('Y-m-d h:i:s', time()));

			//Adding the new customer record.
			$customerRecordExists->add();

			//updating the product record
			$productRecordExists->dislikes += 1;
			$productRecordExists->update();
		}

		Module::hookExec('productDisliked', array('id_product' => $productId));

		break;

	default: break;
}
?>
