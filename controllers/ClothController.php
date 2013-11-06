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

class ClothControllerCore extends FrontController
{
	public $ssl = false;
	public $php_self = 'cloth.php';

	public function preProcess()
	{
		parent::preProcess();

		// Check if Cloth category is enabled or not
		$category_details = Category::searchByNameAndParentCategoryId((int)(self::$cookie->id_lang), 'Cloth', 1);
		if($category_details['active'] == 0)
			Tools::redirect('404.php');

		$showSite = self::$cookie->show_site;
		
		if (!$showSite) {
			// if user is notlogged redirect to authentication page
			// after signin directly redirect to lookbook page
			if (!self::$cookie->isLogged())
				Tools::redirect('authentication.php?back=cloth.php');
			//get id of customer if already a user
			if (intval(self::$cookie->id_customer))
				$customer = new Customer(intval(self::$cookie->id_customer));
			else
				Tools::redirect('authentication.php?back=cloth.php');

			if (!Validate::isLoadedObject($customer))//if customer is not validated redirect to authentication page
				Tools::redirect('authentication.php?back=cloth.php');
		}
		
		$category_id = $category_details['id_category'];//getting all the details about products based on id
		$category = new Category($category_id);
		$nb = 75;//limiting the products
		$all_cloth_products= $category->getProducts((int)(self::$cookie->id_lang), 1, $nb, 'position',NULL, false,true,false,1,true,true);

		/*ShowRoom Disappear Start*/
		/*Disapper the price reduced products, if the customer bought that product*/
		if (Configuration::get('PRODUCT_DISAPPEAR_FROM_BOUTIQUE') AND $all_cloth_products){
			$all_cloth_products = $customer->disappearDiscountedProducts($all_cloth_products);
		}
		/*ShowRoom Disappear End*/

		/*$customer->combination = 0;
		//unset($product);unset($combination_product);
		//unset($customer_products);unset($comb);
		//unset($result);
		if($all_celebrity_products)
		foreach($all_celebrity_products as $product)
		{
			if($product['product_combination']>0)
			{
				if($product['id_product'] == $product['product_combination'])
					$combination_product[$product['position']][$product['id_product']][] = $product;
			}
			else
			{
				$customer_products[$product['position']][ ]= $product;
			}
		}
		if(!empty($combination_product))
		{
			foreach($combination_product AS  $pos => $product_postion)
			{
				foreach($product_postion AS $combination)
				{
					foreach($combination AS $key => $comb)
					{
						if($comb['default_combination'] == $comb['id_product_attribute'])
						{
							$customer_products[$pos][]=$comb;
						}
					}
				}
			}
			ksort($customer_products);
			foreach($customer_products as $res)
			{
				foreach($res as $res1)
					$result[] = $res1;
			}
			self::$smarty->assign('all_celebrity_products', $result);
		}
		else
		{
			self::$smarty->assign( 'all_celebrity_products', $all_celebrity_products);
		}*/

		self::$smarty->assign( 'all_cloth_products', $all_cloth_products);

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

        // product low stock
		$configs = Configuration::getMultiple(array('PS_ORDER_OUT_OF_STOCK', 'PS_LAST_QTIES'));

		self::$smarty->assign(array(
                        'last_qties'    => intval($configs['PS_LAST_QTIES']),
                        'HOOK_CLOTH_SLIDE_SHOW' => Module::hookExec('clothslideshow'),
                        //		'HOOK_CELEBRITY_NAV' => Module::hookExec('celebrityhook'),
                        'clothsmallSize'		 => Image::getSize('clothsmall'),
                        'col_img_dir' => _PS_COL_IMG_DIR_
		));

		self::$smarty->assign('errors', $this->errors);
		Tools::safePostVars();

	}
	public function setMedia()
	{
		parent::setMedia();
		Tools::addCSS(_THEME_CSS_DIR_.'cloth.css');
		Tools::addJS(array(
			_PS_JS_DIR_ . 'jquery/jquery.lazyloader.js',
			'http://connect.facebook.net/tr_TR/all.js#xfbml=1'
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

	public function displayContent()/*displays content of celebrity.tpl*/
	{

		parent::displayContent();
		self::$smarty->display(_PS_THEME_DIR_.'cloth.tpl');
	}
}

?>