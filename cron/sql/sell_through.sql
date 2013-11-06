CREATE TEMPORARY TABLE `sellThrough` (
    `NumberOfSoldItems` int(10) DEFAULT 0,
    `NumberOfItemsInStock` int(10) DEFAULT 0,
    `WontBeSoldAgainThisYear` tinyint(1) unsigned DEFAULT 0,
    `X` decimal(20,6) DEFAULT 0,
    `Y` decimal(20,6) DEFAULT 0
);

INSERT INTO `sellThrough`
    SELECT @NumberOfSoldItems := IFNULL(
            (
                SELECT SUM(bod.`product_quantity`)
                FROM bu_order_detail bod
                WHERE bod.`product_id` = bp.`id_product`
            ),
            0
        ) AS 'NumberOfSoldItems',
        @NumberOfItemsInStock := bp.`quantity` AS 'NumberOfItemsInStock',
        @WontBeSoldAgainThisYear := bp.`wont_be_sold_again_this_year` AS 'WontBeSoldAgainThisYear',
        @X := @NumberOfSoldItems * @WontBeSoldAgainThisYear AS 'X',
        @Y := (@NumberOfSoldItems + @NumberOfItemsInStock) * @WontBeSoldAgainThisYear AS 'Y'
    FROM `bu_product` bp;

SET @AVG = (
    SELECT SUM(X) / SUM(Y)
    FROM `sellThrough`
);

SELECT * INTO OUTFILE 'REPORT_PATH' FIELDS TERMINATED BY ';'
FROM (
    (
        SELECT 'ProductId', 'ProductName', 'ProductURL', 'FirstDayOfSale', 'LastDayOfSale',
            'NumberOfDaysOnSale', 'NumberOfItemsInCart', 'NumberOfSoldItems', 'NumberOfItemsInStock',
            'ProductCost', 'RevenueFromProduct', 'DesignQualityScore', 'WontBeSoldAgainThisYear',
            'WillBeSoldAgainNextYear', 'WontBeSoldAgain', 'SellThru', 'AVG'
    )
    UNION
    (
        SELECT bp.`id_product` AS 'ProductId',
            IFNULL(
                (
                    SELECT bpl.`name`
                    FROM `bu_product_lang` bpl
                    WHERE bpl.`id_lang` = 4 AND bpl.`id_product` = bp.`id_product`
                ),
                ''
            ) AS 'ProductName',
            IFNULL(
                (
                    SELECT CONCAT('/', IF(cl.`link_rewrite` != '', CONCAT(cl.`link_rewrite`, '/'), ''), p.`id_product`, '-', pl.`link_rewrite`, '.html')
                    FROM `bu_product` p
                    JOIN `bu_product_lang` pl ON pl.`id_product` = p.`id_product` AND pl.`id_lang` = 4
                    JOIN `bu_category_lang` cl ON cl.`id_category` = p.`id_category_default` AND cl.`id_lang` = 4
                    WHERE p.`id_product` = bp.`id_product`
                ),
                ''
            ) AS 'ProductURL',
            @FirstDayOfSale := IFNULL(
                (
                    SELECT MIN(bpi.`date_publish`)
                    FROM `bu_product_publish_info` bpi
                    WHERE bpi.`id_product` = bp.`id_product`
                ),
                IFNULL(bp.`date_of_last_publish`, '')
            ) AS 'FirstDayOfSale',
            IFNULL(
                IF(
                    bp.`wont_be_sold_again_this_year` OR bp.`will_be_sold_again_next_year` OR bp.`wont_be_sold_again`,
                    (
                        SELECT MAX(bpi.`date_unpublish`)
                        FROM `bu_product_publish_info` bpi
                        WHERE bpi.`id_product` = bp.`id_product`
                    ),
                    ''
                ),
                ''
            ) AS 'LastDayOfSale',
            IFNULL(
                IF(
                    bp.`date_of_last_publish` > (
                        SELECT MAX(bpi.`date_unpublish`)
                        FROM `bu_product_publish_info` bpi
                        WHERE bpi.`id_product` = bp.`id_product`
                    ),
                    (
                        SELECT SUM(bpi.`interval`)
                        FROM `bu_product_publish_info` bpi
                        WHERE bpi.`id_product` = bp.`id_product`
                    ) + (DATEDIFF(NOW(), bp.`date_of_last_publish`)),
                    IFNULL(
                        (
                            SELECT SUM(bpi.`interval`)
                            FROM `bu_product_publish_info` bpi
                            WHERE bpi.`id_product` = bp.`id_product`
                        ),
                        (DATEDIFF(NOW(), bp.`date_of_last_publish`))
                    )
                ),
                ''
            ) AS 'NumberOfDaysOnSale',
            (
                SELECT COUNT(`id_cart`)
                FROM `bu_cart_product`
                WHERE `id_product` = bp.`id_product`
                    AND `date_add` >= @FirstDayOfSale
            ) AS 'NumberOfItemsInCart',
            @NumberOfSoldItems := IFNULL(
                (
                    SELECT SUM(bod.`product_quantity`)
                    FROM `bu_order_detail` bod
                    JOIN `bu_orders` bo ON bo.`id_order` = bod.`id_order`
                    WHERE bod.`product_id` = bp.`id_product`
                        AND bo.`date_add` >= @FirstDayOfSale
                ),
                0
            ) AS 'NumberOfSoldItems',
            @NumberOfItemsInStock := bp.`quantity` AS 'NumberOfItemsInStock',
            IF(
                bp.`wholesale_price` > 0,
                (
                    REPLACE(REPLACE(REPLACE(FORMAT(
                        bp.`wholesale_price` * IF(bp.`id_product_type` IN (3, 6), 1.18, 1.08),
                        2
                    ), ".", "@"), ",", "."), "@", ",")
                ),
                REPLACE(REPLACE(REPLACE(FORMAT(0, 2), ".", "@"), ",", "."), "@", ",")
            ) AS 'ProductCost',
            (
                SELECT IFNULL(
                    REPLACE(REPLACE(REPLACE(FORMAT(SUM(
                        bod.`product_price` * IF(bp.`id_product_type` IN (3, 6), 1.18, 1.08) * bod.`product_quantity`
                    ), 2), ".", "@"), ",", "."), "@", ","),
                    REPLACE(REPLACE(REPLACE(FORMAT(0, 2), ".", "@"), ",", "."), "@", ",")
                )
                FROM `bu_order_detail` bod
                JOIN `bu_orders` bo ON bo.`id_order` = bod.`id_order`
                WHERE bo.`valid` = 1
                    AND bod.`product_id` = bp.`id_product`
                    AND bo.`date_add` >= @FirstDayOfSale
            ) AS 'RevenueFromProduct',
            IF(
                bp.`design_quality_score` IS NOT NULL AND bp.`design_quality_score` != 0,
                bp.`design_quality_score`,
                ''
            ) AS 'DesignQualityScore',
            @WontBeSoldAgainThisYear := bp.`wont_be_sold_again_this_year` AS 'WontBeSoldAgainThisYear',
            bp.`will_be_sold_again_next_year` AS 'WillBeSoldAgainNextYear',
            bp.`wont_be_sold_again` AS 'WontBeSoldAgain',
            IF(
                (@NumberOfSoldItems + @NumberOfItemsInStock) > 0,
                REPLACE(REPLACE(REPLACE(FORMAT((@NumberOfSoldItems / (@NumberOfSoldItems + @NumberOfItemsInStock)) * @WontBeSoldAgainThisYear, 2), ".", "@"), ",", "."), "@", ","),
                REPLACE(REPLACE(REPLACE(FORMAT(0, 2), ".", "@"), ",", "."), "@", ",")
            ) AS 'SellThru',
            REPLACE(REPLACE(REPLACE(FORMAT(@AVG, 2), ".", "@"), ",", "."), "@", ",") AS 'AVG'
        FROM `bu_product` bp
        WHERE bp.`date_of_last_publish` IS NOT NULL
    )
) SELLTHROUGH;
