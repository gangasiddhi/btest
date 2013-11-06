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

include(dirname(__FILE__).'/config/config.inc.php');
include(dirname(__FILE__).'/init.php');

$cron_user = Tools::getValue('crnu');
$cron_pass = Tools::getValue('crnp');
if(!$cron_user)
	$cron_user = $argv[1];
if(!$cron_pass)
	$cron_pass = $argv[2];
$cron_pass = Tools::encrypt($cron_pass);

if($cron_user == _CRON_USER_ && $cron_pass == _CRON_PASSWD_)
{
	$category_month_id = intval( date('n') + 1);
	//echo  date('n')."--".$category_month_id;
	//$customer_style = $customer->getCategory();
	$featured_category_name = 'Featured';
	$discount_category_name = 'Discount';
	$shoes_category_name = 'Shoes';
	$handbags_category_name = 'Handbags';
	$jewelry_category_name = 'Jewelry';
	$more_category_exclusive = array($featured_category_name, $discount_category_name);
	$category_id = array();
	$customer_style=array('YAR', 'FEM', 'MOD', 'KLA', 'TRE');

	foreach($customer_style as $customer_cat_style)
	{
		// Get Personalized Style shoes - new working method
		$category_shoes = Category::searchByNameAndParentCategoryId(1, $shoes_category_name, $category_month_id);
		$category_shoes_id = $category_shoes['id_category'];
		echo "category_shoes_id=".$category_shoes_id."<br>\n";
		$category_shoes_style = Category::searchByNameAndParentCategoryId(1, $customer_cat_style, $category_shoes['id_category']);
		$category_shoes_style_id = $category_shoes_style['id_category'];
		echo "category_shoes_style_id=".$category_shoes_style_id."<br>\n";

		// Get See More shoes - for current month (MOD_MORE)
		unset($customer_cat_style_seemore);
		$customer_cat_style_seemore = $customer_cat_style."_MORE";
		$category_shoes_seemore_style = Category::searchByNameAndParentCategoryId(1, $customer_cat_style_seemore, $category_shoes['id_category']);
		$category_seemore_shoes_id = $category_shoes_seemore_style['id_category'];
		echo "category_seemore_shoes_id=".$category_seemore_shoes_id."<br>\n";

		// Get See More shoes - for current month, other than customer's style
		/*
		$category_shoes_children = Category::getChildren($category_shoes['id_category'], intval($cookie->id_lang));
		$more_shoes = array();
		unset($category_id);
		foreach ($category_shoes_children AS $category)
		{
			if( $category['id_category'] != $category_shoes_style['id_category']
				AND !in_array($category['name'], $more_category_exclusive) )
			{
				$category_id[] = $category['id_category'];
			}
		}
		$category_seemore_shoes_id = implode("-", $category_id);
		echo "category_seemore_shoes_id:".$category_seemore_shoes_id."<br>\n";

		// Get additional See More shoes - for last 3 months, other than customer's style
		unset($child_category);
		for( $prev_month_ctr = 1; $prev_month_ctr <= 3; $prev_month_ctr++ )
		{
			$category_shoes_prev = Category::searchByNameAndParentCategoryId(1, $shoes_category_name, $category_month_id - $prev_month_ctr);
			$category_shoes_style_prev = Category::searchByNameAndParentCategoryId(1, $customer_cat_style, $category_shoes_prev['id_category']);
			$category_shoes_children_prev = Category::getChildren($category_shoes_prev['id_category'], intval($cookie->id_lang));
			foreach ($category_shoes_children_prev AS $category)
			{
				if( $category['id_category'] != $category_shoes_style_prev['id_category']
					AND !in_array($category['name'], $more_category_exclusive) )
				{
					$child_category[] = $category['id_category'];
				}
			}
		}
		$category_additionalseemore_shoes_id = implode("-", $child_category);
		echo "category_additionalseemore_shoes: ".$category_additionalseemore_shoes_id."<br>\n";
		*/
		
		// Get Featured shoes for the month
		/*
		$category_featured_shoes = Category::searchByNameAndParentCategoryId(1, $featured_category_name, $category_shoes['id_category']);
		$category_featured_shoes_id = $category_featured_shoes['id_category'];
		echo "category_featured_shoes_id= ".$category_featured_shoes_id."<br>\n";
		 */

		// Get Handbags for the month
		$category_search_handbags = Category::searchByNameAndParentCategoryId(1, $handbags_category_name, $category_month_id);
		$category_search_handbags_id = $category_search_handbags['id_category'];
		echo "category_search_handbags_id= ".$category_search_handbags_id."<br>\n";

		// Get See More Handbags for the month
		unset($handbags_category_name_seemore);
		$handbags_category_name_seemore = $handbags_category_name."_more";
		$category_search_handbags_seemore = Category::searchByNameAndParentCategoryId(1, $handbags_category_name_seemore, $category_month_id);
		$category_additionalseemore_handbags_id = $category_search_handbags_seemore['id_category'];
		echo "category_seemore_handbags_id= ".$category_additionalseemore_handbags_id."<br>\n";

		
		// Get additional See More handbags - for last 3 months, other than current month
		/*
		$more_handbags = array();
		unset($category_handbags_prev_id);
		for( $prev_month_ctr = 1; $prev_month_ctr <= 3; $prev_month_ctr++ )
		{
			$category_handbags_prev = Category::searchByNameAndParentCategoryId(1, $handbags_category_name, $category_month_id - $prev_month_ctr);
			$category_handbags_prev_id[] = $category_handbags_prev['id_category'];
		}
		$category_additionalseemore_handbags_id = implode("-", $category_handbags_prev_id);
		echo "category_additionalseemore_bags: ".$category_additionalseemore_handbags_id."<br>\n";
		 */

		// Get Jewelry for the month
		$category_jewelry_search = Category::searchByNameAndParentCategoryId(1, $jewelry_category_name, $category_month_id);
		$category_jewelry_search_id=$category_jewelry_search['id_category'];
		echo "category_jewelry_search_id =".$category_jewelry_search_id."<br>\n";

		// Get See More Jewelry for the month
		unset($jewelry_category_name_seemore);
		$jewelry_category_name_seemore = $jewelry_category_name."_more";
		$category_jewelry_search_seemore = Category::searchByNameAndParentCategoryId(1, $jewelry_category_name_seemore, $category_month_id);
		$category_seemore_jewelry_id=$category_jewelry_search_seemore['id_category'];
		echo "category_seemore_jewelry_search_id =".$category_seemore_jewelry_id."<br>\n";
		echo "--------------------";
		// Get See More jewelry - for last 3 months, other than current month
		/*
		unset($category_jewelry_prev_id);
		for( $prev_month_ctr = 1; $prev_month_ctr <= 3; $prev_month_ctr++ )
		{
			$category_jewelry_prev = Category::searchByNameAndParentCategoryId(1, $jewelry_category_name, $category_month_id - $prev_month_ctr);
			$category_jewelry_prev_id[] = $category_jewelry_prev['id_category'];
		}
		$category_seemore_jewelry_id = implode("-",$category_jewelry_prev_id);
		echo "category_seemore_jewelry_id= ".$category_seemore_jewelry_id."<br>\n";
		*/

		$update_ids = array(
			//'category_shoes_id'				 => $category_shoes_id,
			'shoes_cat_id'					 => $category_shoes_style_id,
			'shoes_seemore_cat_id'			 => $category_seemore_shoes_id,
			//'shoes_seemore_cat_id'			 => $category_seemore_shoes_id."-".$category_additionalseemore_shoes_id,
			// 'shoes_additionalseemore_cat_id' => $category_additionalseemore_shoes_id,
			//'shoes_featured_cat_id'			 => $category_featured_shoes_id,
			'handbags_cat_id'				 => $category_search_handbags_id,
			'handbags_seemore_cat_id'		 => $category_additionalseemore_handbags_id,
			'jewelry_cat_id'			     => $category_jewelry_search_id,
			'jewelry_seemore_cat_id'		 => $category_seemore_jewelry_id
		);

		//Category::updateids($update_ids,$customer_cat_style);
		Category::updateShowroomCategoryIds($update_ids,$customer_cat_style);
	}
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
