<?php

require_once(dirname(__FILE__).'/config/config.inc.php');
require_once(dirname(__FILE__).'/init.php');

//Get the ids from the URL
$ids_string = $_GET['ids'];

//Separating the product_ids and attributes by pipe line character
$product_and_attributes = explode('|', $ids_string);

//Forming an array as  key=product_id & value=product_attribute_id 
foreach($product_and_attributes as $product_ids)
{
	$product = explode(',', $product_ids);
	$products[] = array('product_id' =>$product[0],'attribute_id' =>$product[1] );
}
//echo "<pre>"; print_r($products); echo "</pre>";

//Checking for the product availability
foreach($products as $product)
{
			
	//echo "productid:=".$product['product_id']." ,attribute_id:".$product['attribute_id']."<br>";
	if($product['product_id'] AND $product['attribute_id'])
	{
		$sql = '
		SELECT p.`id_product` as product_id,pa.`id_product_attribute` as attribute_id,(p.`price` * IF(t.`rate`,((100 + (t.`rate`))/100),1)) AS price,IF( p.`active` = 1 AND pa.`active`= 1 AND p.`quantity`> 0 AND pa.`quantity`> 0 ,1,0) as available, pl.`link_rewrite`, IF(pa.default_image, pa.`default_image` ,i.`id_image`) as id_image
		FROM `'._DB_PREFIX_.'product` p
		LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa ON (p.`id_product` = pa.`id_product`)
		LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product` AND pl.`id_lang` = '.(int)($cookie->id_lang).')
		LEFT JOIN `'._DB_PREFIX_.'image` i ON (i.`id_product` = p.`id_product` AND IF(pa.default_image ,\'  \', i.`cover` = 1))
		LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = '.(int)($cookie->id_lang).')
		LEFT JOIN `'._DB_PREFIX_.'tax_rule` tr ON (p.`id_tax_rules_group` = tr.`id_tax_rules_group`
										   AND tr.`id_country` = '.(int)Country::getDefaultCountryId().'
										   AND tr.`id_state` = 0)
		LEFT JOIN `'._DB_PREFIX_.'tax` t ON (t.`id_tax` = tr.`id_tax`)
		LEFT JOIN `'._DB_PREFIX_.'tax_lang` tl ON (t.`id_tax` = tl.`id_tax` AND tl.`id_lang` = '.(int)($cookie->id_lang).')
		WHERE p.`id_product` ='.$product["product_id"].' AND pa.`id_product_attribute`='.$product["attribute_id"];
		//echo $sql."<br>";
		$data = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql);
		if(empty($data))
		{
			$data[0]['product_id'] = $product['product_id'];
			$data[0]['attribute_id'] = $product['attribute_id'];
			$data[0]['price'] = 0.00;
			$data[0]['available'] = 0;
			$data[0]['thumb_image_url'] = "";
			$data[0]['large_image_url'] = "";
		}
	}
	else if($product['product_id'])
	{	
		$sql = '
		SELECT p.`id_product` as product_id,(p.`price` * IF(t.`rate`,((100 + (t.`rate`))/100),1)) AS price, p.`active` = 1 as available, IF(pa.default_image, pa.`default_image` ,i.`id_image`) as id_image, pl.`link_rewrite`
		FROM `'._DB_PREFIX_.'product` p
		LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa ON (p.`id_product` = pa.`id_product` AND IF(pa.default_image ,1 , pa.`default_on` = 1))
		LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product` AND pl.`id_lang` = '.(int)($cookie->id_lang).')
		LEFT JOIN `'._DB_PREFIX_.'image` i ON (i.`id_product` = p.`id_product` AND IF(pa.default_image ,\'  \', i.`cover` = 1))
		LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = '.(int)($cookie->id_lang).')
		LEFT JOIN `'._DB_PREFIX_.'tax_rule` tr ON (p.`id_tax_rules_group` = tr.`id_tax_rules_group`
										   AND tr.`id_country` = '.(int)Country::getDefaultCountryId().'
										   AND tr.`id_state` = 0)
		LEFT JOIN `'._DB_PREFIX_.'tax` t ON (t.`id_tax` = tr.`id_tax`)
		LEFT JOIN `'._DB_PREFIX_.'tax_lang` tl ON (t.`id_tax` = tl.`id_tax` AND tl.`id_lang` = '.(int)($cookie->id_lang).')
		WHERE p.`id_product` ='.$product["product_id"];
		//echo $sql."<br>";
		$data = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql);
		
		if(empty($data))
		{
			$data[0]['product_id'] = $product['product_id'];
			$data[0]['attribute_id'] = $product['attribute_id'];
			$data[0]['price'] = 0.00;
			$data[0]['available'] = 0;
			$data[0]['id_image'] = "";
			$data[0]['link_rewrite'] = "";
		}
	}
	
	$datas[] = $data;
	
		//$complete_data = rtrim(ltrim(str_replace("\\/","/",json_encode($data)),'['),']');
	//echo "<pre>"; print_r(str_replace("\\/","/",json_encode($data))); echo "</pre>";
		//echo "<pre>"; print_r($complete_data); echo "</pre>";
}
//echo "<pre>"; print_r($datas); echo "</pre>";
$i = 0;
foreach($datas as $data)
{
	//echo "<pre>"; print_r($data[0]); echo "</pre>";
	if(!empty($data))
	{
		$product_details[$i]['product_id'] = $data[0]['product_id'];
		if(!empty($data[0]['attribute_id']))
			$product_details[$i]['attribute_id'] = $data[0]['attribute_id'];
		$product_details[$i]['price'] = number_format($data[0]['price'], 2, '.', '');
		$product_details[$i]['available'] = $data[0]['available'];
		
		if($data[0]['id_image'] != null)
		{
			$thumbImage_url = "http://".Configuration::get('PS_SHOP_DOMAIN')."/";
			$mediumImage_url = "http://".Configuration::get('PS_SHOP_DOMAIN')."/";
			if($_SERVER['SERVER_NAME']=='localhost')
			{
				$thumbImage_url  .= strtolower(Configuration::get('PS_SHOP_NAME'))."/";
				$mediumImage_url .= strtolower(Configuration::get('PS_SHOP_NAME'))."/";
			}
			$thumbImage_url .= $data[0]['product_id']."-".$data[0]['id_image']."-prodthumb/".$data[0]['link_rewrite'].".jpg";
			$mediumImage_url .= $data[0]['product_id']."-".$data[0]['id_image']."-medium/".$data[0]['link_rewrite'].".jpg";

			$product_details[$i]['thumb_image_url'] = $thumbImage_url;
			$product_details[$i]['large_image_url'] = $mediumImage_url;
		}
		else{
			$product_details[$i]['thumb_image_url'] = "";
			$product_details[$i]['large_image_url'] = "";
		}
	}
	
	$i++;
}
//$product_detailss['products'] = $product_details;
//echo "<pre>"; print_r($product_details); echo "</pre>";
//$json = str_replace("\\/","/",Tools::jsonEncode($product_details));
die(Tools::jsonEncode($product_details));

?>
