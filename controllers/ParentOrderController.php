<?php

/*
 * 2007-2011 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
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
 *  @version  Release: $Revision: 7697 $
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

/* Class FreeOrder to use PaymentModule (abstract class, cannot be instancied) */

class FreeOrder extends PaymentModule {

}

class ParentOrderControllerCore extends FrontController {

    public $ssl = true;
    public $php_self = 'order.php';
    public $nbProducts;

    public function __construct() {
        parent::__construct();

        /* Disable some cache related bugs on the cart/order */
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    }

    public function init() {
        parent::init();
        $this->nbProducts = self::$cart->nbProducts();
    }

    public function preProcess() {
        global $isVirtualCart;

        parent::preProcess();

        // Redirect to the good order process
        if (Configuration::get('PS_ORDER_PROCESS_TYPE') == 0 AND strpos($_SERVER['PHP_SELF'], 'order.php') === false)
            Tools::redirect('order.php');
        if (Configuration::get('PS_ORDER_PROCESS_TYPE') == 1 AND strpos($_SERVER['PHP_SELF'], 'order-opc.php') === false) {
            if (isset($_GET['step']) AND $_GET['step'] == 3)
                Tools::redirect('order-opc.php?isPaymentStep=true');
            Tools::redirect('order-opc.php');
        }

        if (Configuration::get('PS_CATALOG_MODE'))
            $this->errors[] = Tools::displayError('This store has not accepted your new order.');

        if (Tools::isSubmit('submitReorder') AND $id_order = (int) Tools::getValue('id_order')) {
            $oldCart = new Cart(Order::getCartIdStatic((int) $id_order, (int) self::$cookie->id_customer));
            $duplication = $oldCart->duplicate();
            if (!$duplication OR !Validate::isLoadedObject($duplication['cart']))
                $this->errors[] = Tools::displayError('Sorry, we cannot renew your order.');
            elseif (!$duplication['success'])
                $this->errors[] = Tools::displayError('Missing items - we are unable to renew your order');
            else {
                self::$cookie->id_cart = $duplication['cart']->id;
                self::$cookie->write();
                if (Configuration::get('PS_ORDER_PROCESS_TYPE') == 1)
                    Tools::redirect('order-opc.php');
                Tools::redirect('order.php');
            }
        }

        if ($this->nbProducts) {
            if (Tools::isSubmit('submitAddDiscount') AND Tools::getValue('discount_name')) {
                $discountName = preg_replace('/[^A-Za-z0-9_]/', '', Tools::getValue('discount_name'));
                if (!Validate::isDiscountName($discountName))
                    $this->errors[] = Tools::displayError('Voucher name invalid.');
                else {
                    $discount = new Discount((int) (Discount::getIdByName($discountName)));
                    if (Validate::isLoadedObject($discount)) {
                        if ($tmpError = self::$cart->checkDiscountValidity($discount, self::$cart->getDiscounts(), self::$cart->getOrderTotal(), self::$cart->getProducts(), true))
                            $this->errors[] = $tmpError;
                    }
                    else
                        $this->errors[] = Tools::displayError('Voucher name invalid.');
                    if (!sizeof($this->errors)) {
                        self::$cart->addDiscount((int) ($discount->id));
                        Tools::redirect('order-opc.php');
                    }
                }
                self::$smarty->assign(array(
                    'errors' => $this->errors,
                    'discount_name' => Tools::safeOutput($discountName)
                ));
            } elseif (isset($_GET['deleteDiscount']) AND Validate::isUnsignedId($_GET['deleteDiscount'])) {
                self::$cart->deleteDiscount((int) ($_GET['deleteDiscount']));
                Tools::redirect('order-opc.php');
            }

            /* Is there only virtual product in cart */
            if ($isVirtualCart = self::$cart->isVirtualCart())
                $this->_setNoCarrier();
        }

        self::$smarty->assign('back', Tools::safeOutput(Tools::getValue('back')));
    }

    public function setMedia() {
        global $new_checkout_process;
        parent::setMedia();

        // Adding CSS style sheet
        Tools::addCSS(_THEME_CSS_DIR_ . 'order-steps.css');
        if($new_checkout_process) {
            Tools::addCSS(_THEME_CSS_DIR_ . 'cart-summary-new.css');
            Tools::addCSS(_THEME_CSS_DIR_ . 'address-new.css');
        } else {
            Tools::addCSS(_THEME_CSS_DIR_ . 'cart-summary.css');
            Tools::addCSS(_THEME_CSS_DIR_ . 'address.css');
        }
        //Tools::addCSS(_THEME_CSS_DIR_ . 'addresses.css');
        if (Configuration::get('TWO_STEP_CHECKOUT')) {
            if($new_checkout_process) {
                Tools::addCSS(_THEME_CSS_DIR_ . 'singlepage-checkout-new.css');
                Tools::addJS(_THEME_JS_DIR_ . 'twopage-checkout-new.js');
            } else {
                Tools::addCSS(_THEME_CSS_DIR_ . 'singlepage-checkout.css');
                Tools::addJS(_THEME_JS_DIR_ . 'twopage-checkout.js');
            }
        }

        // Adding JS files
        Tools::addJS(_THEME_JS_DIR_ . 'tools.js');
        if ((Configuration::get('PS_ORDER_PROCESS_TYPE') == 0 AND Tools::getValue('step') == 2) OR Configuration::get('PS_ORDER_PROCESS_TYPE') == 1) {
            if ($new_checkout_process) {
                Tools::addJS(_THEME_JS_DIR_ . 'order-address-new.js');
            } else {
                Tools::addJS(_THEME_JS_DIR_ . 'order-address.js');
            }
        }

        if ((int) (Configuration::get('PS_BLOCK_CART_AJAX')) OR Configuration::get('PS_ORDER_PROCESS_TYPE') == 1) {
            if ($new_checkout_process) {
                Tools::addJS(_THEME_JS_DIR_ . 'cart-summary-new.js');
            } else {
                Tools::addJS(_THEME_JS_DIR_ . 'cart-summary.js');
            }

            Tools::addJS(_PS_JS_DIR_ . 'jquery/jquery-typewatch.pack.js');
            Tools::addJS(_PS_JS_DIR_ . 'jquery/jquery.tinycarousel.min.js');
        }
    }

    /**
     * @return boolean
     */
    protected function _checkFreeOrder() {
        if (self::$cart->getOrderTotal() <= 0) {
            $order = new FreeOrder();
            $order->free_order_class = true;
            $order->validateOrder((int) (self::$cart->id), Configuration::get('PS_OS_PAYMENT'), 0, Tools::displayError('Free order', false), 1, 0, 0, null, array(), null, false, self::$cart->secure_key);
            return (int) Order::getOrderByCartId((int) self::$cart->id);
        }
        return false;
    }

    protected function _updateMessage($messageContent) {
        if ($messageContent) {
            if (!Validate::isMessage($messageContent))
                $this->errors[] = Tools::displayError('Invalid message');
            elseif ($oldMessage = Message::getMessageByCartId((int) (self::$cart->id))) {
                $message = new Message((int) ($oldMessage['id_message']));
                $message->message = htmlentities($messageContent, ENT_COMPAT, 'UTF-8');
                $message->update();
            } else {
                $message = new Message();
                $message->message = htmlentities($messageContent, ENT_COMPAT, 'UTF-8');
                $message->id_cart = (int) (self::$cart->id);
                $message->id_customer = (int) (self::$cart->id_customer);
                $message->add();
            }
        } else {
            if ($oldMessage = Message::getMessageByCartId((int) (self::$cart->id))) {
                $message = new Message((int) ($oldMessage['id_message']));
                $message->delete();
            }
        }
        return true;
    }

    protected function _processCarrier() {
        self::$cart->recyclable = (int) (Tools::getValue('recyclable'));
        self::$cart->gift = (int) (Tools::getValue('gift'));
        if ((int) (Tools::getValue('gift'))) {
            if (!Validate::isMessage($_POST['gift_message']))
                $this->errors[] = Tools::displayError('Invalid gift message');
            else
                self::$cart->gift_message = strip_tags($_POST['gift_message']);
        }

        if (isset(self::$cookie->id_customer) AND self::$cookie->id_customer) {
            $address = new Address((int) (self::$cart->id_address_delivery));
            if (!($id_zone = Address::getZoneById($address->id)))
                $this->errors[] = Tools::displayError('No zone match with your address');
        }
        else
            $id_zone = Country::getIdZone((int) Configuration::get('PS_COUNTRY_DEFAULT'));

        if (self::$cart->id_carrier == 0 /* || self::$cart->id_carrier != (int)(Configuration::get('PS_CARRIER_DEFAULT')) */) {
            if (Validate::isInt(Tools::getValue('id_carrier')) AND sizeof(Carrier::checkCarrierZone((int) (Tools::getValue('id_carrier')), (int) ($id_zone))))
                self::$cart->id_carrier = (int) (Tools::getValue('id_carrier'));
            elseif (!self::$cart->isVirtualCart() AND (int) (Tools::getValue('id_carrier')) == 0)
                $this->errors[] = Tools::displayError('Invalid carrier or no carrier selected');
        }

        Module::hookExec('processCarrier', array('cart' => self::$cart));

        return self::$cart->update();
    }

    protected function _assignSummaryInformations() {
        global $currency;

        if (file_exists(_PS_SHIP_IMG_DIR_ . (int) (self::$cart->id_carrier) . '.jpg'))
            self::$smarty->assign('carrierPicture', 1);
        $summary = self::$cart->getSummaryDetails();
        $customizedDatas = Product::getAllCustomizedDatas((int) (self::$cart->id));
        /**
         * Following block is commented out due to comment
         * https://github.com/butigo/butigo/issues/134#issuecomment-14233898
         */

        // /* Get the Product accessories details */
        // $numberOfAccessorisedProducts = 0;
        // $numberOfAccessories = 0;
        // $accessories = array();

        // foreach ($summary['products'] AS $product) {
        //     $category = new Category((int) $product['id_category_default']);
        //     $categoryName = $category->getName(self::$cookie->id_lang);

        //     if (strtolower($categoryName) === "accessoriesedproducts") {
        //         $numberOfAccessorisedProducts = $numberOfAccessorisedProducts + $product['quantity'];
        //         $category_details = Category::searchByNameAndParentCategoryId((int) (self::$cookie->id_lang), 'ProductAccessories', 1);
        //         $category_id = $category_details['id_category'];
        //         $category = new Category($category_id);
        //         $accessories = $category->getProducts((int) (self::$cookie->id_lang), 1, 10, 'position', NULL, false, true, false, 1, true, true);

        //     } elseif (strtolower($categoryName) === "productaccessories") {
        //         $numberOfAccessories = $numberOfAccessories + $product['quantity'];
        //     }
        // }

        // self::$smarty->assign(array(
        //     'Productaccessories' => $accessories,
        //     'numberOfAccessorisedProducts' => $numberOfAccessorisedProducts,
        //     'numberOfAccessories' => $numberOfAccessories
        // ));

        // override customization tax rate with real tax (tax rules)
        foreach ($summary['products'] AS &$productUpdate) {
            $productId = (int) (isset($productUpdate['id_product']) ? $productUpdate['id_product'] : $productUpdate['product_id']);
            $productAttributeId = (int) (isset($productUpdate['id_product_attribute']) ? $productUpdate['id_product_attribute'] : $productUpdate['product_attribute_id']);

            if (isset($customizedDatas[$productId][$productAttributeId]))
                $productUpdate['tax_rate'] = Tax::getProductTaxRate($productId, self::$cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')});
        }

        Product::addCustomizationPrice($summary['products'], $customizedDatas);

        if ($free_ship = Tools::convertPrice((float) (Configuration::get('PS_SHIPPING_FREE_PRICE')), new Currency((int) (self::$cart->id_currency)))) {
            $discounts = self::$cart->getDiscounts();
            $total_free_ship = $free_ship - ($summary['total_products_wt'] + $summary['total_discounts']);
            foreach ($discounts as $discount)
                if ($discount['id_discount_type'] == 3) {
                    $total_free_ship = 0;
                    break;
                }
            self::$smarty->assign('free_ship', $total_free_ship);
        }
        // for compatibility with 1.2 themes
        foreach ($summary['products'] AS $key => $product)
            $summary['products'][$key]['quantity'] = $product['cart_quantity'];

        self::$smarty->assign($summary);
        self::$smarty->assign(array(
            'token_cart' => Tools::getToken(false),
            'isVirtualCart' => self::$cart->isVirtualCart(),
            'productNumber' => self::$cart->nbProducts(),
            'voucherAllowed' => Configuration::get('PS_VOUCHERS'),
            'shippingCost' => self::$cart->getOrderTotal(true, Cart::ONLY_SHIPPING),
            'shippingCostTaxExc' => self::$cart->getOrderTotal(false, Cart::ONLY_SHIPPING),
			'shippingChargeForCashOnDelivery'=> Delivery::getShippingChargeForCashOnDelivery(self::$cookie->id_customer, self::$cookie->id_lang),
            'customizedDatas' => $customizedDatas,
            'CUSTOMIZE_FILE' => _CUSTOMIZE_FILE_,
            'CUSTOMIZE_TEXTFIELD' => _CUSTOMIZE_TEXTFIELD_,
            'lastProductAdded' => self::$cart->getLastProduct(),
            'displayVouchers' => Discount::getVouchersToCartDisplay((int) (self::$cookie->id_lang), (isset(self::$cookie->id_customer) ? (int) (self::$cookie->id_customer) : 0)),
            'currencySign' => $currency->sign,
            'currencyRate' => $currency->conversion_rate,
            'currencyFormat' => $currency->format,
            'currencyBlank' => $currency->blank));
        self::$smarty->assign(array(
            'HOOK_SHOPPING_CART' => Module::hookExec('shoppingCart', $summary),
            'HOOK_SHOPPING_CART_EXTRA' => Module::hookExec('shoppingCartExtra', $summary)
        ));
    }

    protected function _assignAddress() {
        /* if guest checkout disabled and flag is_guest  in cookies is actived */
        if (Configuration::get('PS_GUEST_CHECKOUT_ENABLED') == 0 AND ((int) self::$cookie->is_guest != Configuration::get('PS_GUEST_CHECKOUT_ENABLED'))) {
            self::$cookie->logout();
            Tools::redirect('');
        } elseif (!Customer::getAddressesTotalById((int) (self::$cookie->id_customer)))
            Tools::redirect('shipping.php?back=order.php?step=3');
        $customer = new Customer((int) (self::$cookie->id_customer));
        if (Validate::isLoadedObject($customer)) {
            /* Getting customer addresses */
            $customerAddresses = $customer->getAddresses((int) (self::$cookie->id_lang));

            // Getting a list of formated address fields with associated values
            $formatedAddressFieldsValuesList = array();
            foreach ($customerAddresses as $address) {
                $tmpAddress = new Address($address['id_address']);

                $formatedAddressFieldsValuesList[$address['id_address']]['ordered_fields'] = AddressFormat::getOrderedAddressFields($address['id_country']);

                $formatedAddressFieldsValuesList[$address['id_address']]['formated_fields_values'] = AddressFormat::getFormattedAddressFieldsValues(
                                $tmpAddress, $formatedAddressFieldsValuesList[$address['id_address']]['ordered_fields']);

                unset($tmpAddress);
            }
            self::$smarty->assign(array(
                'addresses' => $customerAddresses,
                'formatedAddressFieldsValuesList' => $formatedAddressFieldsValuesList));

            $deliveryDeleted = new Address(self::$cart->id_address_delivery);
            $InvoiceDeleted = new Address(self::$cart->id_address_invoice);
            /* Setting default addresses for cart */
            if ((!isset(self::$cart->id_address_delivery) OR empty(self::$cart->id_address_delivery)) OR !$deliveryDeleted->id OR $deliveryDeleted->deleted AND sizeof($customerAddresses)) {
                self::$cart->id_address_delivery = (int) ($customerAddresses[0]['id_address']);
                $update = 1;
            }
            if ((!isset(self::$cart->id_address_invoice) OR empty(self::$cart->id_address_invoice)) OR !$InvoiceDeleted->id OR $InvoiceDeleted->deleted AND sizeof($customerAddresses)) {
                self::$cart->id_address_invoice = (int) ($customerAddresses[0]['id_address']);
                $update = 1;
            }
            /* Update cart addresses only if needed */
            if (isset($update) AND $update)
                self::$cart->update();

            /* If delivery address is valid in cart, assign it to Smarty */
            if (isset(self::$cart->id_address_delivery)) {
                $deliveryAddress = new Address((int) (self::$cart->id_address_delivery));
                if (Validate::isLoadedObject($deliveryAddress) AND ($deliveryAddress->id_customer == $customer->id))
                    self::$smarty->assign('delivery', $deliveryAddress);
            }

            /* If invoice address is valid in cart, assign it to Smarty */
            if (isset(self::$cart->id_address_invoice)) {
                $invoiceAddress = new Address((int) (self::$cart->id_address_invoice));
                if (Validate::isLoadedObject($invoiceAddress) AND ($invoiceAddress->id_customer == $customer->id))
                    self::$smarty->assign('invoice', $invoiceAddress);
            }
        }
        if ($oldMessage = Message::getMessageByCartId((int) (self::$cart->id)))
            self::$smarty->assign('oldMessage', $oldMessage['message']);
    }

    protected function _assignCarrier() {
        $customer = new Customer((int) (self::$cookie->id_customer));
        $address = new Address((int) (self::$cart->id_address_delivery));
        $id_zone = Address::getZoneById((int) ($address->id));
        $carriers = Carrier::getCarriersForOrder($id_zone, $customer->getGroups());

        self::$smarty->assign(array(
            'checked' => $this->_setDefaultCarrierSelection($carriers),
            'carriers' => $carriers,
            'default_carrier' => (int) (Configuration::get('PS_CARRIER_DEFAULT'))
        ));
        self::$smarty->assign(array(
            'HOOK_EXTRACARRIER' => Module::hookExec('extraCarrier', array('address' => $address)),
            'HOOK_BEFORECARRIER' => Module::hookExec('beforeCarrier', array('carriers' => $carriers))
        ));
    }

    protected function _assignWrappingAndTOS() {
        // Wrapping fees
        $wrapping_fees = (float) (Configuration::get('PS_GIFT_WRAPPING_PRICE'));
        $wrapping_fees_tax = new Tax((int) (Configuration::get('PS_GIFT_WRAPPING_TAX')));
        $wrapping_fees_tax_inc = $wrapping_fees * (1 + (((float) ($wrapping_fees_tax->rate) / 100)));

        // TOS
        $cms = new CMS((int) (Configuration::get('PS_CONDITIONS_CMS_ID')), (int) (self::$cookie->id_lang));
        $this->link_conditions = self::$link->getCMSLink($cms, $cms->link_rewrite, true);
        if (!strpos($this->link_conditions, '?'))
            $this->link_conditions .= '?content_only=1';
        else
            $this->link_conditions .= '&content_only=1';

        self::$smarty->assign(array(
            'checkedTOS' => (int) (self::$cookie->checkedTOS),
            'recyclablePackAllowed' => (int) (Configuration::get('PS_RECYCLABLE_PACK')),
            'giftAllowed' => (int) (Configuration::get('PS_GIFT_WRAPPING')),
            'cms_id' => (int) (Configuration::get('PS_CONDITIONS_CMS_ID')),
            'conditions' => (int) (Configuration::get('PS_CONDITIONS')),
            'link_conditions' => $this->link_conditions,
            'recyclable' => (int) (self::$cart->recyclable),
            'gift_wrapping_price' => (float) (Configuration::get('PS_GIFT_WRAPPING_PRICE')),
            'total_wrapping_cost' => Tools::convertPrice($wrapping_fees_tax_inc, new Currency((int) (self::$cookie->id_currency))),
            'total_wrapping_tax_exc_cost' => Tools::convertPrice($wrapping_fees, new Currency((int) (self::$cookie->id_currency)))));
    }

    protected function _assignPayment() {
        self::$smarty->assign(array(
            'HOOK_TOP_PAYMENT' => Module::hookExec('paymentTop'),
            'HOOK_PAYMENT' => Module::hookExecPayment()
        ));
    }

    /**
     * Set id_carrier to 0 (no shipping price)
     *
     */
    protected function _setNoCarrier() {
        self::$cart->id_carrier = 0;
        self::$cart->update();
    }

    /**
     * Decides what the default carrier is and update the cart with it
     *
     * @param array $carriers
     * @return number the id of the default carrier
     */
    protected function _setDefaultCarrierSelection($carriers) {
        if (sizeof($carriers)) {
            $defaultCarrierIsPresent = false;
            if ((int) self::$cart->id_carrier != 0)
                foreach ($carriers AS $carrier)
                    if ($carrier['id_carrier'] == (int) self::$cart->id_carrier)
                        $defaultCarrierIsPresent = true;
            if (!$defaultCarrierIsPresent)
                foreach ($carriers AS $carrier)
                    if ($carrier['id_carrier'] == (int) Configuration::get('PS_CARRIER_DEFAULT')) {
                        $defaultCarrierIsPresent = true;
                        self::$cart->id_carrier = (int) $carrier['id_carrier'];
                    }
            if (!$defaultCarrierIsPresent)
                self::$cart->id_carrier = (int) $carriers[0]['id_carrier'];
        }
        else
            self::$cart->id_carrier = 0;
        if (self::$cart->update())
            return self::$cart->id_carrier;
        return 0;
    }

}

