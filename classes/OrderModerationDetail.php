<?php
/*
* 2007-2011 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2011 PrestaShop SA
*  @version  Release: $Revision: 7690 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/


class OrderModerationDetailCore extends ObjectModel {
    public $id_moderation;
    public $id_moderation_type;
    public $id_order;
    public $id_reason;
    public $message;
    public $shipping;
    public $was_moderated;
    public $id_employee;
    public $shipping_price;
    public $used_discounts_json;
    public $bank_refund_amount;
    public $id_order_history;
    public $date_add;
    public $date_moderated;

    /**
     * Using  on moderation_result table, OrderModeration/Product Moderation
     */
    const MODERATION_OBJECT_TYPE = 1;

    protected   $fieldsRequired = array('id_moderation_type', 'id_order');

    protected   $fieldsValidate = array('id_moderation_type' => 'isUnsignedId', 'id_order' => 'isUnsignedId',
        'id_reason' => 'isUnsignedId', 'message' => 'isString', 'shipping' => 'isBool', 'was_moderated' => 'isBool',
        'id_employee' => 'isString', 'shipping_price' => 'isString', 'bank_refund_amount' => 'isFloat', 'id_employee' => 'isInt', 'id_order_history' => 'isInt');

    protected $table = 'order_moderation';
    protected static $tableName = 'order_moderation';
    protected   $identifier = 'id_moderation';

    public function getFields() {
        parent::validateFields();

        $fields = array(
            'id_moderation_type' => (int) $this->id_moderation_type,
            'id_order' =>  (int) $this->id_order,
            'id_reason' => (int) $this->id_reason,
            'message' => pSQL($this->message),
            'shipping' =>  $this->shipping,
            'was_moderated' => $this->was_moderated,
            'id_employee' => $this->id_employee,
            'shipping_price' => $this->shipping_price,
            'used_discounts_json' => $this->used_discounts_json,
            'bank_refund_amount' =>  $this->bank_refund_amount,
            'id_order_history' => $this->id_order_history,
            'date_add' => $this->date_add,
            'date_moderated' => $this->date_moderated,
        );

        return $fields;
    }

    public static function isExistByOrderId($id_order, $getModerated = false) {
        $sql = "Select id_moderation from " . _DB_PREFIX_ .self::$tableName."  WHERE id_order=$id_order and  was_moderated=".($getModerated ? 1 : 0);
        return DB::getInstance()->getValue($sql) ? true : false;
    }

    public static function getModerations($limit = 10, $page = 1, $getModerated = false) {
        $sql = 'Select SQL_CALC_FOUND_ROWS * from '._DB_PREFIX_.'order_moderation WHERE  was_moderated='. ($getModerated ? 1 : 0) .' ORDER BY id_moderation DESC'
        .' LIMIT '.(((int)($page) - 1) * (int)($limit)).', '.(int)($limit);

        $result['objects'] = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        $result['totalItem'] = (int) Db::getInstance()->getValue('SELECT FOUND_ROWS() as rowCount');
        return $result;
    }

    /**
     * Approve moderation(Refund/Cancel)
     */
    public function approve() {

        $iOrder = new Order($this->id_order);
        if ($this->id_moderation_type == Configuration::get('MODERATION_TYPE_CANCEL')) {
            $isCancellable = $iOrder->isCancellable();
            if ($isCancellable) {
                return $this->cancelOrder();
            } else {
                return $this->refundOrder();
            }

        } elseif ($this->id_moderation_type == Configuration::get('MODERATION_TYPE_EXCHANGE')) {
            return $this->exchangeOrder();
        }

        return false;
    }

    // Cancel moderation, not order
    public function cancel() {
        global $cookie;

        $iOrder = new Order($this->id_order);
        $iOrder->setCurrentState(OrderHistory::getPreviousOrderStateId($iOrder->id));

        $this->id_employee = $cookie->id_employee;
        $this->date_moderated = date("Y-m-d H:i:s", time());
        $this->was_moderated = true;
        $this->id_employee = $cookie->id_employee;
        return  $this->save();
    }

    public function __construct($id = NULL, $id_lang = NULL) {
        parent::__construct($id, $id_lang);

        $this->log = Logger::getLogger(get_class($this));
    }

    private function cancelOrder() {
        global $cookie;

        $errors = array();

        $this->beginTransaction();

        try {
            $iOrder = new Order($this->id_order);
            $shippingPrice = $this->shipping && $iOrder->total_shipping && $iOrder->total_products_wt < Configuration::get('PS_SHIPPING_FREE_PRICE') ? $iOrder->total_shipping : 0;

            if ($iOrder->module != 'cashondelivery' && $iOrder->module != 'freeorder') {
                $refundAmount = $iOrder->total_paid_real;

                if (! $shippingPrice) { // total_paid_real already contains shipping cost so decrease if shipping not selected
                    $refundAmount -= $shippingPrice;
                } else {
                    $this->shipping_price = $shippingPrice;
                }

                /*Get the response from the bank on refund*/
                $bank_response = Hook::getBankResponseOnOrderStatusChange(_PS_OS_CANCELED_, (int)($this->id_order), $refundAmount);

                if(!$bank_response) {
                    $this->log->error("No response from bank, \t id-order:".$iOrder->id);
                }

                $this->log->debug("State: cancelled,\t Bank response AdminOrders:$bank_response,\t id-order:$iOrder->id,\t total paid:.$iOrder->total_paid");

                $response_list = explode('|', $bank_response);
                if($response_list[0] != 'Approved' AND $response_list[0] != 1) {
                    $err_msg = isset($response_list[2]) && $response_list[2]  ? $response_list[2] : '';
                    $errors[] = Tools::displayError('The Response from the bank is '.$response_list[0].', '.$err_msg.' Please try again later');
                    return $errors;
                }

                $this->bank_refund_amount = ($shippingPrice) ? $refundAmount - $shippingPrice : $refundAmount;
            }

            $detailIds = $iOrder->getOrderDetailIds();

            foreach($detailIds as $i=>$id_order_detail) {
                $orderDetail = new OrderDetail($id_order_detail);

                $price = $orderDetail->product_price * (1 + $orderDetail->tax_rate * 0.01);
                if ($orderDetail->reduction_percent != 0.00)
                    $reduction_amount = $price * $orderDetail->reduction_percent / 100;
                elseif ($orderDetail->reduction_amount != '0.000000')
                    $reduction_amount = Tools::ps_round($orderDetail->reduction_amount, 2);
                if (isset($reduction_amount) AND $reduction_amount)
                    $price = Tools::ps_round($price - $reduction_amount, 2);
                $price += Tools::ps_round($orderDetail->ecotax * (1 + $orderDetail->ecotax_tax_rate / 100), 2);
                $productPrice = number_format($orderDetail->product_quantity * $price, 2, '.', '');

                $historyDetail = new OrderHistoryDetails();
                $historyDetail->id_order =  $iOrder->id;
                $historyDetail->id_order_detail = $id_order_detail;
                $historyDetail->id_order_state = _PS_OS_CANCELED_;
                $historyDetail->amount = $productPrice;
                $historyDetail->quantity  = abs($orderDetail->product_quantity);
                $historyDetail->product_reference = $orderDetail->product_reference;
                $historyDetail->id_employee = intval($cookie->id_employee);
                if($iOrder->total_shipping > 0 && $i==0) {
                    $historyDetail->shipping_cost = $iOrder->total_shipping;
                }

                if(!$historyDetail->save()) {
                    $errors[] = Tools::displayError('Inserting Order History Details Failed.');
                    return $errors;
                }

                $iOrder->cancelProduct($id_order_detail, $orderDetail->product_quantity, _PS_OS_CANCELED_);
            }

            $history = new OrderHistory();
            $history->id_order = $iOrder->id;
            $history->id_employee = intval($cookie->id_employee);
            $history->changeIdOrderState(_PS_OS_CANCELED_, intval($iOrder->id));
            $carrier = new Carrier(intval($iOrder->id_carrier), intval($iOrder->id_lang));

            if (!$history->addWithemail(true, null)) {
                $errors[] = Tools::displayError('an error occurred while changing status or was unable to send e-mail to the customer');
                return $errors;
            }

            if ($iOrder->total_discounts > 0.00) {
                $createdDiscountsLog = array();
                $orderDiscounts = $iOrder->getDiscounts();

                foreach($orderDiscounts as $orderDiscount) {
                    $iDiscount = new Discount($orderDiscount['id_discount']);
                    $usedCount = $iDiscount->usedByCustomer($iOrder->id_customer);

                    if ($usedCount > 0) {
                        $iNewDiscount = Discount::createDiscountForOrder($iOrder, Mail::l('Refund Voucher'), $iDiscount->id_discount_type , $orderDiscount['value']);
                        $iOrder->sendDiscountMailToCustomer($iNewDiscount);

                        array_push($createdDiscountsLog, array(
                            'id' => $iNewDiscount->id,
                            'id_discount_type' => $iNewDiscount->id_discount_type,
                            'value' => $iNewDiscount->value
                        ));
                    }
                }

                if ($createdDiscountsLog) {
                    $this->used_discounts_json = json_encode($createdDiscountsLog);
                }

                if (! $iNewDiscount) {
                    $errors[] = Tools::displayError('Cannot generate discount during canceling order');
                }
            }

            if ($errors) {
                return $errors;
            }

            $this->id_order_history = $history->id;
            $this->id_employee = $cookie->id_employee;
            $this->date_moderated = date("Y-m-d H:i:s", time());
            $this->was_moderated = true;
            $this->save();

            $this->commitTransaction();
            return true;

        } catch (Exception $e) {
            $this->rollbackTransaction();
            $this->log->fatal("Error:" . $e);
            return false;
        }
    }

    private function refundOrder() {
        global $cookie;
        $errors = array();

        $msgLogFile = new LogFile($this->module . "/order-moderation".date('Y-m-d',time()).".log", 'a');
        $msgLogFile->setPrefix('[refundOrder]');

        $this->beginTransaction();
        try {

            $iOrder = new Order($this->id_order);
            $shipping = $this->shipping ? true : false;
            $shippingPrice = $this->shipping && $iOrder->total_shipping && $iOrder->total_products_wt < Configuration::get('PS_SHIPPING_FREE_PRICE') ? $iOrder->total_shipping : 0;
            $refundAmount = $iOrder->total_paid_real;

            if ($iOrder->module != "freeorder" && !$iOrder->isRefundable()) {
                $errors[] = Tools::displayError("Refund cannot be applied to orders(with installments) on the same day of purchase. Either wait until the next day or exchange the order.");
                return  $errors;
            }

            $detailIds = $iOrder->getOrderDetailIds();
            foreach($detailIds as $id_order_detail) {
                $iOrderDetail = new OrderDetail($id_order_detail);
                $qty = $iOrderDetail->product_quantity;

                $id_order_detail_exist = OrderHistoryDetails::getOrderDetail($id_order_detail, _PS_OS_REFUND_, false);

                $price = $iOrderDetail->product_price * (1 + $iOrderDetail->tax_rate * 0.01);
                if ($iOrderDetail->reduction_percent != 0.00) {
                    $reduction_amount = $price * $iOrderDetail->reduction_percent / 100;
                } elseif ($iOrderDetail->reduction_amount != '0.000000') {
                    $reduction_amount = Tools::ps_round($iOrderDetail->reduction_amount, 2);
                }

                if (isset($reduction_amount) AND $reduction_amount) {
                    $price = Tools::ps_round($price - $reduction_amount, 2);
                }

                $price += Tools::ps_round($iOrderDetail->ecotax * (1 + $iOrderDetail->ecotax_tax_rate / 100), 2);
                $productPrice = number_format($qty * $price, 2, '.', '');

                //$refundAmount += $productPrice;

                if($id_order_detail_exist) {
                    $msgLogFile->addLine("Updating the Existing");

                    if(OrderHistoryDetails::updateOrderDetail($id_order_detail_exist, floatval($productPrice), abs($qty), $shipping ? $iOrder->total_shipping : 0, intval($cookie->id_employee))) {
                        $errors[] = Tools::displayError('Updating Order History Details Failed.');
                        return $errors;
                    }
                } else {
                    $msgLogFile->addLine("Adding New Entry bu_order_history_details:")
                       ->addLine("Quantity:".abs($qty));

                    $details = new OrderHistoryDetails();
                    $details->id_order =  $iOrder->id;
                    $details->id_order_detail = $id_order_detail;
                    $details->id_order_state = _PS_OS_REFUND_;
                    $details->product_reference = $iOrderDetail->product_reference;
                    $details->id_employee = intval($cookie->id_employee);
                    $details->quantity  = abs($qty);
                    $details->amount = floatval($productPrice);
                    $details->refund_product_price_with_tax = floatval($productPrice);
                    $details->discounts = floatval(Tools::ps_round($iOrder->total_discounts, 2));
                    $details->shipping_cost = $shippingPrice;

                    if(!$details->save()) {
                        $errors[] = Tools::displayError('Inserting Order History Details Failed.');
                        return $errors;
                    }
                }
            }


            if (!$shippingPrice) { // total_paid_real already contains shipping cost so decrease if shipping not selected
                $refundAmount -= $shippingPrice;
            }

            if (!$iOrder->total_shipping && $shippingPrice) { // order->total_paid contains shipping price already.
                $refundAmount += $shippingPrice;
            }

            $refundAmount = $refundAmount > 0 ? $refundAmount : 0; // If less than 0, set 0

            $history = new OrderHistory();
            $history->id_order = $iOrder->id;
            $history->id_employee = $cookie->id_employee;

            if($iOrder->total_paid_real == 0.00) {
                if( $state = OrderHistoryDetails::returnOrderStatus($iOrder->id, _PS_OS_REFUND_ , 'R')) {
                    $history->changeIdOrderState($state, intval($iOrder->id), $id_order_detail);
                }
            } else {
                $history->changeIdOrderState(_PS_OS_REFUND_, intval($iOrder->id), $id_order_detail);
            }

            $createdDiscountsLog = array();

            if($iOrder->total_discounts > 0.00) {
                // Recreate used discounts
                $orderDiscounts = $iOrder->getDiscounts();
                foreach($orderDiscounts as $orderDiscount) {
                    $iDiscount = new Discount($orderDiscount['id_discount']);
                    $usedCount = $iDiscount->usedByCustomer($iOrder->id_customer);
                    if ($usedCount > 0) {
                        $iNewDiscount = Discount::createDiscountForOrder($iOrder, Mail::l('Refund Voucher'), $iDiscount->id_discount_type , $orderDiscount['value']);
                        $iOrder->sendDiscountMailToCustomer($iNewDiscount);

                        array_push($createdDiscountsLog, array(
                            'id' => $iNewDiscount->id,
                            'id_discount_type' => $iNewDiscount->id_discount_type,
                            'value' => $iNewDiscount->value
                        ));
                    }
                }
            }

            if ($iOrder->module !== 'cashondelivery'  && $iOrder->module !== 'freeorder') {

                $msgLogFile->addLine('Refunded money:'.$refundAmount);
                $result = PG::refundViaBank($iOrder->id, Tools::ps_round($refundAmount, 2));
                $msgLogFile->addLine('Bank Response:'. print_r($result, true));
                if ($result['error']) {
                    $errors[] =  Tools::displayError($result['errorMessage']);
                    return $errors;
                }

                $this->bank_refund_amount = ($shippingPrice) ? $refundAmount - $shippingPrice : $refundAmount;
            }

            if ($refundAmount > 0) {
                if ($iOrder->module == 'cashondelivery') {
                    // Create discount because refund
                    $exchangeCredit = Discount::createDiscountForOrder($iOrder, Mail::l('Refund Voucher'),
                            _EXCHANGE_VOUCHER_TYPE_ID_, $refundAmount);
                    if (!$exchangeCredit) {
                        $msg = Tools::displayError('An error occurred while creating voucher of product');
                        $msgLogFile->addLine($msg);
                        $errors[] =  $msg;
                        return $errors;
                    }

                    $iOrder->sendDiscountMailToCustomer($exchangeCredit);

                    array_push($createdDiscountsLog, array(
                        'id' => $exchangeCredit->id,
                        'id_discount_type' => $exchangeCredit->id_discount_type,
                        'value' => $exchangeCredit->value
                    ));

                    if (!$history->save()) {
                        $msg = Tools::displayError('An error occurred while save history');
                        $errors[] = $msg;
                        $msgLogFile->addLine($msg);
                        return $errors;
                    }
                } else  {
                    $templateVars = array('{refund_amt}' => Tools::ps_round($refundAmount, 2));
                    if (!$history->addWithemail(true, $templateVars)) {
                        $errors[] = Tools::displayError('An error occurred while changing status or was unable to send e-mail to the customer');
                        return $errors;
                    }
                }
            } else {
                if (!$history->save()) {
                    $msg = Tools::displayError('An error occurred while save history');
                    $errors[] = $msg;
                    $msgLogFile->addLine($msg);
                    return $errors;
                }
            }

            if ($createdDiscountsLog) {
                $this->used_discounts_json = json_encode($createdDiscountsLog);
            }

            // Cancelling after money calculations because cancelProduct update total_paid etc of order.
            foreach ($detailIds as $id_order_detail) {
                $iOrderDetail = new OrderDetail($id_order_detail);

                $result = $iOrder->cancelProduct($id_order_detail, $iOrderDetail->product_quantity, $shipping, _PS_OS_REFUND_);
                if ($result !== true) {
                    $errors[] = $result['message'];
                    return $errors;
                }
            }

            $this->id_order_history = $history->id;
            $this->id_employee = $cookie->id_employee;
            $this->date_moderated = date("Y-m-d H:i:s", time());
            $this->was_moderated = true;
            $this->save();
            $this->commitTransaction();
            return true;

        } catch(Exception $e) {
            $this->rollbackTransaction();
            $msgLogFile->addLine("Error:". $e->getMessage());
            return false;
        }
    }

    // DEPRECATED
    public function exchangeOrder() {}
}
