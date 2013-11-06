<?php

require_once(dirname(__FILE__) . '/../config/config.inc.php');
require_once(dirname(__FILE__) . '/../init.php');

global $cookie;

$cron_user = Tools::getValue('crnu');
$cron_pass = Tools::getValue('crnp');

if (! $cron_user) {
    $cron_user = @$argv[1];
}

if (! $cron_pass) {
    $cron_pass = @$argv[2];
}

$cron_pass = Tools::encrypt($cron_pass);

if ($cron_user == _CRON_USER_ AND $cron_pass == _CRON_PASSWD_) {
    $date_from = Tools::getValue('date_from', date('Y-m-01 00:00:00'));
    $date_to = Tools::getValue('date_to', date('Y-m-d H:i:s'));
    $processed = 0;

    echo 'Modifying orders from ' . $date_from . ' to ' . $date_to . PHP_EOL . '<br>';

    $orders = Order::getOrdersIdByDate($date_from, $date_to);

    if (count($orders) === 0) {
        die('No orders are found! Exiting..');
    }

    echo 'Found ' . count($orders) . ' orders between given dates! Looping over them to check for validation necessity..' . PHP_EOL . '<br>';

    foreach ($orders as $orderId) {
        $order = new Order($orderId);
        $status = $order->getCurrentStateFull(intval($cookie->id_lang));

        error_log("$orderId ::: " . print_r($status, true));

        if (! $order->valid AND $status['logable']) {
            $order->valid = true;
            $order->update();

            echo $order->invoice_number . '    ' . $orderId . PHP_EOL . '<br>';

            $processed++;
        }
    }

    die('Done processing ' . $processed . ' items! Quitting..' . PHP_EOL . '<br>');
} else {
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");

    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");

    header("Location: ../");
    exit;
}
