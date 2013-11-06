<?php

/**
 * Description of araskargo
 *
 * This module is used to intergate the Aras kargo via SOAP technic, inorder to send the order
 * details to the Aras kargo for shipment tracking,
 *
 * @author mesuutt
 */
class ArasKargo Extends Module {
    public function __construct() {
        $this->name = 'araskargo';
        $this->tab = 'ExternalShipment';
        $this->version = '1.2';
        $this->author = 'PrestaShop';
        $this->need_instance = 0;

        parent::__construct();

        $this->displayName = $this->l('Aras Kargo Integration');
        $this->description = $this->l('Customer the can track the order status with Aras Kargo');

        require_once(_PS_MODULE_DIR_ . $this->name . '/ArasKargoShipment.php');
        require_once(_PS_MODULE_DIR_ . $this->name . '/XmlParser.php');

        $this->account = new stdClass;
        $this->account->userName = Configuration::get('ARAS_CARGO_USERNAME');
        $this->account->password = Configuration::get('ARAS_CARGO_PASSWORD');

        // get logger
        $this->log = Logger::getLogger(get_class($this));
    }

    /* This module is hooked with externalShippingIntegration hook */

    public function install() {
        if (! parent::install()
        	OR ! $this->registerHook('updateOrderStatus')
            OR ! $this->registerHook('cargoShipped')
            OR ! $this->registerHook('cargoDelivered')
            OR ! $this->registerHook('cargoUndelivered')
            OR ! $this->registerHook('sendOrderDataToCarrier')) {
            return false;
        }

        return true;
    }

	public function uninstall(){
		return (parent::uninstall());
	}

    public function hookSendOrderDataToCarrier($params) {
        return $this->notifyForDispatch($params);
    }

    protected function notifyForDispatch($params) {
        global $cookie;

        try {
            $orderId = $params['orderId'];
            $isExchangedOrder = $params['IsExchangedOrder'] ? 1 : 0;

            $order = new Order((int) $orderId);
            $addressDelivery = new Address($order->id_address_delivery, (int) $cookie->id_lang);
            $aras = new ArasKargoShipment();

            if (_BU_ENV_ == 'development') {
                $aras->Description = 'We are working on integration of Aras Kargo. Please don\'t care or provide us a test environment :)';
            }

            $aras->UserName = $this->account->userName;
            $aras->Password = $this->account->password;
            $aras->CargoKey = $orderId;
            $aras->cityName = State::getNameById($addressDelivery->id_state);
            $aras->invoiceKey = $order->invoice_number;
            $aras->receiverAddress = $addressDelivery->address1;
            $aras->receiverCustName = $addressDelivery->firstname . " " . $addressDelivery->lastname;
            $aras->receiverPhone1 = $addressDelivery->phone ? $addressDelivery->phone : $addressDelivery->phone_mobile;
            $aras->townName = Province::getProvinceNameById($addressDelivery->id_province);
            $aras->orgReceiverCustId = $order->invoice_number;
            $aras->Desi = 1;
            $aras->UnitID = 0;
            $aras->Date = date('Y-m-d') . 'T' . date('H:i:s');
            $aras->LovPayortypeID = 0;
            $aras->ttDocumentSaveType = 0;
            $aras->IsExchangedOrder = $isExchangedOrder;

            if ($order->module == 'cashondelivery') {
                $aras->TtInvoiceAmount = $order->total_paid_real;
                $aras->ttCollectionType = 1;
            } else {
                $aras->TtInvoiceAmount  = 0;
                $aras->ttCollectionType = null;
            }

            $result = $aras->createShipment();
        } catch (Exception $e) {
            $this->log->fatal("Aras Kargo API Error", $e);

            Tools::sendMailToAdmins("Aras Kargo API Error", $e->getMessage());
        }

        return true;
    }

	// When user change order state from BO. notify Aras Kargo again
    public function hookUpdateOrderStatus($params) {
       	$newOS = $params['newOrderStatus'];
        if ($newOS->id == _PS_OS_PROCESSED_) {
            $orderId = $params['id_order'];
            $notifyParams = array("orderId" => $orderId);

            if (OrderHistory::isOrderStateExist($orderId, _PS_OS_PROCESSED_)) {
                $notifyParams['IsExchangedOrder'] = 1;
            }

            return $this->notifyForDispatch($notifyParams);
        }

        return true;
    }

    public function hookCargoShipped($params) {
        if (empty($params['notificationXMLData'])) {
            $this->log->fatal('notificationXMLData is empty!');

            return false;
        }

        $newOSId = Configuration::get('PS_OS_SHIPPING');

        /* Receiving the notifications from the Aras kargo, contains the SHIPMENTNUMBER */
        $xml = XMLHelper::loadFromString($params['notificationXMLData']);

        if (! $xml) {
            $message = 'Invalid xml from Aras Kargo: ' . print_r($params['notificationXMLData'], true);

            $this->log->fatal($message);

            header('HTTP/1.1 400 ' . $message);

            return false;
        }

        foreach ($xml->xpath('//CARGOLIST/CARGO') as $orderItem) {
            $invoiceNumber = trim($orderItem->INTEGRATION_CODE);

            $this->log->info('Getting order with invoice number: ' . $invoiceNumber);

            $order = Order::getOrderByInvoiceNumber($invoiceNumber);

            if (! $order) {
                $message = 'No order has been found with invoice number: ' . $invoiceNumber;

                $this->log->error($message);

                header('HTTP/1.1 400 ' . $message);

                return false;
            }

            $order->setTrackingNumber((int) $orderItem->TRACKING_NUMBER);
            $order->setCurrentState($newOSId);
            $order->save();
        }

        return true;
    }

    public function hookCargoDelivered($params) {
        global $cookie;

        if (empty($params['notificationXMLData'])) {
            $this->log->fatal('notificationXMLData is empty!');

            return false;
        }

        $newOSId = Configuration::get('PS_OS_DELIVERED');

        /* Receiving the notifications from the Aras kargo, contains the Delivered orders */
        $xml = XMLHelper::loadFromString($params['notificationXMLData']);

        if (! $xml) {
            $message = 'Invalid xml from Aras Kargo: ' . print_r($params['notificationXMLData'], true);

            $this->log->fatal($message);

            header('HTTP/1.1 400 ' . $message);

            return false;
        }

        foreach ($xml->xpath('//CARGOLIST/CARGO') as $orderItem) {
            $invoiceNumber = trim($orderItem->INTEGRATION_CODE);

            $this->log->info('Getting order with invoice number: ' . $invoiceNumber);

            $order = Order::getOrderByInvoiceNumber($invoiceNumber);

            if (! $order) {
                $message = 'No order has been found with invoice number: ' . $invoiceNumber;

                $this->log->error($message);

                header('HTTP/1.1 400 ' . $message);

                return false;
            }

            if ($order->getHistory($cookie->id_lang, _PS_OS_CANCELED_)) {
                $this->log->info('Order [' . $order->id . '] update is being skipped as it seems to be cancelled already..');

                return false;
            }

            if ($order->getHistory($cookie->id_lang, _PS_OS_UNDELIVERED_)) {
                $this->log->info('Order [' . $order->id . '] update is being skipped as it seems to be updated anyway..');

                return false;
            }

            if ($order->getHistory($cookie->id_lang, _PS_OS_DELIVERED_)) {
                $this->log->info('Order [' . $order->id . '] update is being skipped as it seems to be updated anyway..');

                return false;
            }

            $order->setCurrentState($newOSId);
        }

        return true;
    }

    public function hookCargoUndelivered($params) {
        global $cookie;

        if (empty($params['notificationXMLData'])) {
            $this->log->fatal('notificationXMLData is empty!');

            return false;
        }

        $newOSId = Configuration::get('PS_OS_UNDELIVERED');

        $xml = XMLHelper::loadFromString($params['notificationXMLData']);

        if (! $xml) {
            $message = 'Invalid xml from Aras Kargo: ' . print_r($params['notificationXMLData'], true);

            $this->log->fatal($message);

            header('HTTP/1.1 400 ' . $message);

            return false;
        }

        foreach ($xml->xpath('//CARGOLIST/CARGO') as $orderItem) {
            $invoiceNumber = trim($orderItem->INTEGRATION_CODE);

            $this->log->info('Getting order with invoice number: ' . $invoiceNumber);

            $order = Order::getOrderByInvoiceNumber($invoiceNumber);

            if (! $order) {
                $message = 'No order has been found with invoice number: ' . $invoiceNumber;

                $this->log->error($message);

                header('HTTP/1.1 400 ' . $message);

                return false;
            }

            if ($order->getHistory($cookie->id_lang, _PS_OS_CANCELED_)) {
                $this->log->info('Order [' . $order->id . '] update is being skipped as it seems to be cancelled already..');

                return false;
            }

            if ($order->getHistory($cookie->id_lang, _PS_OS_DELIVERED_)) {
                $this->log->info('Order [' . $order->id . '] update is being skipped as it seems to be updated anyway..');

                return false;
            }

            if ($order->getHistory($cookie->id_lang, _PS_OS_UNDELIVERED_)) {
                $this->log->info('Order [' . $order->id . '] update is being skipped as it seems to be updated anyway..');

                return false;
            }

            $order->setCurrentState($newOSId);
        }

        return true;
    }

    public static function getTrackingLinkByOrder(Order $iOrder) {
        if (empty($iOrder->tracking_number)) {
            return "http://appl-srv.araskargo.com.tr/CargoInfoV2.aspx?accountid="
                . "418CAE0F55B0894EA4AA40E4E3FBB950&sifre=177716&alici_kod=" . $iOrder->invoice_number;
        }

        return "http://kargotakip.araskargo.com.tr/mainpage.aspx?code=" . $iOrder->tracking_number;
    }
}

?>
