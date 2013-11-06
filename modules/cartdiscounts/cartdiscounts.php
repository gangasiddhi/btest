<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of cartdiscounts
 *
 * @author gangadhar
 */
class CartDiscounts extends Module {

    public function __construct() {
        $this->name = 'cartdiscounts';
        $this->tab = 'front_office_features';
        $this->version = 0.1;
        $this->author = 'Prestashop';

        parent::__construct();

        $this->displayName = $this->l('Cart Discounts');
        $this->description = $this->l('Applying Discounts to the cart');
    }

    public function install() {
        if (!parent::install() OR
                !$this->registerHook('createCartDiscount') OR
                !$this->registerHook('addCartDiscount') OR
                !$this->registerHook('deleteCartDiscount')) {
            return false;
        }

        return true;
    }
	
	public function uninstall() {
		return (parent::uninstall());
	}

    public function hookCreateCartDiscount($params) {
        $cartId = (int) $params['cart']->id;
        $cart = new Cart($cartId);
        $cartProducts = $cart->getProducts();

        /* Creating Discount for the Accessorised Products at the cart */
        if ($cart->nbProducts() >= 2) {
            $accessorisedProductsCount = 0;
            $productAccessoriesCount = 0;
            $productAccessories = array();
            $i = 0;

            foreach ($cartProducts as $cartProduct) {
                $category = new Category((int) $cartProduct['id_category_default']);
                $categoryName = $category->getName($params['cookie']->id_lang);

                if (strtolower($categoryName) === "accessoriesedproducts") {
                    $accessorisedProductsCount = $accessorisedProductsCount + $cartProduct['quantity'];
                } else if (strtolower($categoryName) === "productaccessories") {
                    $productAccessoriesCount = $productAccessoriesCount + $cartProduct['quantity'];
                    for ($j = $i; $j <= $productAccessoriesCount; $j++) {
                        $productAccessories[$j]['id'] = $cartProduct['id_product'];
                        $productAccessories[$j]['price'] = $cartProduct['price_wt'];
                    }
                    $i++;
                }
            }

            /* Getting the Accessorised Discounts (ACCESSPRODUCT) */
            $numberOfAccessoryDiscounts = 0;
            $accessorisedDiscounts = array();
            $acc = 0;
            $customerDiscounts = Discount::getCustomerDiscounts($params['cookie']->id_lang, $params['cookie']->id_customer, true, true, true);

            foreach ($customerDiscounts as $customerDiscount) {
                if (strtolower(substr($customerDiscount['name'], 0, -2)) === 'accessproduct') {
                    $accessorisedDiscounts[$acc] = $customerDiscount;
                    $acc++;
                }
            }

            $numberOfAccessoryDiscounts = sizeof($accessorisedDiscounts);

            /* Creating the discounts if the cart contains atleast one accessorised product and accessory */
            if ($accessorisedProductsCount >= 1 AND $productAccessoriesCount >= 1) {
                if ($productAccessoriesCount >= $accessorisedProductsCount AND $accessorisedProductsCount > $numberOfAccessoryDiscounts) {
                    for ($j = $numberOfAccessoryDiscounts; $j < $accessorisedProductsCount; $j++) {
                        $this->createCartDiscount($params['cookie']->id_customer, $params['cookie']->id_currency, $this->l('Accessory Product Free Discount'), 2, $productAccessories[$j]['price']);
                    }
                } elseif ($productAccessoriesCount < $accessorisedProductsCount AND $accessorisedProductsCount > $numberOfAccessoryDiscounts) {
                    for ($j = $numberOfAccessoryDiscounts; $j < $productAccessoriesCount; $j++) {
                        $this->createCartDiscount($params['cookie']->id_customer, $params['cookie']->id_currency, $this->l('Accessory Product Free Discount'), 2, $productAccessories[$j]['price']);
                    }
                }
            }
        }

        return true;
    }

    public function hookAddCartDiscount($params) {
        /* Applying Accessories Discount */
        $discounts = Discount::getDiscountIdsByType((int) ($params['cookie']->id_lang), (int) ($params['cookie']->id_customer), 2, true, false, true);

        if ($discounts) {
            foreach ($discounts as $discount) {
                $errors = array();
                $accessoryDiscount = new Discount(intval($discount['id_discount']));

                if (strtolower(substr($accessoryDiscount->name, 0, -2)) === 'accessproduct') {
                    /* checking the validity of the discount. */
                    if (is_object($accessoryDiscount) AND $accessoryDiscount) {
                        if ($tmpError = $params['cart']->checkDiscountValidity($accessoryDiscount, $params['cart']->getDiscounts(), $params['cart']->getOrderTotal(), $params['cart']->getProducts(), true))
                            $errors[] = $tmpError;
                    }

                    /* Add the discounts to the cart. */
                    if (!sizeof($errors)) {
                        if ($params['cart']->getDiscountsCustomer($accessoryDiscount->id) <= 0)
                            $params['cart']->addDiscount((int) ($accessoryDiscount->id));
                    }
                }
            }
        }

        return true;
    }

    /* Deleting the Discount of the Accessorised Products */

    public function hookDeleteCartDiscount($params) {
        /* Get All the customer discounts */
        $customerDiscounts = Discount::getCustomerDiscounts($params['cookie']->id_lang, $params['cookie']->id_customer, true, true, true);

        /* Delete only the ACCESSPRODUCT */
        foreach ($customerDiscounts as $customerDiscount) {
            if (strtolower(substr($customerDiscount['name'], 0, -2)) === 'accessproduct') {
                $accessoryDiscountToDelete = new Discount((int) $customerDiscount['id_discount']);
                $accessoryDiscountToDelete->delete();
            }
        }

        return true;
    }

    /* Creating the Discounts(vouchers)
     *
     * @param int $id_customer Customer ID
     * @param int $currency Currency ID
     * @param String $name Discription of the discount
     * @param int $discount_type Type of the discount
     * @param float $discount_value value of the discount
     */

    function createCartDiscount($id_customer, $currency, $name, $discount_type, $discount_value) {
        $languages = Language::getLanguages();
        $name = $name;
        $discount = new Discount();
        $discount->name = "ACCESSPRODUCT" . Tools::passwdGen(2);
        $discount->id_discount_type = $discount_type;
        $discount->behavior_not_exhausted = 2;
        foreach ($languages as $language)
            $discount->description[$language['id_lang']] = strval($name);
        $discount->id_customer = intval($id_customer);
        $discount->id_currency = intval($currency);
        $discount->value = floatval($discount_value);
        $discount->quantity = 1;
        $discount->quantity_per_user = 1;
        $discount->cumulable = 1;
        $discount->cumulable_reduction = 1;
        $discount->date_from = date('Y-m-d H:i:s', time());
        $discount->date_to = date('Y-m-d H:i:s', time() + 30 * 24 * 3600); // 30 day validity.
        $discount->minimal = 0;
        $discount->active = 1;
        $discount->cart_display = 1;
        $discount->add();
        return true;
    }

}

?>
