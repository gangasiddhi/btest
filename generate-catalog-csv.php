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
*  @version  Release: $Revision: 6599 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

require_once(dirname(__FILE__).'/config/config.inc.php');
require_once(dirname(__FILE__).'/init.php');

$cron_user = Tools::getValue('crnu');
$cron_pass = Tools::getValue('crnp');
if(!$cron_user)
	$cron_user = $argv[1];
if(!$cron_pass)
	$cron_pass = $argv[2];
$cron_pass = Tools::encrypt($cron_pass);

if($cron_user == _CRON_USER_ && $cron_pass == _CRON_PASSWD_)
{
	global $cookie;

	//Write the products details into the CSV file
	$fp = fopen(_PS_DOWNLOAD_DIR_.'gc/products_'.date("Y-m-d").'.csv', 'w');
	fputcsv($fp, array('item_id', 'category_name', 'item_attribute_id', 'item_name', 'item_price', /*'size-color',*/ 'item_available', 'item_url', 'item_imageurl', 'item_largeimageurl'));

	$products_sql = '
	SELECT p.*,pl.`link_rewrite` as product_url, cl.`name` AS category_default, pa.`id_product_attribute`, IF(pa.default_image, p.`id_product`,\'  \')  AS product_combination,
			pl.`description`, pl.`description_short`, pl.collection_name, pl.`available_now`, pl.`available_later`, 
			pl.`link_rewrite`, pl.`meta_description`, pl.`meta_keywords`, pl.`meta_title`, pl.`name`,  IF(pa.default_image, pa.`default_image` ,i.`id_image`) as id_image, 
		(p.`price` * IF(t.`rate`,((100 + (t.`rate`))/100),1)) AS order_price, pa.`active` as combination_active
	FROM `'._DB_PREFIX_.'product` p
	LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa ON (p.`id_product` = pa.`id_product`)
	LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product` AND pl.`id_lang` = '.(int)($cookie->id_lang).')
	LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON (p.`id_category_default` = cl.`id_category` AND cl.`id_lang` = '.(int)($cookie->id_lang).')
	LEFT JOIN `'._DB_PREFIX_.'image` i ON (i.`id_product` = p.`id_product` AND IF(pa.default_image ,\'  \', i.`cover` = 1))
	LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = '.(int)($cookie->id_lang).')
	LEFT JOIN `'._DB_PREFIX_.'tax_rule` tr ON (p.`id_tax_rules_group` = tr.`id_tax_rules_group`
											   AND tr.`id_country` = '.(int)Country::getDefaultCountryId().'
											   AND tr.`id_state` = 0)
	LEFT JOIN `'._DB_PREFIX_.'tax` t ON (t.`id_tax` = tr.`id_tax`)
	LEFT JOIN `'._DB_PREFIX_.'tax_lang` tl ON (t.`id_tax` = tl.`id_tax` AND tl.`id_lang` = '.(int)($cookie->id_lang).')
	LEFT JOIN `'._DB_PREFIX_.'manufacturer` m ON m.`id_manufacturer` = p.`id_manufacturer`
	ORDER BY p.`id_product` ASC';

	$products = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($products_sql);

	//$products =  Product::getProductsProperties((int)($cookie->id_lang), $products, false);
	//echo "<pre>"; print_r($products); echo "</pre>";
	/* Retrieving the products Attributes*/
	$attributes_query ='SELECT pac.`id_product_attribute`, 
						GROUP_CONCAT(al.`name` ORDER BY al.`name` ASC SEPARATOR "-") as attribute
						FROM `'._DB_PREFIX_.'product_attribute` pa
						LEFT JOIN `'._DB_PREFIX_.'product_attribute_combination` pac ON pac.`id_product_attribute` = pa.`id_product_attribute`
						LEFT JOIN `'._DB_PREFIX_.'attribute_lang` al ON (al.`id_attribute` = pac.`id_attribute` AND al.`id_lang` = '.(int)($cookie->id_lang).')
						GROUP BY  pac.`id_product_attribute`
						ORDER BY pac.`id_product_attribute` ASC';

	$attributes = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($attributes_query);

	/*Forming the products attributes array ,id_product_attribute as key  and thier attribute as value */
	foreach($attributes as $attribute)
		$product_attributes[$attribute['id_product_attribute']] = $attribute['attribute'];
	$link = new Link();
	foreach($products as $product)
	{
		//Image url
		
		$item_url = $thumbImage_url = $mediumImage_url = "http://".Configuration::get('PS_SHOP_DOMAIN')."/";
		if($_SERVER['SERVER_NAME']=='localhost')
		{
			//$item_url .= strtolower(Configuration::get('PS_SHOP_NAME'))."/";
			$thumbImage_url  .= strtolower(Configuration::get('PS_SHOP_NAME'))."/";
			$mediumImage_url .= strtolower(Configuration::get('PS_SHOP_NAME'))."/";
		}
		
		$category = Category::getLinkRewrite((int)$product['id_category_default'], (int)($cookie->id_lang));
		
		if($product['id_product_attribute'])
			$product_link = $link->getProductLink((int)$product['id_product'], $product['id_product_attribute'] , $product['link_rewrite'], $category, $product['ean13']);
		else
			$product_link = $link->getProductLink((int)$product['id_product'],NULL, $product['link_rewrite'], $category, $product['ean13']);
		//$item_url .= Language::getLanguage($cookie->id_lang)."/".$product['category_default'];
		
		/*if($product['id_product_attribute'])
			$item_url .= $product['id_product']."-".$product['id_product_attribute']."-".$product['link_rewrite'].".html";
		else
			$item_url .= $product['id_product']."-".$product['link_rewrite'].".html";*/
		
		
		$thumbImage_url .= $product['id_product']."-".$product['id_image']."-prodthumb/".$product['link_rewrite'].".jpg";
		$mediumImage_url .= $product['id_product']."-".$product['id_image']."-medium/".$product['link_rewrite'].".jpg";
		
		if(/*$product['quantity'] > 0 AND */$product['active'] AND $product['combination_active'])
			$availability = 1;
		else
			$availability = 0;
		
		if($product['id_product_attribute'])
			$attribute = $product_attributes[$product['id_product_attribute']];
		else
			$attribute = "NULL";
		
		fputcsv($fp, array($product['id_product'], $product['category_default'], $product['id_product_attribute'], $product['name'], number_format($product['order_price'], 2, '.', ''), /*$attribute,*/ $availability, $product_link,  $thumbImage_url, $mediumImage_url));
	}

	fclose($fp);

	//Writing Transactions details into the CSV file
	$fp = fopen(_PS_DOWNLOAD_DIR_.'gc/transactions_'.date("Y-m-d").'.csv', 'w');
	fputcsv($fp, array('order_id', 'user_id', 'product_id', 'product_attribute_id', 'product_quantity', 'order_amount', 'order_datetime'));

	$orders_sql = '
			SELECT o.`id_order` as order_id, o.`id_customer` as user_id, o.`total_paid_real` as order_amount , od.`product_id`, od.`product_attribute_id`, od.`product_quantity`, o.`date_add` as order_datetime
			FROM `'._DB_PREFIX_.'orders` o
			LEFT JOIN `'._DB_PREFIX_.'order_detail` od ON (od.`id_order` = o.`id_order`)
			WHERE  o.`valid`=1
			ORDER BY o.`id_order` DESC';
	$orders = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($orders_sql);

	foreach($orders as $order)
	{	
		if($order['product_attribute_id'])
			$attribute = $product_attributes[$order['product_attribute_id']];
		else
			$attribute = "NULL";
		
		fputcsv($fp, array($order['order_id'], $order['user_id'], $order['product_id'], $attribute, $order['product_quantity'], $order['order_amount'], $order['order_datetime']));
	}

	fclose($fp);
}
else
{
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");

	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");

	header("Location: ../");
	exit;
}

?>
