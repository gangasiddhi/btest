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

class LookBookControllerCore extends FrontController {
	public $ssl = false;
	public $php_self = 'lookbook.php';

	public function preProcess() {
		parent::preProcess();
        setcookie("bu_bcpath", 'koleksiyon');

		$showSite = self::$cookie->show_site;

		if (!$showSite) {
	        // if user is notlogged redirect to authentication page
			// after signin directly redirect to lookbook page
            if (! self::$cookie->isLogged()) {
				Tools::redirect('authentication.php?back=lookbook.php');
			}

	        // get id of customer if already a user
			if (intval(self::$cookie->id_customer)) {
				// get id if customer is 1st time logged
				$customer = new Customer(intval(self::$cookie->id_customer));
			} else {
				Tools::redirect('authentication.php?back=lookbook.php');
			}

			if (! Validate::isLoadedObject($customer)) {
				// if customer is not validated redirect to authentication page
				Tools::redirect('authentication.php?back=lookbook.php');
			}
		}

		// searching category details by name lkbk
		$category_details = Category::searchByNameAndParentCategoryId((int)(self::$cookie->id_lang), 'LKBK', 1);
		// getting all the details about products based on id
		$category_id = $category_details['id_category'];
		$category = new Category($category_id);
        $this->pagination();
		$nb = (int)($this->n);// limiting the products

		$all_featured_products = $category->getProducts((int)(self::$cookie->id_lang), 1, $nb, 'position', NULL, false, true, false, 1, true, true);

        if (self::$cookie->logged) {
			/* BEGIN -- Lookbook Personalization */
			$lookbook_personalization = 0;
			if (self::$cookie->isLogged() && intval(self::$cookie->id_customer)) {
				$customer = new Customer(intval(self::$cookie->id_customer));
				$groups = Group::getGroups(self::$cookie->id_lang);
				foreach($groups AS $group) {
					if(strpos($group['name'], 'NewCheckout') !== false || strpos($group['name'], 'OldCheckout') !== false){
						$group_id_lookbook_personalization = $group['id_group'];
						if($customer->isMemberOfGroup($group_id_lookbook_personalization)) {
							$lookbook_personalization = 1;
						}
					}
				}
			}
			/*END -- Lookbook Personalization */

			/*Merging of CustomerInterest and Lookbook category products*/
			/*$mergedCustomerInterestAndLookbookProducts = array();
			if (Module::isInstalled('sailthru') && $lookbook_personalization == 1) {
				$customerInterestsProductList = Module::hookExec('sailthruCustomerInterests');
				$customerInterestsProductListArray = explode(',', $customerInterestsProductList);
				$customerInterestProducts = Category::getCustomerInterestProducts(intval(self::$cookie->id_lang),$customerInterestsProductList, 1, 10, 'id_product');

				$i = 0;
				if(count($customerInterestProducts) >= 10) {
					//Delete the products which are already in the $customerInterestsProductListArray
					if(!empty($customerInterestsProductListArray))
					{
						foreach($all_featured_products as $key=>$product)
							if(in_array($product['id_product'], $customerInterestsProductListArray))
									unset($all_featured_products[$key]);
					}

					//To display the first 10 products from sailthru
					foreach($customerInterestProducts as $customerInterestProduct){
						if($i < 10){
							$mergedCustomerInterestAndLookbookProducts[$i] = $customerInterestProduct;
							$i++;
						}
					}
				}

				//Appending the LookBook category products next to the CustomerInterest Products
				foreach($all_featured_products as $all_featured_product){
					$mergedCustomerInterestAndLookbookProducts[$i] = $all_featured_product;
					$i++;
				}

			} else {*/
				$mergedCustomerInterestAndLookbookProducts = $all_featured_products;
			/*}*/

			/*ShowRoom Disappear Start*/
			/*Disapper the price reduced products, if the customer bought that product*/
			if (Configuration::get('PRODUCT_DISAPPEAR_FROM_BOUTIQUE') AND $mergedCustomerInterestAndLookbookProducts) {
				$mergedCustomerInterestAndLookbookProducts = $customer->disappearDiscountedProducts($mergedCustomerInterestAndLookbookProducts);
			}
			/*ShowRoom Disappear End*/

			/*Disappear Disliked Products Start*/
			if(Module::isInstalled('customerlikesdislikes')) {
				$mergedCustomerInterestAndLookbookProducts = CustomerLikesAndDislikes::disappearDislikedProducts(self::$cookie->id_customer,$mergedCustomerInterestAndLookbookProducts);
			}
			/*Disappear Disliked Products End*/

			$all_featured_products = $mergedCustomerInterestAndLookbookProducts;

			/*Favourite Button*/
			$is_my_fav_active = Configuration::get('PS_MY_FAV_ACTIVE');

			if ($is_my_fav_active == 1) {
				self::$smarty->assign('is_my_fav_active', $is_my_fav_active);

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

			/**/
			if(Module::isInstalled('customerlikesdislikes')) {
				self::$smarty->assign('customerLikesDislikesEnable', 1);
				$customerLikesAndDislikesDetails = CustomerLikesAndDislikes::getCustomerRecord(self::$cookie->id_customer);
				self::$smarty->assign(array('customerLikes'=> $customerLikesAndDislikesDetails['likes'],
											'customerDislikes'=> $customerLikesAndDislikesDetails['dislikes']
						));
			}
			/**/
			/*Show the 24 hr showroom message*/
			$now = time();
			$registration_time = strtotime($customer->date_add);
			$waiting_time = $registration_time + (10 * 60 * 60); // Made as 10 hours

			if ($now < $waiting_time) {
				$waiting_room = true;
			} else {
				$waiting_room = false;
			}

            if($customer->date_add >= '2012-09-17 00:00:00' && !$customer->hasCompletedSurvey()) {
                self::$smarty->assign('butigim_pop_up', 0);
            } elseif ($waiting_room === true) {
				session_start();

				if (! isset($_SESSION['butigim_pop_up'])) {
					$_SESSION['butigim_pop_up'] = 1;
					self::$smarty->assign('butigim_pop_up', $_SESSION['butigim_pop_up']);
				} else {
					self::$smarty->assign('butigim_pop_up', 0);
				}
			} else {
				self::$smarty->assign('butigim_pop_up', 0);
			}
			/*Show the 24 hr showroom message*/
		}

		if($showSite AND self::$cookie->logged){
			$customer = new Customer(self::$cookie->id_customer);
			$customer_join_month = substr($customer->date_add, 5, 2);
			$customer_join_year = substr($customer->date_add, 0, 4);
			self::$smarty->assign(array(
				'customer_join_month' => $customer_join_month,
				'customer_join_year' => $customer_join_year
			));
		}
		//product low stock
		$configs = Configuration::getMultiple(array('PS_ORDER_OUT_OF_STOCK', 'PS_LAST_QTIES'));

        /* BEGIN - PriceTestGroup0TL, PriceTestGroupP5TL, PriceTestGroupP10TL, and PriceTestGroupM5TL */
        if (self::$cookie->isLogged() && intval(self::$cookie->id_customer)) {
            $customer = new Customer(intval(self::$cookie->id_customer));
            $groups = Group::getGroups(self::$cookie->id_lang);
            foreach($groups AS $group) {
                if(strpos($group['name'], 'PriceTestGroup0TL') !== false)
                $group_id_price_test_group_0TL = $group['id_group'];
                else if(strpos($group['name'], 'PriceTestGroupP5TL') !== false)
                    $group_id_price_test_group_P5TL = $group['id_group'];
                else if(strpos($group['name'], 'PriceTestGroupP10TL') !== false)
                    $group_id_price_test_group_P10TL = $group['id_group'];
                else if(strpos($group['name'], 'PriceTestGroupM5TL') !== false)
                    $group_id_price_test_group_M5TL = $group['id_group'];
            }

            if( $customer->isMemberOfGroup($group_id_price_test_group_0TL)
                || $customer->isMemberOfGroup($group_id_price_test_group_P5TL)
                || $customer->isMemberOfGroup($group_id_price_test_group_P10TL)
                || $customer->isMemberOfGroup($group_id_price_test_group_M5TL))
                    self::$smarty->assign('price_test_group', 1);
        }
        self::$smarty->assign('test_product_ids', array(1));
        /* END - PriceTestGroup0TL, PriceTestGroupP5TL, PriceTestGroupP10TL, and PriceTestGroupM5TL*/

		self::$smarty->assign(array(
            'category'      => $category,
            'last_qties'    => intval($configs['PS_LAST_QTIES']),
			'prodsmallSize' => Image::getSize('prodsmall'),
			'HOOK_LOOKBOOK_NAV' => Module::hookExec('lookbookhook'),
			'col_img_dir' => _PS_COL_IMG_DIR_,
			'all_featured_products' => $all_featured_products
		));

		self::$smarty->assign('errors', $this->errors);
		Tools::safePostVars();
	}

	public function setMedia() {
		parent::setMedia();

		Tools::addCSS(_THEME_CSS_DIR_.'showroom.css');
        Tools::addCSS(_THEME_CSS_DIR_.'showroom-shoe-size.css');
//		Tools::addCSS(_THEME_CSS_DIR_.'infinite_scroll/style.css');
        Tools::addJS(array(
			_PS_JS_DIR_ . 'jquery/jquery.lazyloader.js',
			_THEME_JS_DIR_ . 'hiw.js',
			_THEME_JS_DIR_ . 'lookbook.js',
			'http://connect.facebook.net/tr_TR/all.js#xfbml=1'
		));
	}

	/*displays content of lookbook.tpl*/
	public function displayContent() {
			parent::displayContent();

			self::$smarty->display(_PS_THEME_DIR_ . 'lookbook.tpl');
	}
}

?>
