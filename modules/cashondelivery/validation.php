<?php

/*
 * 2007-2011 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2011 PrestaShop SA
 *  @version  Release: $Revision: 7734 $
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

include(dirname(__FILE__) . '/../../config/config.inc.php');
include(dirname(__FILE__) . '/../../init.php');
include(dirname(__FILE__) . '/cashondelivery.php');

global $cookie, $smarty, $link, $cart, $new_checkout_process;

$cashOnDelivery = new CashOnDelivery();

if ($cart->id_customer == 0 OR $cart->id_address_delivery == 0 OR $cart->id_address_invoice == 0 OR !$cashOnDelivery->active){
    Tools::redirectLink(__PS_BASE_URI__ . 'order.php?step=1');
}
/* Check that this payment option is still available in case the customer changed his address just before the end of
 * the checkout process */
$authorized = false;
foreach (Module::getPaymentModules() as $module){
    if ($module['name'] == 'cashondelivery') {
        $authorized = true;
        break;
    }
}

if (!$authorized){
    die(Tools::displayError('This payment method is not available.'));
}

$customer = new Customer((int) $cart->id_customer);

if (!Validate::isLoadedObject($customer)){
    Tools::redirectLink(__PS_BASE_URI__ . 'order.php?step=1');
}

/* Validating the Order */

if (Tools::getValue('confirm')) {
    if (!$cart->checkQuantities()) {
        Tools::redirect($link->getPageLink('order.php', false) . '?step=1', '');
    }

    $customer = new Customer((int) $cart->id_customer);
    $total = $cart->getOrderTotal(true, Cart::BOTH);

    /*Extra Shipping fee for cash on delivery is adding to the cart total*/
    $deal_category_id=Configuration::get('DAILYDEAL_CATEGORY_ID');
    $cart_products_for_dailydeal_chk = $cart->getProducts();
    $prodIdExistsInCategory=Product::pIdBelongToCategoryId($cart_products_for_dailydeal_chk, $deal_category_id);
    if($prodIdExistsInCategory ==''){
        $total = $total + Delivery::getShippingChargeForCashOnDelivery($cookie->id_customer, $cookie->id_lang);
    }
    
    $cartProducts = $cart->getProducts();
	$orderState = Configuration::get('PS_OS_WAITING_FOR_CUSTOMER');
	foreach($cartProducts as $cartProduct){
		if($cartProduct['out_of_stock'] == 1){
			$orderState = Configuration::get('PS_OS_BACK_ORDER');
			break;
		}
	}

    $cashOnDelivery->validateOrder(
      (int) $cart->id,
      $orderState,
      $total,
      $cashOnDelivery->displayName,
      1,
      0,
      0,
      NULL,
      array(),
      NULL,
      false,
      $customer->secure_key
    );

    Tools::redirectLink(__PS_BASE_URI__ . 'order-confirmation.php?key=' . $customer->secure_key . '&id_cart=' . (int) ($cart->id) . '&id_module=' . (int) $cashOnDelivery->id . '&id_order=' . (int) $cashOnDelivery->currentOrder);

} else {
    /* id_carrier in cart is updated when the user chooses one of the payment options.
      Shipping cost varies depending on payment option cjosen by customer */

    /*$cart->id_carrier = (int) (Tools::getValue('cod_id_carrier'));
    $cart->update();*/
    $free_shipping = Configuration::get('PS_SHIPPING_FREE_PRICE') > 0 ? Configuration::get('PS_SHIPPING_FREE_PRICE') : 0;
    
    $deal_category_id=Configuration::get('DAILYDEAL_CATEGORY_ID');
    $cart_products_for_dailydeal_chk = $cart->getProducts();
    $prodIdExistsInCategory=Product::pIdBelongToCategoryId($cart_products_for_dailydeal_chk, $deal_category_id);
    if($prodIdExistsInCategory ==''){
        $ship_charge = Delivery::getShippingChargeForCashOnDelivery($cookie->id_customer, $cookie->id_lang);
    }else{
        $ship_charge=0;
        $smarty->assign('dailydeal_product',1);

    }
    /*Extra Shipping fee for cash on delivery is adding to the cart total*/
    $smarty->assign(array('total' => $cart->getOrderTotal(true, Cart::BOTH) + $ship_charge,
                          'this_path_ssl' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/cashondelivery/',
                          'is_member' => Customer::memberOfGroup(intval($cart->id_customer)),
                          'free_shipping' => $free_shipping,
                          'order_total_without_shipping' => $cart->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING),
                          'shippingChargeForCashOnDelivery'=> Delivery::getShippingChargeForCashOnDelivery($cookie->id_customer, $cookie->id_lang),
						  'currencySign' => $currency->sign,
						  'this_path' =>  __PS_BASE_URI__ . 'modules/cashondelivery/',
                          'modules_dir' =>  _MODULE_DIR_,
                          'twoStepCheckout' => Configuration::get('TWO_STEP_CHECKOUT')
                    ));
    if(!$new_checkout_process) {
        $template = 'validation.tpl';
    } else {
        $template = 'validation-new-checkout.tpl';
    }

    echo Module::display('cashondelivery', $template);
}

//include(dirname(__FILE__).'/../../footer.php');
