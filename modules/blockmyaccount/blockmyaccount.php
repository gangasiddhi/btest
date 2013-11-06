<?php

if (! defined('_CAN_LOAD_FILES_')) {
    exit;
}

class BlockMyAccount extends Module {
    public function __construct() {
        $this->name = 'blockmyaccount';
        $this->tab = 'front_office_features';
        $this->version = '1.2';
        $this->author = 'PrestaShop';
        $this->need_instance = 0;

        parent::__construct();

        $this->displayName = $this->l('My Account block');
        $this->description = $this->l('Displays a block with links relative to user account.');
    }

    public function install() {
        if (! $this->addMyAccountBlockHook() OR
            ! parent::install() OR
            ! $this->registerHook('header') OR
            ! $this->registerHook('showroomNavigation')) {

            return false;
        }

        return true;
    }

    public function uninstall() {
        return (parent::uninstall() AND $this->removeMyAccountBlockHook());
    }

    public function hookShowroomNavigation($params) {
        global $smarty, $cookie, $cart, $page_name, $link, $new_checkout_process;

        if($new_checkout_process == 2)
            return false;

        if (! $cookie->logged && ! $cookie->show_site || ($cookie->isLogged() && isset($cookie->takeSurvey) == 1)) {
            return false;
        }

		/*To remove the menu bar from in the follwing pages if the user is looged out*/
		if(!$cookie->logged && $cookie->show_site && (strpos($_SERVER['PHP_SELF'], 'authentication') !== false || strpos($_SERVER['PHP_SELF'], 'stylists') !== false || strpos($_SERVER['PHP_SELF'], 'stylesurvey') !== false || strpos($_SERVER['PHP_SELF'], 'cms') !== false || strpos($_SERVER['PHP_SELF'], 'faqs') !== false || strpos($_SERVER['PHP_SELF'], 'testimonials') !== false || strpos($_SERVER['PHP_SELF'], 'landing') !== false)){
			return false;
		}
		
        /* Get category type */
        if(Tools::getIsset('shop_by')) {
            $category_details = Category::getCategoryByLinkRewrite(Tools::getValue('shop_by'), (int)($cookie->id_lang));
            $parent_category_id = $category_details['id_parent'] == 1 && $category_details['level_depth'] == 1 ? $category_details['id_category'] :  $category_details['id_parent'];
            $parent_link_rewrite =  Category::getLinkRewrite($parent_category_id, (int)($cookie->id_lang));
            $smarty->assign('shop_by', $parent_link_rewrite);
        }
        /*Get category type*/

        // To check if Cloth, Reduced and Deal categories are enabled or not
        /*$cloth_category = Category::searchByNameAndParentCategoryId((int)($cookie->id_lang), 'Cloth', 1);
        $reduced_category = Category::searchByNameAndParentCategoryId((int)($cookie->id_lang), 'DSP', 1);
        $deal_category = Category::searchByNameAndParentCategoryId((int)($cookie->id_lang), 'DEAL', 1);
        $ozgur_category = Category::searchByNameAndParentCategoryId((int)($cookie->id_lang), 'OZGUR', 1);
        $ivana_category = Category::searchByNameAndParentCategoryId((int)($cookie->id_lang), 'IVANA', 1);*/
        $accessoriesedProductsCategory = Category::searchByNameAndParentCategoryId((int)($cookie->id_lang), 'AccessoriesedProducts', 1);

        $category_details_shoe = Category::searchByNameAndParentCategoryId((int)($cookie->id_lang), 'Shoes', 1);
        $category_details_hand = Category::searchByNameAndParentCategoryId((int)($cookie->id_lang), 'Handbags', 1);
        $category_details_sandals = Category::searchByNameAndParentCategoryId((int)($cookie->id_lang), 'Sandals', 1);
        $category_details_jewelry = Category::searchByNameAndParentCategoryId((int)($cookie->id_lang), 'Jewelry', 1);
        $category_details_low_heels = Category::searchByNameAndParentCategoryId((int)($cookie->id_lang), 'LowHeels', 1);
        $category_details_accessories = Category::searchByNameAndParentCategoryId((int)($cookie->id_lang), 'Accessories', 1);
        $category_details_discountOutlet = Category::searchByNameAndParentCategoryId((int)($cookie->id_lang), 'DiscountOutlet', 1);
        $category_details_bridalshoes = Category::searchByNameAndParentCategoryId((int)($cookie->id_lang), 'BridalShoes', 1);

        $smarty->assign(array(
            /*'clothEnabled' => intval($cloth_category['active']),
            'reducedEnabled' => intval($reduced_category['active']),
            'dealEnabled' => intval($deal_category['active']),
            'ozgurEnabled' => intval($ozgur_category['active']),
            'ivanaEnabled' => intval($ivana_category['active']),*/
            'accessoriesedProductsEnabled' => intval($accessoriesedProductsCategory['active']),
            'shoe_enabled' => $category_details_shoe['active'],
            'shoe_link_rewrite' => $category_details_shoe['link_rewrite'],
            'hand_enabled' => $category_details_hand['active'],
            'hand_link_rewrite' => $category_details_hand['link_rewrite'],
            'sandals_enabled' => $category_details_sandals['active'],
            'sandals_link_rewrite' => $category_details_sandals['link_rewrite'],
            'jewelry_enabled' => $category_details_jewelry['active'],
            'jewelry_link_rewrite' => $category_details_jewelry['link_rewrite'],
            'low_heels_enabled' => $category_details_low_heels['active'],
            'low_heels_link_rewrite' => $category_details_low_heels['link_rewrite'],
            'accessories_enabled' => $category_details_accessories['active'],
            'accessories_link_rewrite' => $category_details_accessories['link_rewrite'],
            'bridalshoes_enabled' => $category_details_bridalshoes['active'],
            'bridalshoes_link_rewrite' => $category_details_bridalshoes['link_rewrite'],
            'discount_enabled' => $category_details_discountOutlet['active'],
            'discount_link_rewrite' => $category_details_discountOutlet['link_rewrite']
        ));

        /*Favourite Button*/
        $smarty->assign('is_my_fav_active' , Configuration::get('PS_MY_FAV_ACTIVE'));
        $category = array();

        /* Favourite Button */
        /* For Hovering Menus */
        if (isset($category_details_shoe['id_category'])) {
            $categories = Category::getChildren($category_details_shoe['id_category'], (int)($cookie->id_lang));

            if ($categories) {
                foreach($categories as $cat) {
                    $category[$cat['id_category']]['link'] = $link->getCategoryLink($cat['link_rewrite']);
                    $category[$cat['id_category']]['name'] = $cat['name'];
                    $category[$cat['id_category']]['link_rewrite'] = $cat['link_rewrite'];
                }

                $smarty->assign('categories' , $category);
            }
        }
        /* For Hovering Menus */

        /* Survey Vs Register */
        if ($cookie->logged) {
            $customer = new Customer((int) ($cookie->id_customer));

            if ($customer->date_add >= '2012-09-17 00:00:00') {
                $showSite = $cookie->show_site;
                if (! $customer->hasCompletedSurvey() AND ! $showSite) {
                    $smarty->assign('no_butigim_link', 1);
                }
            }
        }
        /* Survey Vs Register */

       //$deal_customer_id_array=array(624715, 347448, 376323, 501667);
       //$deal_customer_id_array=array(70039, 69426, 69826);
        /*if ($cookie->logged) {
            if (in_array(($cookie->id_customer), $deal_customer_id_array)){*/
                $smarty->assign('daily_deal_link', 1);
           /* }            
        }*/
        
        $smarty->assign(array(
            'cart' => $cart,
            'cart_qties' => $cart->nbProducts(),
            'ajax_allowed' => intval(Configuration::get('PS_BLOCK_CART_AJAX')) == 1 ? true : false,
            'order_page' => $page_name == 'order',
            'voucherAllowed' => intval(Configuration::get('PS_VOUCHERS')),
            'returnAllowed' => intval(Configuration::get('PS_ORDER_RETURN')),
            'HOOK_BLOCK_MY_ACCOUNT' => Module::hookExec('myAccountBlock')
        ));

        return $this->display(__FILE__, $this->name.'.tpl');
    }

    private function addMyAccountBlockHook() {
        return Db::getInstance()->Execute('INSERT INTO `' . _DB_PREFIX_ . 'hook` (`name`, `title`, `description`, `position`) VALUES (\'myAccountBlock\', \'My account block\', \'Display extra informations inside the "my account" block\', 1)');
    }

    private function removeMyAccountBlockHook() {
        return Db::getInstance()->Execute('DELETE FROM `' . _DB_PREFIX_ . 'hook` WHERE `name` = \'myAccountBlock\'');
    }

    function hookHeader($params) {
        global $new_checkout_process;
        if($new_checkout_process == 2)
            return;
        Tools::addCSS(($this->_path) . 'blockmyaccount.css', 'all');
    }
}
