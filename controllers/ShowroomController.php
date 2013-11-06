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

class ShowroomControllerCore extends FrontController {
	public $ssl = false;
	public $php_self = 'showroom.php';

	public function preProcess() {
		parent::preProcess();

		global $cookie, $hypothesis;

		if (! self::$cookie->isLogged()) {
			Tools::redirect('authentication.php?back=showroom.php');
		}

		if (intval(self::$cookie->id_customer)) {
			$customer = new Customer(intval(self::$cookie->id_customer));
		} else {
			Tools::redirect('authentication.php?back=showroom.php');
		}

		if (! Validate::isLoadedObject($customer)) {
			Tools::redirect('authentication.php?back=showroom.php');
		}

		// if the survey is not completed, redirect the customer to survey
		$gadsSurveyStartDate = strtotime('2012-09-17 00:00:00');
		if (!$customer->hasCompletedSurvey() AND strtotime($customer->date_add) > $gadsSurveyStartDate) {
				Tools::redirect('gads-stylesurvey.php?stp=1');
		}

		// checking the condition if showroom seen is zero update showroom seen to one//
		if ($customer->showroom_seen == 0) {
			$customer->updateShowroomSeen(1);
		}

		// Get products for new customer to view till 24hrs from the date of survey completion
		$styleSurvey = CustomerStyleSurvey::getByCustomerId($customer->id);
        $completion_time = strtotime($styleSurvey['date_add']);
        $waiting_time = $completion_time + (10 * 60 * 60); // Made as 10 hours
        $now = time();

		if ($now < $waiting_time) {
			$waiting_room = true;
		} else {
			$waiting_room = false;
		}

		/* limiting number of products in each category */

                $this->pagination();
                $shoes_limit = (int)($this->n);
		$handbags_limit =(int)($this->n);
		$more_products_limit = 25;
//		$handbags_limit = 75;
//		$jewelry_limit = 75;

		// Contains all products(shoes and handbags) for NanoIntegactive Integration
		$all_products_on_page = array();
        // if it is waiting_room, page link goes to waiting.php
		if ($waiting_room === true) {
			Tools::redirect(self::$link->getPageLink('waiting.php', false), '');
		} else {
			// if customer category_name and age=0 select these default combination of name age and category
			if (($customer->category_name == 0 OR ! ($customer->category_name)) AND $customer->age == 0) {
				$personality_categories = array('YAR', 'FEM', 'MOD', 'KLA', 'TRE');
				$category_key = array_rand( $personality_categories, 1 );
				$customer->id_gender = 9;
				$customer->age = '24-29';
				$customer->category_name =  $personality_categories[$category_key];
				$customer->dress_size = '6-8';
				$customer->ip_registration_newsletter = 'NULL';
				$customer->update();
			} elseif($customer->category_name == "") {
				$personality_categories = array('YAR', 'FEM', 'MOD', 'KLA', 'TRE');
				$category_key = array_rand( $personality_categories, 1 );
				$customer->category_name              =  $personality_categories[$category_key];
				$customer->update();
			}

			// Current Month Category
			$category_month_id = intval( date('n') + 1); //echo $customer_style['category_name'];
			$customer_style = $customer->getCategory();//getting customer category name
			//print_r($customer_style);exit;
			$featured_category_name = 'Featured';
			$discount_category_name = 'Discount';
			$shoes_category_name = 'Shoes';
			$handbags_category_name = 'Handbags';
			$jewelry_category_name = 'Jewelry';
			$more_category_exclusive = array($featured_category_name, $discount_category_name);
			self::$smarty->assign('customer_style_name',$customer_style['category_name']);
			$combination_shoe = array();
			$seperated_customer_shoe =  array();
			$combination_assigned= array();
			$customer->combination = 0;

			// Deal product
			unset($product);unset($combination_product);
			unset($customer_products);unset($comb);
			unset($result);

			$category_details = Category::searchByNameAndParentCategoryId((int)(self::$cookie->id_lang),'DEAL', 1);//searching category details by name lkbk
			$category_id = $category_details['id_category']; //getting all the details about products based on id
			$category = new Category($category_id);
			$nb = 25; //limiting the products for 10 (each color combination is considared as 1 product)
			$deal_products = $category->getProducts((int)(self::$cookie->id_lang), 1, $nb, 'position',NULL, false,true,false,1,true,true);

			/*ShowRoom Disappear Start*/
			/*Disapper the price reduced products, if the customer bought that product*/
			if (Configuration::get('PRODUCT_DISAPPEAR_FROM_BOUTIQUE') AND $deal_products) {
				$deal_products = $customer->disappearDiscountedProducts($deal_products);
			}
			/*ShowRoom Disappear End*/

			if ($deal_products) {
				foreach ($deal_products as $product) {
					if ($product['product_combination'] > 0) {
						if ($product['id_product'] == $product['product_combination']) {
							$combination_product[$product['position']][$product['id_product']][] = $product;
						}
					} else {
						$customer_products[$product['position']][ ]= $product;
					}
				}
			}

			if (! empty($combination_product)) {
				foreach ($combination_product AS  $pos => $product_postion) {
					foreach ($product_postion AS $combination) {
						foreach ($combination AS $key => $comb) {
							if ($comb['default_combination'] == $comb['id_product_attribute']) {
								$customer_products[$pos][]=$comb;
							}
						}
					}
				}

				ksort($customer_products);

				foreach ($customer_products as $res) {
					foreach ($res as $res1) {
						$result[] = $res1;
					}
				}
				self::$smarty->assign('deal_products', $result);
			} else {
				self::$smarty->assign( 'deal_products', $deal_products);
			}

			$compare_email = "destek@butigo.com";

			if (strcmp(self::$cookie->email,$compare_email) == 0) {
				$category_shoes_featured = Category::searchByNameAndParentCategoryId(1, $shoes_category_name, $category_month_id);
				$category_details = Category::getChildren($category_shoes_featured['id_category'], (int)(self::$cookie->id_lang));
				$personality_categories = array('yar', 'fem', 'mod', 'kla', 'tre');
				$customer_shoes = array();

				foreach($category_details as $cat) {
					if (in_array($cat['link_rewrite'],$personality_categories)) {
						$all_style[] = $cat['id_category'];
					}
				}

				foreach($all_style as $category) {
					$child_category = new Category($category);
					$child_products = $child_category->getProducts( intval(self::$cookie->id_lang), 1, $more_products_limit, 'position');

					if ($child_products) {
						$customer_shoes = array_merge($customer_shoes, $child_products);
					}
				}
			} else {
				$category_customer_style = new Category( $customer_style['shoes_cat_id'] );
				$customer_shoes = $category_customer_style->getProducts( intval(self::$cookie->id_lang),1, $shoes_limit, 'position');
			}

			/*Unset the Shoes from Showroom which are moved to Discounts*/
			if (Configuration::get('PRODUCT_DISAPPEAR_FROM_BOUTIQUE') AND $customer_shoes) {
				$customer_shoes = $customer->disappearDiscountedProducts($customer_shoes);
			}

			unset($product);unset($combination_product);
			unset($customer_products);unset($comb);
			unset($result);

			/*if ($customer_shoes) {
				foreach ($customer_shoes as $product) {
					if ($product['product_combination'] > 0) {
						if ($product['id_product'] == $product['product_combination']) {
							$combination_product[$product['position']][$product['id_product']][] = $product;
						}
					} else {
						$customer_products[$product['position']][]= $product;
					}
				}
			}

			if (! empty($combination_product)) {
				foreach ($combination_product AS  $pos => $product_postion) {
					foreach ($product_postion AS $combination) {
						foreach ($combination AS $key => $comb) {
							if ($comb['default_combination'] == $comb['id_product_attribute']) {
								$customer_products[$pos][] = $comb;
							}
						}
					}
				}

				ksort($customer_products);

				foreach ($customer_products as $res) {
					foreach ($res as $res1) {
						$result[] = $res1;
					}
				}

				self::$smarty->assign('customer_shoes', $result);
			} else {*/
				self::$smarty->assign( 'customer_shoes', $customer_shoes);
				if(is_array($customer_shoes))
					$all_products_on_page = array_merge($all_products_on_page, $customer_shoes);
			//}

			$child_category = new Category($customer_style['shoes_seemore_cat_id']);
			$more_shoes_final = $child_category->getProducts( intval(self::$cookie->id_lang), 1, $more_products_limit, 'position');

			/*ShowRoom Disappear Start*/
			/*Disapper the price reduced products, if the customer bought that product*/
			if (Configuration::get('PRODUCT_DISAPPEAR_FROM_BOUTIQUE') AND $more_shoes_final) {
				$more_shoes_final = $customer->disappearDiscountedProducts($more_shoes_final);
			}
			/*ShowRoom Disappear End*/

			unset($product);unset($combination_product);
			unset($customer_products);unset($comb);
			unset($result);

			if ($more_shoes_final) {
				foreach ($more_shoes_final as $product) {
					if ($product['product_combination'] > 0) {
						if ($product['id_product'] == $product['product_combination']) {
							$combination_product[$product['position']][$product['id_product']][] = $product;
						}
					} else {
						$customer_products[$product['position']][ ]= $product;
					}
				}
			}

			if (! empty($combination_product)) {
				foreach ($combination_product AS  $pos => $product_postion) {
					foreach ($product_postion AS $combination) {
						foreach ($combination AS $key => $comb) {
							if ($comb['default_combination'] == $comb['id_product_attribute']) {
								$customer_products[$pos][]=$comb;
							}
						}
					}
				}

				ksort($customer_products);

				foreach ($customer_products as $res) {
					foreach ($res as $res1) {
						$result[] = $res1;
					}
				}

				self::$smarty->assign('more_shoes', $result);
			} else {
				self::$smarty->assign( 'more_shoes', $more_shoes_final);
			}
			// Get Handbags for the month
			$category_handbags = new Category($customer_style['handbags_cat_id']);
			$products_handbags = $category_handbags->getProducts( intval(self::$cookie->id_lang),(int)($this->p), $handbags_limit, 'position');

			/*ShowRoom Disappear Start*/
			/*Disapper the price reduced products, if the customer bought that product*/
			if (Configuration::get('PRODUCT_DISAPPEAR_FROM_BOUTIQUE') AND $products_handbags) {
				$products_handbags = $customer->disappearDiscountedProducts($products_handbags);
			}
			/*ShowRoom Disappear End*/

			unset($product);unset($combination_product);
			unset($customer_products);unset($comb);
			unset($result);

			if ($products_handbags) {
				foreach ($products_handbags as $product) {
					if ($product['product_combination'] > 0) {
						if ($product['id_product'] == $product['product_combination']) {
							$combination_product[$product['position']][$product['id_product']][] = $product;
						}
					} else {
						$customer_products[$product['position']][ ]= $product;
					}
				}
			}

			if (! empty($combination_product)) {
				foreach ($combination_product AS  $pos => $product_postion) {
					foreach ($product_postion AS $combination) {
						foreach ($combination AS $key => $comb) {
							if ($comb['default_combination'] == $comb['id_product_attribute']) {
								$customer_products[$pos][]=$comb;
							}
						}
					}
				}

				ksort($customer_products);

				foreach($customer_products as $res) {
					foreach($res as $res1) {
						$result[] = $res1;
					}
				}

				self::$smarty->assign('products_handbags', $result);
				if(is_array($result))
					$all_products_on_page = array_merge($all_products_on_page, $result);
			} else {
				self::$smarty->assign( 'products_handbags', $products_handbags);
				if(is_array($products_handbags))
					$all_products_on_page = array_merge($all_products_on_page, $products_handbags);
			}

			$prev_category = new Category($customer_style['handbags_seemore_cat_id']);
			$more_handbags_final = $prev_category->getProducts( intval(self::$cookie->id_lang), 1, $more_products_limit, 'position' );

			/*ShowRoom Disappear Start*/
			/*Disapper the price reduced products, if the customer bought that product*/
			if (Configuration::get('PRODUCT_DISAPPEAR_FROM_BOUTIQUE') AND $more_handbags_final){
				$more_handbags_final = $customer->disappearDiscountedProducts($more_handbags_final);
			}
			/*ShowRoom Disappear End*/

			unset($product);unset($combination_product);
			unset($customer_products);unset($comb);
			unset($result);

			if ($more_handbags_final) {
				foreach ($more_handbags_final as $product) {
					if ($product['product_combination'] > 0) {
						if ($product['id_product'] == $product['product_combination']) {
							$combination_product[$product['position']][$product['id_product']][] = $product;
						}
					} else {
						$customer_products[$product['position']][ ]= $product;
					}
				}
			}

			if (! empty($combination_product)) {
				foreach ($combination_product AS  $pos => $product_postion) {
					foreach ($product_postion AS $combination) {
						foreach ($combination AS $key => $comb) {
							if ($comb['default_combination'] == $comb['id_product_attribute']) {
								$customer_products[$pos][]=$comb;
							}
						}
					}
				}

				ksort($customer_products);

				foreach ($customer_products as $res) {
					foreach ($res as $res1) {
						$result[] = $res1;
					}
				}

				self::$smarty->assign('more_handbags', $result);
				if(is_array($result))
					$all_products_on_page = array_merge($all_products_on_page, $result);
			} else {
				self::$smarty->assign( 'more_handbags', $more_handbags_final);
				if(is_array($more_handbags_final))
					$all_products_on_page = array_merge($all_products_on_page, $more_handbags_final);
			}
			// Customer Details
			$customer_name = $customer->firstname;

			// For "skip the month" feature
			if (self::$cookie->id_lang == 4) {
				$months_tr = array('Ocak','Şubat','Mart','Nisan','Mayıs','Haziran','Temmuz','Ağustos','Eylül','Ekim','Kasım','Aralık');
				$current_month_tr = date('n');
				$current_month_fullname = $months_tr[$current_month_tr-1];
			} else {
				$current_month_fullname = Tools::strtolower(date('F'));
			}

			$current_day = date('d');
			$current_month = Tools::strtolower(date('M'));

			self::$smarty->assign(array(
				'day' => $current_day,
				'month' => $current_month,
				'month_fullname' => $current_month_fullname,
				'name' => $customer_name
			));
		}

		//product low stock
		$configs = Configuration::getMultiple(array('PS_ORDER_OUT_OF_STOCK', 'PS_LAST_QTIES'));

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

		self::$smarty->assign( array(
			'last_qties'		 => intval($configs['PS_LAST_QTIES']),
			'prodsmallSize'		 => Image::getSize('prodsmall'),
			'HOOK_AFTER_MENU' => Module::hookExec('afterMenu'),
			'HOOK_BEFORE_FOOTER' => Module::hookExec('beforeFooterBlock'),
			'col_img_dir' => _PS_COL_IMG_DIR_
		));
		// For NanoIntegactive Integration
		self::$smarty->assign('all_products_on_page', $all_products_on_page);
		self::$smarty->assign('errors', $this->errors);
		Tools::safePostVars();

	}

	public function setMedia() {
		parent::setMedia();

		Tools::addCSS(_THEME_CSS_DIR_.'showroom.css');
        Tools::addCSS(_THEME_CSS_DIR_.'showroom-shoe-size.css');

		Tools::addJS(array(
			_PS_JS_DIR_ . 'jquery/jquery.lazyloader.js',
			_THEME_JS_DIR_ . 'showroom.js',
			_THEME_JS_DIR_ . 'hiw.js',
			'http://connect.facebook.net/tr_TR/all.js#xfbml=1'
		));

		if (isset(self::$cookie->deal_id) && (self::$cookie->deal_id > 0)) {
			Tools::addJS(_PS_JS_DIR_ . 'jquery/jquery.countdown.js');
			self::$smarty->assign('deal_id', self::$cookie->deal_id);
		}
	}

	public function process() {
		parent::process();

        setcookie("bu_bcpath", 'butigim');

		$back = Tools::getValue('back');
		$key = Tools::safeOutput(Tools::getValue('key'));
        
		if (! empty($key)) {
			$back .= (strpos($back, '?') !== false ? '&' : '?') . 'key=' . $key;
		}

		if (! empty($back)) {
			self::$smarty->assign('back', Tools::safeOutput($back));

			if (strpos($back, 'order.php') !== false) {
				$countries = Country::getCountries((int)(self::$cookie->id_lang), true);

				self::$smarty->assign(array(
					'inOrderProcess' => true,
					'PS_GUEST_CHECKOUT_ENABLED' => Configuration::get('PS_GUEST_CHECKOUT_ENABLED'),
					'sl_country' => (int)Tools::getValue('id_country', Configuration::get('PS_COUNTRY_DEFAULT')),
					'countries' => $countries
				));
			}
		}
	}

	public function displayContent() {
		parent::displayContent();
		self::$smarty->display(_PS_THEME_DIR_ . 'showroom.tpl');
	}

	public function run() {
		$this->init();

		$this->preProcess();
		$this->setMedia();
		$this->process();
		$this->displayHeader();
		$this->displayContent();
		$this->displayFooter();
	}
}

?>
