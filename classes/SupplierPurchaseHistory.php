<?php

class SupplierPurchaseHistory extends ObjectModel {
    public $id_purchase;
    public $reference;
    public $quantity_success;
    public $quantity_fail;
    public $reason;
    public $date_add;
    public $date_upd;

    protected $fieldsRequired = array(
        'reference'
    );
    protected $fieldsSize = array(
        'reference' => 32
    );
    protected $fieldsValidate = array(
        'reference' => 'isString',
        'quantity_success' => 'isInt',
        'quantity_fail' => 'isInt',
        'reason' => 'isString'
    );
    protected $tables = array('supplier_purchase_history');
    protected $table = 'supplier_purchase_history';
    protected static $tableName = 'supplier_purchase_history';
    protected $identifier = 'id_purchase';
    protected static $identifierName = 'id_purchase';

    public function __construct($id = NULL, $id_lang = NULL) {
        parent::__construct($id, $id_lang);

        $this->log = Logger::getLogger(get_class($this));
    }

    public function getFields() {
        parent::validateFields();

        $fields['id_purchase'] = ($this->id ? pSQL($this->id) : pSQL($this->id_purchase));
        $fields['reference'] = pSQL($this->reference);
        $fields['quantity_success'] = pSQL($this->quantity_success);
        $fields['quantity_fail'] = pSQL($this->quantity_fail);
        $fields['reason'] = pSQL(json_encode($this->reason));
        $fields['date_add'] = pSQL($this->date_add);
        $fields['date_upd'] = pSQL($this->date_upd);

        return $fields;
    }
}
