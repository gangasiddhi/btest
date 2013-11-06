<?php

require_once(dirname(__FILE__) . '/../../config/config.inc.php');
require_once(dirname(__FILE__) . '/../../init.php');
require_once(_PS_MODULE_DIR_ . 'warehouse/lib/OGLI.php');

$start = 0;
$limit = 100;
$errors = 0;
$totalMainProductsSent = 0;
$totalProductsSent = 0;
$log = Logger::getLogger('WarehouseMasterDataImport');

while (count($products = Product::getAllProductIds($start, $limit))) {
    $ogli = new OGLI();
    $tmp = array();

    $log->info('Starting batch: ' . (($start / $limit) + 1));

    foreach ($products as $product) {
        $tmp[] = new Product($product['id_product']);
    }

    try {
        $response = $ogli->dispatchProducts($tmp);

        if (empty($response)) {
            // retry once again..
            $response = $ogli->dispatchProducts($tmp);
        }

        foreach ($response['InventoryItemResult'] as $r) {
            if ($r->Code == 'ERROR') {
                $message1 = 'Product could not be sent to the warehouse due to error: ' . $r->Result;
                $message2 = 'Products being sent were: ' . print_r($products, true);

                $log->error($message1);
                $log->error($message2);

                $errors++;
            } else {
                $totalProductsSent++;
            }
        }
    } catch (Exception $e) {
        $log->fatal('Products in batch ' . (($start / $limit) + 1) . ' could not be sent to the warehouse!', $e);

        $errors++;
    }

    $start += $limit;
    $totalMainProductsSent += count($tmp);
    unset($ogli);
}

$log->info('Done! ' . $totalMainProductsSent . ' products and ' . $totalProductsSent . ' combinations were sent! ' . $errors . ' errors occured!');
