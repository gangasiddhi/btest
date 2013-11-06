SELECT * INTO OUTFILE 'REPORT_PATH' FIELDS TERMINATED BY ';'
FROM (
    SELECT bo.`date_add` AS 'OrderDateTime',
        bod.`product_id` AS 'ProductId',
        bod.`product_reference` AS 'ProductReference',
        bo.`id_order` AS 'OrderId',
        bo.`invoice_number` AS 'InvoiceNumber',
        REPLACE(REPLACE(REPLACE(FORMAT(
            bp.`wholesale_price`,
            2
        ), ".", "@"), ",", "."), "@", ",") AS 'WholesalePrice',
        REPLACE(REPLACE(REPLACE(FORMAT(
            IF(
                bod.`reduction_percent` > 0,
                bod.`product_price` * (1 + (bod.`tax_rate` / 100)) * (100 - bod.`reduction_percent`) / 100,
                IF(
                    bod.`reduction_amount` > 0,
                    (bod.`product_price` * (1 + (bod.`tax_rate` / 100))) - bod.`reduction_amount`,
                    bod.`product_price` * (1 + (bod.`tax_rate` / 100))
                )
            ),
            2
        ), ".", "@"), ",", "."), "@", ",") AS 'ProductSoldAt',
        REPLACE(REPLACE(REPLACE(FORMAT(
            bp.`price` * IF(bp.`id_product_type` IN (3, 6), 1.18, 1.08),
            2
        ), ".", "@"), ",", "."), "@", ",") AS 'ProductPrice',
        REPLACE(REPLACE(REPLACE(FORMAT(
            bod.`reduction_amount`,
            2
        ), ".", "@"), ",", "."), "@", ",") AS 'ReductionAmount',
        REPLACE(REPLACE(REPLACE(FORMAT(
            bod.`reduction_percent`,
            2
        ), ".", "@"), ",", "."), "@", ",") AS 'ReductionPercent'
    FROM `bu_order_detail` bod
    JOIN `bu_orders` bo ON bo.`id_order` = bod.`id_order`
    JOIN `bu_product` bp ON bp.`id_product` = bod.`product_id`
) SALESPERPRODUCT;
