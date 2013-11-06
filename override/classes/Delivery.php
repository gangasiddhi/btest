<?php
/**
 * Description of Delivery
 *
 * @author gangadhar
 */
class Delivery extends DeliveryCore
{
	public function getShippingChargeForCashOnDelivery($customerId, $langId){
		$customer = new Customer(intval($customerId));
		$groups = Group::getGroups($langId);
		
		/*Extra Shipping fee for cash on delivery is adding to the cart total*/
		$shippingChargeForCashOnDelivery = (float) Configuration::get('COD_EXTRA_SHIPPING_CHARGE');
		foreach($groups AS $group) {
			if(strpos($group['name'], 'ExtraShippingCharge5TL') !== false ){
				$group_id_new_product_page = $group['id_group'];
				if( $customer->isMemberOfGroup($group_id_new_product_page)) {
					$shippingChargeForCashOnDelivery = (float) Configuration::get('COD_EXTRA_SHIPPING_CHARGE_5TL');
				}
			}else if(strpos($group['name'], 'ExtraShippingCharge3TL') !== false ){
				$group_id_new_product_page = $group['id_group'];
				if( $customer->isMemberOfGroup($group_id_new_product_page)) {
					$shippingChargeForCashOnDelivery = (float) Configuration::get('COD_EXTRA_SHIPPING_CHARGE_3TL');
				}
			}
		}
		
		return $shippingChargeForCashOnDelivery;
	}
}

?>
