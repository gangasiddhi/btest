<?php

/**
 * Description of productrecommend
 *
 * @author gangadhar
 */
class ProductRecommend extends Module
{

	public function __construct()
	{
		$this->name = 'productrecommend';
		$this->tab = 'Advertising & Marketing';
		$this->version = 1.0;
		$this->author = 'PrestaShop';

		parent::__construct();

		$this->displayName = $this->l('Products Recommendation');
		$this->description = $this->l('This module must be enabled if you want to Recommend the products along with product.');
	}

	public function install()
	{
		if (!parent::install() OR
				!$this->registerHook('productRecommend'))
			return false;

		return true;
	}
	
	public function uninstall() 
	{
		return (parent::uninstall());
	}

	public function hookProductRecommend($params)
	{
		global $cookie, $smarty;

		$defaultLanguage = (int) (Configuration::get('PS_LANG_DEFAULT'));

		if (!$params['content']) {
			$smarty->assign(array(
				'idTab' => $params['idTab']));
			return $this->display(__FILE__, 'product-recommend-head-tab.tpl');
		} else {
			$product = $params['product'];
			$langId = $params['defaultLanguage'];
			
			if ($product->recommendations) {
				$productRecommendations = json_decode($product->recommendations);
				$productIds = explode('|', $productRecommendations->product_id_list);
				$productIdsList = array();
				$i = 0;
				foreach ($productIds as $productId) {
					if ($productId) {
						$data = explode('_', $productId);
						$productIdsList[$i]['id_product'] = $data[0];
						$productIdsList[$i]['id_product_attribute'] = $data[1];						
						$productIdsList[$i]['name'] = $this->getProductName($defaultLanguage, $data[0]);
						$productIdsList[$i]['position'] = $data[2];
					}	
					$i++;
				}
				
				$recommandProductListString = '';
				foreach($productIdsList as $productIdList){
					$recommandProductListString .= $productIdList['id_product'].'_'.$productIdList['id_product_attribute'].'_'.$productIdList['name'].'_'.$productIdList['position'].'|';
				}
					
				$smarty->assign(array('previousRecommendations' => $productRecommendations,
					'previousProductRecommendList' => $productIdsList,
					'recommandProductListString' => $recommandProductListString,
					));
				
			}
			
			$trads = array(
					'Home' => $this->l('Home'),
					'selected' => $this->l('selected'),
					'Collapse All' => $this->l('Collapse All'),
					'Expand All' => $this->l('Expand All'),
			);
			
			$category = new Category((int)$productRecommendations->category_id);
			
			$selectedCat = array(
				$productRecommendations->category_id => array(
					'id_category' => $category->id,
					'name' => $category->getName($langId)
				)
			);

			$categories = Category::getCategories($cookie->id_lang, true, true, $sql_filter = '', $sql_sort = '', $sql_limit = '');
			$smarty->assign(array(
				'product' => $product,
				'categories' => $categories,
				'categoryTree' => $this->renderAdminCategorieTree($trads, $selectedCat, 'productRecommendCategoryCheckbox', $this->_path.'js/product-recommend-categories-tree.js'),
				'baseUri' => __PS_BASE_URI__,
				'langId' => $langId,
				'path' => $this->_path
			));

			return $this->display(__FILE__, 'product-recommend-content.tpl');
		}
	}

	/* get the product name
	 * param $langId is int,
	 * param $productId is int,
	 */

	function getProductName($langId, $productId)
	{
		$sql = 'SELECT p.id_product, pl.name
			FROM `' . _DB_PREFIX_ . 'product` p
			LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (p.id_product = pl.id_product AND pl.id_lang = ' . $langId . ')
			WHERE p.id_product = ' . $productId;

		$result = Db::getInstance()->getRow($sql);

		return $result['name'];
	}

	public static function renderAdminCategorieTree($trads, $selected_cat = array(), $input_name = 'categoryBox', $custom_js='../js/admin-categories-tree.js" type="text/javascript')
	{
		$html = '
		<script src="../js/jquery/treeview/jquery.treeview.js" type="text/javascript"></script>
		<script src="../js/jquery/treeview/jquery.treeview.async.js" type="text/javascript"></script>
		<script src="../js/jquery/treeview/jquery.treeview.edit.js" type="text/javascript"></script>
		<script src="'.$custom_js.'"></script>
		
		<script type="text/javascript">
			var inputName = "'.$input_name.'";
			var selectedCat = "'.implode(',', array_keys($selected_cat)).'";
		</script>
		<script type="text/javascript">
			var selectedLabel = \''.$trads['selected'].'\';
			var home = \''.$trads['Home'].'\';
		</script>
		<link type="text/css" rel="stylesheet" href="../css/jquery.treeview.css" />
		';
		
		$html .= '
		<div style="background-color:#F4E6C9; width:99%;padding:5px 0 5px 5px;">
			<a href="#" id="collapse_all" >'.$trads['Collapse All'].'</a>
			 - <a href="#" id="expand_all" >'.$trads['Expand All'].'</a>
		</div>
		';
		
		$home_is_selected = false;
		foreach($selected_cat AS $cat)
		{
			if (is_array($cat))
			{
				if  ($cat['id_category'] != 1)
					$html .= '<input type="hidden" name="'.$input_name.'[]" value="'.$cat['id_category'].'" >';
				else
					$home_is_selected = true;
			}
			else
			{
				if  ($cat != 1)
					$html .= '<input type="hidden" name="'.$input_name.'[]" value="'.$cat.'" >';
				else
					$home_is_selected = true;
			}
		}
		$html .= '
			<ul id="recommend-categories-treeview" class="filetree">
				<li id="1" class="hasChildren">
					<span class="folder"> <input type="checkbox" name="'.$input_name.'[]" value="1" '.($home_is_selected ? 'checked' : '').' onclick="clickOnCategoryBox($(this));" /> '.$trads['Home'].'</span>
					<ul>
						<li><span class="placeholder">&nbsp;</span></li>	
				  </ul>
				</li>
			</ul>';
		return $html;
	}
	
	public function getProductSizes($productId){
		global $cookie, $smarty, $link;
		$product = new Product((int)$productId);
		
		/* Attributes / Groups & colors */
		$colors = array();
		$attributesGroups = $product->getAttributesGroups((int)($cookie->id_lang));  // @todo (RM) should only get groups and not all declination ?

		if (is_array($attributesGroups) AND $attributesGroups)
		{
			$groups = array();
			$combinationImages = $product->getCombinationImages((int)($cookie->id_lang));

			foreach ($attributesGroups AS $k => $row)
			{
				/* Color management */
				if (((isset($row['attribute_color']) AND $row['attribute_color']) OR (file_exists(_PS_COL_IMG_DIR_.$row['id_attribute'].'.jpg'))) AND $row['id_attribute_group'] == $product->id_color_default)
				{
					$colors[$row['id_attribute']]['value'] = $row['attribute_color'];
					$colors[$row['id_attribute']]['name'] = $row['attribute_name'];
					if (!isset($colors[$row['id_attribute']]['attributes_quantity']))
						$colors[$row['id_attribute']]['attributes_quantity'] = 0;
					$colors[$row['id_attribute']]['attributes_quantity'] += (int)($row['quantity']);
				}
				if (!isset($groups[$row['id_attribute_group']]))
				{
					$groups[$row['id_attribute_group']] = array(
						'name' =>			$row['public_group_name'],
						'is_color_group' =>	$row['is_color_group'],
		//								'default' =>		-1
					);
				}

				$groups[$row['id_attribute_group']]['attributes'][$row['id_attribute']] = $row['attribute_name'];
		//						if ($row['default_on'] && $groups[$row['id_attribute_group']]['default'] == -1)
		//							$groups[$row['id_attribute_group']]['default'] = intval($row['attribute_name']); //(int)($row['id_attribute']);
				if (!isset($groups[$row['id_attribute_group']]['attributes_quantity'][$row['id_attribute']]))
					$groups[$row['id_attribute_group']]['attributes_quantity'][$row['id_attribute']] = 0;
				$groups[$row['id_attribute_group']]['attributes_quantity'][$row['id_attribute']] += (int)($row['quantity']);


				$combinations[$row['id_product_attribute']]['attributes_values'][$row['id_attribute_group']] = $row['attribute_name'];
				$combinations[$row['id_product_attribute']]['attributes'][] = (int)($row['id_attribute']);
				$combinations[$row['id_product_attribute']]['price'] = (float)($row['price']);
				$combinations[$row['id_product_attribute']]['ecotax'] = (float)($row['ecotax']);
				$combinations[$row['id_product_attribute']]['weight'] = (float)($row['weight']);
				$combinations[$row['id_product_attribute']]['quantity'] = (int)($row['quantity']);
				$combinations[$row['id_product_attribute']]['reference'] = $row['reference'];
				$combinations[$row['id_product_attribute']]['unit_impact'] = $row['unit_price_impact'];
				$combinations[$row['id_product_attribute']]['minimal_quantity'] = $row['minimal_quantity'];
				$combinations[$row['id_product_attribute']]['id_image'] = isset($combinationImages[$row['id_product_attribute']][0]['id_image']) ? $combinationImages[$row['id_product_attribute']][0]['id_image'] : -1;
				if ($row['default_image']){
					$combinations[$row['id_product_attribute']]['default_ipa'] = $row['default_image'];
				}
			}

		//					$id_product = (int)Tools::getValue('id_product');
		//					$id_attribute_color = Product::getIpaAttribute($id_product);
			//$ipa = Tools::getValue('id_product_attribute');

			if($url_product_attribute > 0)
			{
				$id_attri_color = Product::getIdAttribute($url_product_attribute);
				$smarty->assign('id_attribute_color' , $id_attri_color[0]['id_attribute']);
				$smarty->assign('color_id_quantity',$colors[$id_attri_color[0]['id_attribute']]['attributes_quantity']);
			}
				$color_combination = array();
				foreach($combinations AS $key => $combination)
				{

					if((isset($combination['attributes_values'][2]) && isset($combination['attributes_values'][4])) || isset($combination['attributes_values'][4]))
					{
						if($url_product_attribute > 0)
						{
							if($url_product_attribute == $key )
							{
								$smarty->assign('default_color' , $combination['attributes'][1]);
								if(isset($combination['default_ipa']))
									$color_id_image = $combination['default_ipa'];
							}

		//								if(isset($combination['default_ipa']))
		//									$colors[$combination['attributes'][1]]['id_product_attribute'] = $combination['default_ipa'];

							//$color_combination[$combination['attributes'][1]]['ipa'][] = $key;

							if(isset($combination['default_ipa']))
								$colors[$combination['attributes'][1]]['id_product_attribute'] = $key;
							$color_combination[$combination['attributes'][1]]['shoesize'][$combination['attributes'][0]]['size'] = $combination['attributes_values'][4];
							$color_combination[$combination['attributes'][1]]['shoesize'][$combination['attributes'][0]]['quantity'] = $combination['quantity'];
							$color_combination[$combination['attributes'][1]]['shoesize'][$combination['attributes'][0]]['ipa'] = $key;
						}
						if(isset($combination['attributes_values'][4]))
							$smarty->assign('color_shoe_combination' , 1);

					}else if((isset($combination['attributes_values'][2]) && isset($combination['attributes_values'][5])) || isset($combination['attributes_values'][5])){
						if($url_product_attribute > 0)
						{
							if($url_product_attribute == $key )
							{
								$smarty->assign('default_color' , $combination['attributes'][1]);
								if(isset($combination['default_ipa']))
									$color_id_image = $combination['default_ipa'];
							}

		//								if(isset($combination['default_ipa']))
		//									$colors[$combination['attributes'][1]]['id_product_attribute'] = $combination['default_ipa'];

							//$color_combination[$combination['attributes'][1]]['ipa'][] = $key;

							if(isset($combination['default_ipa']))
								$colors[$combination['attributes'][1]]['id_product_attribute'] = $key;
							$color_combination[$combination['attributes'][1]]['shoesize'][$combination['attributes'][0]]['size'] = $combination['attributes_values'][5];
							$color_combination[$combination['attributes'][1]]['shoesize'][$combination['attributes'][0]]['quantity'] = $combination['quantity'];
							$color_combination[$combination['attributes'][1]]['shoesize'][$combination['attributes'][0]]['ipa'] = $key;
						}
						if(isset($combination['attributes_values'][5]))
							$smarty->assign('accessory_size_combination' , 1);
					}else {
						if($url_product_attribute == $key )
						{
							$smarty->assign('default_color' , $combination['attributes'][0]);
							if(isset($combination['default_ipa']))
								$color_id_image = $combination['default_ipa'];
						}
						if(isset($combination['default_ipa']))
							$colors[$combination['attributes'][0]]['id_product_attribute'] = $key;
						//$color_combination[$combination['attributes'][1]]['ipa'][] = $key;
						$smarty->assign('color_shoe_combination' , 0);
					}
				}


		$smarty->assign('color_combination' , $color_combination);
			$images = $product->getImages((int)$cookie->id_lang);

		$productImages = array();
		foreach ($images AS $k => $image)
		{
			if($url_product_attribute>0)
			{
				$image['fb_image'] = $color_id_image;
			}
			else
			{
				$image['fb_image'] = $image['id_image'];
			}
			if ($image['cover'])
			{
				$smarty->assign('mainImage', $images[0]);
				$cover = $image;
				$cover['id_image'] = (Configuration::get('PS_LEGACY_IMAGES') ? ($product->id.'-'.$image['id_image']) : $image['id_image']);
				$cover['fb_image'] = (Configuration::get('PS_LEGACY_IMAGES') ? ($product->id.'-'.$image['fb_image']) : $image['fb_image']);
				$cover['id_image_only'] = (int)($image['id_image']);
			}
			$productImages[(int)$image['id_image']] = $image;
		}

		if (!isset($cover))
			$cover = array('id_image' => Language::getIsoById($cookie->id_lang).'-default', 'legend' => 'No picture', 'title' => 'No picture');

						/*facebook like button sharing issue in facebook profile */

						if($url_product_attribute > 0)
							$product_url = $link->getProductLink($product, $url_product_attribute);
							else
							$product_url = $link->getProductLink($product);

							$smarty->assign('product_url' , $product_url);

							/*facebook like button sharing issue in facebook profile */

							$size = Image::getSize('large');
		$smarty->assign(array(
			'cover' => $cover,
			'fb_image' => $cover['id_image'],
			//'fb_image_host' => $content_base_url,
			'thumbSize' => Image::getSize('prodthumb'),
			'imgWidth' => (int)($size['width']),
			'mediumSize' => Image::getSize('medium'),
			'largeSize' => Image::getSize('large'),
			'accessories' => $product->getAccessories((int)$cookie->id_lang)
		));

		if($product->recommendations != null){
			$recommendationsDetails = json_decode($product->recommendations);
			$recommendProducts = array();
			$recommendProductString = '';
			$recommendProductLists = explode('|',$recommendationsDetails->product_id_list);
			$recommendProductDetails = array();
			foreach($recommendProductLists as $recommendProductList){
				$recommendProductArray = explode('_', $recommendProductList);
				$recommendProducts[$recommendProductArray[2]] = $recommendProductArray[0];
				$recommendProductString .= $recommendProductArray[0] . ',';
				$recommendProductDetails[] = $product->getRecommendProductDetails((int)$cookie->id_lang, $recommendProductArray[0]);
			}
			$positionedRecommendProductDetails = array();
			foreach($recommendProductDetails as $recommendProductDetail){
				if(!empty($recommendProductDetail[0]) AND $recommendProductDetail[0]['quantity'] > 0 ){
					$positionedRecommendProductDetails[] = $recommendProductDetail[0];
				}
			}

			$smarty->assign(array(
				'recommendEnable' => $recommendationsDetails->recommend,
				'recommendProductDetails'=>$positionedRecommendProductDetails
			));
		}


		if (count($productImages))
			$smarty->assign('images', $productImages);

			$attr_q = array();
			//wash attributes list (if some attributes are unavailables and if allowed to wash it)
			//commented for Back Order feature
			//if (!Product::isAvailableWhenOutOfStock($product->out_of_stock) && Configuration::get('PS_DISP_UNAVAILABLE_ATTR') == 0)
			{
				foreach ($groups AS &$group)
					foreach ($group['attributes_quantity'] AS $key => &$quantity)
						if (!$quantity)
							$attr_q[] = $group['attributes'][$key];
		//						foreach ($colors AS $key => $color)
		//							if (!$color['attributes_quantity'])
		//								unset($colors[$key]);

			}

		//					foreach ($groups AS &$group)
		//						natcasesort($group['attributes']);

			$smarty->assign('attr_q' , $attr_q);
							//unset($group['attributes'][$key]);
			/*$customer_shoe_size = intval(Customer::getShoeSize(intval($cookie->id_customer)));
			$customerShoeSizeDefault = $customer_shoe_size ;*/
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
							$smarty->assign('customerShoeSizeDefault' ,$customerShoeSizeDefault);
					}*/
			}

			foreach ($combinations AS $id_product_attribute => $comb)
			{
				$attributeList = '';
				foreach ($comb['attributes'] AS $id_attribute)
					$attributeList .= '\''.(int)($id_attribute).'\',';
				$attributeList = rtrim($attributeList, ',');
				$combinations[$id_product_attribute]['list'] = $attributeList;
			}


			$smarty->assign(array(
				'groups' => $groups,
				'combinaisons' => $combinations, /* Kept for compatibility purpose only */
				'combinations' => $combinations,
				'colors' => (sizeof($colors) AND $product->id_color_default) ? $colors : false,
				'combinationImages' => $combinationImages,
				//'customer_shoe_size'=>$customer_shoe_size
				));
		}
		$smarty->assign('path', $this->_path); 
		$smarty->assign('themePath', _THEME_DIR_); 
		$smarty->assign('productId' , $productId);
		echo $this->display(__FILE__, 'product-recommend-shoe-size.tpl');

	}
}
?>
