<?php

/**
 * Description of AccessoriesedProductsController
 *
 * This Controller is used to show the Accessoriesed Products
 */

class AccessoriesedProductsControllerCore extends FrontController {

    public $ssl = false;
    public $php_self = 'accessoriesed-products.php';

    public function preProcess() {
        parent::preProcess();

        setcookie("bu_bcpath", 'accessories');

        /* Check if Accessoriesed Products category is enabled or not */
        $categoryDetails = Category::searchByNameAndParentCategoryId((int) (self::$cookie->id_lang), 'AccessoriesedProducts', 1);

        if ($categoryDetails['active'] == 0) {
            Tools::redirect('404.php');
        }

        $showSite = self::$cookie->show_site;
		
		if (!$showSite) {
            /* if user is notlogged redirect to authentication page after signin directly redirect to lookbook page*/
            if (!self::$cookie->isLogged()) {
                Tools::redirect('authentication.php?back=accessoriesed-products.php');
            }

            /* get id of customer if already a user*/
            if (intval(self::$cookie->id_customer)) {
                /* get id if customer is 1st time logged */
                $customer = new Customer(intval(self::$cookie->id_customer));
            } else {
                Tools::redirect('authentication.php?back=accessoriesed-products.php');
            }

            /* if customer is not validated redirect to authentication page */
            if (!Validate::isLoadedObject($customer)) {
                Tools::redirect('authentication.php?back=accessoriesed-products.php');
            }
        }

        /* searching category details by name Accessoriesed Products */
        $categoryDetails = Category::searchByNameAndParentCategoryId((int) (self::$cookie->id_lang), 'AccessoriesedProducts', 1);
        $categoryId = $categoryDetails['id_category'];
        $category = new Category($categoryId);
        $this->pagination();
        $nb = (int) ($this->n);/*Number of products per page*/

        $accessoriesedProducts = $category->getProducts((int) (self::$cookie->id_lang), 1, $nb, 'position', NULL, false, true, false, 1, true, true);

        if (!$showSite) {
            /* ShowRoom Disappear Start , Disapper the price reduced products, if the customer bought that product */
           if (Configuration::get('PRODUCT_DISAPPEAR_FROM_BOUTIQUE') AND $accessoriesedProducts) {
                    $accessoriesedProducts = $customer->disappearDiscountedProducts($accessoriesedProducts);
            }

            /* Favourite Button */
            $isMyFavouriteActive = Configuration::get('PS_MY_FAV_ACTIVE');

            if ($isMyFavouriteActive == 1) {

                self::$smarty->assign('is_my_fav_active', $isMyFavouriteActive);

                if ($myFavouriteProducts = Customer::getFavouriteProductsByIdCustomer(self::$cookie->id_customer)) {
                    foreach ($myFavouriteProducts as $product) {
                        $myFavouriteProductIds[] = $product['id_product'];
                        $myFavouriteProductAttributes[] = $product['id_product_attribute'];
                    }

                    self::$smarty->assign(array(
                        'my_fav_ids' => $myFavouriteProductIds,
                        'my_fav_ipa' => $myFavouriteProductAttributes
                    ));

                }
            }
        }

        /*product low stock*/
        $configs = Configuration::getMultiple(array('PS_ORDER_OUT_OF_STOCK', 'PS_LAST_QTIES'));

        self::$smarty->assign(array(
            'last_qties' => intval($configs['PS_LAST_QTIES']),
            'HOOK_ACCESSORIESED_PRODUCT_SLIDE_SHOW' => Module::hookExec('accessoriesedProductSlideShow'),
            'HOOK_CELEBRITY_NAV' => Module::hookExec('celebrityhook'),
            'prodsmallSize' => Image::getSize('prodsmall'),
            'col_img_dir' => _PS_COL_IMG_DIR_,
            'accessoriesedProducts' => $accessoriesedProducts,
            'errors' => $this->errors
        ));

        // utm_source parameter checking for prevent if hash parameters come from another place for another purpose.
        if (Tools::getValue('utm_medium') == 'stilsos' AND Tools::getValue('hash')){
            self::$smarty->assign('stilsos_hash', Tools::getValue('hash'));
        }

        Tools::safePostVars();
    }

    public function setMedia() {
        parent::setMedia();

        Tools::addCSS(_THEME_CSS_DIR_ . 'showroom.css');
        Tools::addCSS(_THEME_CSS_DIR_.'showroom-shoe-size.css');

        Tools::addJS(array(
            _PS_JS_DIR_ . 'jquery/jquery.lazyloader.js',
            'http://connect.facebook.net/tr_TR/all.js#xfbml=1'
        ));
    }

    public function displayContent() {
        parent::displayContent();
        self::$smarty->display(_PS_THEME_DIR_ . 'accessoriesed-products.tpl');
    }

}

?>
