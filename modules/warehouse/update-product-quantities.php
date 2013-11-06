<?php

require_once(dirname(__FILE__) . '/../../config/config.inc.php');
require_once(dirname(__FILE__) . '/../../init.php');

require_once(_PS_MODULE_DIR_ . 'butigocache/butigocache.php');
require_once(_PS_MODULE_DIR_ . 'warehouse/lib/OGLI.php');

$log = Logger::getLogger(__FILE__);
$cron_user = Tools::getValue('crnu');
$cron_pass = Tools::getValue('crnp');

// disable cache invalidation
ButigoCache::$isEnabled = false;

if (! $cron_user) {
    $cron_user = $argv[1];
}

if (! $cron_pass) {
    $cron_pass = $argv[2];
}

$cron_pass = Tools::encrypt($cron_pass);

if (! ($cron_user == _CRON_USER_ AND $cron_pass == _CRON_PASSWD_)) {
    header("Location: " . __PS_BASE_URI__);

    exit;
}

$log->info('Warehouse Stock Querying starts..');

$attributes = Product::getProductsWithAttribute();
$combinationCount = array_sum(Tools::array_column($attributes, 'quantity'));

$log->info('We currently have ' . $combinationCount . ' products with ' . count($attributes) . ' barcodes!');
$log->info('Getting allocated products from open orders..');

$allocatedProducts = Product::getAllocatedProductsFromOpenOrders();
$transformedAllocatedProducts = array();

$log->info('Got ' . count($allocatedProducts) . ' from open orders. Transforming array..');

foreach ($allocatedProducts as $p) {
    if (! array_key_exists($p['RefCode'], $transformedAllocatedProducts)) {
        $transformedAllocatedProducts[$p['RefCode']] = array(
            'Orders' => array($p['OrderId']),
            'Quantity' => (int) $p['ProductQuantity']
        );
    } else {
        $transformedAllocatedProducts[$p['RefCode']]['Orders'][] = $p['OrderId'];
        $transformedAllocatedProducts[$p['RefCode']]['Quantity'] += (int) $p['ProductQuantity'];
    }
}

try {
    $ogli = new OGLI();
    $stock = $ogli->queryStock();
    $uniqueStock = array();

    foreach ($stock as $s) {
        $refCode = substr((string) $s->InventoryItemCode, 3);
        $qCode = trim((string) $s->QuarantineCode);
        $freeQuantity = (int) $s->CUFreeQantity;

        if (! isset($uniqueStock[$refCode])) {
            $uniqueStock[$refCode] = 0;
        }

        if (empty($qCode)) {
            $uniqueStock[$refCode] += $freeQuantity;
        } else {
            $qDesc = trim((string) $s->QuarantineDescription);

            $log->info(sprintf('%s has %d unaccepted items! Reason is: %s', $refCode, $freeQuantity, $qDesc));
        }
    }

    $log->info('Merged all retrieved barcodes (from warehouse) into an array of ' . count($uniqueStock) . ' items.');

    foreach ($attributes as $a) {
        $refCode = $a['reference'];
        $existingQty = $a['quantity'];
        $quantity = $uniqueStock[$refCode];

        if (array_key_exists($refCode, $uniqueStock)) {
            $log->info("Warehouse has " . $uniqueStock[$refCode] . " products [$refCode]. Checking stock on sale..");

            if (! ($p = Product::getProductByReference($refCode))) {
                $log->error('Product cannot be found for refCode: ' . $refCode);

                continue;
            }

            $backOrder = Product::isProductCanBackOrder($p['id_product']);

            if ($backOrder['out_of_stock'] == 1) {
                $log->info('Product [' . $refCode . '] seems to be set for back order. Checking quantity..');

                if ($quantity === 0) {
                    $log->info('Looks like product has still not made it to the warehouse. Skipping..');

                    continue;
                } else {
                    /**
                     * Leaving quantity decrease to below blocks as it's already covered thanks to
                     * above call for allocated products..
                     */

                    $backOrders = Order::getOrderIdsByStatus(_PS_OS_BACK_ORDER_);

                    $log->info('Seems like warehouse has just received the product! Removing back order flag..');

                    if (! Product::removeProductFromBackOrder($p['id_product'])) {
                        $log->error('Removing back order flag from product has failed!');
                    }

                    if ($backOrders) {
                        $log->info('Changing state of back orders to PIP..');

                        foreach ($backOrders as $orderId) {
                            $log->debug('Changing state of: ' . $orderId);

                            $o = new Order($orderId);
                            $o->setCurrentState(_PS_OS_PREPARATION_);
                        }
                    }
                }
            }

            $log->info('Checking warehouse stock against allocated products..');

            $allocatedQty = ($transformedAllocatedProducts[$refCode]['Quantity'] ?
                $transformedAllocatedProducts[$refCode]['Quantity'] : 0);

            $unallocatedQuantity = $quantity - $allocatedQty;

            $log->info('Allocated quantity: ' . $allocatedQty . ', left unallocated quantity: ' . $unallocatedQuantity);

            $qtyToBeAdded = $unallocatedQuantity - $p['prev_qty'];

            $log->info('Previous quantity: ' . $p['prev_qty'] . ', quantity to be added: ' . $qtyToBeAdded
                 . ', new quantity: ' . $unallocatedQuantity);

            $stockMovementReason = ($qtyToBeAdded > 0 ? _STOCK_MOVEMENT_WAREHOUSE_STOCK_POSITIVE_CORRECTION_ : _STOCK_MOVEMENT_WAREHOUSE_STOCK_NEGATIVE_CORRECTION_);

            if ($unallocatedQuantity < 0) {
                $log->info('New quantity cannot be less than zero! Skipping..');
                continue;
            }

            if ($qtyToBeAdded !== 0) {
                $log->debug('Stock Movement Reason: ' . $stockMovementReason);
                $log->debug('Stock quantity that will be applied: ' . $qtyToBeAdded); // will be abs()'ed during addition of stock movement

                $product = new Product($p['id_product']);
                $product->addStockMvt(
                    $qtyToBeAdded,
                    $stockMovementReason,
                    $p['id_product_attribute']
                );
            }
        } else {
            // warehouse doesn't report any quantities when it doesn't
            // have the product.. so assigning 0 as quantity..
            if ($existingQty > 0) {
                $log->info("We have $existingQty products [$refCode] on sale but none in warehouse! Resetting stock..");

                $product = new Product($a['id_product']);
                $qtyToBeAdded = - $existingQty;
                $stockMovementReason = _STOCK_MOVEMENT_WAREHOUSE_STOCK_NEGATIVE_CORRECTION_;

                $product->addStockMvt(
                    $qtyToBeAdded,
                    $stockMovementReason,
                    $a['id_product_attribute']
                );
            }
        }
    }

    $log->info('Flushing cache..');

    BCache::flushAll();
} catch (Exception $e) {
    $log->fatal('Querying stock failed!', $e);
}

$log->info('Warehouse Stock Querying ends..');
