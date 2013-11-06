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
 *  @version  Release: $Revision: 6594 $
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_CAN_LOAD_FILES_'))
    exit;

require_once(_PS_INTERFACE_DIR_ . 'IPG.php');
require_once(_PS_CLASS_DIR_ . 'PG.php');

class CashOnDelivery extends PG implements IPG {

    public function __construct() {
        $this->name = 'cashondelivery';
        $this->tab = 'payments_gateways';
        $this->version = '0.3';
        $this->author = 'PrestaShop';
        $this->need_instance = 0;

        $this->currencies = false;

        parent::__construct();

        $this->displayName = $this->l('Cash on delivery (COD)');
        $this->description = $this->l('Accept cash on delivery payments');
    }

    public function install() {
        if (!parent::install() OR !$this->registerHook('payment') OR !$this->registerHook('paymentReturn'))
            return false;
        return true;
    }

    public function hookPayment($params) {
        if (!$this->active)
            return;

        global $smarty;

        /* Check if cart has product download */
        foreach ($params['cart']->getProducts() AS $product) {
            $pd = ProductDownload::getIdFromIdProduct((int) ($product['id_product']));
            if ($pd AND Validate::isUnsignedInt($pd))
                return false;
        }

        $smarty->assign(array(
            'this_path' => $this->_path,
            'this_path_ssl' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->name . '/'
        ));
        return $this->display(__FILE__, 'payment.tpl');
    }

    public function hookPaymentReturn($params) {
        global $smarty, $cookie, $cart;

        if (!$this->active)
            return;

        $number_of_customer_orders = Order::getCustomerNbOrders($cookie->id_customer);
        
        $deal_category_id=Configuration::get('DAILYDEAL_CATEGORY_ID');
        $cart_products_for_dailydeal_chk = $cart->getProducts();
        $prodIdExistsInCategory=Product::pIdBelongToCategoryId($cart_products_for_dailydeal_chk, $deal_category_id);
        if($prodIdExistsInCategory ==''){
            $shippingChargeForCashOnDelivery = Delivery::getShippingChargeForCashOnDelivery($cookie->id_customer, $cookie->id_lang);
        }else{
            $shippingChargeForCashOnDelivery='';
        }
        $order_summary = $params['objOrder'];
        $smarty->assign(array('products'=> $order_summary->getProducts(),
                              'order' => $order_summary,
                              'discounts' => $order_summary->getDiscounts(),
                              'first_cart_discount_name' => strval(Configuration::get('PS_FIRST_CART_DISCOUNT_NAME')),
                              'two_page_checkout'=> Configuration::get('TWO_STEP_CHECKOUT'),
                              'cashOnDeliveryExtraShippingFee' => $shippingChargeForCashOnDelivery,
                              'HOOK_ETTIKETT' => Module::hookExec('EttikettOrderConfirmation'),
                              'HOOK_TODAY_DISCOUNT' => Module::hookexec('todayDiscount', array('id_customer' => $order_summary->id_customer, 'id_order' => $order_summary->id))
                            ));
		
        $totalHistoryRevenue=Order::getCustomerTotalRevenue($order_summary->id_customer);
        $smarty->assign('totalRealTimeValue', $totalHistoryRevenue);
        
        return $this->display(__FILE__, 'confirmation.tpl');
    }

    public static function isOrderCancellable(Order $iOrder) {
        if (OrderHistory::isOrderStateExist($iOrder->id, _PS_OS_SHIPPING_)) {
            return false; // Customer pay money. So refund money with remittance etc.
        }

        return true;
    }

    public static function isOrderRefundable(Order $iOrder) {
        return true;
    }

}
