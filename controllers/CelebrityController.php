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
*  @version  Release: $Revision: 7551 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class CelebrityControllerCore extends FrontController {
	public $ssl = false;
	public $php_self = 'celebrity.php';

	public function preProcess() {
		parent::preProcess();

	    // Check if IVANA category is enabled or not
		$category_details = Category::searchByNameAndParentCategoryId((int)(self::$cookie->id_lang), 'IVANA', 1);

		if ($category_details['active'] == 0) {
			Tools::redirect('404.php');
		}

		$showSite = self::$cookie->show_site;
		
		if (!$showSite) {
	        // if user is notlogged redirect to authentication page
			// after signin directly redirect to lookbook page
			if (! self::$cookie->isLogged()) {
				Tools::redirect('authentication.php?back=celebrity.php');
			}

			// get id of customer if already a user
			if (intval(self::$cookie->id_customer)) {
				// get id if customer is 1st time logged
				$customer = new Customer(intval(self::$cookie->id_customer));
			} else {
				Tools::redirect('authentication.php?back=celebrity.php');
			}

			// if customer is not validated redirect to authentication page
			if (! Validate::isLoadedObject($customer)) {
				Tools::redirect('authentication.php?back=celebrity.php');
			}
		}

		// searching category details by name ivana
		$category_details = Category::searchByNameAndParentCategoryId((int)(self::$cookie->id_lang),'IVANA', 1);
		// getting all the details about products based on id
		$category_id = $category_details['id_category'];
		$category = new Category($category_id);
		// limiting the products
		$this->pagination();
		$nb = (int)($this->n);
		$all_celebrity_products = $category->getProducts((int)(self::$cookie->id_lang), 1, $nb, 'position', NULL, false, true, false, 1, true, true);

		if (!$showSite) {
			/*ShowRoom Disappear Start*/
			/*Disapper the price reduced products, if the customer bought that product*/
			if (Configuration::get('PRODUCT_DISAPPEAR_FROM_BOUTIQUE') AND $all_celebrity_products) {
				$all_celebrity_products = $customer->disappearDiscountedProducts($all_celebrity_products);
			}
			/*ShowRoom Disappear End*/

			/*Favourite Button*/
			$is_my_fav_active = Configuration::get('PS_MY_FAV_ACTIVE');

			if ($is_my_fav_active == 1) {
				self::$smarty->assign('is_my_fav_active' , $is_my_fav_active);

				if ($favourite_products = Customer::getFavouriteProductsByIdCustomer(self::$cookie->id_customer)) {
					foreach ($favourite_products as $product) {
						$product_ids[] = $product['id_product'];
						$ipas[] = $product['id_product_attribute'];
					}

					self::$smarty->assign(array(
						'my_fav_ids' => $product_ids,
						'my_fav_ipa' =>  $ipas
					));
				}
			}
			/*Favourite Button*/
		}
         //product low stock
		$configs = Configuration::getMultiple(array('PS_ORDER_OUT_OF_STOCK', 'PS_LAST_QTIES'));

		self::$smarty->assign(array(
                            'last_qties'    => intval($configs['PS_LAST_QTIES']),
                            'HOOK_CELEBRITY_SLIDE_SHOW' => Module::hookExec('celebrityslideShow'),
                            'HOOK_CELEBRITY_NAV' => Module::hookExec('celebrityhook'),
                            'prodsmallSize' => Image::getSize('prodsmall'),
                            'col_img_dir' => _PS_COL_IMG_DIR_,
                            'all_celebrity_products' => $all_celebrity_products,
                            'errors' => $this->errors
		));

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

	/*displays content of celebrity.tpl*/
	public function displayContent() {
		parent::displayContent();

		self::$smarty->display(_PS_THEME_DIR_ . 'celebrity.tpl');
	}
}

?>
