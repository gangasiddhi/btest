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
 *  @version  Release: $Revision: 7540 $
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

class ProductSaleCore {
    /*
     * * Fill the `product_sale` SQL table with data from `order_detail`
     * * @return bool True on success
     */

    public static function fillProductSales() {
        return Db::getInstance()->Execute('
		REPLACE INTO ' . _DB_PREFIX_ . 'product_sale
		(`id_product`, `quantity`, `sale_nbr`, `date_upd`)
		SELECT od.product_id, COUNT(od.product_id), SUM(od.product_quantity), NOW()
					FROM ' . _DB_PREFIX_ . 'order_detail od GROUP BY od.product_id');
    }

    /*
     * * Get number of actives products sold
     * * @return int number of actives products listed in product_sales
     */

    public static function getNbSales() {
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
			SELECT COUNT(ps.`id_product`) AS nb
			FROM `' . _DB_PREFIX_ . 'product_sale` ps
			LEFT JOIN `' . _DB_PREFIX_ . 'product` p ON p.`id_product` = ps.`id_product`
			WHERE p.`active` = 1');
        return (int) ($result['nb']);
    }

    /*
     * * Get required informations on best sales products
     * *
     * * @param integer $id_lang Language id
     * * @param integer $pageNumber Start from (optional)
     * * @param integer $nbProducts Number of products to return (optional)
     * * @return array from Product::getProductProperties
     */

    public static function getBestSales($id_lang, $pageNumber = 0, $nbProducts = 10, $orderBy = NULL, $orderWay = NULL) {
        if ($pageNumber < 0)
            $pageNumber = 0;
        if ($nbProducts < 1)
            $nbProducts = 10;
        if (empty($orderBy) || $orderBy == 'position')
            $orderBy = 'sales';
        if (empty($orderWay))
            $orderWay = 'DESC';

        $groups = FrontController::getCurrentCustomerGroups();
        $sqlGroups = (count($groups) ? 'IN (' . implode(',', $groups) . ')' : '= 1');

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT p.*,
			pl.`description`, pl.`description_short`, pl.`link_rewrite`, pl.`meta_description`, pl.`meta_keywords`, pl.`meta_title`, pl.`name`, m.`name` AS manufacturer_name, p.`id_manufacturer` as id_manufacturer,
			i.`id_image`, il.`legend`,
			ps.`quantity` AS sales, t.`rate`, pl.`meta_keywords`, pl.`meta_title`, pl.`meta_description`,
			DATEDIFF(p.`date_add`, DATE_SUB(NOW(), INTERVAL ' . (Validate::isUnsignedInt(Configuration::get('PS_NB_DAYS_NEW_PRODUCT')) ? Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20) . ' DAY)) > 0 AS new
		FROM `' . _DB_PREFIX_ . 'product_sale` ps
		LEFT JOIN `' . _DB_PREFIX_ . 'product` p ON ps.`id_product` = p.`id_product`
		LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (p.`id_product` = pl.`id_product` AND pl.`id_lang` = ' . (int) ($id_lang) . ')
		LEFT JOIN `' . _DB_PREFIX_ . 'image` i ON (i.`id_product` = p.`id_product` AND i.`cover` = 1)
		LEFT JOIN `' . _DB_PREFIX_ . 'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = ' . (int) ($id_lang) . ')
		LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` m ON (m.`id_manufacturer` = p.`id_manufacturer`)
		LEFT JOIN `' . _DB_PREFIX_ . 'tax_rule` tr ON (p.`id_tax_rules_group` = tr.`id_tax_rules_group`
		                                           AND tr.`id_country` = ' . (int) Country::getDefaultCountryId() . '
	                                           	   AND tr.`id_state` = 0)
	    LEFT JOIN `' . _DB_PREFIX_ . 'tax` t ON (t.`id_tax` = tr.`id_tax`)
		WHERE p.`active` = 1
		AND p.`id_product` IN (
			SELECT cp.`id_product`
			FROM `' . _DB_PREFIX_ . 'category_group` cg
			LEFT JOIN `' . _DB_PREFIX_ . 'category_product` cp ON (cp.`id_category` = cg.`id_category`)
			WHERE cg.`id_group` ' . $sqlGroups . '
		)
		ORDER BY ' . (isset($orderByPrefix) ? $orderByPrefix . '.' : '') . '`' . pSQL($orderBy) . '` ' . pSQL($orderWay) . '
		LIMIT ' . (int) ($pageNumber * $nbProducts) . ', ' . (int) ($nbProducts));

        if ($orderBy == 'price')
            Tools::orderbyPrice($result, $orderWay);
        if (!$result)
            return false;
        return Product::getProductsProperties($id_lang, $result);
    }

    /*
     * * Get required informations on best sales products
     * *
     * * @param integer $id_lang Language id
     * * @param integer $pageNumber Start from (optional)
     * * @param integer $nbProducts Number of products to return (optional)
     * * @return array keys : id_product, link_rewrite, name, id_image, legend, sales, ean13, upc, link
     */

    public static function getBestSalesProductName($id_lang, $id_product) {
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
			SELECT  pl.`name`, pl.`id_product` , pl.`link_rewrite`, pa.`id_product_attribute` , IF(pa.default_image, pa.`default_image` ,i.`id_image`) as id_image
			FROM `' . _DB_PREFIX_ . 'product_lang` pl
			LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute` pa ON (pa.`id_product` = pl.`id_product` AND pa.`default_on` = 1)
			LEFT JOIN `' . _DB_PREFIX_ . 'image` i ON (i.`id_product` = pl.`id_product` AND IF(pa.default_image ,\'  \', i.`cover` = 1))
			WHERE pl.`id_lang` = ' . $id_lang . ' AND pl.`id_product` = ' . $id_product
        );
        return $result;
    }

    public static function getBestSalesProduct($id_lang, $id_product) {
        global $link;

        $groups = FrontController::getCurrentCustomerGroups();
        $sqlGroups = (count($groups) ? 'IN (' . implode(',', $groups) . ')' : '= 1');

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT p.id_product, pa.`id_product_attribute` , IF(pa.default_image, pa.`default_image` ,i.`id_image`) as id_image , pl.`link_rewrite`, pl.`name`, pl.`description_short`,  il.`legend`, ps.`quantity` AS sales, p.`ean13`, p.`upc`, cl.`link_rewrite` AS category
		FROM `' . _DB_PREFIX_ . 'product_sale` ps
		LEFT JOIN `' . _DB_PREFIX_ . 'product` p ON ps.`id_product` = p.`id_product`
		LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute` pa ON (pa.`id_product` = p.`id_product` AND pa.`default_on` = 1 AND p.`id_color_default` = 2)
		LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (p.`id_product` = pl.`id_product` AND pl.`id_lang` = ' . (int) $id_lang . ')
		LEFT JOIN `' . _DB_PREFIX_ . 'image` i ON (i.`id_product` = p.`id_product` AND IF(pa.default_image ,\'  \', i.`cover` = 1))
		LEFT JOIN `' . _DB_PREFIX_ . 'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = ' . (int) $id_lang . ')
		LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl ON (cl.`id_category` = p.`id_category_default` AND cl.`id_lang` = ' . (int) $id_lang . ')
		WHERE p.`active` = 1 AND p.`id_product` = ' . $id_product . ' AND p.`date_add` <= \'' . date("Y-m-d H:i:s", strtotime("-1 days")) . '\'
		AND p.`id_product` IN (
			SELECT cp.`id_product`
			FROM `' . _DB_PREFIX_ . 'category_group` cg
			LEFT JOIN `' . _DB_PREFIX_ . 'category_product` cp ON (cp.`id_category` = cg.`id_category`)
			WHERE cg.`id_group` ' . $sqlGroups . '
		)
		ORDER BY sales DESC');


        if (!$result)
            return false;

        foreach ($result AS &$row) {
            $row['link'] = $link->getProductLink($row['id_product'], $row['id_product_attribute'], $row['link_rewrite'], $row['category'], $row['ean13']);
            $row['id_image'] = Product::defineProductImage($row, $id_lang);
        }
        return $result;
    }

    public static function getBestSalesLight($id_lang, $pageNumber = 0, $nbProducts = 10) {
        global $link;

        if ($pageNumber < 0)
            $pageNumber = 0;
        if ($nbProducts < 1)
            $nbProducts = 10;

        $groups = FrontController::getCurrentCustomerGroups();
        $sqlGroups = (count($groups) ? 'IN (' . implode(',', $groups) . ')' : '= 1');

        $start = date("Y-m-d H:i:s", strtotime("today"));
        $end = strtotime($start) + (3600 * 24) - 1;
        $end = date("Y-m-d H:i:s", $end);
        $lastsevendays = date("Y-m-d H:i:s", strtotime("-7days"));

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT p.id_product, pa.`id_product_attribute` , IF(pa.default_image, pa.`default_image` ,i.`id_image`) as id_image , pl.`link_rewrite`, pl.`name`, pl.`description_short`,  il.`legend`, ps.`quantity` AS sales, p.`ean13`, p.`upc`, cl.`link_rewrite` AS category
		FROM `' . _DB_PREFIX_ . 'product_sale` ps
		LEFT JOIN `' . _DB_PREFIX_ . 'product` p ON ps.`id_product` = p.`id_product`
		LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute` pa ON (pa.`id_product` = p.`id_product` AND pa.`default_on` = 1 AND p.`id_color_default` = 2)
		LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (p.`id_product` = pl.`id_product` AND pl.`id_lang` = ' . (int) $id_lang . ')
		LEFT JOIN `' . _DB_PREFIX_ . 'image` i ON (i.`id_product` = p.`id_product` AND IF(pa.default_image ,\'  \', i.`cover` = 1))
		LEFT JOIN `' . _DB_PREFIX_ . 'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = ' . (int) $id_lang . ')
		LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl ON (cl.`id_category` = p.`id_category_default` AND cl.`id_lang` = ' . (int) $id_lang . ')
		WHERE p.`active` = 1 AND  ps.`date_upd` >= \'' . ($start) . '\' AND ps.`date_upd` <= \'' . ($end) . '\'
		AND p.`id_product` IN (
			SELECT cp.`id_product`
			FROM `' . _DB_PREFIX_ . 'category_group` cg
			LEFT JOIN `' . _DB_PREFIX_ . 'category_product` cp ON (cp.`id_category` = cg.`id_category`)
			WHERE cg.`id_group` ' . $sqlGroups . '
		)
		ORDER BY sales DESC
		LIMIT ' . (int) ($pageNumber * $nbProducts) . ', ' . (int) ($nbProducts));
        $todayProdId = '';
        foreach ($result AS $row) {
            $todayProdId .= (int) ($row['id_product']) . ',';
        }
        $todayProdId = rtrim($todayProdId, ',');
        if (sizeof($result) < 5) {
            $lastsevendays = date("Y-m-d H:i:s", strtotime("-7days"));
            $result1 = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
                    SELECT p.id_product, pa.`id_product_attribute` , IF(pa.default_image, pa.`default_image` ,i.`id_image`) as id_image , pl.`link_rewrite`, pl.`name`, pl.`description_short`,  il.`legend`, ps.`quantity` AS sales, p.`ean13`, p.`upc`, cl.`link_rewrite` AS category
                    FROM `' . _DB_PREFIX_ . 'product_sale` ps
                    LEFT JOIN `' . _DB_PREFIX_ . 'product` p ON ps.`id_product` = p.`id_product`
                    LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute` pa ON (pa.`id_product` = p.`id_product` AND pa.`default_on` = 1 AND p.`id_color_default` = 2)
                    LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (p.`id_product` = pl.`id_product` AND pl.`id_lang` = ' . (int) $id_lang . ')
                    LEFT JOIN `' . _DB_PREFIX_ . 'image` i ON (i.`id_product` = p.`id_product` AND IF(pa.default_image ,\'  \', i.`cover` = 1))
                    LEFT JOIN `' . _DB_PREFIX_ . 'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = ' . (int) $id_lang . ')
                    LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl ON (cl.`id_category` = p.`id_category_default` AND cl.`id_lang` = ' . (int) $id_lang . ')
                    WHERE p.`active` = 1 AND  ps.`date_upd` >= \'' . ($lastsevendays) . '\' AND ps.`date_upd` <= \'' . ($end) . '\'
                    AND p.`id_product` IN (
                            SELECT cp.`id_product`
                            FROM `' . _DB_PREFIX_ . 'category_group` cg
                            LEFT JOIN `' . _DB_PREFIX_ . 'category_product` cp ON (cp.`id_category` = cg.`id_category`)
                            WHERE cg.`id_group` ' . $sqlGroups . '
                    ) AND p.`id_product` NOT IN (' . pSQL($todayProdId) . ')
                    ORDER BY sales DESC
                    LIMIT ' . (int) ($pageNumber * $nbProducts) . ', ' . (int) ($nbProducts));

            if ($result1) {
                $today = sizeof($result);
                $lastseven = 5 - $today;

                foreach ($result1 AS $key => $row) {

                    if ($key < $lastseven) {
                        $last_7_products[] = $row;
                    }
                }

                $final_five = array_merge($result, $last_7_products);
                foreach ($final_five AS $key => &$row) {
                    $row['link'] = $link->getProductLink($row['id_product'], $row['id_product_attribute'], $row['link_rewrite'], $row['category'], $row['ean13']);
                    $row['id_image'] = Product::defineProductImage($row, $id_lang);
                }
                return $final_five;
            } else {
                return false;
            }
        } else {
            if (!$result)
                return false;
            foreach ($result AS &$row) {
                $row['link'] = $link->getProductLink($row['id_product'], $row['id_product_attribute'], $row['link_rewrite'], $row['category'], $row['ean13']);
                $row['id_image'] = Product::defineProductImage($row, $id_lang);
            }
            return $result;
        }
    }

    public static function addProductSale($product_id, $qty = 1) {
        return Db::getInstance()->Execute('
			INSERT INTO ' . _DB_PREFIX_ . 'product_sale
			(`id_product`, `quantity`, `sale_nbr`, `date_upd`)
			VALUES (' . (int) ($product_id) . ', ' . (int) ($qty) . ', 1, NOW())
			ON DUPLICATE KEY UPDATE `quantity` = `quantity` + ' . (int) ($qty) . ', `sale_nbr` = `sale_nbr` + 1, `date_upd` = NOW()');
    }

    public static function getNbrSales($id_product) {
        $result = Db::getInstance()->getRow('SELECT `sale_nbr` FROM ' . _DB_PREFIX_ . 'product_sale WHERE `id_product` = ' . (int) ($id_product));
        if (!$result OR empty($result) OR !key_exists('sale_nbr', $result))
            return -1;
        return (int) ($result['sale_nbr']);
    }

    public static function removeProductSale($id_product, $qty = 1) {
        $nbrSales = self::getNbrSales($id_product);
        if ($nbrSales > 1)
            return Db::getInstance()->Execute('UPDATE ' . _DB_PREFIX_ . 'product_sale SET `quantity` = `quantity` - ' . (int) ($qty) . ', `sale_nbr` = `sale_nbr` - 1, `date_upd` = NOW() WHERE `id_product` = ' . (int) ($id_product));
        elseif ($nbrSales == 1)
            return Db::getInstance()->Execute('DELETE FROM ' . _DB_PREFIX_ . 'product_sale WHERE `id_product` = ' . (int) ($id_product));
        return true;
    }

    /* To get the Todays best Sales , if not get the last 7 days best sales
     * Param $id_lang is the Language.
     * Param $pageNumber is the lower limit for the selected products
     * Param $nbProducts is the Upper limit of the selected products.
     */

    public static function getTodayBestSales($id_lang, $pageNumber = 0, $nbProducts = 5) {

        global $link;

        $start = date("Y-m-d H:i:s", strtotime("today"));
        $end = strtotime($start) + (3600 * 24) - 1;
        $end = date("Y-m-d H:i:s", $end);
        $lastsevendays = date("Y-m-d H:i:s", strtotime("today") - (3600 * 24 * 7));

        $result = ProductSaleCore::getProductBestSales($id_lang, $start, $end, $pageNumber , $nbProducts );

        if (sizeof($result) < 5) {
            $start = $lastsevendays;
            $result = ProductSaleCore::getProductBestSales($id_lang, $start, $end, $pageNumber , $nbProducts );
        }

		/*TODO, Please replace the NULL with $row['id_product_attribute'], when multiple color combination are used in a single product_id*/
        foreach ($result AS &$row) {
            $row['link'] = $link->getProductLink($row['id_product'], NULL, $row['link_rewrite'], $row['category'], $row['ean13']);
            $row['id_image'] = Product::defineProductImage($row, $id_lang);
        }

        return $result;
    }

    /* To get the best sales between the start and end days
     * Param $id_lang is the Language.
     * Param $start is Date (From date).
     * Param $end is Date (end date).
     * Param $pageNumber is the lower limit for the selected products
     * Param $nbProducts is the Upper limit of the selected products.
     */

    public static function getProductBestSales($id_lang, $start, $end, $pageNumber = 0, $nbProducts = 5) {

        $query = 'SELECT p.id_product, pl.name, p.`id_category_default`, pa.`id_product_attribute` , IF(pa.default_image, pa.`default_image` ,i.`id_image`) as id_image , pl.`link_rewrite`, pl.`name`, pl.`description`, pl.`description_short`,  il.`legend`, p.`ean13`, p.`upc`, cl.`link_rewrite` AS category,(
                    SELECT COUNT(*)
                    FROM ' . _DB_PREFIX_ . 'image i
                    WHERE i.id_product = p.id_product
                    ) as nbImages, (
                            SELECT SUM(od.product_quantity)
                            FROM ' . _DB_PREFIX_ . 'orders o
                            LEFT JOIN ' . _DB_PREFIX_ . 'order_detail od ON o.id_order = od.id_order
                            WHERE od.product_id = p.id_product
                            AND o.date_add BETWEEN \'' . pSQL($start) . '\' AND  \'' . pSQL($end) . '\'
                    ) as nbSales, IFNULL((
                            SELECT SUM(pa.quantity)
                            FROM ' . _DB_PREFIX_ . 'product_attribute pa
                            WHERE pa.id_product = p.id_product
                    ), p.quantity) as stock
                    FROM ' . _DB_PREFIX_ . 'product p
                    LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute` pa ON (pa.`id_product` = p.`id_product` AND pa.`default_on` = 1 AND p.`id_color_default` = 2)
                    LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (p.`id_product` = pl.`id_product` AND pl.`id_lang` = ' . (int) $id_lang . ')
                    LEFT JOIN `' . _DB_PREFIX_ . 'image` i ON (i.`id_product` = p.`id_product` AND IF(pa.default_image ,\'  \', i.`cover` = 1))
                    LEFT JOIN `' . _DB_PREFIX_ . 'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = ' . (int) $id_lang . ')
                    LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl ON (cl.`id_category` = p.`id_category_default` AND cl.`id_lang` = ' . (int) $id_lang . ')
                    WHERE p.`active` = 1
                    GROUP BY p.id_product
                    HAVING nbSales >= 1
                    ORDER BY nbSales DESC
                    LIMIT ' . (int) ($pageNumber * $nbProducts) . ', ' . (int) ($nbProducts);

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($query);

        return $result;
    }

}

