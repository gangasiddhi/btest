<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Cart extends CartCore
{

	public function getProductPriceToApplyCredit($cartProducts)
	{
		$cart_products = $cartProducts;
		$cart_prices = array();
		foreach ($cart_products as $cart_product) {
			$cart_prices[] = $cart_product['price_wt'];
		}

		sort($cart_prices, SORT_NUMERIC);
		$index = sizeof($cart_prices);
		$price = 0;

		for ($i = $index - 1; $i >= 0; $i--) {
			if ($cart_prices[$i] <= (int)Configuration::get('BU_CREDIT_DISCOUNT_LIMIT')) {
				$price = $cart_prices[$i];
				return $price;
			} else {
				continue;
			}
		}

		return $price;
	}

}

?>
