<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
if (!defined('_CAN_LOAD_FILES_'))
    exit;

class ProductFilter extends Module {

    public function __construct() {
        $this->name = 'productfilter';
        $this->tab = 'front office features';
        $this->version = 1.0;
        $this->author = 'PrestaShop';

        parent::__construct();

        $this->displayName = $this->l('Products Filter');
        $this->description = $this->l('This module must be enabled if you want to use Product Filter.');
        
        $this->hookHeader();
    }

    public function install() {
        if (!parent::install()
            OR !$this->registerHook('header')
            OR !$this->registerHook('productFilter'))
            return false;

        return true;
    }
	
	public function uninstall() 
	{
		return (parent::uninstall());
	}
    
    public function hookHeader() {
        /*Tools::addCSS(_THEME_CSS_DIR_.'product-quick.css');*/
        if (strpos($_SERVER['PHP_SELF'], 'category') !== false) {
            Tools::addCSS(_THEME_CSS_DIR_."modules/productfilter/productfilter.css");
            Tools::addJS(array($this->_path . "assets/productfilter.js", 
                                _PS_JS_DIR_ . 'jquery/jquery.lazyloader.js'/*,
                                _THEME_JS_DIR_ . 'quick-view.js'*/));
        }
    }
    
    public function hookProductFilter() {
        global $cookie, $smarty;
        
        /*Get the Shoe sizes and Available colors of the products*/
        $productAttributes = Attribute::getAttributes($cookie->id_lang);
        
        $shoeSizes = array();
        $colors = array();
        
        $customerSelectedShoeSizes = array();
        $customerSelectedColors = array();
        if(isset($_COOKIE['shoeSize'])){
            $customerSelectedShoeSizes = explode(',', $_COOKIE['shoeSize']);
        }/*else{
            $customerSelectedShoeSizes = Customer::getShoeSize($cookie->id_customer);
        }*/
            
        if(isset($_COOKIE['color'])){
            $customerSelectedColors = explode(',', $_COOKIE['color']);
        }
        
        foreach($productAttributes as $attribure){
            if(intval($attribure['id_attribute_group']) === 4 && intval($attribure['is_color_group']) === 0){
                    $shoeSizes[] = $attribure['name'];
            }
            else if(intval($attribure['id_attribute_group']) === 2 && intval($attribure['is_color_group']) === 1){
                    $colors[] = $attribure['name'];
            }
        }
        
        $smarty->assign(array( 'shoeSizes' => $shoeSizes,
                                'colors' => $colors,
                                'customerSelectedShoeSizes' => $customerSelectedShoeSizes,
                                'customerSelectedColors' => $customerSelectedColors
                        ));
        
        return $this->display(__FILE__, 'productfilter.tpl');
    }
    
    public function displayFilterProducts($categoryId,$filterList){
        global $cookie, $smarty;

        $log = false;

        if($categoryId){ 
            $category = new Category($categoryId);
            
            /*Number of products to be displayed per page*/
            $numberOfProductsPerPage = (int)(Configuration::get('PS_PRODUCTS_PER_PAGE'));
                        
            $products = $category->getFilterProducts((int)($cookie->id_lang), 1 , $numberOfProductsPerPage, 'position', 'ASC', false, true, false, 1, true, false, $filterList);
           
            if ($log){
                $myFile = _PS_LOG_DIR_."/productFilterdata.txt";
                $fh = fopen($myFile, 'a') or die("can't open file");
                fwrite($fh,"\nCategory:".print_r($category,true)."\nProducts:".print_r($products,true));
                fclose($fh);
            }
            
            /*Favourite Button*/
            $is_my_fav_active = Configuration::get('PS_MY_FAV_ACTIVE');
            $product_ids = array();
            $ipas = array();
            $favourite_products = array();
            if ($is_my_fav_active == 1) {
                    $smarty->assign('is_my_fav_active', $is_my_fav_active);
                    
                    $favourite_products = Customer::getFavouriteProductsByIdCustomer($cookie->id_customer);
                    if ($favourite_products) {
                            foreach ($favourite_products as $product) {
                                    $product_ids[] = $product['id_product'];
                                    $ipas[] = $product['id_product_attribute'];
                            }

                            $smarty->assign(array(
                                    'my_fav_ids' => $product_ids,
                                    'my_fav_ipa' =>  $ipas
                            ));
                    }
            }
            /*Favourite Button*/
                        
            $smarty->assign(array(
                                'products' => (isset($products) AND $products) ? $products : NULL,
                                'id_category' => (int)($categoryId),
                                'tpl_dir' => _PS_THEME_DIR_,
                                'last_qties'    => intval(Configuration::get('PS_LAST_QTIES')),
                                'prodsmallSize' => Image::getSize('prodsmall'),
                                'img_ps_dir' => _PS_IMG_ ,
                                'add_prod_display' => Configuration::get('PS_ATTRIBUTE_CATEGORY_DISPLAY'),
                                'categorySize' => Image::getSize('category'),
                                'col_img_dir' => _PS_COL_IMG_DIR_,
                                'mediumSize' => Image::getSize('medium'),
                                'thumbSceneSize' => Image::getSize('thumb_scene'),
                                'homeSize' => Image::getSize('home')
                        ));

           return $this->display(__FILE__, 'filter.tpl');
            
        }
    }
    
}
?>
