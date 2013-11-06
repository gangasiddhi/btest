<?php

/**
 * Description of Product
 *
 * This is overide class of Product
 *
 * @author gangadhar
 */
class Product extends ProductCore
{

    public function isProductCanBackOrder($productId)
    {
        $sql = 'SELECT p.out_of_stock
                FROM `' . _DB_PREFIX_ . 'product` p
                WHERE p.`id_product` = ' . $productId;
        return Db::getInstance()->getRow($sql);
    }

    public function removeProductFromBackOrder($productId)
    {
        $sql = 'UPDATE `' . _DB_PREFIX_ . 'product` SET `out_of_stock` = 0 WHERE `id_product` = ' . $productId;
        return Db::getInstance()->Execute($sql);
    }

    public function isProductHaveCustomerShoeSize($id_lang, $productId, $customerShoeSize)
    {
        $sql = 'SELECT p.`id_product`, p.`id_category_default`, pa.`quantity`,
                    FROM `' . _DB_PREFIX_ . 'product` p
                    LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute` pa ON (p.`id_product` = pa.`id_product` AND pa.active = 1 AND pa.`quantity` >= 1 )
                    LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute_combination` pac on (pac.`id_product_attribute` = pa.`id_product_attribute`)
                    LEFT JOIN `' . _DB_PREFIX_ . 'attribute` a on (pac.`id_attribute` = a.`id_attribute`)
                    LEFT JOIN `' . _DB_PREFIX_ . 'attribute_lang` al ON (al.`id_attribute` = a.`id_attribute` AND al.`id_lang` = ' . (int) $id_lang . ')
                    WHERE p.`id_product` = ' . $productId . ' AND al.name IN (' . $customerShoeSize . ')';

        return Db::getInstance()->getRow($sql);
    }

    public function isProductInCustomerFavouriteColor($id_lang, $productId, $customerFavouriteColors)
    {
        $sql = 'SELECT p.`id_product`, p.`id_category_default`, pa.`quantity`,
                    FROM `' . _DB_PREFIX_ . 'product` p
                    LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute` pa ON (p.`id_product` = pa.`id_product` AND pa.active = 1 AND pa.`quantity` >= 1 )
                    LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute_combination` pac on (pac.`id_product_attribute` = pa.`id_product_attribute`)
                    LEFT JOIN `' . _DB_PREFIX_ . 'attribute` a on (pac.`id_attribute` = a.`id_attribute`)
                    LEFT JOIN `' . _DB_PREFIX_ . 'attribute_lang` al ON (al.`id_attribute` = a.`id_attribute` AND al.`id_lang` = ' . (int) $id_lang . ')
                    WHERE p.`id_product` = ' . $productId . ' AND al.name IN (' . $customerFavouriteColors . ')';

        return Db::getInstance()->getRow($sql);
    }


    /**
    * Get product accessories
    *
    * @param integer $id_lang Language id
    * @return array Product accessories
    */
    public function getRecommendProductDetails($id_lang, $recommendProductIdsList)
    {
        $sql = '
            SELECT p.*, pl.`description`,
                pl.`description_short`,
                pl.`link_rewrite`,
                pl.`meta_description`,
                pl.`meta_keywords`,
                pl.`meta_title`,
                pl.`name`,
                p.`ean13`,
                p.`upc`,
                i.`id_image`,
                il.`legend`,
                t.`rate`,
                m.`name` as manufacturer_name,
                cl.`name` AS category_default,
                DATEDIFF(p.`date_add`, DATE_SUB(NOW(), INTERVAL '
                    . (Validate::isUnsignedInt(Configuration::get('PS_NB_DAYS_NEW_PRODUCT')) ? Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20)
                    . ' DAY)) > 0 AS new,
                (
                    SELECT SUM(quantity)
                    FROM `bu_product_attribute` pa
                    WHERE pa.`id_product` = p.`id_product`
                ) AS quantity
            FROM `' . _DB_PREFIX_ . 'product` p
            LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (p.`id_product` = pl.`id_product` AND pl.`id_lang` = ' . (int)($id_lang) . ')
            LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl ON (p.`id_category_default` = cl.`id_category` AND cl.`id_lang` = ' . (int)($id_lang) . ')
            LEFT JOIN `' . _DB_PREFIX_ . 'image` i ON (i.`id_product` = p.`id_product` AND i.`cover` = 1)
            LEFT JOIN `' . _DB_PREFIX_ . 'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = ' . (int)($id_lang) . ')
            LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` m ON (p.`id_manufacturer`= m.`id_manufacturer`)
            LEFT JOIN `' . _DB_PREFIX_ . 'tax_rule` tr ON (p.`id_tax_rules_group` = tr.`id_tax_rules_group`
               AND tr.`id_country` = ' . (int)Country::getDefaultCountryId() . '
               AND tr.`id_state` = 0)
            LEFT JOIN `' . _DB_PREFIX_ . 'tax` t ON (t.`id_tax` = tr.`id_tax`)
            WHERE ' . (! empty($recommendProductIdsList) ? 'p.`id_product` IN (' . $recommendProductIdsList . ') AND' : '') . ' p.`active` = 1' ;

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql);

        if (!$result)
            return false;

        return $this->getProductsProperties($id_lang, $result);
    }

    public function getShoeSizeByProductAttributeId($langId, $productAttributeId){
        $query = 'SELECT al.name
                  FROM `' . _DB_PREFIX_ . 'product_attribute_combination` pac
                  LEFT JOIN `' . _DB_PREFIX_ . 'attribute_lang` al ON (al.id_attribute = pac.id_attribute AND al.id_lang = '.$langId.')
                  WHERE pac.id_product_attribute = '.$productAttributeId;

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($query);

        return $result['name'];
    }

    public function getProductQuantityOfParticularCombination($productId, $productAttributeId){
        $query = 'SELECT pa.`quantity`
                  FROM `'._DB_PREFIX_.'product_attribute` pa
                  WHERE pa.id_product = '.$productId.' AND pa.`id_product_attribute` = '.(int)($productAttributeId);

        $result = Db::getInstance()->getRow($query);

        return $result['quantity'];
    }

    public function isAnyofTheProductCombinationIsOutOfStock($productId){
        $query = 'SELECT pa.id_product , pa.id_product_attribute, pa.`quantity`
                  FROM `'._DB_PREFIX_.'product_attribute` pa
                  WHERE pa.id_product = '.$productId;

        $results = Db::getInstance()->ExecuteS($query);

        foreach($results as $result){
            if($result['quantity'] <= 0){
                return 1;
            }
        }
        return 0;
    }

    public static function getTotalBackOrderQuantityByReference($reference) {
        $sql = "
            SELECT COUNT(bod.`product_quantity`) AS `total_quantity`
            FROM `bu_order_detail` bod
            JOIN `bu_orders` bo ON bo.`id_order` = bod.`id_order`
            WHERE bod.`product_reference` = '" . $reference . "' AND " . _PS_OS_BACK_ORDER_ . " = (
                    SELECT id_order_state
                    FROM `bu_order_history` oh
                    WHERE oh.`id_order` = bo.`id_order`
                    ORDER BY oh.`date_add` DESC,
                        oh.`id_order_history` DESC
                    LIMIT 1
                )
            GROUP BY bod.`product_reference`
        ";

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
    }
    
    public static function pIdBelongToCategoryId($cart_products, $category_id)
	{
        foreach($cart_products as $cart_product) {
             $cart_products_array[]=$cart_product['id_product'];
         }
         $productIds= implode(',', $cart_products_array);
         
		$sql = 'SELECT id_product FROM `' . _DB_PREFIX_ . 'category_product` WHERE `id_product` IN(' . $productIds.') AND `id_category`='.(int)($category_id);
        
        $results = Db::getInstance()->ExecuteS($sql);
        
        /*echo'<br>hh<pre>';print_r($results);echo'<pre>';*/
        
        return $results[0]['id_product'];
	}
}

?>
