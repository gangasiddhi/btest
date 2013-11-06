<?php

class SupplierPurchase extends ObjectModel {
    public $id_warehouse;
    public $id_supplier;
    public $id_status = 0;
    public $date_add;
    public $date_upd;

    public $purchaseDetail = array();

    protected $fieldsRequired = array(
        'id_warehouse',
        'id_supplier'
    );
    protected $fieldsSize = array(
        'id_warehouse' => 20
    );
    protected $fieldsValidate = array(
        'id_supplier' => 'isUnsignedId',
        'id_status' => 'isUnsignedId'
    );
    protected $table = 'supplier_purchase';
    protected $detailTable = 'supplier_purchase_detail';
    protected $historyTable = 'supplier_purchase_history';
    protected static $tableName = 'supplier_purchase';
    protected static $detailTableName = 'supplier_purchase_detail';
    protected static $historyTableName = 'supplier_purchase_history';
    protected $identifier = 'id_purchase';
    protected static $identifierName = 'id_purchase';

    public function __construct($id = NULL, $id_lang = NULL) {
        parent::__construct($id, $id_lang);

        // as we're working for only 1 warehouse..
        $this->id_warehouse = Configuration::get('WAREHOUSE_ID');

        // filling details..
        $this->getDetails();

        $this->log = Logger::getLogger(get_class($this));
    }

    public function getFields() {
        parent::validateFields();

        $fields['id_warehouse'] = pSQL($this->id_warehouse);
        $fields['id_supplier'] = pSQL($this->id_supplier);
        $fields['id_status'] = pSQL($this->id_status);
        $fields['date_add'] = pSQL($this->date_add);
        $fields['date_upd'] = pSQL($this->date_upd);

        return $fields;
    }

    public function getDetails() {
        $sql = 'SELECT bspd.`reference`,
                bspd.`quantity`
            FROM `bu_supplier_purchase_detail` bspd
            WHERE bspd.`id_purchase` = ' . pSQL($this->id);

        $tmp = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql);

        foreach ($tmp as $i => $d) {
            $this->purchaseDetail[$d['reference']] = $d['quantity'];
        }

        return $this->purchaseDetail;
    }

    public static function getPurchase($id_purchase) {
        if (empty($id_purchase)) {
            return false;
        }

        $log = Logger::getLogger(get_class(self));
        $sql = 'SELECT *
            FROM `' . _DB_PREFIX_ . self::$tableName . '` bsp
            JOIN `' . _DB_PREFIX_ . self::$detailTableName . '` bspd ON bspd.`' . self::$identifierName . '` = bsp.`' . self::$identifierName . '`
            WHERE bsp.`' . self::$identifierName . '` = ' . pSQL($id_purchase);

        $log->debug('Getting supplier purchases with following SQL: ' . $sql);

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        $log->debug('Got purchases: ' . print_r($result, true));

        return $result;
    }

    public function wipeDetails() {
        $log = Logger::getLogger(get_class(self));
        $sql = 'DELETE FROM `' . _DB_PREFIX_ . $this->detailTable . '` WHERE `' . $this->identifier . '` = ' . $this->id;

        $log->debug('Getting supplier purchases with following SQL: ' . $sql);

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        $log->debug('Got purchases: ' . print_r($result, true));
    }

    public static function getPurchases($limit = 10, $page = 1) {
        $log = Logger::getLogger(get_class(self));
        $sql = 'SELECT SQL_CALC_FOUND_ROWS bsp.*,
                (
                    SELECT SUM(bspd.`quantity`)
                    FROM `' . _DB_PREFIX_ . self::$detailTableName . '` bspd
                    WHERE bspd.`' . self::$identifierName . '` = bsp.`' . self::$identifierName . '`
                ) AS "poQuantity",
                (
                    SELECT SUM(bsph.`quantity_success`)
                    FROM `' . _DB_PREFIX_ . self::$historyTableName . '` bsph
                    WHERE bsph.`' . self::$identifierName . '` = bsp.`' . self::$identifierName . '`
                ) AS "success",
                (
                    SELECT SUM(bsph.`quantity_fail`)
                    FROM `' . _DB_PREFIX_ . self::$historyTableName . '` bsph
                    WHERE bsph.`' . self::$identifierName . '` = bsp.`' . self::$identifierName . '`
                ) AS "fail"
            FROM `' . _DB_PREFIX_ . self::$tableName . '` bsp
            ORDER BY bsp.`' . self::$identifierName . '` DESC
            LIMIT ' . pSQL((((int)($page) - 1) * (int)($limit)) . ', ' . (int)($limit));

        $log->debug('Getting supplier purchases with following SQL: ' . $sql);

        $result['objects'] = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        $result['totalItem'] = (int) Db::getInstance()->getValue('SELECT FOUND_ROWS() as rowCount');

        $log->info('Got ' . $result['totalItem'] . ' purchases..');

        return $result;
    }

    public function saveDetails($wipeDetailsFirst = false) {
        $err = true;

        if ($wipeDetailsFirst) {
            $this->wipeDetails();
        }

        foreach ($this->purchaseDetail as $reference => $quantity) {
            $this->log->debug('Purchase detail is being created for ' . $reference . ' with quantity ' . $quantity);

            $detail = new SupplierPurchaseDetail();
            $detail->id_purchase = $this->id;
            $detail->reference = $reference;
            $detail->quantity = $quantity;

            $err = $detail->save();
        }

        return $err;
    }

    public function save($nullValues = false, $autodate = true, $wipeDetailsFirst = false) {
        $err = true;
        $isNew = ! isset($this->id);

        if (empty($this->purchaseDetail)) {
            $this->log->error('Supplier purchase details don\'t exist, aborting..');

            return false;
        }

        $err = parent::save($nullValues, $autodate);

        if ($isNew) {
            $this->log->debug('Purchase is stored into the database with id: ' . $this->id);
        }

        if ($isNew OR $wipeDetailsFirst) {
            $err = $this->saveDetails($wipeDetailsFirst);
        }

        return $err;
    }
}
