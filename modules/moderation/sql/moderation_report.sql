SELECT * INTO OUTFILE 'REPORT_PATH' FIELDS TERMINATED BY ';'
FROM
(
    (
        SELECT 'OrderDateTime',
            'OrderNo',
            'CustomerName',
            'ProductReference',
            'ModerationDatetime',
            'RequestType',
            'ProductName',
            'RequestReason'
    )
    UNION
    (
        SELECT bo.`date_add` AS 'OrderDateTime',
            bo.`id_order` AS 'OrderNo',
            CONCAT_WS(' ', bc.`firstname`, bc.`lastname`) AS 'CustomerName',
            bod.`product_reference` AS 'ProductReference',
            IF(
                bom.`was_moderated` = 1 AND bom.`date_moderated` IS NOT NULL,
                bom.`date_moderated`,
                ''
            ) AS 'ModerationDatetime',
            IF(
                bom.`id_moderation_type` IN (2, 3),
                'Bedel İadesi',
                IF(
                    bom.`id_moderation_type` = 1,
                    'Değişim Talebi',
                    'Değişim İptali'
                )
            ) AS 'RequestType',
            bod.`product_name` AS 'ProductName',
            bmr.`text` AS 'RequestReason'
        FROM `bu_order_moderation` bom
        JOIN `bu_orders` bo ON bo.`id_order` = bom.`id_order`
        JOIN `bu_customer` bc ON bc.`id_customer` = bo.`id_customer`
        JOIN `bu_order_detail` bod ON bod.`id_order` = bom.`id_order`
        JOIN `bu_moderation_reason` bmr ON bmr.`id` = bom.`id_reason`
    )
    UNION
    (
        SELECT bo.`date_add` AS 'OrderDateTime',
            bo.`id_order` AS 'OrderNo',
            CONCAT_WS(' ', bc.`firstname`, bc.`lastname`) AS 'CustomerName',
            bod.`product_reference` AS 'ProductReference',
            IF(
                bpm.`was_moderated` = 1 AND bpm.`date_moderated` IS NOT NULL,
                bpm.`date_moderated`,
                ''
            ) AS 'ModerationDatetime',
            IF(
                bpm.`id_moderation_type` IN (2, 3),
                'Bedel İadesi',
                IF(
                    bpm.`id_moderation_type` = 1,
                    'Değişim Talebi',
                    'Değişim İptali'
                )
            ) AS 'RequestType',
            bod.`product_name` AS 'ProductName',
            bmr.`text` AS 'RequestReason'
        FROM `bu_product_moderation` bpm
        JOIN `bu_orders` bo ON bo.`id_order` = bpm.`id_order`
        JOIN `bu_customer` bc ON bc.`id_customer` = bo.`id_customer`
        JOIN `bu_order_detail` bod ON bod.`id_order_detail` = bpm.`id_order_detail`
        JOIN `bu_moderation_reason` bmr ON bmr.`id` = bpm.`id_reason`
    )
) MODERATION_REPORT;
