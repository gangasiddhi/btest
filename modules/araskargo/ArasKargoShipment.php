<?php

/**
 * Description of araskargoshippment
 *
 * This class is used for SOAP Integration with Aras kargo
 *
 * @author gangadhar
 */

require_once(dirname(__file__) . '/../../config/config.inc.php');

class ArasKargoShipment {
    var $Servis;
    var $DefaultEncoding = 'UTF-8';
    var $Url = 'http://appls-srv.araskargo.com.tr/arascargoservice/arascargoservice.asmx?WSDL';
    var $UserName = '';
    var $Password = '';
    var $CargoKey = '';
    var $invoiceKey = '';
    var $receiverCustName = '';
    var $receiverAddress = '';
    var $receiverPhone1 = '';
    var $cityName = '';
    var $townName = '';
    var $custProdId = '';
    var $orgReceiverCustId = '';
    var $Desi = '';
    var $Kg = '';
    var $CargoCount = '';
    var $TtInvoiceAmount = '';
    var $TtDocumentId = '';
    var $TaxOfficeId = '';
    var $UnitID = '';
    var $Date = '';
    var $LovPayortypeID = '';
    var $data = array();
    var $ttDocumentSaveType = '';
    var $ttCollectionType = '';
    var $IsExchangedOrder;

    private $log;

    function __construct() {
        $this->log = Logger::getLogger(get_class($this));

        try {
            $return = $this->Servis = new SoapClient($this->Url, array(
                'encoding' => $this->DefaultEncoding,
                'exceptions' => true
            ));
        } catch (Exception $exp) {
            $this->log->fatal("Error during create SoapClient", $exp);

            Tools::sendMailToAdmins("Aras Kargo API Error", $e->getMessage());
        }
    }

    function ShippingOrder() {
        $result = array(
            "UserName" => $this->UserName,
            "Password" => $this->Password,
            "CargoKey" => $this->CargoKey,
            "InvoiceKey" => $this->invoiceKey,
            "ReceiverCustName" => $this->receiverCustName,
            "ReceiverAddress" => $this->receiverAddress,
            "ReceiverPhone1" => $this->receiverPhone1,
            "CityName" => $this->cityName,
            "TownName" => $this->townName,
            "CustProdId" => $this->CustProdId,
            "OrgReceiverCustId" => $this->orgReceiverCustId,
            "Desi" => $this->Desi,
            "Kg" => $this->Kg,
            "CargoCount" => $this->CargoCount,
            "TtInvoiceAmount" => $this->TtInvoiceAmount,
            "TtDocumentId" => $this->TtDocumentId,
            "TaxOfficeId" => $this->TaxOfficeId,
            "UnitID" => $this->UnitID,
            "Date" => $this->Date,
            "LovPayortypeID" => $this->LovPayortypeID,
            "TtDocumentSaveType" => $this->ttDocumentSaveType,
            "TtCollectionType" => $this->ttCollectionType,
            "IsExchangedOrder" => $this->IsExchangedOrder
        );

        return $result;
    }

    function createShipment() {
        try {
            $reqParams = $this->ShippingOrder();

            $this->log->debug("Request Parameters:\n" . print_r($reqParams, true));

            $result = $this->Servis->SetDispatch(array(
                "shippingOrders" => array(
                    "ShippingOrder" => $reqParams
                ),
                "userName" => $this->UserName,
                "password" => $this->Password
            ));

            $this->log->debug("Request Result:\n" .  print_r($result, true));

            return $result;

        } catch (Exception $e) {
            $this->log->fatal("Error during SetDispatch", $e);

            Tools::sendMailToAdmins("Error during SetDispatch", $e->getMessage());
        }
    }

    function Functions() {
        return $this->Servis->__getFunctions();
    }

    function LastRequest() {
        return $this->Servis->__getLastRequest();
    }

    public function getShipments($params) {
        return $this->Servis->GetDispatch($params);
    }
}

?>
