<?php
require_once(_PS_ROOT_DIR_.'/config/config.inc.php');

require_once('WarehouseCore.php');

class Siber extends WarehouseCore {

    public $secretKey;
    public function __construct() {
        $this->secretKey = Tools::encrypt(Configuration::get('SIBER_HASH'));
    }
}


class SiberN1 extends Siber {
    public $hash;
    protected $xmlString;
    public $xml;
    public $incorrectOrderIds;

    public function setParams($params) {
        $this->hash = $params['hash'];
        $this->xmlString = $params['data'];
        $this->xml = XMLHelper::loadFromString($this->xmlString);
        return $this;
    }

    public function validateParams() {
        return ($this->hash == $this->secretKey AND XMLHelper::isValid($this->xmlString));
    }

    public function checkOrders() {
        foreach ($this->xml->ORDERS->ORDER as $orderObj) {
            $orderId = (int)$orderObj;
            if (!Order::isExist($orderId)) {
                array_push($this->incorrectOrderIds, $orderId);
            }
        }
        return ($this->incorrectOrderIds) ? false : true;
    }
}

class SiberN2 extends Siber {
    public function setParams($params) {
        $this->hash = $params['hash'];
        $this->orderId = $params['orderId'];
        return $this;
    }

    public function validateParams() {
        return ($this->hash == $this->secretKey AND $this->orderId AND Order::isExist($this->orderId));
    }
}

?>
