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
class VoucherControllerCore extends FrontController {
    public $auth = true;
    public $php_self = 'vouchers.php';
    public $authRedirection = 'vouchers.php';
    public $ssl = true;

    public function process() {
        parent::process();

        $pageNo = (Tools::getValue('pageno') ? Tools::getValue('pageno') : 1 );
        $itemPerPage = Configuration::get('VOUCHERS_PAGE_ITEM_LIMIT');

        $vouchers = Discount::getCustomerDiscounts(
            (int) self::$cookie->id_lang,
            (int) self::$cookie->id_customer,
            false, // also get the disabled ones
            false, // don't get the generic ones
            false, // stock
            true, // Pagination
            $itemPerPage,
            $pageNo
        );

        $totalItem = $vouchers['totalItem'];

        unset($vouchers['totalItem']);

        self::$smarty->assign(array(
            'vouchers' => $vouchers,
            'paginationParams' => array(
                'pageNo' => $pageNo,
                'itemPerPage' => $itemPerPage,
                'totalItem' => $totalItem
            )
        ));
    }

    public function setMedia() {
        parent::setMedia();

        Tools::addCSS(_THEME_CSS_DIR_ . 'vouchers.css');
        Tools::addCSS(_THEME_CSS_DIR_ . 'my-acc-sidebar.css');
        Tools::addJS(_THEME_JS_DIR_ . 'pagination/jquery.pagination.js');
        Tools::addCSS(_THEME_JS_DIR_ . 'pagination/pagination.css');
    }

    public function displayContent() {
        parent::displayContent();

        self::$smarty->display(_PS_THEME_DIR_ . 'vouchers.tpl');
    }
}
