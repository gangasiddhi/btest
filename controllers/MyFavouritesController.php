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

class MyFavouritesControllerCore extends FrontController
{
	public $ssl = false;
	public $php_self = 'my-favourites.php';

	public function preProcess()
	{
		parent::preProcess();

		$from_sailthru_spider = $_SERVER["HTTP_USER_AGENT"] == "Sailthru Content Spider [Butigo/7960c2582bec87e53771387ab15dd345]" ? true : false;
		
		if (!$from_sailthru_spider) {
			if (!self::$cookie->isLogged())
				Tools::redirect('authentication.php?back=my-favourites.php');

			if (intval(self::$cookie->id_customer))
				$customer = new Customer(intval(self::$cookie->id_customer));
			else
				Tools::redirect('authentication.php?back=my-favourites.php');

			if (!Validate::isLoadedObject($customer))
				Tools::redirect('authentication.php?back=my-favourites.php');
		}
		
		self::$smarty->assign(array(
			'HOOK_MY_FAVOURITES' => Module::hookExec('myFavourites'),
			'HOOK_AFTER_MENU' => Module::hookExec('afterMenu'),
		));
	}


	public function setMedia()
	{
		parent::setMedia();
		Tools::addCSS(_THEME_CSS_DIR_.'showroom.css');
		Tools::addJS(array(
			_PS_JS_DIR_.'jquery/jquery.lazyloader.js'
			, _THEME_JS_DIR_.'hiw.js'
			, _THEME_JS_DIR_.'lookbook.js'
			,'http://connect.facebook.net/tr_TR/all.js#xfbml=1'
		));
//		Tools::addCSS(_THEME_CSS_DIR_.'referrals-friends.css' ,'all');
//		Tools::addCSS(__PS_BASE_URI__.'ct/ajax_ct.css','all');
//		Tools::addJS(array(_PS_JS_DIR_.'main.js',__PS_BASE_URI__.'ct/ajax_ct.js'));
	}

	public function displayContent()
	{
		parent::displayContent();
		self::$smarty->display(_PS_THEME_DIR_.'my-favourites.tpl');
	}

}

?>