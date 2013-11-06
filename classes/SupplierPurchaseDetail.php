<?php

class SupplierPurchaseDetail extends ObjectModel {
    public $id_purchase;
    public $reference;
    public $quantity;

    protected $fieldsRequired = array(
        'reference',
        'quantity'
    );
    protected $fieldsSize = array(
        'reference' => 32
    );
    protected $fieldsValidate = array(
        'reference' => 'isString',
        'quantity' => 'isInt'
    );
    protected $tables = array('supplier_purchase_detail');
    protected $table = 'supplier_purchase_detail';
    protected static $tableName = 'supplier_purchase_detail';
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
        $fields['quantity'] = pSQL($this->quantity);

        return $fields;
    }
}
