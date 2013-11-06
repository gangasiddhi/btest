<?php

require_once(_PS_ROOT_DIR_ . '/config/config.inc.php');

abstract class WarehouseCore {
    /**
     * Used for sending a single order of a customer.
     *
     * order and customer variables are objects of their respective classes
     * while products is a custom Array.. Please take a look at the implementation.
     *
     * @param Order order
     * @param Integer warehouseId
     * @param Array invoiceCalculations Output of Tools::generateXml($o, true)
     */
    public function dispatchOrder($order, $warehouseId, $invoiceCalculations) {
        throw new Exception("NOT IMPLEMENTED YET!");
    }

    /**
     * Batch function for sending multiple product information
     * to the remote end.
     *
     * $products consists of the following field(s):
     *
     *     * productId (read only)
     *
     * @param Array products
     */
    public function dispatchProducts($products) {
        throw new Exception("NOT IMPLEMENTED YET!");
    }

    /**
     * Used for sending purchase (from suppliers) requests to notify
     * the warehouse about it so that they won't reject the cargo.
     *
     * products consists of the following:
     *
     *     * barcode (refCode) that we assign
     *     * quantity
     *
     * @param SupplierPurchase supplierPurchase
     */
    public function dispatchPurchase($supplierPurchase) {
        throw new Exception("NOT IMPLEMENTED YET!");
    }

    /**
     * Queries stock against the warehouse and returns product data
     */
    public function queryStock($warehouseId, $productId = null) {
        throw new Exception("NOT IMPLEMENTED YET!");
    }
}
