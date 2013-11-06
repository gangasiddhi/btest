<?php

/**
 * Description of Order
 *
 * @author gangadhar
 */
class Order extends OrderCore
{
	/* To check whether the cart contains backorder product or not
	 * if the cart contains the backorder product return 1 else return 0.
	 * @param $cart is Cart Id
	 */

	public function isBackOrder()
	{
		$cart = new Cart($this->id_cart);
		$cartProducts = $cart->getProducts();
		foreach ($cartProducts as $cartProduct) {
			if ($cartProduct['out_of_stock'] == 1) {
				return 1;
			}
		}
		return 0;
	}
    
    /*to get customer total revnue*/
    public function getCustomerTotalRevenue($id_customer)
	{
		$sql = 'SELECT sum(`total_paid_real`) as total_revenue FROM `'._DB_PREFIX_.'orders`WHERE `id_customer` = '.(int)$id_customer.' AND `valid`!=0 GROUP BY `id_customer`';
        $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql);
        $total_revenue = '';
        foreach ($res AS $total_paid){
            
            $total_revenue = $total_paid['total_revenue'];
        }        
        return $total_revenue; 
	}

}

?>
