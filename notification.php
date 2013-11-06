<?php

require(dirname(__FILE__) . '/config/config.inc.php');
require(dirname(__FILE__) . '/init.php');

$log = Logger::getLogger('ButigoNotificationServiceEndpoint');
$hash = Tools::getValue('hash');

function requestFailure($msg) {
    header('HTTP/1.1 400 ' . $msg);

    exit;
}

$log->debug('Hash retrieved as: ' . $hash);

if ($hash === Tools::encrypt(Configuration::get('OGLI_HASH'))) {
    $log->info('Hash belongs to OGLI [' . Tools::encrypt(Configuration::get('OGLI_HASH')) . '], checking request method..');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $xmlString = $_REQUEST['data'];
        $xml = XMLHelper::loadFromString($xmlString);

        $log->debug('Request method is POST with following XML data: ' . $xmlString);

        switch ($xml->TYPE) {
            case 'PROCESSING':
                $log->info('Notification is about [N1] Order Processing, calling related hook..');

                Module::hookExec('packageProcessing', $_REQUEST);
                break;

            case 'PO_RESPONSE':
                $log->info('Notification is about [N3] Supplier Purchase Order Response, calling related hook..');

                Module::hookExec('warehouseImportResponse', $_REQUEST);
                break;

            case 'RETURN':
                $log->info('Notification is about [N4] Warehouse Return Response, calling related hook..');

                Module::hookExec('warehouseReturn', $_REQUEST);
                break;

            default:
                $msg = 'Notification is not known, aborting request..';
                $log->error($msg);

                requestFailure($msg);
                break;
        }
    } else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $log->debug('Request method is GET, calling related hook..');

        Module::hookExec('packageProcessed', $_REQUEST);
    } else {
        $msg = 'Request method is not known to this service, aborting..';
        $log->error($msg);

        requestFailure($msg);
    }
} elseif ($hash === Tools::encrypt(Configuration::get('ARAS_CARGO_HASH'))) {
    $state = Tools::getValue('state', false);
    $data = Tools::getValue('data', false);

    $log->info('Hash belongs to Aras Kargo [' . Tools::encrypt(Configuration::get('ARAS_CARGO_HASH')) . '], checking state and data..');

    if ($state && $data) {
        if ($state == Configuration::get('PS_OS_SHIPPING')) {
            $log->info('State is Shipped, calling related hook..');

            Module::hookExec('cargoShipped', array('notificationXMLData' => $data));
        } else if ($state == Configuration::get('PS_OS_DELIVERED')) {
            $log->info('State is Delivered, calling related hook..');

            Module::hookExec('cargoDelivered', array('notificationXMLData' => $data));
        } else if ($state == Configuration::get('PS_OS_UNDELIVERED')) {
            $log->info('State is Undelivered, calling related hook..');

            Module::hookExec('cargoUndelivered', array('notificationXMLData' => $data));
        }
    } else {
        $msg = 'Seems like there is a problem with either state or data, aborting request..';
        $log->error($msg);

        requestFailure($msg);
    }
} else {
    $msg = 'Hash ' . $hash . ' not found!';
    $log->error($msg);

    requestFailure($msg);
}

?>
