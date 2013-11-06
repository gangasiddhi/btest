<?php

if (! defined('_CAN_LOAD_FILES_')) {
    exit;
}

class Warehouse extends Module {
    public $name = 'warehouse';
    public $tab = 'AdminSupplierPurchase';
    public $version = 1.0;
    public $author = 'Alper Kanat';
    public $tabName = array(
        1 => 'Supplier Purchase',
        4 => 'Tedarikçi Siparişi'
    );

    public function __construct() {
        require_once(_PS_MODULE_DIR_ . $this->name . '/lib/WarehouseCore.php');
        require_once(_PS_MODULE_DIR_ . $this->name . '/lib/OGLI.php');

        parent::__construct();

        $this->displayName = $this->l('Warehouse');
        $this->description = $this->l('Warehouse');

        $this->log = Logger::getLogger(get_class($this));
    }

    public function installAdminTab() {
        $tab = new Tab();
        $tab->module = $this->name;
        $tab->name = $this->tabName;
        $tab->class_name = $this->tab;
        $tab->id_parent = 1; // AdminCatalog

        return $tab->save() ? true : false;
    }

    public function uninstallAdminTab() {
        $idTab = Tab::getIdFromClassName($this->tab);

        if ($idTab === 0) {
            return false;
        }

        $tab = new Tab($idTab);
        $tab->delete();

        return true;
    }

    public function install() {
        if (! parent::install()
            OR ! $this->registerHook('updateOrderStatus')
            OR ! $this->registerHook('addProduct')
            OR ! $this->registerHook('updateProduct')
            OR ! $this->registerHook('packageProcessing')
            OR ! $this->registerHook('packageProcessed')
            OR ! $this->registerHook('newSupplierPurchase')
            OR ! $this->registerHook('updateProductAttribute')
            OR ! $this->registerHook('warehouseImportResponse')
            OR ! $this->registerHook('warehouseReturn')
            OR ! $this->registerHook('dispatchOrder')
            OR ! $this->installAdminTab()) {

            return false;
        }

        return true;
    }

    public function uninstall() {
        if (! parent::uninstall()
            OR ! $this->uninstallAdminTab()) {

            return false;
        }

        return true;
    }

    protected function dispatchOrder($order) {
        /**
         * Before order id 93970, we weren't using a state like PROCESSING. So we didn't know
         * whether or not the order was sent to the warehouse. So placing a hardcoded check for
         * such orders to prevent re-transmission of that orders.
         */
        if ($order->id < 93970) {
            $this->log->info('Order [' . $order->id . '] is pretty old! Skipping it without transmission to the warehouse..');

            return false;
        }

        $this->log->info('Order [' . $order->id . '] is being transmitted to the warehouse!');

        try {
            $warehouseId = Configuration::get('WAREHOUSE_ID');
            $orderCalculations = Tools::generateXml($order, true);
            $ogli = new OGLI();
            $response = $ogli->dispatchOrder($order, $warehouseId, $orderCalculations);
            $response = $response['WarehouseOrderResult'];

            if ($response->Code === 'ERROR') {
                $message = 'Order [' . $order->id . '] could not be sent to the warehouse due to error: ' . $response->Result;

                $this->log->error($message);

                Tools::sendMailToAdmins('Warehouse Order Failure', $message);

                return false;
            }
        } catch (Exception $e) {
            $this->log->fatal('Order [' . $order->id . '] could not be sent to the warehouse!', $e);

            Tools::sendMailToAdmins('Warehouse Order Failure', $e->getMessage());

            return false;
        }

        return true;
    }

    public function hookDispatchOrder($params) {
        global $cookie;

        $order = new Order($params['id_order']);

        if ($order->getHistory($cookie->id_lang, _PS_OS_CANCELED_)) {
            $this->log->info('Order [' . $order->id . '] is being skipped as it seems to be cancelled already..');

            return false;
        }

        if ($order->getHistory($cookie->id_lang, _PS_OS_REFUND_)) {
            $this->log->info('Order [' . $order->id . '] is being skipped as it seems to be fully refunded already..');

            return false;
        }

        if ($order->getHistory($cookie->id_lang, _PS_OS_EXCHANGE_)) {
            $this->log->info('Order [' . $order->id . '] is being skipped as it seems to be fully exchanged already..');

            return false;
        }

        if ($order->getHistory($cookie->id_lang, _PS_OS_PROCESSING_)) {
            $this->log->info('Order [' . $order->id . '] is being skipped as it has already passed the stage where it can be modified..');

            return false;
        }

        if ($order->getHistory($cookie->id_lang, _PS_OS_PROCESSED_)) {
            $this->log->info('Order [' . $order->id . '] is being skipped as it has already passed the stage where it can be modified..');

            return false;
        }

        if (! $order->getHistory($cookie->id_lang, _PS_OS_PREPARATION_)) {
            $this->log->info('Order [' . $order->id . '] is being skipped as it still has not passed Preparation in Progress status..');

            return false;
        }

        return $this->dispatchOrder($order);
    }

    public function hookWarehouseImportResponse($params) {
        $OGLI_N3 = new OGLI_N3();
        $OGLI_N3->setParams($params);

        if (! $OGLI_N3->validateParams()) {
            $this->log->fatal("Incorrect notification parameters from OGLI warehouse.\nParameters:\n"
                . print_r($params, true));

            header('HTTP/1.0 403 Incorrect Parameters');

            die();
        }

        if (! $OGLI_N3->validate()) {
            $this->log->fatal("Supplier Purchase Not Found or Already Complete!");

            header('HTTP/1.0 404 Supplier Purchase Not Found or Already Complete');

            die();
        }

        $OGLI_N3->process();
    }

    public function hookWarehouseReturn($params) {
        $OGLI_N4 = new OGLI_N4();
        $OGLI_N4->setParams($params);

        if (! $OGLI_N4->validateParams()) {
            $this->log->fatal("Incorrect notification parameters from OGLI warehouse.\nParameters:\n"
                . print_r($params, true));

            header('HTTP/1.0 403 Incorrect Parameters');

            die();
        }

        $OGLI_N4->process();
    }

    public function hookNewSupplierPurchase($params) {
        $supplierPurchase = $params['supplierPurchase'];

        try {
            $ogli = new OGLI();
            $response = $ogli->dispatchPurchase($supplierPurchase);

            foreach ($response as $k => $r) {
                if ($r->Code === 'ERROR') {
                    $message1 = 'Purchase request could not be sent to the warehouse due to error: ' . $r->Result;
                    $message2 = 'Purchased items were: ' . print_r($supplierPurchase->purchaseDetail, true);

                    $this->log->error($message1);
                    $this->log->error($message2);

                    Tools::sendMailToAdmins('Supplier Purchase Failure', sprintf('%s<br><br>%s', $message1, $message2));
                }
            }
        } catch (Exception $e) {
            $this->log->fatal('Supplier Purchase could not be sent to the warehouse!', $e);
        }

        return true;
    }

    private function dispatchProduct($params) {
        $product = $params['product'];
        $products = array($product);

        try {
            $ogli = new OGLI();
            $response = $ogli->dispatchProducts($products);

            foreach ($response as $r) {
                if ($r->Code === 'ERROR') {
                    $message1 = 'Product could not be sent to the warehouse due to error: ' . $r->Result;
                    $message2 = 'Products being sent were: ' . print_r($products, true);

                    $this->log->error($message1);
                    $this->log->error($message2);

                    Tools::sendMailToAdmins('Product Information Update Failure', sprintf('%s<br><br>%s', $message1, $message2));
                }
            }
        } catch (Exception $e) {
            $this->log->fatal('Products could not be sent to the warehouse!', $e);
        }

        return true;
    }

    public function hookAddProduct($params) {
        return $this->dispatchProduct($params);
    }

    public function hookUpdateProduct($params) {
        return $this->dispatchProduct($params);
    }

    public function hookUpdateProductAttribute($params) {
        $id_product_attribute = $params['id_product_attribute'];
        $id_product = Product::getIdByAttributeId($id_product_attribute);

        $this->log->debug("id_product_attribute: $id_product_attribute, id_product: $id_product");

        $arr = array(
            'product' => new Product($id_product)
        );

        return $this->dispatchProduct($arr);
    }

    public function hookUpdateOrderStatus($params) {
        global $cookie;

        $status = $params['newOrderStatus'];
        $order = new Order($params['id_order']);

        if ($status->id == _PS_OS_CANCELED_ OR $order->getHistory($cookie->id_lang, _PS_OS_CANCELED_)) {
            $this->log->info('Order [' . $order->id . '] is being skipped as it seems to be cancelled already..');

            return false;
        }

        if ($status->id == _PS_OS_REFUND_ OR $order->getHistory($cookie->id_lang, _PS_OS_REFUND_)) {
            $this->log->info('Order [' . $order->id . '] is being skipped as it seems to be fully refunded already..');

            return false;
        }

        if ($status->id == _PS_OS_EXCHANGE_ OR $order->getHistory($cookie->id_lang, _PS_OS_EXCHANGE_)) {
            $this->log->info('Order [' . $order->id . '] is being skipped as it seems to be fully exchanged already..');

            return false;
        }

        if ($status->id == _PS_OS_PROCESSING_ OR $order->getHistory($cookie->id_lang, _PS_OS_PROCESSING_)) {
            $this->log->info('Order [' . $order->id . '] is being skipped as it has already passed the stage where it can be modified..');

            return false;
        }

        if ($status->id == _PS_OS_PROCESSED_ OR $order->getHistory($cookie->id_lang, _PS_OS_PROCESSED_)) {
            $this->log->info('Order [' . $order->id . '] is being skipped as it has already passed the stage where it can be modified..');

            return false;
        }

        if ($status->id != _PS_OS_PREPARATION_ AND ! $order->getHistory($cookie->id_lang, _PS_OS_PREPARATION_)) {
            $this->log->info('Order [' . $order->id . '] is being skipped as it still has not passed Preparation in Progress status..');

            return false;
        }

        return $this->dispatchOrder($order);
    }

    public function hookPackageProcessing($params) {
        $OGLI_N1 = new OGLI_N1();
        $OGLI_N1->setParams($params);

        if (! $OGLI_N1->validateParams()) {
            $this->log->fatal("Incorrect notification parameters from OGLI warehouse.\nParameters:\n"
                . print_r($params, true));

            header('HTTP/1.0 403 Incorrect Parameters');

            die();
        }

        $OGLI_N1->checkOrders();
        $OGLI_N1->process();
    }

    public function hookPackageProcessed($params) {
        $OGLI_N2 = new OGLI_N2();
        $OGLI_N2->setParams($params);

        if (! $OGLI_N2->validateParams()) {
            $this->log->fatal("Incorrect notification parameters from OGLI warehouse.\nParameters:\n"
                . print_r($params,true));

            header('HTTP/1.0 403 Incorrect Parameters');

            die();
        }

        if (! $OGLI_N2->checkOrder()) {
            $this->log->fatal("Incorrect Order Id from OGLI warehouse: " . $this->orderId);

            header('HTTP/1.0 404 Incorrect Order Id');

            die();
        }

        $OGLI_N2->process();
    }
}
