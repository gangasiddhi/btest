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
	$customer = new Customer(intval($cookie->id_customer));
	$customer_style = $customer->getCategory();

	// reorder customer shoes
	Product::reorderPositions( $customer_style['shoes_cat_id']);

	/*
	// reorder shoes	of the presnt month and last three months except to the shoes belonging to the customer style category
	// Get See More shoes ids - for current month, other than customer's style
	$category_seemore_shoes = explode("-",$customer_style['shoes_seemore_cat_id']);
	foreach($category_seemore_shoes as $category)
	{
		Product::reorderPositions($category);
	}

	// reorder Featured shoes
	// Get Featured shoes for the month
	Product::reorderPositions( $customer_style['shoes_featured_cat_id']);

	// reorder Handbags
	// Get Handbags for the month
	Product::reorderPositions( $customer_style['handbags_cat_id']);

	// reorder See More handbags
	// Get additional See More handbags - for last 3 months, other than current month
	$category_handbags_prev = explode("-",$customer_style['handbags_seemore_cat_id']);
	foreach($category_handbags_prev as $category_handbags)
	{
		Product::reorderPositions($category_handbags);
	}

	// reorder Jewelry
	// Get Jewelry for the month
	Product::reorderPositions($customer_style['jewelry_cat_id']);

	// reorder See More jewelry
	// Get See More jewelry - for last 3 months, other than current month
	$category_jewelry_prev = explode("-",$customer_style['jewelry_seemore_cat_id']);
	foreach($category_jewelry_prev as $category_jewelry)
	{
		Product::reorderPositions($customer_style['jewelry_cat_id']);
	}
	*/
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
