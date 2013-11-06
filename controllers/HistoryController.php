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
*  @version  Release: $Revision: 7197 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class HistoryControllerCore extends FrontController
{
	public $auth = true;
	public $php_self = 'history.php';
	public $authRedirection = 'history.php';
	public $ssl = true;

	public function setMedia()
	{
		parent::setMedia();
		Tools::addCSS(_THEME_CSS_DIR_.'history.css');
		//Tools::addCSS(_THEME_CSS_DIR_.'addresses.css');
		Tools::addCSS(_THEME_CSS_DIR_.'my-acc-sidebar.css');
		Tools::addJS(array(
			_PS_JS_DIR_.'jquery/jquery.scrollTo.min.js',
			_THEME_JS_DIR_.'history.js',
			_THEME_JS_DIR_.'tools.js'));


		Tools::addJS(_THEME_JS_DIR_.'pagination/jquery.pagination.js');
		Tools::addCSS(_THEME_JS_DIR_.'pagination/pagination.css');
	}

	public function process()
	{
		parent::process();

		$pageNo = (Tools::getValue('pageno') ? Tools::getValue('pageno') : 1 );
		$itemPerPage = Configuration::get('ORDER_HISTORY_ORDER_ITEM_LIMIT');

		$orders = Order::getCustomerOrders((int)(self::$cookie->id_customer), false,
			true, $itemPerPage, $pageNo);

		foreach ($orders AS &$order) {
			$myOrder = new Order((int)($order['id_order']));
			if (Validate::isLoadedObject($myOrder)) {
				$order['virtual'] = $myOrder->isVirtual(false);
			}
		}

		$paginationParams = array(
			pageNo => $pageNo,
			itemPerPage => $itemPerPage,
			totalItem => $orders['totalItem']
        );

        unset($orders[totalItem]);

		self::$smarty->assign(array(
			'orders' => $orders,
			'invoiceAllowed' => (int)(Configuration::get('PS_INVOICE')),
			'slowValidation' => Tools::isSubmit('slowvalidation'),
			paginationParams => $paginationParams
		));
	}

	public function displayContent()
	{
		parent::displayContent();
		self::$smarty->display(_PS_THEME_DIR_.'history.tpl');
	}
}

