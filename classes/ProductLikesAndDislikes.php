<?php

/**
 * Description of CustomerLikesDislikes
 *
 * @author gangadhar
 */
class ProductLikesAndDislikes extends MongoObjectModel
{	
	/* @var Integer, Customer Id */
	public $id_product;

	/* @var , Number of Likes */
	public $likes;

	/* @var ,Number of Dislikes */
	public $dislikes;
	
	protected $collections = array('product_likes_and_dislikes');
	protected $fieldsRequired = array('id_product');
	
	
	protected 	$collection = 'product_likes_and_dislikes';
	protected 	$identifier = 'id_product';

	public function getFields()
	{
		parent::validateFields();
		$fields['id_product'] = (int)($this->id_product);
		$fields['likes'] = $this->likes;
		$fields['dislikes'] = $this->dislikes;
		
		return $fields;
	}
	
	public function checkProductRecordExists($productId) {
		$productQuery = array('id_product' => (int)$productId);
		$result = MongoDbApiCore::getInstance()->getRow(_DB_PREFIX_.'product_likes_and_dislikes', $productQuery);
		return $result;
	}	

}

?>
