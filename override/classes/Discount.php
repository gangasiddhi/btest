<?php

/**
 * Description of Discount
 *
 * This is overide class for DiscountCore
 * 
 * @author gangadhar
 */
class Discount extends DiscountCore
{
	/* Get the Discount details based on the name of the discount
	 * @param $discountName is String , Name of the discount
	 */

	public function getDiscountIdByname($discountName)
	{
		return Db::getInstance()->getRow('SELECT * FROM `' . _DB_PREFIX_ . 'discount` WHERE `name` = "' . $discountName . '"');
	}
	
	/* To check whether the customer as already used a discount or not based on the discountId and customerId
	 * @param $discountId is int , discount Id,
	 * @param $customerId is int , customer Id,
	 */
	public function checkCustomerAsUsedDiscount($discountId, $customerId){
		$sql = 'SELECT od.`id_order_discount`
				FROM `' . _DB_PREFIX_ . 'orders` o
				LEFT JOIN `' . _DB_PREFIX_ . 'order_discount` od ON (o.`id_order` = od.`id_order`)
				WHERE od.`id_discount` = '. $discountId .' AND o.`id_customer` = '. $customerId;
		
		 $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
		
		 if(empty($result)){
			 return 0;
		 }else{
			 return 1;
		 }
		
	}
	
	/* Get the Discount details based on the name of the discount
	 * @param $discountName is String , Name of the discount
	 */

	public function getDiscountNameById($discountId)
	{
		$result =  Db::getInstance()->getRow('SELECT name FROM `' . _DB_PREFIX_ . 'discount` WHERE `id_discount` = "' . $discountId . '"');
		return $result['name'];
	}

}

?>
