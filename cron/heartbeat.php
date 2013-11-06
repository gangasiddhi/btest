<?php

/**
 * Calculates orders in the last N hours and notifies certain
 * people if there are any problems.
 */

include(dirname(__FILE__) . "/../config/config.inc.php");
include(dirname(__FILE__) . "/../init.php");

$cron_user = Tools::getValue('crnu');
$cron_pass = Tools::getValue('crnp');

if (! $cron_user) {
    $cron_user = $argv[1];
}

if (! $cron_pass) {
    $cron_pass = $argv[2];
}

$cron_pass = Tools::encrypt($cron_pass);

if ($cron_user != _CRON_USER_ OR $cron_pass != _CRON_PASSWD_) {
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");

    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");

    header("Location: ../");

    exit;
}

$interval = 1;
$reportIntervals = array(15, 21);
$subjectPrefix = "[HEARTBEAT] ";
$subjectSuccess = "Sales Summary as of " . date("d.m.Y, H:i");
$subjectFailure = "Seems like there's a problem with the orders!";

$heartbeatSql = sprintf("
    SELECT `id_order`
    FROM bu_orders
    WHERE `date_add` > DATE_ADD(NOW(), INTERVAL -%s HOUR)
    ORDER BY `date_add` DESC
    LIMIT 1
", $interval);

$since = date('Y-m-d 00:00:00');
$reportSql = sprintf("
    SELECT bo.`id_order` as 'Order Id',
        bo.`invoice_number` as 'Invoice Number',
        CONCAT_WS(' ', bc.`firstname`, bc.`lastname`) as 'Customer',
        bo.`total_paid_real` as 'Total Paid',
        bo.`module` as 'Payment Gateway',
        IFNULL(
            bsl.`name`,
            'Preparation In Progress'
        ) as 'Last Status',
        bo.`date_add` as 'Order Created On'
    FROM bu_orders bo
    JOIN bu_customer bc ON bc.`id_customer` = bo.`id_customer`
    JOIN (
        SELECT *
        FROM (
            SELECT *
            FROM bu_order_history
            ORDER BY `date_add` DESC
        ) as boh
        GROUP BY `id_order`
    ) boh ON boh.`id_order` = bo.`id_order`
    JOIN bu_order_state_lang bsl ON bsl.`id_order_state` = boh.`id_order_state` AND bsl.`id_lang` = 1
    WHERE bo.`date_add` > '%s'
    ORDER BY bo.`date_add` DESC
", $since);

$heartbeat = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($heartbeatSql);

if (empty($heartbeat)) {
    $subject = "$subjectPrefix $subjectFailure";
    $message = "Heartbeat reports that there has been no orders placed within the last $interval hours, please check!";
    $to = "managers@butigo.com";
    $headers = "From: Butigo Heartbeat <root@butigo.com>\r\n" .
        "Cc: root@butigo.com\r\n" .
        "Reply-To: root@butigo.com";

    mail($to, $subject, $message, $headers);

    // return error
    return 1;
}

if (in_array(date("H"), $reportIntervals)) {
    $orders = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($reportSql);
    $subject = "$subjectPrefix $subjectSuccess";
    $to = "managers@butigo.com";
    $headers = "MIME-Version: 1.0\r\n" .
        "Content-type: text/html; charset=utf-8\r\n" .
        "From: Butigo Heartbeat <root@butigo.com>\r\n" .
        "Reply-To: root@butigo.com";

    $tableHeader = sprintf(
        "<th style='width: 55px'>%s</th>\r\n" .
        "<th style='width: 100px'>%s</th>\r\n" .
        "<th style='width: 200px'>%s</th>\r\n" .
        "<th style='width: 70px'>%s</th>\r\n" .
        "<th style='width: 180px'>%s</th>\r\n" .
        "<th style='width: 120px'>%s</th>",

        "Order Id", "Invoice Number", "Customer", "Total Paid",
        "Last Status", "Order Created On");
    $content = "";

    foreach ($orders as $order) {
        $content .= sprintf("<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>\n",
            $order["Order Id"], $order["Invoice Number"], $order["Customer"], $order["Total Paid"],
            $order["Last Status"], $order["Order Created On"]);
    }

    $body = "
        <html>
            <head>
                <title>$subject</title>
            </head>
            <body>
                <h3>Orders Created Since $since</h3>
                <table style='text-align: left; font-family: Arial; font-size: 12px'>
                    <tr>
                        $tableHeader
                    </tr>
                    $content
                </table>
            </body>
        </html>
    ";

    mail($to, $subject, $body, $headers);
}
