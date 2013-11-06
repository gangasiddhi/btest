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

class ProductQuickviewControllerCore extends FrontController
{
	protected $product;
	public $ssl = false;
	public $php_self = 'product-quickview.php';


	public function setMedia()
	{
		parent::setMedia();

			Tools::addCSS(_THEME_CSS_DIR_.'showroom.css' , 'screen');  //'product.css');
			Tools::addJS(_THEME_JS_DIR_.'tools.js');
			Tools::addJS(_PS_JS_DIR_.'main.js');

			if (Configuration::get('PS_DISPLAY_JQZOOM') == 1)
			{
				Tools::addJS(_PS_JS_DIR_.'jquery/jquery.cloudzoom.js');
				Tools::addCSS(_PS_CSS_DIR_.'cloudzoom.css', 'screen');
			}
			Tools::addJS(array(
				_PS_JS_DIR_.'jquery/jquery.idTabs.modified.js',
				_PS_JS_DIR_.'jquery/jquery.scrollto.js',
				_PS_JS_DIR_.'jquery/jquery.serialScroll.js',
				//_THEME_JS_DIR_.'tools.js',
				_THEME_JS_DIR_.'productquick.js',
//				_THEME_JS_DIR_.'quick-view.js'
				));

			/* jqZoom */
			$jqZoomEnabled = (Configuration::get('PS_DISPLAY_JQZOOM') == 1);

	}

	public function run()
	{
		$this->init();

		if (Tools::getValue('ajax') == 'true')
		{
				Tools::addCSS(_THEME_CSS_DIR_.'showroom.css' , 'screen');
			//Tools::addJS(_THEME_JS_DIR_.'product.js');
			$this->preProcess();
//			$this->setMedia();
//			if ($id_product = (int)Tools::getValue('id_product'))
//			$this->product = new Product($id_product, true, self::$cookie->id_lang);
			$this->process();
//			$this->displayContent();
//			$this->displayHeader();
//			$result = '<script src="themes/butigo/js/product.js" type="text/javascript"></script>';
			$result = $this->displayContent();
			//print_r($result);exit;
//			$this->displayFooter();
			//die($result);

		}
		else
		{
			die();
		}
	}

	public function preProcess()
	{

		if ($id_product = (int)Tools::getValue('id_product'))
			$this->product = new Product($id_product, true, self::$cookie->id_lang);

		if (!Validate::isLoadedObject($this->product))
		{
			header('HTTP/1.1 404 Not Found');
			header('Status: 404 Not Found');
		}
		else
			$this->canonicalRedirection();

		parent::preProcess();


		if(intval(Configuration::get('PS_REWRITING_SETTINGS')) === 1)
			$rewrited_url = null;


	}
	

	public function process()
	{
		parent::process();

		if (Tools::getValue('ajax') == 'true')
		{
			self::$smarty->assign('static_token' , Tools::getToken(false));
		}

		self::$smarty->assign(array(
					'HOOK_EXTRA_RIGHT' => Module::hookExec('extraRight')
				));
		$colorindex=0;

		$url_product_attribute = Tools::getValue('id_product_attribute');
		$color_product = $this->product->id_color_default; 
//		$color_product = Product::isColorProduct((int)Tools::getValue('id_product'));
//		if($color_product == 2)
//		{
			$color_flag = false;
			$groups = array();
			$attributesGroups = $this->product->getAttributesGroups((int)(self::$cookie->id_lang));  //Print_r($this->product);exit;
			foreach ($attributesGroups AS $k => $row)
			{
			  if($color_product == 2)
			  {
				 $color_flag = true;
				if (((isset($row['attribute_color']) AND $row['attribute_color']) OR (file_exists(_PS_COL_IMG_DIR_.$row['id_attribute'].'.jpg'))) AND $row['id_attribute_group'] == $this->product->id_color_default)
				{
					$colors[$row['id_attribute']]['value'] = $row['attribute_color'];
					$colors[$row['id_attribute']]['name'] = $row['attribute_name'];
					if (!isset($colors[$row['id_attribute']]['attributes_quantity']))
						$colors[$row['id_attribute']]['attributes_quantity'] = 0;
					$colors[$row['id_attribute']]['attributes_quantity'] += (int)($row['quantity']);
				}
			  }
				//groups
				if (!isset($groups[$row['id_attribute_group']]))
				{
					$groups[$row['id_attribute_group']] = array(
						'name' =>			$row['public_group_name'],
						'is_color_group' =>	$row['is_color_group'],
						'default' =>		-1,
					);
				}
				$groups[$row['id_attribute_group']]['attributes'][$row['id_attribute']] = $row['attribute_name'];
				if ($row['default_on'] && $groups[$row['id_attribute_group']]['default'] == -1)
					$groups[$row['id_attribute_group']]['default'] = intval($row['attribute_name']); //(int)($row['id_attribute']);
				if (!isset($groups[$row['id_attribute_group']]['attributes_quantity'][$row['id_attribute']]))
					$groups[$row['id_attribute_group']]['attributes_quantity'][$row['id_attribute']] = 0;
				$groups[$row['id_attribute_group']]['attributes_quantity'][$row['id_attribute']] += (int)($row['quantity']);
				//groups

				$combinations[$row['id_product_attribute']]['attributes_values'][$row['id_attribute_group']] = $row['attribute_name'];
				$combinations[$row['id_product_attribute']]['attributes'][] = (int)($row['id_attribute']);
//				$combinations[$row['id_product_attribute']]['price'] = (float)($row['price']);
//				$combinations[$row['id_product_attribute']]['ecotax'] = (float)($row['ecotax']);
//				$combinations[$row['id_product_attribute']]['weight'] = (float)($row['weight']);
				$combinations[$row['id_product_attribute']]['quantity'] = (int)($row['quantity']);
				if ($row['default_image'])
				{
					$combinations[$row['id_product_attribute']]['default_ipa'] = $row['default_image'];
					$combinations[$row['id_product_attribute']]['default_url_ipa'] = $row['id_product_attribute'];
				}
				
				/*To get the default combination id for single color product so that when a user favourites a product,that default combination id can be passed*/
				if ($row['default_on'])
				{
					self::$smarty->assign('single_product_default_ipa' , $row['id_product_attribute']);
				}
			}
			$combinationImages = $this->product->getCombinationImages((int)(self::$cookie->id_lang));
//			print_r($combinationImages);//exit;
//			$combImages = array();
//			$combName =array();
//			foreach($combinationImages AS $key => $combinationImage)
//			{
//				//echo "<pre>";
//				foreach($combinationImage AS $key => $combinationImg)
//				{
//					$combImages[$combinationImg['id_product_attribute']] = $combinationImg['id_image'];
//					$combName[$combinationImg['id_product_attribute']] = $combinationImg['legend'];
//					//print_r($combinationImg);
//					echo $combinationImg['id_image'];echo "--<br>";
//					print_r($combImages); echo "----";print_r($combName);
//
//				}
//				//echo "</pre>";
//			}
//			print_r($combImages); echo "----";print_r($combName);
//echo "<pre>";print_r($combinationImages);echo "----";exit;
//print_r($combinations);echo "</pre>";exit;


	$colorindex = 0;
	if($color_product == 2)
	 {
			foreach($combinations AS $key => $combination)
			{
				if(isset($combination['attributes_values'][2]) && isset($combination['attributes_values'][4]))
				{ 
					$flag = 1;
				}
				else if(isset($combination['attributes_values'][2]))
				{
					$flag = 0;
				}
				if((isset($combination['attributes_values'][2]) && isset($combination['attributes_values'][4])) || isset($combination['attributes_values'][4]))
				{
						self::$smarty->assign('color_shoe_combination' , 1);
				}
				foreach($combinationImages AS $ipa  => $combinationImage)
				{
//				  if($ipa == $key)
				  if(isset($combination['default_url_ipa']) && $ipa == $combination['default_url_ipa'] )
				   {
					   $color_combination[$combination['attributes'][$flag]]['default_ipa'] = $ipa;
					    foreach($combinationImage AS $key1  => $combinationImg)
						{  
						   //if($key1 != 'coverimage')
							$color_combination[$combination['attributes'][$flag]]['images'][] = $combinationImg;
						}
				   }
				}
				if(isset($combination['attributes_values'][2]) && isset($combination['attributes_values'][4])) 
				{  
				  
					if($url_product_attribute == $key )
					{
						self::$smarty->assign('default_color' , $combination['attributes'][1]);
						if(isset($combination['default_ipa']))
							$color_id_image = $combination['default_ipa']; 
					}
					$color_combination[$combination['attributes'][1]]['qty'] = $colors[$combination['attributes'][1]]['attributes_quantity'];

					$color_combination[$combination['attributes'][1]]['ipa'][] = $key;
				
					if(isset($combination['default_ipa']))
						$colors[$combination['attributes'][1]]['id_product_attribute'] = $key;
					$color_combination[$combination['attributes'][1]]['product_data'][$combination['attributes'][0]]['size'] = $combination['attributes_values'][4];
					$color_combination[$combination['attributes'][1]]['product_data'][$combination['attributes'][0]]['quantity'] = $combination['quantity'];
					$color_combination[$combination['attributes'][1]]['product_data'][$combination['attributes'][0]]['ipa'] = $key;
//					if(isset($combination['attributes_values'][4]))
//						self::$smarty->assign('color_shoe_combination' , 1);
				
				}
				else
				{
					if($url_product_attribute == $key )
					{
						self::$smarty->assign('default_color' , $combination['attributes'][0]);
						if(isset($combination['default_ipa']))
							$color_id_image = $combination['default_ipa'];
					}
					$color_combination[$combination['attributes'][0]]['qty'] = $colors[$combination['attributes'][0]]['attributes_quantity'];
					$color_combination[$combination['attributes'][0]]['ipa'][] = $key;
					if(isset($combination['default_ipa']))
						$colors[$combination['attributes'][0]]['id_product_attribute'] = $key;
					$color_combination[$combination['attributes'][0]]['product_data'][$combination['attributes'][0]]['quantity'] = $combination['quantity'];
					$color_combination[$combination['attributes'][0]]['product_data'][$combination['attributes'][0]]['ipa'] = $key;
					//$color_combination[$combination['attributes'][1]]['ipa'][] = $key;
					self::$smarty->assign('color_shoe_combination' , 0);
				}
			}
			$colorindex++;
	 }
	 else if($color_product != 2)
	 {
			foreach($combinations AS $key => $combination)
			{
				if((isset($combination['attributes_values'][2]) && isset($combination['attributes_values'][4])) || isset($combination['attributes_values'][4]))
				{
						self::$smarty->assign('color_shoe_combination' , 1);
				}

			}
	 }
			
			foreach ($combinations AS $id_product_attribute => $comb)
					{
						$attributeList = '';
						foreach ($comb['attributes'] AS $id_attribute)
							$attributeList .= '\''.(int)($id_attribute).'\',';
						$attributeList = rtrim($attributeList, ',');
						$combinations[$id_product_attribute]['list'] = $attributeList;
					}
//			echo "<pre>";print_r($combinationImages);echo "</pre>";
//			echo "<pre>";print_r($combinations);echo "</pre>";
//			echo "<pre>";
//			print_r($groups);echo "--------------";
//			echo "</pre>";
//			echo "<pre>";
//			print_r($color_combination);
//			echo "</pre>";
//			echo "<pre>coloe-combinatiom";print_r($color_combination);echo "</pre>";exit;

//single
//if($color_product == 0)
	{
				$images = $this->product->getImages((int)self::$cookie->id_lang);
					//echo "************images**************";print_r($images);
				$productImages = array();
				foreach ($images AS $k => $image)
				{
					if($url_product_attribute>0)
					{
						$image['fb_image'] = 1;//$color_id_image;
					}
					else
					{
						$image['fb_image'] = $image['id_image'];
					}
					if ($image['cover'])
					{
						self::$smarty->assign('mainImage', $images[0]);
						$cover = $image;
						$cover['id_image'] = (Configuration::get('PS_LEGACY_IMAGES') ? ($this->product->id.'-'.$image['id_image']) : $image['id_image']);
						$cover['fb_image'] = (Configuration::get('PS_LEGACY_IMAGES') ? ($this->product->id.'-'.$image['fb_image']) : $image['fb_image']);
						$cover['id_image_only'] = (int)($image['id_image']);
					}
					$productImages[(int)$image['id_image']] = $image;
				}
				if (!isset($cover))
					$cover = array('id_image' => Language::getIsoById(self::$cookie->id_lang).'-default', 'legend' => 'No picture', 'title' => 'No picture');

				$size = Image::getSize('large');
				self::$smarty->assign(array(
					'cover' => $cover,
					'fb_image' => $cover['fb_image'],//$cover['id_image'],
					//'fb_image_host' => $content_base_url,
					'thumbSize' => Image::getSize('prodthumb'),
					'imgWidth' => (int)($size['width']),
					'mediumSize' => Image::getSize('medium'),
					'largeSize' => Image::getSize('large'),
					'accessories' => $this->product->getAccessories((int)self::$cookie->id_lang)
				));
//				print_r($cover);exit;
//				print_r($productImages);
				if (count($productImages))
					self::$smarty->assign('images', $productImages);

				$attr_q = array();
					//wash attributes list (if some attributes are unavailables and if allowed to wash it)
					if (!Product::isAvailableWhenOutOfStock($this->product->out_of_stock) && Configuration::get('PS_DISP_UNAVAILABLE_ATTR') == 0)
					{
						foreach ($groups AS &$group)
							foreach ($group['attributes_quantity'] AS $key => &$quantity)
								if (!$quantity)
									$attr_q[] = $group['attributes'][$key];
//echo "<pre>";print_r($groups);echo "--<br/>--";print_r($colors);echo "</pre>";exit;
//						foreach ($colors AS $key => $color)374
//							if (!$color['attributes_quantity'])
//								unset($colors[$key]);

					}

//					foreach ($groups AS &$group)
//						natcasesort($group['attributes']);

					self::$smarty->assign('attr_q' , $attr_q);
					if(!isset(self::$cookie->show_site) && self::$cookie->logged)
					{
						$customer_shoe_size = intval(Customer::getShoeSize(intval(self::$cookie->id_customer)));
					}
					$customerShoeSizeDefault = 0;
					foreach($groups AS &$group)
					{
							natcasesort($group['attributes']);
							/*$customer_default_shoe_size = false;
							$customer_default_attribute = false;
							if($group['is_color_group'] == 0)
							{
								foreach($group['attributes'] AS $key => &$attribute)
									{
										if($customer_shoe_size == $attribute && (in_array($customer_shoe_size, $attr_q)== false))
										{
											  $customerShoeSizeDefault = $attribute;
											  $customer_default_shoe_size = true;
											  $customer_default_attribute = true;
										}
									}
									if($customer_default_shoe_size == false)
									{
										foreach($group['attributes'] AS $key => &$attribute)
											{
												if($group['default'] == $attribute && (in_array($group['default'], $attr_q)== false))
												{
													  $customerShoeSizeDefault = $attribute;
													  $customer_default_attribute = true;
													  break;
												}
											}
									}
									if($customer_default_attribute == false)
									{
										foreach($group['attributes'] AS $key => &$attribute)
											{
												if(in_array($attribute, $attr_q)== false)
												{
													  $customerShoeSizeDefault = $attribute;
													  break;
												}
											}
									}
									//echo "css=".$customerShoeSizeDefault;
									self::$smarty->assign('customerShoeSizeDefault' ,$customerShoeSizeDefault);
							}*/
					}
}
//single

//echo "<pre>";print_r($color_combination);echo "</pre>";exit;
			if(!isset(self::$cookie->show_site) && self::$cookie->logged)
			{
				$customer_shoe_size = intval(Customer::getShoeSize(intval(self::$cookie->id_customer)));

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
			}
		/*Favourite Button*/
			
			if(!isset(self::$cookie->show_site) && self::$cookie->logged)
			{
				self::$smarty->assign(array(
					'customer_shoe_size' => $customer_shoe_size
				));
			}
			self::$smarty->assign(array(
					'product' => $this->product,
					'url_product_attribute' =>  $url_product_attribute,
//				    'color_combination' => $color_combination,
//					'colors' => $colors,
					'groups' => $groups,
					'mediumSize' => Image::getSize('medium'),
					//'customer_shoe_size' => $customer_shoe_size,
					'combinations' => $combinations,
					'color_flag' => $color_flag,
					'col_img_dir' => _PS_COL_IMG_DIR_
				));
			if($color_product == 2)
			{
			self::$smarty->assign(array(
				    'color_combination' => $color_combination,
					'colors' => $colors,
//					'groups' => $groups
				));
			}
//		}
//		else{
//			echo "hello";
//		}
//echo "<pre>";
//print_r($color_combination);
//echo "</pre>";
//exit;
//		$images = $this->product->getImages((int)self::$cookie->id_lang);
//		echo "***images***********************";print_r($color_combination);//exit;
//		$attributesGroups = $this->product->getAttributesGroups((int)(self::$cookie->id_lang));  // @todo (RM) should only get groups and not all declination ?
		//echo "***attributesGroups***********************";print_r($attributesGroups);
		

	}

	public function displayContent()
	{

		parent::displayContent();
//		Tools::safePostVars();
//		self::$smarty->assign('errors', $this->errors);
		//if(Tools::getValue('ajax') == 'true')
//			self::$smarty->display(_PS_THEME_DIR_.'productq.tpl',NULL,NULL,NULL,false);
		//else
		self::$smarty->display(_PS_THEME_DIR_.'productquick.tpl',NULL,NULL,NULL,false);
	}

	
}

?>