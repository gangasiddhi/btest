<?php

class AdminSupplierPurchase extends AdminTab {
    public function __construct() {
        parent::__construct();

        $this->log = Logger::getLogger(get_class($this));
    }

    public function display() {
        if ($this->tabAccess['edit'] !== '1') {
            $this->_errors[] = Tools::displayError('You do not have permission to delete here.');
            return;
        }

        $editPurchase = Tools::getValue('editPurchase');

        $this->displayList($editPurchase);
    }

    public function displayList($editPurchase = null) {
        global $currentIndex;
        global $smarty;

        $pageNo = (Tools::getValue('page') ? Tools::getValue('page') : 1 );
        $itemPerPage = Configuration::get('SUPPLIER_PURCHASE_ITEM_PER_PAGE');
        $purchases = SupplierPurchase::getPurchases($itemPerPage, $pageNo);
        $suppliers = Supplier::getSuppliers();
        $editablePurchase = SupplierPurchase::getPurchase($editPurchase);
        $purchaseData = array();

        foreach ($purchases['objects'] as $p) {
            $supplier = new Supplier($p['id_supplier']);
            $p['supplier'] = $supplier->name;
            $purchaseData[] = $p;
        }

        $css = array(
            _PS_CSS_DIR_ . 'jquery-ui-1.8.10.custom.css',
            _THEME_JS_DIR_ . 'pagination/pagination.css',
            _MODULE_DIR_ . 'warehouse/static/list.css'
        );
        $js = array(
            _PS_JS_DIR_ . 'json2.js',
            _PS_JS_DIR_ . 'jquery/jquery-ui-1.8.10.custom.min.js',
            _THEME_JS_DIR_ . 'pagination/jquery.pagination.js',
            _MODULE_DIR_ . 'warehouse/static/list.js'
        );

        $smarty->assign(array(
            'css' => $css,
            'js' => $js,
            'currentIndex' => $currentIndex,
            'itemPerPage' => $itemPerPage,
            'page' => $pageNo,
            'token' => $this->token,
            'purchases' => $purchaseData,
            'suppliers' => $suppliers,
            'numberOfPurchases' => $purchases['totalItem'],
            'editablePurchase' => $editablePurchase
        ));
        $smarty->display(_PS_MODULE_DIR_ . 'warehouse/templates/list.tpl');
    }

    public function postProcess() {
        global $currentIndex;

        parent::postProcess();

        $submitPurchase = Tools::getValue('submitPurchase');
        $supplierId = Tools::getValue('supplier');
        $data = Tools::getValue('data');
        $editPurchase = Tools::getValue('editPurchase');

        $this->log->debug('JSON endecoded data: ' . $data);

        if ($submitPurchase AND $supplierId AND $data) {
            $supplier = new Supplier($supplierId);
            $data = json_decode($data, true);

            $this->log->debug('JSON decoded data: ' . print_r($data, true));

            $sp = new SupplierPurchase($editPurchase);
            $sp->id_warehouse = Configuration::get('WAREHOUSE_ID');
            $sp->id_supplier = $supplier->id;
            $sp->purchaseDetail = $data;

            if ($sp->save(false, true, true)) {
                $this->log->debug('Supplier purchase record has been saved with id: ' . $sp->id);

                // sending purchase to warehouse..
                Module::hookExec('newSupplierPurchase', array(
                    'supplierPurchase' => $sp
                ));

                $redirectTo = sprintf('%s&token=%s', $currentIndex, $this->token);

                $this->log->debug('Redirecting to: ' . $redirectTo);

                Tools::redirect($redirectTo);

                return true;
            }

            $this->log->debug($this->l('Supplier purchase record has been failed!'));

            Tools::displayError($this->l('Supplier purchase record has been failed!'));

            return false;
        }
    }
}
