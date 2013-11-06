<?php

/**
 * Description of CustomerLikesDislikes
 *
 * @author gangadhar
 */
class CustomerLikesAndDislikes extends MongoObjectModel
{
	/* @var Integer, Customer Id */

	public $id_customer;

	/* @var Associative array , array('productId' => date('Y-m-d h:i:s'))Customer Likes */
	public $likes;

	/* @var Associative array,  array('productId' => date('Y-m-d h:i:s'))Customer Dislikes */
	public $dislikes;
	protected $collections = array('customer_likes_and_dislikes');
	protected $fieldsRequired = array('id_customer');
	protected $collection = 'customer_likes_and_dislikes';
	protected $identifier = 'id_customer';

	public function getFields()
	{
		parent::validateFields();
		$fields['id_customer'] = (int) ($this->id_customer);
		$fields['likes'] = $this->likes;
		$fields['dislikes'] = $this->dislikes;

		return $fields;
	}

	public function getCustomerRecord($customerId)
	{
		$customerQuery = array('id_customer' => (int) $customerId);
		$result = MongoDbApi::getInstance()->getRow(_DB_PREFIX_ . 'customer_likes_and_dislikes', $customerQuery);
		return $result;
	}

	public function getCustomerLikedProducts($customerId)
	{
		$customerQuery = array('id_customer' => (int) $customerId);
		$result = MongoDbApi::getInstance()->getRow(_DB_PREFIX_ . 'customer_likes_and_dislikes', $customerQuery);
		return $result['likes'];
	}

	public function getCustomerDislikedProducts($customerId)
	{
		$customerQuery = array('id_customer' => (int) $customerId);
		$result = MongoDbApi::getInstance()->getRow(_DB_PREFIX_ . 'customer_likes_and_dislikes', $customerQuery);
		return $result['dislikes'];
	}

	public function disappearDislikedProducts($customerId, $products)
	{
		$customerDislikes = CustomerLikesAndDislikes::getCustomerDislikedProducts($customerId);
		$i = 0;
		foreach ($products AS $product) {
			foreach ($customerDislikes AS $dislikeProductId => $dislikedDate) {
				if ($product['id_product'] == $dislikeProductId) {
					unset($products[$i]);
				}
			}
			$i++;
		}

		return $products;
	}

}

?>
