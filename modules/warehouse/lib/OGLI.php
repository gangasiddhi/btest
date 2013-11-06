<?php

require_once(_PS_MODULE_DIR_ . 'warehouse/lib/WarehouseCore.php');

class OGLI extends WarehouseCore {
    private $serviceOpts = array(
        'defaultEncoding' => 'UTF-8',
        'soap_version' => SOAP_1_2
    );
    private $host = 'http://ws.ogliplatform.com/lointegration/services/lointegrationservice.asmx?wsdl';
    private $depositorId = 55;
    private $depositorCode = 'Butigo';
    private $userName = 'butigo';
    private $password = '167271251121152461851063652816869425537';
    private $unitType = 'Adet';
    private $log;
    private $service;

    public function __construct() {
        $this->log = Logger::getLogger(get_class($this));

        if (_BU_ENV_ != 'production') {
            $this->host = 'http://ws.ogliplatform.com/lointegrationtest/services/lointegrationservice.asmx?wsdl';
            $this->depositorId = 52;

            $this->log->debug('Using host: ' . $this->host);
            $this->log->debug('Using depositorId: ' . $this->depositorId);
        }

        $this->service = new SoapClient($this->host, $this->serviceOpts);
    }

    public function getService() {
        return $this->service;
    }

    public function doRequest($endpoint, $data = null) {
        $params = array(
            'UserName_' => $this->userName,
            'Password_' => $this->password,
            'ContinueOnError_' => true,
            'SecurityKey_' => ''
        );

        if ($data) {
            $params = array_merge($params, $data);
        }

        $this->log->debug('data: ' . print_r($params, true));

        try {
            $response = $this->service->$endpoint($params);

            $this->log->debug('response: ' . print_r($response, true));

            return $response;
        } catch (Exception $e) {
            $message = "Request to OGLI Web Service (' . $endpoint . ') has failed due to error:\n\n" . $e->getMessage();

            $this->log->fatal('Request to OGLI Web Service (' . $endpoint . ') has failed!', $e);

            Tools::sendMailToAdmins('Warehouse Order Failure', $message);
        }

        return array();
    }

    public function dispatchOrder($order, $warehouseId, $invoiceCalculations) {
        $endpoint = 'DoWarehouseOrderImport';
        $invoiceAddress = new Address($order->id_address_invoice);
        $invoiceProvince = new Province($invoiceAddress->id_province);
        $invoiceState = new State($invoiceAddress->id_state);
        $deliveryAddress = new Address($order->id_address_delivery);
        $deliveryProvince = new Province($invoiceAddress->id_province);
        $deliveryState = new State($invoiceAddress->id_state);
        $products = $order->getProducts();
        $data = array(
            'Orders_' => array(
                'WarehouseOrder' => array(
                    'Code' => $order->id,
                    'Depositor' => $this->depositorCode,
                    'InventorySite' => $warehouseId,
                    'WarehouseOrderType' => 'TamAloke',
                    'OrderDate' => $order->date_add,
                    'InvoiceCustomer' => $invoiceAddress->id_customer,
                    'InvoiceCustomerDescription' => sprintf('%s %s', $invoiceAddress->firstname, $invoiceAddress->lastname),
                    'InvoiceCustomerAddress' => sprintf('%s, %s, %s', $invoiceAddress->address1, $invoiceProvince->name, $invoiceState->name),
                    'InvoiceCustomerCity' => $invoiceState->name,
                    'InvoiceCustomerTown' => $invoiceProvince->name,
                    'InvoiceCustomerPhone' => $invoiceAddress->phone,
                    'TotalSalesGrossPrice' => $invoiceCalculations['totalWithTax'],
                    'TotalSalesVat' => $invoiceCalculations['totalTax'],
                    'CargoGrossPrice' => $invoiceCalculations['shippingWithInstallmentAndDiscount'],
                    'CargoDiscount' => $invoiceCalculations['totalLineValueWithoutTaxWithExtras'],
                    'TotalSalesDiscount' => $invoiceCalculations['totalDiscountValueWithoutTax'],
                    'ExtraNotes' => sprintf('%s - %s', $invoiceCalculations['message3'], $order->payment), // note to cargo company
                    'Notes' => $invoiceCalculations['message1'], // message 1
                    'GiftNote' => $invoiceCalculations['message2'], // message 2
                    'Customer' => array(
                        'Code' => $deliveryAddress->id_customer,
                        'Description' => sprintf('%s %s', $deliveryAddress->firstname, $deliveryAddress->lastname),
                        'Address' => sprintf('%s, %s, %s', $deliveryAddress->address1, $deliveryProvince->name, $deliveryState->name),
                        'City' => $deliveryState->name,
                        'Town' => $deliveryProvince->name,
                        'Phone' => $deliveryAddress->phone
                    ),
                    'WOPriority' => 0,
                    'CurrencyRate' => $invoiceCalculations['paymentValue'],
                    'WarehouseOrderDetails' => array()
                )
            )
        );

        foreach ($products as $product) {
            $tmp = array(
                'InventoryItem' => $product['product_reference'],
                'InventoryItemPackType' => $this->unitType,
                'PlannedPackQuantity' => $product['product_quantity'],
                'TaxRatio' => $invoiceCalculations['products'][$product['product_id']]['UNIT_TAX'],
                'SalesUnitPrice' => $invoiceCalculations['products'][$product['product_id']]['PRODUCT_PRICE_WITHOUT_TAX'],
                'SalesUnitVat' => $invoiceCalculations['products'][$product['product_id']]['LINE_TAX_VALUE']
            );

            $data['Orders_']['WarehouseOrder']['WarehouseOrderDetails'][] = $tmp;
        }

        return (array) $this->doRequest($endpoint, $data)->DoWarehouseOrderImportResult;
    }

    public function dispatchProducts($products) {
        global $cookie;

        $endpoint = 'DoInventoryItemImport';
        $data = array(
            'Items_' => array()
        );

        foreach ($products as $product) {
            if (! Validate::isLoadedObject($product)) {
                $this->log->error('Product cannot be found!');
                break;
            }

            $combinations = $product->getAttributeCombinaisons();

            $this->log->debug('Product has ' . count($combinations) . ' combinations.');

            foreach ($combinations as $combination) {
                $tmp = array(
                    'Code' => $combination['reference'],
                    'Description' => $product->name[$cookie->id_lang],
                    'CUUnit' => $this->unitType,
                    'ImageURL' => $product->getDefaultImage($cookie->id_lang),
                    'Depositor' => $this->depositorCode,
                    'InventoryItemBarcodes' => array(
                        array(
                            'Barcode' => $combination['reference'],
                            'InventoryItemPackType' => $this->unitType
                        )
                    )
                );

                $data['Items_'][] = $tmp;
            }
        }

        return (array) $this->doRequest($endpoint, $data)->DoInventoryItemImportResult;
    }

    public function dispatchPurchase($supplierPurchase) {
        global $cookie;

        $endpoint = 'DoPurchaseOrderImport';
        $supplier = new Supplier($supplierPurchase->id_supplier);
        $data = array(
            'Orders_' => array(
                'PurchaseOrder' => array(
                    'PurchaseOrderType' => 'Serbest',
                    'Code' => $supplierPurchase->id,
                    'Depositor' => $this->depositorCode,
                    'InventorySite' => $supplierPurchase->id_warehouse,
                    'OrderDate' => $supplierPurchase->date_add,
                    'PurchaseOrderSupplier' => array(
                        'Code' => $supplierPurchase->id_supplier,
                        'Description' => $supplier->name,
                        'Address' => $supplier->description[$cookie->id_lang],
                        'LowerCuQuantityPercent' => 0,
                        'LowerWeightPercent' => 0,
                        'UpperCuQuantityPercent' => 0,
                        'UpperWeightPercent' => 0
                    ),
                    'PurchaseOrderItems' => array()
                )
            )
        );

        foreach ($supplierPurchase->purchaseDetail as $reference => $quantity) {
            $tmp = array(
                'InventoryItemPackType' => $this->unitType,
                'PurchaseItem' => $reference,
                'Quantity' => $quantity
            );

            $data['Orders_']['PurchaseOrder']['PurchaseOrderItems'][] = $tmp;
        }

        return (array) $this->doRequest($endpoint, $data)->DoPurchaseOrderImportResult;
    }

    public function queryStock($warehouseId, $productId = null) {
        $endpoint = 'GetStockReport';
        $data = array(
            'DepositorID_' => $this->depositorId
        );

        return (array) $this->doRequest($endpoint, $data)->GetStockReportResult->StockReportItem;
    }
}

class OGLI_Notification extends WarehouseCore {
    protected $secretKey;
    protected $log;

    public function __construct() {
        $this->secretKey = Tools::encrypt(Configuration::get('OGLI_HASH'));
        $this->log = Logger::getLogger(get_class($this));
    }

    public function setOrderStatus($status) {
        if (method_exists($this, 'getObjects')) {
            $orderIds = $this->getObjects();

            foreach ($orderIds as $orderId) {
                $oid = (int) $orderId;

                if (! Order::isExist($oid)) {
                    $this->log->info('Skipping order id: ' . $oid);
                    continue;
                }

                $this->log->info('Setting state of ' . $oid . ' to ' . $status);

                $order = new Order($oid);
                $order->setCurrentState($status);
            }
        } else {
            if (! Order::isExist($this->orderId)) {
                $this->log->info('Skipping order id: ' . $this->orderId);
                continue;
            }

            $this->log->info('Setting state of ' . $this->orderId . ' to ' . $status);

            $order = new Order($this->orderId);
            $order->setCurrentState($status);
        }
    }
}

class OGLI_XML extends OGLI_Notification {
    protected $hash;
    protected $xml;
    protected $xmlString;

    public function setParams($params) {
        $this->hash = $params['hash'];
        $this->xmlString = $params['data'];
        $this->xml = XMLHelper::loadFromString($this->xmlString);
    }

    public function validateParams() {
        $this->log->debug('Hash: ' . $this->hash);
        $this->log->debug('SecretKey: ' . $this->secretKey);
        $this->log->debug('Is XML Valid: ' . (XMLHelper::isValid($this->xmlString) ? 'yes' : 'no'));

        return ($this->hash === $this->secretKey AND XMLHelper::isValid($this->xmlString));
    }

    public function updateStock($stockMvtReason, $callbackPerProduct = null) {
        $this->log->info('Warehouse Stock Update In Progress..');

        $newProductsAtWarehouse = $this->getObjects();

        foreach ($newProductsAtWarehouse as $o) {
            $refCode = (string) $o->REFCODE;
            $quantity = (int) $o->SUCCESS;

            $this->log->info("refCode: $refCode -- quantity: $quantity");

            if ($quantity == 0) {
                $this->log->info("Product [$refCode] has $quantity quantity, skipping..");
                continue;
            }

            if (! ($p = Product::getProductByReference($refCode))) {
                $log->error('Product cannot be found for refCode: ' . $refCode);
                continue;
            }

            $backOrder = Product::isProductCanBackOrder($p['id_product']);

            if ($backOrder['out_of_stock'] == 1) {
                $this->log->info('Product [' . $refCode . '] seems to be set for back order. Checking quantity..');

                if ($quantity == 0) {
                    $this->log->info('Looks like product has still not made it to the warehouse. Skipping..');
                    continue;
                } else {
                    $this->log->info('Seems like warehouse has just received the product! Checking for open back orders..');

                    $backOrders = Order::getOrderIdsByStatus(_PS_OS_BACK_ORDER_);

                    if (! empty($backOrders)) {
                        $totalQuantityInBackOrders = Product::getTotalBackOrderQuantityByReference($refCode);

                        $this->log->info('Decreasing new quantity [' . $totalQuantityInBackOrders['total_quantity'] . '] for open orders..');

                        $quantity -= $totalQuantityInBackOrders['total_quantity'];

                        $this->log->info('Remaning quantity after substraction: ' . $quantity);
                    }

                    $this->log->info('Removing back order flag..');

                    if (! Product::removeProductFromBackOrder($p['id_product'])) {
                        $this->log->error('Removing back order flag from product has failed!');
                    }

                    if ($backOrders) {
                        $this->log->info('Changing state of back orders to PIP..');

                        foreach ($backOrders as $orderId) {
                            $this->log->debug('Changing state of: ' . $orderId);

                            $o = new Order($orderId);
                            $o->setCurrentState(_PS_OS_PREPARATION_);
                        }
                    }
                }
            }

            $this->log->info('Increasing stock of ' . $refCode . ' by ' . $quantity . ' due to acceptance of goods..');

            $product = new Product($p['id_product']);
            $product->addStockMvt(
                $quantity,
                $stockMvtReason,
                $p['id_product_attribute']
            );

            if ($callbackPerProduct AND method_exists($this, $callbackPerProduct)) {
                call_user_method($callbackPerProduct, $this, $o);
            }
        }

        $this->log->info('Warehouse Stock Update Ended!');
    }
}

/**
 * Warehouse Processing Notification
 */
class OGLI_N1 extends OGLI_XML {
    protected $hash;
    protected $xml;
    protected $xmlString;

    public function getObjects() {
        return $this->xml->ORDERS->ORDER;
    }

    public function checkOrders() {
        $incorrectOrderIds = array();

        foreach ($this->getObjects() as $orderId) {
            $oid = (int) $orderId;

            if (! Order::isExist($oid)) {
                $incorrectOrderIds[] = $oid;
            }
        }

        if (count($incorrectOrderIds) > 0) {
            $message = "Incorrect Order Ids from OGLI warehouse:\n"
                . print_r($incorrectOrderIds, true);

            $this->log->fatal($message);

            Tools::sendMailToAdmins('OGLI N1 Failure', $message);

            return false;
        }

        return true;
    }

    public function process() {
        $this->setOrderStatus((int) Configuration::get('PS_OS_PROCESSING'));
    }
}

/**
 * Warehouse Processed Notification
 */
class OGLI_N2 extends OGLI_Notification {
    protected $hash;
    protected $orderId;

    protected $consignmentInvoiceId;
    protected $deliverySlipId;

    public function setParams($params) {
        foreach ($params as $key => $value) {
            $this->{$key} = $value;
        }

        return $this;
    }

    public function validateParams() {
        return $this->hash === $this->secretKey
            AND ! empty($this->orderId)
            AND (
                property_exists($this, 'consignmentInvoiceId')
                OR
                property_exists($this, 'deliverySlipId')
            );
    }

    public function checkOrder() {
        return Order::isExist($this->orderId);
    }

    protected function saveDocumentInfo() {
        $this->log->debug('Saving consignmentInvoiceId [' . $this->consignmentInvoiceId
            . '] or deliverySlipId [' . $this->deliverySlipId . ']..');

        $order = new Order($this->orderId);

        if (! empty($this->consignmentInvoiceId)) {
            $order->invoice_number = $this->consignmentInvoiceId;
            $order->invoice_date = strftime('%Y-%m-%d %H:%M:%S');
            $order->save();
        } else if (! empty($this->deliverySlipId)) {
            $order->delivery_number = $this->deliverySlipId;
            $order->delivery_date = strftime('%Y-%m-%d %H:%M:%S');
            $order->save();
        }

        $this->log->info('Writing XML invoice copies for ' . $this->orderId . '...');

        $order->createOrderxmlDaily($this->orderId);
    }

    public function process() {
        $this->saveDocumentInfo();
        $this->setOrderStatus((int) Configuration::get('PS_OS_PROCESSED'));
    }
}

/**
 * Warehouse Purchase Order Import Items
 */
class OGLI_N3 extends OGLI_XML {
    private $id;
    private $status;
    private $sp;

    public function getObjects() {
        return $this->xml->PRODUCT;
    }

    public function validate() {
        $this->log->debug('ID: ' . $this->id);
        $this->log->debug('STATUS: ' . ($this->status ? 'COMPLETE' : 'PENDING'));

        $this->sp = new SupplierPurchase($this->id);

        if ((! Validate::isLoadedObject($this->sp)) OR $this->sp->status) {
            return false;
        }

        // comparing items..
        foreach ($this->getObjects() as $o) {
            $refCode = (string) $o->REFCODE;

            if (! array_key_exists($refCode, $this->sp->purchaseDetail)) {
                $this->log->error('At least 1 item [' . $refCode . '] does NOT belong to this PO, aborting..');

                return false;
            }
        }

        return true;
    }

    public function setParams($params) {
        parent::setParams($params);

        $this->id = (int) $this->xml->ID;
        $this->status = (int) $this->xml->STATUS;
    }

    protected function updatePOHistory($o) {
        $this->log->info('Storing the same into the history for reference..');

        $reasons = (array) $o->REASONS;

        $sph = new SupplierPurchaseHistory();
        $sph->id_purchase = (int) $this->id;
        $sph->reference = (string) $o->REFCODE;
        $sph->quantity_success = (int) $o->SUCCESS;
        $sph->quantity_fail = (int) $o->FAILURE;
        $sph->reason = (count($reasons) ? json_encode($reasons) : null);
        $sph->save();
    }

    public function process() {
        if (! $this->sp->id_status AND $this->status) {
            $this->log->info('Purchase Order [' . $this->id . '] is completed by the warehouse, changing status..');
            $this->sp->id_status = $this->status;
            $this->sp->save();
        }

        $this->updateStock(_STOCK_MOVEMENT_WAREHOUSE_STOCK_POSITIVE_CORRECTION_, 'updatePOHistory');
    }
}

/**
 * Warehouse Return Acceptance
 */
class OGLI_N4 extends OGLI_XML {
    public function getObjects() {
        return $this->xml->PRODUCT;
    }

    public function process() {
        $this->updateStock(_STOCK_MOVEMENT_WAREHOUSE_RETURN_);
    }
}
