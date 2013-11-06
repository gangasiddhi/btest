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

class ReducedControllerCore extends FrontController
{
	public $ssl = false;
	public $php_self = 'reduced.php';

	public function preProcess()
	{
		parent::preProcess();

		// Check if DSP category is enabled or not
		$category_details = Category::searchByNameAndParentCategoryId((int)(self::$cookie->id_lang), 'DSP', 1);
		if($category_details['active'] == 0)
			Tools::redirect('404.php');

		$showSite = self::$cookie->show_site;

		if (!$showSite) {
			// if user is notlogged redirect to authentication page
			// after signin directly redirect to lookbook page
			if (!self::$cookie->isLogged())
				Tools::redirect('authentication.php?back=reduced.php');
			//get id of customer if already a user
			if (intval(self::$cookie->id_customer))
				$customer = new Customer(intval(self::$cookie->id_customer));
			else
				Tools::redirect('authentication.php?back=reduced.php');

			if (!Validate::isLoadedObject($customer))
				Tools::redirect('authentication.php?back=reduced.php');//if customer is not validated redirect to authentication page
		}

		$category_id = $category_details['id_category'];//getting all the details about products based on id
		$category = new Category($category_id);
		$nb = 50;//(int)(Configuration::get('HOME_FEATURED_NBR'));//limiting the products for 25
		$discount_products = $category->getProducts((int)(self::$cookie->id_lang), 1, $nb, 'position',NULL, false,true,false,1,true,true);

		/*ShowRoom Disappear Start*/
		/*Disapper the price reduced products, if the customer bought that product*/
		if (Configuration::get('PRODUCT_DISAPPEAR_FROM_BOUTIQUE') AND $discount_products){
			$discount_products =$customer->disappearDiscountedProducts($discount_products);
		}
		/*ShowRoom Disappear EnD*/

		self::$smarty->assign( 'discount_products', $discount_products);

		/*Favourite Button*/
			$is_my_fav_active = Configuration::get('PS_MY_FAV_ACTIVE');
			if($is_my_fav_active == 1)
			{
				self::$smarty->assign('is_my_fav_active' , $is_my_fav_active);
				if($favourite_products = Customer::getFavouriteProductsByIdCustomer(self::$cookie->id_customer))
				{
					foreach($favourite_products as $product)
					{
						$product_ids[] = $product['id_product'];
						$ipas[] = $product['id_product_attribute'];
					}
					self::$smarty->assign(array('my_fav_ids' => $product_ids,'my_fav_ipa' =>  $ipas));
				}
			}
		/*Favourite Button*/

        //product low stock
		$configs = Configuration::getMultiple(array('PS_ORDER_OUT_OF_STOCK', 'PS_LAST_QTIES'));

		self::$smarty->assign(array(
            'last_qties'    => intval($configs['PS_LAST_QTIES']),
			'prodsmallSize'		 => Image::getSize('prodsmall'),
			'HOOK_AFTER_MENU' => Module::hookExec('afterMenu'),
//			'HOOK_LOOKBOOK_NAV' => Module::hookExec('lookbookhook'),
			'col_img_dir' => _PS_COL_IMG_DIR_
			));
//		print_r($all_featured_products);

		self::$smarty->assign('errors', $this->errors);
		Tools::safePostVars();

	}
	public function setMedia()
	{
		parent::setMedia();
		Tools::addCSS(_THEME_CSS_DIR_.'showroom.css');
        Tools::addCSS(_THEME_CSS_DIR_.'showroom-shoe-size.css');
		Tools::addCSS(_THEME_CSS_DIR_.'reduced.css');

		Tools::addJS(array(
			_PS_JS_DIR_.'jquery/jquery.lazyloader.js'
			, _THEME_JS_DIR_.'hiw.js'
//			, _THEME_JS_DIR_.'lookbook.js'
			,'http://connect.facebook.net/tr_TR/all.js#xfbml=1'
		));

	}
//	public function process()
//	{
//		parent::process();
//
//		$back = Tools::getValue('back');
//		$key = Tools::safeOutput(Tools::getValue('key'));
//		if (!empty($key))
//			$back .= (strpos($back, '?') !== false ? '&' : '?').'key='.$key;
//		if (!empty($back))
//		{
//			self::$smarty->assign('back', Tools::safeOutput($back));
//			if (strpos($back, 'order.php') !== false)
//			{
//				$countries = Country::getCountries((int)(self::$cookie->id_lang), true);
//				self::$smarty->assign(array(
//					'inOrderProcess' => true,
//					'PS_GUEST_CHECKOUT_ENABLED' => Configuration::get('PS_GUEST_CHECKOUT_ENABLED'),
//					'sl_country' => (int)Tools::getValue('id_country', Configuration::get('PS_COUNTRY_DEFAULT')),
//					'countries' => $countries
//				));
//			}
//		}
//
//	}

	public function displayContent()/*displays content of lookbook.tpl*/
	{

		parent::displayContent();
		self::$smarty->display(_PS_THEME_DIR_.'reduced.tpl');
	}
}

?>
