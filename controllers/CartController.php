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
 *  @version  Release: $Revision: 7541 $
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

class CartControllerCore extends FrontController {

    public $php_self = 'cart.php';

    // This is not a public page, so the canonical redirection is disabled
    public function canonicalRedirection() {

    }

    public function run() {
        $this->init();
        $this->preProcess();

        if (Tools::getValue('ajax') == 'true') {
            if (Tools::getIsset('summary')) {
                if (Configuration::get('PS_ORDER_PROCESS_TYPE') == 1) {
                    if (self::$cookie->id_customer) {
                        $customer = new Customer((int) (self::$cookie->id_customer));
                        $groups = $customer->getGroups();
                    }
                    else
                        $groups = array(1);
                    if ((int) self::$cart->id_address_delivery)
                        $deliveryAddress = new Address((int) self::$cart->id_address_delivery);
                    $result = array('carriers' => Carrier::getCarriersForOrder((int) Country::getIdZone((isset($deliveryAddress) AND (int) $deliveryAddress->id) ? (int) $deliveryAddress->id_country : (int) Configuration::get('PS_COUNTRY_DEFAULT')), $groups));
                }
                //sleep(10);
                $result['summary'] = self::$cart->getSummaryDetails();
                $result['customizedDatas'] = Product::getAllCustomizedDatas((int) (self::$cart->id));
                $result['HOOK_SHOPPING_CART'] = Module::hookExec('shoppingCart', $result['summary']);
                $result['HOOK_SHOPPING_CART_EXTRA'] = Module::hookExec('shoppingCartExtra', $result['summary']);
                die(Tools::jsonEncode($result));
            }
            else
                $this->includeCartModule();
        }
        else {
            $this->setMedia();
            $this->displayHeader();
            $this->process();
            $this->displayContent();
            $this->displayFooter();
        }
    }

    public function includeCartModule() {
        require_once(_PS_MODULE_DIR_ . '/blockcart/blockcart-ajax.php');
    }

    public function preProcess() {
        parent::preProcess();
        $test = false;
        $add = Tools::getIsset('add') ? 1 : 0;
        $delete = Tools::getIsset('delete') ? 1 : 0;

        $orderTotal = self::$cart->getOrderTotal(true, Cart::ONLY_PRODUCTS);
        if (Tools::getValue('ajax') == 'true' AND $delete == 1 AND Cart::getNbProducts((int) (self::$cart->id)) == 1) {
            $test = false;
        } elseif (Tools::getValue('ajax') == 'true' AND $delete == 1 AND Cart::getNbProducts((int) (self::$cart->id)) > 1)
            $test = true;


        if ($test == false) {
            $this->cartDiscounts = self::$cart->getDiscounts();
            foreach ($this->cartDiscounts AS $k => $this->cartDiscount)
                if ($error = self::$cart->checkDiscountValidity(new Discount((int) ($this->cartDiscount['id_discount'])), $this->cartDiscounts, $orderTotal, self::$cart->getProducts()))
                    self::$cart->deleteDiscount((int) ($this->cartDiscount['id_discount']));
        }

        if (Configuration::get('PS_TOKEN_ENABLE') == 1
                && strcasecmp(Tools::getToken(false), strval(Tools::getValue('token')))
                && self::$cookie->isLogged() === true)
            $this->errors[] = Tools::displayError('Invalid token');

        // Update the cart ONLY if $this->cookies are available, in order to avoid ghost carts created by bots
        if (($add OR Tools::getIsset('update') OR $delete) AND isset($_COOKIE[self::$cookie->getName()])) {
            //get the values
            $idProduct = (int) (Tools::getValue('id_product', NULL));
            $idProductAttribute = (int) (Tools::getValue('id_product_attribute', Tools::getValue('ipa')));

            /* Get the accesorised Products */
            $accessoryProductId = (int) (Tools::getValue('accessoryProductId', NULL));
            $accessoryProductAttributeId = (int) (Tools::getValue('accessoryProductAttributeId', NULL));
            $products = array();
            $i = 0;
            $products[$i] = array('id_product' => $idProduct, 'idProductAttribute' => $idProductAttribute);

            if ($accessoryProductId) {
                $i++;
                $products[$i] = array('id_product' => $accessoryProductId, 'idProductAttribute' => $accessoryProductAttributeId);
            }

            $color_shoe_combination = (int) (Tools::getValue('color_shoe_combination', 0));
            $customizationId = (int) (Tools::getValue('id_customization', 0));
            $qty = (int) (abs(Tools::getValue('qty', 1)));

            if ($qty == 0)
                $this->errors[] = Tools::displayError('Null quantity');
            elseif (!$idProductAttribute && $color_shoe_combination == 1)
                $this->errors[] = Tools::displayError('Select the shoe size');
            elseif (!$idProduct)
                $this->errors[] = Tools::displayError('Product not found');
            else {
                foreach ($products as $product) {
                    $idProduct = (int) $product['id_product'];
                    $idProductAttribute = (int) $product['idProductAttribute'];

                    $producToAdd = new Product((int) ($idProduct), true, (int) (self::$cookie->id_lang));

                    if ((!$producToAdd->id OR !$producToAdd->active) AND !$delete)
                        if (Tools::getValue('ajax') == 'true')
                            die('{"hasError" : true, "errors" : ["' . Tools::displayError('Product is no longer available.', false) . '"]}');
                        else
                            $this->errors[] = Tools::displayError('Product is no longer available.', false);
                    else {
                        /* Check the quantity availability */
                        if ($idProductAttribute AND is_numeric($idProductAttribute)) {
                            if (!$delete /*AND !$producToAdd->isAvailableWhenOutOfStock($producToAdd->out_of_stock)*/ AND !Attribute::checkAttributeQty((int) $idProductAttribute, (int) $qty))
                                if (Tools::getValue('ajax') == 'true')
                                    die('{"hasError" : true, "errors" : ["' . Tools::displayError('There is not enough product in stock.', false) . '"]}');
                                else
                                    $this->errors[] = Tools::displayError('There is not enough product in stock.');
                        }
                        elseif ($producToAdd->hasAttributes() AND !$delete) {
                            $idProductAttribute = Product::getDefaultAttribute((int) $producToAdd->id, (int) $producToAdd->out_of_stock == 2 ? !(int) Configuration::get('PS_ORDER_OUT_OF_STOCK') : !(int) $producToAdd->out_of_stock);
                            if (!$idProductAttribute)
                                Tools::redirectAdmin($link->getProductLink($producToAdd));
                            elseif (!$delete /*AND !$producToAdd->isAvailableWhenOutOfStock($producToAdd->out_of_stock)*/ AND !Attribute::checkAttributeQty((int) $idProductAttribute, (int) $qty))
                                if (Tools::getValue('ajax') == 'true')
                                    die('{"hasError" : true, "errors" : ["' . Tools::displayError('There is not enough product in stock.', false) . '"]}');
                                else
                                    $this->errors[] = Tools::displayError('There is not enough product in stock.');
                        }
                        elseif (!$delete AND !$producToAdd->checkQty((int) $qty))
                            if (Tools::getValue('ajax') == 'true')
                                die('{"hasError" : true, "errors" : ["' . Tools::displayError('There is not enough product in stock.') . '"]}');
                            else
                                $this->errors[] = Tools::displayError('There is not enough product in stock.');
                        /* Check vouchers compatibility */
                        if ($add AND (($producToAdd->specificPrice AND (float) ($producToAdd->specificPrice['reduction'])) OR $producToAdd->on_sale)) {
                            $discounts = self::$cart->getDiscounts();
                            $hasUndiscountedProduct = null;
                            foreach ($discounts as $discount) {
                                if (is_null($hasUndiscountedProduct)) {
                                    $hasUndiscountedProduct = false;
                                    foreach (self::$cart->getProducts() as $product)
                                        if ($product['reduction_applies'] === false) {
                                            $hasUndiscountedProduct = true;
                                            break;
                                        }
                                }
                                if (!$discount['cumulable_reduction'] && ($discount['id_discount_type'] != 1 || !$hasUndiscountedProduct))
                                    if (Tools::getValue('ajax') == 'true')
                                        die('{"hasError" : true, "errors" : ["' . Tools::displayError('Cannot add this product because current voucher does not allow additional discounts.') . '"]}');
                                    else
                                        $this->errors[] = Tools::displayError('Cannot add this product because current voucher does not allow additional discounts.');
                            }
                        }
                        if (!sizeof($this->errors)) {
                            if ($add AND $qty >= 0) {
                                /* Product addition to the cart */
                                if (!isset(self::$cart->id) OR !self::$cart->id) {
                                    self::$cart->add();
                                    if (self::$cart->id)
                                        self::$cookie->id_cart = (int) (self::$cart->id);
                                }
                                if ($add AND !$producToAdd->hasAllRequiredCustomizableFields() AND !$customizationId)
                                    $this->errors[] = Tools::displayError('Please fill in all required fields, then save the customization.');
                                if (!sizeof($this->errors)) {
                                    $updateQuantity = self::$cart->updateQty((int) ($qty), (int) ($idProduct), (int) ($idProductAttribute), $customizationId, Tools::getValue('op', 'up'));

                                    /* Discount For Holidays */
                                    if (Configuration::get('PS_BUY1_GET1_FREE_VOUCHERS') == 1) {
											$errors = array();
	//									$cart_discount = false;
											if ($cart_discounts = self::$cart->getDiscounts()) {
												foreach ($cart_discounts AS $discount) {
													if ($discount['id_discount_type'] == _PS_BUY1_GET1_FREE_TYPE_) {
														self::$cart->updateDiscountValueInCart($discount['id_discount']);
														//												$cart_discount = true;
													}
												}
											} elseif (self::$cart->nbProducts() > 1) {
												if ($discounts = Discount::getDiscountIdsByType((int) (self::$cookie->id_lang), (int) (self::$cookie->id_customer), _PS_BUY1_GET1_FREE_TYPE_, true, false, true)) {
													foreach ($discounts AS $discount) {
														$discountObj = new Discount(intval($discount['id_discount']));
														if (is_object($discountObj) AND $discountObj) {
															if ($tmpError = self::$cart->checkDiscountValidity($discountObj, self::$cart->getDiscounts(), self::$cart->getOrderTotal(), self::$cart->getProducts(), true))
																$errors[] = $tmpError;
														}
													}
													if (!sizeof($errors)) {
														if ((self::$cart->updateDiscountValueInCart($discountObj->id)) /* && (self::$cart->getDiscountsCustomer($discount->id) <= 0) */)
															self::$cart->addDiscount((int) ($discountObj->id));
														elseif (Tools::getValue('ajax') == 'true')
															die('{"hasError" : true, "errors" : ["' . Tools::displayError('Cannot add discount to the cart.') . '"]}');
														else
															$this->errors[] = Tools::displayError('Cannot add discount to the cart.');
													}
												}
												else {
													$price = self::$cart->updateDiscountValueInCart(0, true);
													$discount = Discount::createPeriodicalDiscounts($price, _PS_BUY1_GET1_FREE_TYPE_, '2 AL 1 ÖDE KAMPANYASI', (int) (self::$cookie->id_customer));
													if (self::$cart->getDiscountsCustomer($discount->id) <= 0)
														self::$cart->addDiscount((int) ($discount->id));
												}
											}
                                    }//end of buy1get1free
                                    /* Discount For Holidays */
                                    if ($updateQuantity < 0) {
                                        /* if product has attribute, minimal quantity is set with minimal quantity of attribute */
                                        if ((int) $idProductAttribute)
                                            $minimal_quantity = Attribute::getAttributeMinimalQty((int) $idProductAttribute);
                                        else
                                            $minimal_quantity = $producToAdd->minimal_quantity;
                                        if (Tools::getValue('ajax') == 'true')
                                            die('{"hasError" : true, "errors" : ["' . Tools::displayError('You must add', false) . ' ' . $minimal_quantity . ' ' . Tools::displayError('Minimum quantity', false) . '"]}');
                                        else
                                            $this->errors[] = Tools::displayError('You must add') . ' ' . $minimal_quantity . ' ' . Tools::displayError('Minimum quantity')
                                                    . ((isset($_SERVER['HTTP_REFERER']) AND basename($_SERVER['HTTP_REFERER']) == 'order.php' OR (!Tools::isSubmit('ajax') AND substr(basename($_SERVER['REQUEST_URI']), 0, strlen('cart.php')) == 'cart.php')) ? ('<script language="javascript">setTimeout("history.back()",5000);</script><br />- ' .
                                                            Tools::displayError('You will be redirected to your cart in a few seconds.')) : '');
                                    }
                                    elseif (!$updateQuantity) {
                                        if (Tools::getValue('ajax') == 'true')
                                            die('{"hasError" : true, "errors" : ["' . Tools::displayError('You already have the maximum quantity available for this product.', false) . '"]}');
                                        else
                                            $this->errors[] = Tools::displayError('You already have the maximum quantity available for this product.')
                                                    . ((isset($_SERVER['HTTP_REFERER']) AND basename($_SERVER['HTTP_REFERER']) == 'order.php' OR (!Tools::isSubmit('ajax') AND substr(basename($_SERVER['REQUEST_URI']), 0, strlen('cart.php')) == 'cart.php')) ? ('<script language="javascript">setTimeout("history.back()",5000);</script><br />- ' .
                                                            Tools::displayError('You will be redirected to your cart in a few seconds.')) : '');
                                    }
                                }
                            }
                            elseif ($delete) {
                                // get product default category name to understand whether it's an
                                // accessoriesed product or not..
                                $targetCategories = array('accessoriesedproducts', 'productaccessories');
                                $category = new Category((int) $producToAdd->id_category_default);
                                $categoryName = $category->getName(self::$cookie->id_lang);

                                if (in_array(strtolower($categoryName), $targetCategories)) {
                                    // product is either a accessoriesed product or a product accessory
                                    // so deleting all kinds of those from cart..
                                    $cartProducts = self::$cart->getProducts();

                                    // delete all products except for the deleted product itself because
                                    // it's going to be deleted within the next block
                                    foreach ($cartProducts as $product) {
                                        $prodCategory = new Category((int) $product['id_category_default']);
                                        $prodCategoryName = $prodCategory->getName(self::$cookie->id_lang);

                                        if ($product['reference'] != $producToAdd->reference AND in_array(strtolower($prodCategoryName), $targetCategories)) {
                                            self::$cart->deleteProduct($product['id_product'], $product['id_product_attribute'], $product['id_customization']);
                                        }
                                    }
                                }

                                if (self::$cart->deleteProduct((int) ($idProduct), (int) ($idProductAttribute), (int) ($customizationId)))
                                    if (!Cart::getNbProducts((int) (self::$cart->id))) {
                                        self::$cart->id_carrier = 0;
                                        self::$cart->gift = 0;
                                        self::$cart->gift_message = '';
                                        self::$cart->update();
                                    } else {
                                        //Application of credit.Applying the highest priced item as discount value
                                        if ($discounts = self::$cart->getDiscounts()) {
                                            foreach ($discounts AS $discount) {
                                                /* Discount For Holidays */
                                                if (Configuration::get('PS_BUY1_GET1_FREE_VOUCHERS') == 1) {
                                                    if ($discount['id_discount_type'] == _PS_BUY1_GET1_FREE_TYPE_) {
                                                        if (!self::$cart->updateDiscountValueInCart($discount['id_discount']))
                                                            if (Tools::getValue('ajax') == 'true')
                                                                die('{"hasError" : true, "errors" : ["' . Tools::displayError('Cannot add discount to the cart.') . '"]}');
                                                            else
                                                                $this->errors[] = Tools::displayError('Cannot add discount to the cart');
                                                    }
                                                }
                                                /* Discount For Holidays */
                                                elseif ($discount['id_discount_type'] == _PS_OS_CREDIT_ID_TYPE_) {
                                                    $creditPrice = Cart::getProductPriceToApplyCredit(self::$cart->getProducts());
													if($creditPrice > 0){
														$credit_discount = new Discount($discount['id_discount']);
														$credit_discount->value = $creditPrice;
														$credit_discount->update();
													}else {
														self::$cart->deleteDiscount((int) ($discount['id_discount']));
													}                                                    
                                                    //Discount::updateDiscountValue(floatval($price),(int)($discount['id_discount']));
                                                }
                                            }
                                        }
                                        //$errorLogFile = @fopen('test.txt', "a");
                                        //fwrite($errorLogFile, 'updated\n');fclose($errorLogFile);
                                    }
                            }
                        }
                        $discounts = self::$cart->getDiscounts();
                        foreach ($discounts AS $discount) {
                            $discountObj = new Discount((int) ($discount['id_discount']), (int) (self::$cookie->id_lang));
                            if ($error = self::$cart->checkDiscountValidity($discountObj, $discounts, self::$cart->getOrderTotal(true, Cart::ONLY_PRODUCTS), self::$cart->getProducts())) {
                                self::$cart->deleteDiscount((int) ($discount['id_discount']));
                                self::$cart->update();
                                $errors[] = $error;
                            }
                        }
                        if (!sizeof($this->errors)) {
                            $queryString = Tools::safeOutput(Tools::getValue('query', NULL));
                            if ($queryString AND !Configuration::get('PS_CART_REDIRECT'))
                                Tools::redirect('search.php?search=' . $queryString);
                            if (isset($_SERVER['HTTP_REFERER'])) {
                                // Redirect to previous page
                                preg_match('!http(s?)://(.*)/(.*)!', $_SERVER['HTTP_REFERER'], $regs);
                                if (isset($regs[3]) AND !Configuration::get('PS_CART_REDIRECT') AND Tools::getValue('ajax') != 'true')
                                    Tools::redirect($_SERVER['HTTP_REFERER']);
                            }
                        }
                    }
                }                
                /* Creating the discount for the Accessoried Products
                  Module::hookExec('createCartDiscount'); */

                if (Tools::getValue('ajax') != 'true' AND !sizeof($this->errors)) {
                    /* $number_of_customer_orders = Order::getCustomerNbOrders((int)(self::$cookie->id_customer));
                      if ($number_of_customer_orders == 0)
                      {
                      $first_cart_discount_name = strval(Configuration::get('PS_FIRST_CART_DISCOUNT_NAME'));
                      if(self::$cart->nbProducts())
                      {
                      $discount = new Discount((int)(Discount::getIdByName($first_cart_discount_name)));
                      if(self::$cart->getDiscountsCustomer($discount->id) <= 0)
                      {
                      self::$cart->addDiscount((int)($discount->id));
                      }
                      if(!CustomerDiscount::customerDiscountExists((int)(self::$cookie->id_customer), 5))
                      {
                      $cartDiscount = new CustomerDiscount();
                      $cartDiscount->id_discount = (int)($discount->id);
                      $cartDiscount->id_discount_type = (int)($discount->id_discount_type);
                      $cartDiscount->id_customer = (int)(self::$cookie->id_customer);
                      if(!$cartDiscount->add())
                      $errors[] = Tools::displayError('cannot add');
                      }
                      }
                      } */
                    if (self::$cart->nbProducts()) {
                        global $cookie;
                        //Applying Credits [TO BE DONE]
                        //Application of credit.Applying the highest priced item as discount value						
						if ($discounts = Discount::getDiscountIdsByType((int) (self::$cookie->id_lang), (int) (self::$cookie->id_customer), _PS_OS_CREDIT_ID_TYPE_, true, false, true)) {
							$creditPrice = Cart::getProductPriceToApplyCredit(self::$cart->getProducts());
							if($creditPrice > 0) {
								foreach ($discounts as $discount) {
									$credit_discount = new Discount(intval($discount['id_discount']));
									if (is_object($credit_discount) AND $credit_discount) {
										if ($tmpError = self::$cart->checkDiscountValidity($credit_discount, self::$cart->getDiscounts(), self::$cart->getOrderTotal(), self::$cart->getProducts(), true))
											$errors[] = $tmpError;
									}
									
									$credit_discount->value = $creditPrice;
									$credit_discount->update();

									//Discount::updateDiscountValue(floatval($price), (int)$credit_discount->id);
									if (!sizeof($errors)) {
										if (self::$cart->getDiscountsCustomer($credit_discount->id) <= 0){						
											self::$cart->addDiscount((int) ($credit_discount->id));
										}
									}

								}
							}
						}                        
                        //Applying Credits

                        /* START Applying the discounts */
                        Module::hookExec('shoppingCartDiscounts');

						/* Check if Accessoriesed Products category is enabled or not */
						$accessoryCategoryDetails = Category::searchByNameAndParentCategoryId((int) (self::$cookie->id_lang), 'AccessoriesedProducts', 1);

						if ($accessoryCategoryDetails['active'] != 0) {
							// Deleting discount of the Accessoried Products 
							Module::hookExec('deleteCartDiscount');
							
							// Creating the discount for the Accessoried Products 
							Module::hookExec('createCartDiscount');

							// Applying discounts for the Accessorised Products
							Module::hookExec('addCartDiscount');
						}
                        $dailydeal_category_enable_chk = Category::searchByNameAndParentCategoryId((int) (self::$cookie->id_lang), 'Dailydeal', 1);
                        if ($dailydeal_category_enable_chk['active'] != 0) {
                            Module::hookExec('addShippingDiscount');
                        }
                    }
                    
                    $step = 1;
                    Tools::redirect('order.php?' . (isset($idProduct) ? 'ipa=' . (int) ($idProduct) : '') . '&step=' . $step);
                }
            }
        }
    }

    public function displayContent() {
        parent::displayContent();
        self::$smarty->display(_PS_THEME_DIR_ . 'errors.tpl');
    }

}
