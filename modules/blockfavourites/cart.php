<?php
/*
* 2007-2011 PrestaShop 
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you fav to upgrade PrestaShop to newer
* versions in the future. If you fav to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2011 PrestaShop SA
*  @version  Release: $Revision: 7706 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

require_once(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../init.php');
require_once(dirname(__FILE__).'/Favourite.php');
require_once(dirname(__FILE__).'/blockfavourites.php');

$action = Tools::getValue('action');
$add = (!strcmp($action, 'add') ? 1 : 0);
$delete = (!strcmp($action, 'delete') ? 1 : 0);
$id_favlist = (int)(Tools::getValue('id_favlist'));
$id_product = (int)(Tools::getValue('id_product'));
$quantity = (int)(Tools::getValue('quantity'));
$id_product_attribute = (int)(Tools::getValue('id_product_attribute'));

if (Configuration::get('PS_TOKEN_ENABLE') == 1 AND
	strcmp(Tools::getToken(false), Tools::getValue('token')) AND
	$cookie->isLogged() === true)
	echo Tools::displayError('Invalid token');

if ($cookie->isLogged())
{
	if ($id_favlist AND Favourite::exists($id_favlist, $cookie->id_customer) === true)
	{
		$cookie->id_favlist = (int)($id_favlist);
	}

	if($cus_fav_list_details = Favourite::getByIdCustomer((int)($cookie->id_customer), true))
	{
		$id_favlist = $cus_fav_list_details[0]['id_favlist'];
		$cookie->id_favlist = (int)($id_favlist);
	}
	
	if (($add OR $delete) AND empty($id_product) === false)
	{
		if (!isset($cookie->id_favlist) OR $cookie->id_favlist == '')
		{	
			$favlist = new Favourite();
			$modFavlist = new BlockFavourites();
			$favlist->name = $modFavlist->_default_favlist_name;
			$favlist->id_customer = (int)($cookie->id_customer);
			list($us, $s) = explode(' ', microtime());
			srand($s * $us);
			$favlist->token = strtoupper(substr(sha1(uniqid(rand(), true)._COOKIE_KEY_.$cookie->id_customer), 0, 16));
			$favlist->add();
			$cookie->id_favlist = (int)($favlist->id);
		}
		if ($add AND $quantity)
			Favourite::addProduct($cookie->id_favlist, $cookie->id_customer, $id_product, $id_product_attribute, $quantity);
		elseif ($delete)
			Favourite::removeProduct($cookie->id_favlist, $cookie->id_customer, $id_product, $id_product_attribute);
	}

	if (empty($cookie->id_favlist) === true OR $cookie->id_favlist == false)
	{
		$smarty->assign('error', true);
	}
	
	$smarty->assign('products', Favourite::getProductByIdCustomer($cookie->id_favlist, $cookie->id_customer, $cookie->id_lang, null, true));
	
	if (Tools::file_exists_cache(_PS_THEME_DIR_.'modules/blockfavourites/blockfavourites-ajax.tpl'))
		$smarty->display(_PS_THEME_DIR_.'modules/blockfavourites/blockfavourites-ajax.tpl');
	elseif (Tools::file_exists_cache(dirname(__FILE__).'/blockfavourites-ajax.tpl'))
		$smarty->display(dirname(__FILE__).'/blockfavourites-ajax.tpl');
	else
		echo Tools::displayError('No template found');
}
else
	echo Tools::displayError('You must be logged in to manage your favlist.');
