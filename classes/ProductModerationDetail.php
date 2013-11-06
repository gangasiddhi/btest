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

require_once (_PS_MODULE_DIR_ . '/moderation/moderation.php');

class ProductModerationDetailCore extends ObjectModel {
    public $id_moderation;
    public $id_moderation_type;
    public $id_order;
    public $id_order_detail;
    public $id_reason;
    public $message;
    public $message2; // Keep product reference if moderation is a shoe size change moderation.
    public $shipping_price;
    public $was_moderated;
    public $discounts_json;
    public $bank_refund_amount;
    public $id_employee;
    public $date_add;
    public $date_moderated;
    public $id_order_history;
    public $shipping;
    public $quantity;

    /**
     * Using  on moderation_result table, OrderModeration/Product Moderation
     */
    const MODERATION_OBJECT_TYPE = 2;

    protected $fieldsRequired = array('id_moderation_type', 'id_order', 'id_order_detail', 'quantity');
    protected $fieldsValidate = array('id_moderation_type' => 'isUnsignedId',
        'id_order' => 'isUnsignedId',
        'id_order_detail' => 'isUnsignedId',
        'shipping' => 'isBool',
        'id_reason' => 'isUnsignedId',
        'quantity' => 'isUnsignedId',
        'message' => 'isString',
        'message2' => 'isString',
        'was_moderated' => 'isBool',
        'bank_refund_amount' => 'isFloat',
        'id_employee' => 'isInt',
        'id_order_history' => 'isInt',
        'shipping_price' => 'isFloat'
    );

    protected $table = 'product_moderation';
    protected $identifier = 'id_moderation';

    public function __construct($id = NULL, $id_lang = NULL) {
        parent::__construct($id, $id_lang);

        $this->log = Logger::getLogger(get_class($this));
    }

    public function getFields() {
        parent::validateFields();

        $fields = array(
            'id_moderation_type' => (int) $this->id_moderation_type,
            'id_order' =>  (int) $this->id_order,
            'id_order_detail' => (int) $this->id_order_detail,
            'shipping' => (bool) $this->shipping,
            'id_reason' => (int) $this->id_reason,
            'quantity' => (int) $this->quantity,
            'message' => pSQL($this->message),
            'message2' => pSQL($this->message2),
            'shipping_price' => $this->shipping_price,
            'was_moderated' => $this->was_moderated,
            'discounts_json' => $this->discounts_json,
            'bank_refund_amount' => $this->bank_refund_amount,
            'id_employee' => $this->id_employee,
            'date_add' => $this->date_add,
            'date_moderated' => $this->date_moderated,
            'id_order_history' => $this->id_order_history
        );

        return $fields;
    }

    // Cancel moderation, not product
    public function cancel() {
        global $cookie;

        $this->log->warn('FIXME: 201306191536 -- CANCELING SHOULD ALSO CANCEL ORDER & PRODUCT STATUS!');

        $this->id_employee = $cookie->id_employee;
        $this->date_moderated = date("Y-m-d H:i:s", time());
        $this->was_moderated = true;
        $this->id_employee = $cookie->id_employee;
        $this->save();

        return true;
    }

    public static function isExistByIdOrderDetail($id_order_detail, $getModerated = false) {
        $sql = "SELECT id_moderation
            FROM " . _DB_PREFIX_ ."product_moderation
            WHERE id_order_detail = $id_order_detail
                AND was_moderated = ". pSQL($getModerated ? 1 : 0);

        return DB::getInstance()->getValue($sql) ? true : false;
    }

    /**
     * Employee can crate 2 moderation recort for same product in same order. For example there are 2 same product in order, employee can create
     * 1 exchange product moderation record and 1 cancel product moderation record.
     * @param  int $id_order_detail
     * @return int
     */
    public static function getTotalQuantityOfProductByOrderDetailId($id_order_detail, $getModerated = false) {
        $sql = "SELECT SUM(quantity)
            FROM " . _DB_PREFIX_ ."product_moderation
            WHERE id_order_detail = $id_order_detail
                AND was_moderated = " . pSQL($getModerated ? 1 : 0);

        $qty = DB::getInstance()->getValue($sql);

        return $qty ? $qty : 0;
    }


    public static function getModerationsOfProductByOrderDetailId($orderDetailId, $getModerated = false) {
        $sql = 'SELECT *
            FROM ' . _DB_PREFIX_ . 'product_moderation
            WHERE id_order_detail = ' . $orderDetailId . '
                AND was_moderated = '. pSQL($getModerated ? 1 : 0) . '
            ORDER BY id_moderation DESC';

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        return $result;
    }

    public static function getModerations($limit = 10, $page = 1, $getModerated = false) {
        $sql = 'SELECT SQL_CALC_FOUND_ROWS *
            FROM ' . _DB_PREFIX_ . 'product_moderation WHERE was_moderated = '. pSQL($getModerated ? 1 : 0) . '
            ORDER BY id_moderation DESC
            LIMIT ' . pSQL(((int) $page - 1) * (int) $limit) . ', ' . pSQL((int) $limit);

        $result['objects'] = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        $result['totalItem'] = (int) Db::getInstance()->getValue('SELECT FOUND_ROWS() as rowCount');

        return $result;
    }

    /**
     * Approve moderation(Refund/Cancel)
     */
    public function approve() {
        if ($this->id_moderation_type == moderation::MODERATION_TYPE_CANCEL) {
            return $this->refundProduct();
        } elseif ($this->id_moderation_type == moderation::MODERATION_TYPE_EXCHANGE) {
            return $this->exchangeProduct();
        } elseif ($this->id_moderation_type == moderation::PROD_MOD_TYPE_MANUAL_REFUND) {
            return $this->manualRefund();
        } elseif ($this->id_moderation_type == moderation::PROD_MOD_TYPE_CANCEL_EXCHANGE ) {
            return $this->cancelExchange();
        }

        return false;
    }

    public function refundProduct() {
        global $cookie;

        $errors = array();

        $this->beginTransaction();

        try {
            $id_order_detail = $this->id_order_detail;
            $iOrder = new Order($this->id_order);

            if ($iOrder->module != "freeorder" && ! $iOrder->isRefundable()) {
                $errors[] = Tools::displayError("Partial refund cannot be applied to orders(with installments) on the same day of purchase. Either wait until the next day or exchange the product.");
                return  $errors;
            }

            $iOrderDetail = new OrderDetail($this->id_order_detail);

            $qty = $this->quantity;
            $shipping = $this->shipping ? true : false;
            $price = $iOrderDetail->product_price * (1 + $iOrderDetail->tax_rate * 0.01);

            // Calculating the Installment Interest Amount
            if ($iOrder->installment_count > 1) {
                $orderInstallmentIntrestRate = $iOrder->installment_interest/*Configuration::get(strtoupper($iOrder->module) . '_' . $iOrder->installment_count . '_INSTALLMENT_INTEREST_RATE')*/;
                $orderInstallmentInterest = ($TotalOrderAmountWithOutinstallmentAndDiscount - $totalDiscountPriceExcludingCreditType) * ($orderInstallmentIntrestRate / 100 );
            }

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

            $productPriceWithTax = $productPrice;
            $totalRefundAmount = 0;
            $refundedInstallmentInterestAmount = 0;
            $refundShippingPrice = 0;
            $refundOfIntsallmentInterestOfShipping = 0;
            $shippingPriceToCharge = 0;
            $InstallmentAmountOfShippingPriceToCharge = 0;
            $refundDiscountPrice = 0;
            $refundOfIntsallmentInterestOfDiscount = 0;
            $orderInstallmentInterest = 0;
            $orderInstallmentIntrestRate = 0;
            $TotalOrderAmountWithOutinstallmentAndDiscount = $iOrder->initial_product_shipping;
            $originalTotalOrderAmount = 0;
            $totalDiscountPriceExcludingCreditType = 0;

            $createdDiscounts = array();
            $updatedDiscounts = array();

            /**
             * Calculating the discount amount excluding the credit type,
             * the credit will be given back as a credit
             */
            $orderDiscounts = $iOrder->getDiscounts();

            foreach ($orderDiscounts as $orderDiscount) {
                $discount = new Discount($orderDiscount['id_discount']);

                if ($discount->id_discount_type == Configuration::get('PS_CREDIT_DISCOUNT_ID_TYPE')) {
                    $creditDiscounts[] = $discount;
                } else {
                    $totalDiscountPriceExcludingCreditType += $orderDiscount['value'];
                }
            }

            $this->log->info("Number of quantity to refund: $qty");
            $this->log->info("Shipping to refund: $shipping");
            $this->log->info("productPriceWithTax: $productPriceWithTax");

            // Calculating the Installment Interest Amount
            if ($iOrder->installment_count > 1) {
                $orderInstallmentIntrestRate = Configuration::get(strtoupper($iOrder->module) . '_' . $iOrder->installment_count . '_INSTALLMENT_INTEREST_RATE');
                $orderInstallmentInterest = ($TotalOrderAmountWithOutinstallmentAndDiscount - $totalDiscountPriceExcludingCreditType) * ($orderInstallmentIntrestRate / 100 );
            }

            $originalTotalOrderAmount = ($TotalOrderAmountWithOutinstallmentAndDiscount - $totalDiscountPriceExcludingCreditType) + $orderInstallmentInterest;
            $refundOfIntsallmentInterestOfProductPrice = ($productPriceWithTax / ($originalTotalOrderAmount + $totalDiscountPriceExcludingCreditType - $orderInstallmentInterest)) * $orderInstallmentInterest;

            // Calculation of refunding the shipping
            if ($shipping) {
                $refundShippingPrice = $iOrder->total_shipping;
                $refundOfIntsallmentInterestOfShipping = ($refundShippingPrice / ($originalTotalOrderAmount + $totalDiscountPriceExcludingCreditType - $orderInstallmentInterest)) * $orderInstallmentInterest;
                $this->shipping_price = $iOrder->total_shipping;
            }

            // Total Interest Amount Refunded (Product Refund price + shipping refund price - discount refund amount)
            $refundedInstallmentInterestAmount = ($refundOfIntsallmentInterestOfProductPrice + $refundOfIntsallmentInterestOfShipping);

            // Total Refund Amount
            $totalRefundAmount += ($productPriceWithTax + $refundShippingPrice) + $refundedInstallmentInterestAmount;

            // Discount amount Refunded/Exchanged Along with the product
            if ($totalDiscountPriceExcludingCreditType > 0) {
                foreach ($orderDiscounts as $orderDiscount) {
                    $iDiscount = new Discount($orderDiscount['id_discount']);

                    if ($iDiscount->id_discount_type == Configuration::get('PS_CREDIT_DISCOUNT_ID_TYPE')) {
                        $TotalOrderAmountWithOutinstallmentAndDiscount -= $iDiscount->value;
                    }
                }

                $refundDiscountPrice = ($productPriceWithTax * $totalDiscountPriceExcludingCreditType) / $TotalOrderAmountWithOutinstallmentAndDiscount;

                if ($shipping) {
                    $refundDiscountPrice += ($refundShippingPrice * $totalDiscountPriceExcludingCreditType ) / $TotalOrderAmountWithOutinstallmentAndDiscount;
                }

                foreach($creditDiscounts as $creditDiscount) {
                    if ($creditDiscount->value != $totalRefundAmount) {
                        $totalRefundAmount += $refundDiscountPrice;
                    }
                }
            }

            $totalRefundAmount = $totalRefundAmount - $refundDiscountPrice;

            // To avoid floating point presision
            $productPriceWithTax = Tools::ps_round($productPriceWithTax, 2);
            $totalRefundAmount = Tools::ps_round($totalRefundAmount, 2);
            $refundedInstallmentInterestAmount = Tools::ps_round($refundedInstallmentInterestAmount, 2);
            $refundShippingPrice = Tools::ps_round($refundShippingPrice, 2);
            $refundOfIntsallmentInterestOfShipping = Tools::ps_round($refundOfIntsallmentInterestOfShipping, 2);
            $shippingPriceToCharge = Tools::ps_round($shippingPriceToCharge, 2);
            $InstallmentAmountOfShippingPriceToCharge = Tools::ps_round($InstallmentAmountOfShippingPriceToCharge, 2);
            $refundDiscountPrice = Tools::ps_round($refundDiscountPrice, 2);
            $refundOfIntsallmentInterestOfDiscount = Tools::ps_round($refundOfIntsallmentInterestOfDiscount, 2);

            $this->log->info("TotalPaidAmount: " . $iOrder->total_paid_real);
            $this->log->info("InstallmentInterest: " . $orderInstallmentInterest);
            $this->log->info("Installments Count: " . $iOrder->installment_count);
            $this->log->info("Discounts: " . $iOrder->total_discounts);
            $this->log->info("totalDiscountPriceExcludingCreditType: " . $totalDiscountPriceExcludingCreditType);
            $this->log->info("refundShippingPrice: " . $refundShippingPrice);
            $this->log->info("RefundedInstallmentInterestAmount: " . $refundedInstallmentInterestAmount);
            $this->log->info("Refunded distributedDiscountPrice of the product: " . $refundDiscountPrice);
            $this->log->info("RefundedTotalAmount: " . $totalRefundAmount);
            $this->log->info("Sending a request to the bank");

            // Check for credit vouchered product. If true we should not refund to credit cart.
            $products = $iOrder->getProducts();

            foreach ($products as $id_order_det => $product) {
                // If product payed with credit voucher
                if ($product['credit_quantity_' . $id_order_det] && $totalRefundAmount == $product['product_price_wt']) {
                    $creditProduct = true;
                    break;
                }
            }

            if (! $creditProduct && $iOrder->module != 'cashondelivery' && $totalRefundAmount > 0) {
                $result = PG::refundViaBank($iOrder->id, $totalRefundAmount);

                if ($result['error']) {
                    $errors[] =  Tools::displayError($result['errorMessage']);
                    return $errors;
                }

                $this->bank_refund_amount = ($shipping) ? $totalRefundAmount - $refundShippingPrice : $totalRefundAmount;
            }

            $id_order_detail_exist = OrderHistoryDetails::getOrderDetail($id_order_detail, _PS_OS_PARTIALREFUND_, false);

            if ($id_order_detail_exist) {
                $this->log->info("Updating existing order..");

                if (OrderHistoryDetails::updateOrderDetail($id_order_detail_exist, floatval($totalRefundAmount), abs($qty), $refundShippingPrice, intval($cookie->id_employee))) {
                    $msg = 'Updating Order History Details Failed.';
                    $errors[] = Tools::displayError($msg);
                    $this->log->error($msg);
                    return $errors;
                }
            } else {
                if ($totalRefundAmount > 0) {
                    $this->log->info("Adding New Entry into bu_order_history_details..");
                    $this->log->info("Quantity: " . abs($qty));

                    $details = new OrderHistoryDetails();
                    $details->id_order =  $iOrder->id;
                    $details->id_order_detail = $id_order_detail;
                    $details->id_order_state = _PS_OS_PARTIALREFUND_;
                    $details->amount = floatval($totalRefundAmount);
                    $details->current_refund_amt = floatval($totalRefundAmount);
                    $details->refund_product_price_with_tax = floatval($productPriceWithTax);
                    $details->discounts = floatval($refundDiscountPrice);
                    $details->installment_interest_refund_amt = $refundedInstallmentInterestAmount ? floatval($refundedInstallmentInterestAmount) : floatval(0);
                    $details->product_reference = $iOrderDetail->product_reference;
                    $details->id_employee = intval($cookie->id_employee);

                    if ($shipping) {
                        $details->shipping_cost = $refundShippingPrice;
                    }

                    if (abs($totalRefundAmount) > 0) {
                        $details->quantity  = abs($qty);
                    }

                    if (! $details->save()) {
                        $msg = 'Inserting Order History Details Failed.';
                        $errors[] = Tools::displayError($msg);
                        $this->log->error($msg);
                        return $errors;
                    }
                }
            }

            $result = $iOrder->cancelProduct($id_order_detail, (int) $qty, $shipping, _PS_OS_REFUND_);

            if ($result !== true) {
                $errors[] = $result['message'];
                $this->log->error($result['message']);
                return $errors;
            }

            $history = new OrderHistory();
            $history->id_order = $iOrder->id;
            $history->id_employee = intval($cookie->id_employee);

            if ($iOrder->total_paid_real == 0.00) {
                if ($state = OrderHistoryDetails::returnOrderStatus($iOrder->id, _PS_OS_PARTIALREFUND_, 'R')) {
                    $history->changeIdOrderState($state, intval($iOrder->id), $id_order_detail);
                }
            } else {
                $history->changeIdOrderState(_PS_OS_PARTIALREFUND_, intval($iOrder->id), $id_order_detail);
            }

            $carrier = new Carrier(intval($iOrder->id_carrier), intval($iOrder->id_lang));

            $templateVars = array('{refund_amt}' => floatval($totalRefundAmount));

            // Get the unused discounts related to this order to update.
            $existingOrderDiscounts = Discount::getExistingDiscountsForOrder($iOrder->id);
            $existingDiscounts = array();

            foreach ($existingOrderDiscounts as $existingOrderDiscount) {
                if ($existingOrderDiscount['active'] == 1) {
                    $existingDiscounts[$existingOrderDiscount['id_discount_type']]['id_discount'] = $existingOrderDiscount['id_discount'];
                    $existingDiscounts[$existingOrderDiscount['id_discount_type']]['value'] = $existingOrderDiscount['value'];
                }
            }

            // If there is credit voucher, and amount voucher in same order, we don't give share from amount voucher to credit voucher.
            $orderDiscounts = $iOrder->getDiscounts();
            $discounts = array();

            foreach ($orderDiscounts as $orderDiscount) {
                $iDiscount = new Discount($orderDiscount['id_discount']);
                $usedCount = $iDiscount->usedByCustomer($iOrder->id_customer);

                if ($iDiscount->value ==  $productPriceWithTax && $usedCount > 0) {
                    $creditDiscount = Discount::createDiscountForOrder($iOrder, Mail::l('Refund Voucher.'), $iDiscount->id_discount_type, $productPriceWithTax);

                    if (!$creditDiscount) {
                        $errors[] = Tools::displayError('Cannot generate discount of type voucher code');
                    } else {
                        $this->log->info("Sending mail to the Customer : Refund Discount Amount: " . print_r($creditDiscount->id,true));

                        $customer = new Customer((int)($iOrder->id_customer));
                        $params['{firstname}'] = $customer->firstname;
                        $currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
                        $params['{voucher_amount}'] = Tools::displayPrice($creditDiscount->value, $currency, false, false);
                        $params['{voucher_num}'] = $creditDiscount->name;
						
						/*Sending the Exchange/Refund/Cancel Voucher details to the Sailthru*/
						if(Module::isInstalled('sailthru')){
							$voucherDetail = array('customerEmail' =>  $customer->email,
												  'customerFirstName' => $customer->firstname,
												  'customerLastName' => $customer->lastname,
												  'voucherAmount' => Tools::displayPrice($creditDiscount->value, $currency, false, false),
												  'voucherNumber' => $creditDiscount->name);
							Module::hookExec('sailThruMailSend', array(
								'sailThruEmailTemplate' => 'voucher',
								'voucherDetail' => $voucherDetail
							));
						}else{
							Mail::Send(
								intval($iOrder->id_lang),
								'voucher',
								Mail::l('New voucher regarding your order'),
								$params,
								$customer->email,
								$customer->firstname . ' ' . $customer->lastname
							);
						}
						
                        array_push($createdDiscounts, array(
                            'id' => $creditDiscount->id,
                            'id_discount_type' => $creditDiscount->id_discount_type,
                            'value' => $creditDiscount->value
                        ));
                    }

                    break;
                }
            }

            if ($refundDiscountPrice > 0 && ! $creditDiscount) {
                // Distributed Discount amount is refunded in the form of voucher
                // Get the discounts applied to this order while on placement.
                $orderDiscounts = $iOrder->getDiscounts();
                $discounts = array();

                foreach ($orderDiscounts as $orderDiscount) {
                    $discountDetails = Discount::getDiscount($orderDiscount['id_discount']);
                    $discounts[$discountDetails['id_discount_type']] += $discountDetails['value'];
                }

                // Get the unused discounts related to this order to update.
                $existingOrderDiscounts = Discount::getExistingDiscountsForOrder($iOrder->id);
                $existingDiscounts = array();

                foreach ($existingOrderDiscounts as $existingOrderDiscount) {
                    if ($existingOrderDiscount['active'] == 1) {
                        $existingDiscounts[$existingOrderDiscount['id_discount_type']]['id_discount'] = $existingOrderDiscount['id_discount'];
                        $existingDiscounts[$existingOrderDiscount['id_discount_type']]['value'] = $existingOrderDiscount['value'];
                    }
                }

                $this->log->debug("orderDiscounts: " . print_r($orderDiscounts, true) . "\ndiscounts: " . print_r($discounts, true));
                $this->log->debug("existingOrderDiscounts: " . print_r($existingOrderDiscounts, true) . "\nexistingDiscounts: "
                    . print_r($existingDiscounts, true));

                // Create a discount if the unused discounts doesn't exists for this order, if exists updated the quantity
                foreach ($discounts as $discountType => $discountAmount) {
                    if ($iOrder->module == "freeorder") {
                        $refundDiscountAmount = $productPriceWithTax;
                    } else {
                        $refundDiscountAmount = ($refundDiscountPrice * $discountAmount) / $totalDiscountPriceExcludingCreditType;
                    }

                    $this->log->debug('Refunded discount type: ' . $discountType);

                    // Ratio vouchers should be refunded as amount vouchers..
                    if ($discountType == _PS_DISCOUNT_RATIO_TYPE_) {
                        $this->log->info('Changing discount type from ratio to amount..');
                        $discountType = _PS_DISCOUNT_AMOUNT_TYPE_;
                    }

                    $refundDiscountCredit = Discount::createDiscountForOrder($iOrder, Mail::l('Refund Voucher.'), $discountType , $refundDiscountAmount);

                    if (! $refundDiscountCredit) {
                        $errors[] = Tools::displayError('Cannot generate discount of type voucher code');
                    } else {
                        $this->log->info("Sending a Mail to the Customer : Refund Discount Amount: " . $refundDiscountCredit->id);

                        $customer = new Customer((int) $iOrder->id_customer);
                        $params['{firstname}'] = $customer->firstname;
                        $currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
                        $params['{voucher_amount}'] = Tools::displayPrice($refundDiscountCredit->value, $currency, false, false);
                        $params['{voucher_num}'] = $refundDiscountCredit->name;

						if(Module::isInstalled('sailthru')){
							$voucherDetail = array('customerEmail' =>  $customer->email,
												  'customerFirstName' => $customer->firstname,
												  'customerLastName' => $customer->lastname,
												  'voucherAmount' => Tools::displayPrice($refundDiscountCredit->value, $currency, false, false),
												  'voucherNumber' => $refundDiscountCredit->name);
							Module::hookExec('sailThruMailSend', array(
								'sailThruEmailTemplate' => 'voucher',
								'voucherDetail' => $voucherDetail
							));
						}else{
							Mail::Send(
								intval($iOrder->id_lang),
								'voucher',
								Mail::l('New voucher regarding your order'),
								$params,
								$customer->email,
								$customer->firstname . ' ' . $customer->lastname
							);
						}

                        array_push($createdDiscounts, array(
                            'id' => $refundDiscountCredit->id,
                            'id_discount_type' => $refundDiscountCredit->id_discount_type,
                            'value' => $refundDiscountCredit->value
                        ));
                    }
                }
            }

            if (! $history->addWithemail(true, $templateVars)) {
                $msg = 'an error occurred while changing status or was unable to send e-mail to the customer';
                $errors[] = Tools::displayError($msg);
                $this->log->error($msg);
            }

            if ($errors) {
                return $errors;
            }

            $this->id_order_history = $history->id;
            $this->id_employee = $cookie->id_employee;
            $this->date_moderated = date("Y-m-d H:i:s", time());
            $this->was_moderated = true;

            if ($createdDiscounts || $updatedDiscounts) {
                $this->discounts_json = json_encode(array(
                    "created_discounts" => $createdDiscounts,
                    "updaed_discounts" => $updatedDiscounts
                ));
            }

            $this->save();
            $this->commitTransaction();

            return true;

        } catch(Exception $e) {
            $this->rollbackTransaction();

            $this->log->error("Error occured during refund of product: ", $e);

            return false;
        }
    }

    public function exchangeProduct() {
        global $cookie;

        $errors = array();
        $reduction_amount = 0;

        $this->beginTransaction();

        try {
            $shipping = $this->shipping ? true : false;
            $id_order_detail = $this->id_order_detail;

            $this->log->debug('Shipping: ' . ($shipping ? 'true' : 'false'));

            $iOrder = new Order($this->id_order);
            $iOrderDetail = new OrderDetail($this->id_order_detail);

            $qty = $this->quantity;
            $product_ref = $iOrderDetail->product_reference;
            $price = $iOrderDetail->product_price * (1 + $iOrderDetail->tax_rate * 0.01);

            if ($iOrderDetail->reduction_percent != 0.00) {
                $reduction_amount = $price * $iOrderDetail->reduction_percent / 100;
            } elseif ($iOrderDetail->reduction_amount != '0.000000') {
                $reduction_amount = Tools::ps_round($iOrderDetail->reduction_amount, 2);
            }

            if ($reduction_amount > 0) {
                $price = Tools::ps_round($price - $reduction_amount, 2);
            }

            $productPrice = Tools::ps_round($qty * $price, 2);

            $this->log->debug('Product price: ' . $price);
            $this->log->debug('Total product price: ' . $productPrice);

            // Calculation of Refund Amount
            $productPriceWithTax = $productPrice;
            $totalRefundAmount = 0;
            $refundedInstallmentInterestAmount = 0;
            $refundShippingPrice = 0;
            $refundOfIntsallmentInterestOfShipping = 0;
            $shippingPriceToCharge = 0;
            $InstallmentAmountOfShippingPriceToCharge = 0;
            $refundDiscountPrice = 0;
            $refundOfIntsallmentInterestOfDiscount = 0;
            $orderInstallmentInterest = 0;
            $orderInstallmentIntrestRate = 0;
            $TotalOrderAmountWithOutinstallmentAndDiscount = $iOrder->initial_product_shipping;
            $originalTotalOrderAmount = 0;
            $totalDiscountPriceExcludingCreditType = 0;
            $createdDiscounts = array();

            $this->log->debug('Initial total product price (incl. shipping): ' . $TotalOrderAmountWithOutinstallmentAndDiscount);

            /**
             * Calculating the discount amount excluding the credit type,
             * the credit will be given back as a credit
             */
            $orderDiscounts = $iOrder->getDiscounts();

            foreach ($orderDiscounts as $orderDiscount) {
                $discount = new Discount($orderDiscount['id_discount']);

                if ($discount->id_discount_type == Configuration::get('PS_CREDIT_DISCOUNT_ID_TYPE')) {
                    $creditDiscounts[] = $discount;
                } else {
                    $totalDiscountPriceExcludingCreditType += $orderDiscount['value'];
                }
            }

            $this->log->debug('Total value of discounts (excl. credit vouchers): ' . $totalDiscountPriceExcludingCreditType);

            // Calculating the Installment Interest Amount
            if ($iOrder->installment_count > 1) {
                $orderInstallmentIntrestRate = $iOrder->installment_interest;
                $orderInstallmentInterest = ($TotalOrderAmountWithOutinstallmentAndDiscount - $totalDiscountPriceExcludingCreditType) * ($orderInstallmentIntrestRate / 100 );
            }

            $this->log->debug('Installment interest rate: ' . $iOrder->installment_interest);
            $this->log->debug('Installment interest amount: ' . $orderInstallmentInterest);

            $originalTotalOrderAmount = ($TotalOrderAmountWithOutinstallmentAndDiscount - $totalDiscountPriceExcludingCreditType) + $orderInstallmentInterest;
            $refundOfIntsallmentInterestOfProductPrice = ($productPriceWithTax / ($originalTotalOrderAmount + $totalDiscountPriceExcludingCreditType - $orderInstallmentInterest)) * $orderInstallmentInterest;

            $this->log->debug('Cart (aka Original Total Order) Amount: ' . $originalTotalOrderAmount);

            // Calculation of refunding the shipping
            if ($shipping) {
                $refundShippingPrice = $iOrder->total_shipping;
                $refundOfIntsallmentInterestOfShipping = ($refundShippingPrice / ($originalTotalOrderAmount + $totalDiscountPriceExcludingCreditType - $orderInstallmentInterest)) * $orderInstallmentInterest;
                $this->shipping_price = $iOrder->total_shipping;
            }

            // Total Interest Amount Refunded (Product Refund price + shipping refund price - discount refund amount)
            $refundedInstallmentInterestAmount = ($refundOfIntsallmentInterestOfProductPrice + $refundOfIntsallmentInterestOfShipping);

            // Total Refund Amount
            $totalRefundAmount += ($productPriceWithTax + $refundShippingPrice) + $refundedInstallmentInterestAmount;

            // Discount amount Refunded/Exchanged Along with the product
            if ($totalDiscountPriceExcludingCreditType > 0){
                foreach ($orderDiscounts as $orderDiscount) {
                    $iDiscount = new Discount($orderDiscount['id_discount']);

                    if ($iDiscount->id_discount_type == Configuration::get('PS_CREDIT_DISCOUNT_ID_TYPE')) {
                        $TotalOrderAmountWithOutinstallmentAndDiscount -= $iDiscount->value;
                    }
                }

                $refundDiscountPrice = ($productPriceWithTax * $totalDiscountPriceExcludingCreditType) / $TotalOrderAmountWithOutinstallmentAndDiscount;
                if($shipping) {
                    $refundDiscountPrice += ($refundShippingPrice * $totalDiscountPriceExcludingCreditType ) / $TotalOrderAmountWithOutinstallmentAndDiscount;
                }

                foreach($creditDiscounts as $creditDiscount) {
                    if ($creditDiscount->value != $totalRefundAmount) {
                        $totalRefundAmount += $refundDiscountPrice;
                    }
                }
            }

            /*To avoid floating point presision*/
            $productPriceWithTax = Tools::ps_round($productPriceWithTax, 2);
            $totalRefundAmount = Tools::ps_round($totalRefundAmount, 2);
            $refundedInstallmentInterestAmount = Tools::ps_round($refundedInstallmentInterestAmount, 2);
            $refundShippingPrice = Tools::ps_round($refundShippingPrice, 2);
            $refundOfIntsallmentInterestOfShipping = Tools::ps_round($refundOfIntsallmentInterestOfShipping, 2);
            $shippingPriceToCharge = Tools::ps_round($shippingPriceToCharge, 2);
            $InstallmentAmountOfShippingPriceToCharge = Tools::ps_round($InstallmentAmountOfShippingPriceToCharge, 2);
            $refundDiscountPrice = Tools::ps_round($refundDiscountPrice, 2);
            $refundOfIntsallmentInterestOfDiscount = Tools::ps_round($refundOfIntsallmentInterestOfDiscount, 2);

            $this->log->info("TotalPaidAmount: " . $iOrder->total_paid_real);
            $this->log->info("InstallmentInterest: " . $orderInstallmentInterest);
            $this->log->info("Installments Count: " . $iOrder->installment_count);
            $this->log->info("Discounts: " . $iOrder->total_discounts);
            $this->log->info("ExchangedProductPrice: " . $productPriceWithTax);
            $this->log->info("refundShippingPrice: " . $refundShippingPrice);
            $this->log->info("ExchnagedInstallmentInterestAmount: " . $refundedInstallmentInterestAmount);
            $this->log->info("ExchangedDiscountPrice Along with product: " . $refundDiscountPrice);
            $this->log->info("TotalRefundAmount: " . $totalRefundAmount);

            if ($id_order_detail_exist = OrderHistoryDetails::getOrderDetail($id_order_detail, _PS_OS_PARTIALEXCHANGE_, false)) {
                $this->log->info("Updating the Existing Entry in the bu_order_istory_details table");

                if(OrderHistoryDetails::updateOrderDetail($id_order_detail_exist, floatval($totalRefundAmount), abs($qty), $refundShippingPrice, intval($cookie->id_employee))) {
                    $errors[] = Tools::displayError('Updating Order History Details Failed.');
                    return $errors;
                }
            } else {
                $this->log->info("Adding a new Entry in bu_order_istory_details table");

                $details = new OrderHistoryDetails();
                $details->id_order =  $iOrder->id;
                $details->id_order_detail = $id_order_detail;
                $details->id_order_state = _PS_OS_PARTIALEXCHANGE_;
                $details->amount = floatval($totalRefundAmount);
                $details->current_refund_amt = floatval($totalRefundAmount);
                $details->refund_product_price_with_tax = floatval($productPriceWithTax);
                $details->installment_interest_refund_amt = floatval($refundedInstallmentInterestAmount);
                $details->discounts = floatval($refundDiscountPrice);
                $details->product_reference = $product_ref;
                if($shipping) {
                    $details->shipping_cost = $refundShippingPrice;
                }
                if(abs($qty) > 0) {
                    $details->quantity  = abs($qty);
                }
                $details->id_employee = intval($cookie->id_employee);
                if(!$details->save()) {
                    $errors[] = Tools::displayError('Inserting Order History Details Failed.');
                    return $errors;
                }
            }

            $result = $iOrder->cancelProduct($id_order_detail, $qty, $shipping, _PS_OS_EXCHANGE_);
            if ($result !== true) {
                $errors[] = $result['message'];
                return $errors;
            }

            $history = new OrderHistory();
            $history->id_order = $iOrder->id;
            $history->id_employee = intval($cookie->id_employee);

            if($iOrder->total_paid_real == 0.00 && !$iOrder->hasBeenPaid()) {
                if($state = OrderHistoryDetails::returnOrderStatus($iOrder->id, _PS_OS_PARTIALEXCHANGE_, 'E')) {
                    $history->changeIdOrderState($state, intval($iOrder->id), $id_order_detail);
                }
            } else {
                $history->changeIdOrderState(_PS_OS_PARTIALEXCHANGE_, intval($iOrder->id), $id_order_detail);
            }

            $carrier = new Carrier(intval($iOrder->id_carrier), intval($iOrder->id_lang));
            $templateVars = array();

            if ($history->id_order_state == _PS_OS_SHIPPING_ AND $iOrder->invoice_number) {
                $shipment_tracking_link = $iOrder->getTrackingLink();

                $templateVars = array(
                    '{followup}' => str_replace('@', $iOrder->shipping_number, $carrier->url),
                    '{exchangeMoney}'=> $totalRefundAmount,
                    '{shipment_tracking_link}' => $shipment_tracking_link
                );
            }

            if (! $history->addWithemail(true, $templateVars)) {
                $errors[] = Tools::displayError('an error occurred while changing status or was unable to send e-mail to the customer');
                return $errors;
            }

             // Recreate used credit voucher
            foreach($orderDiscounts as $orderDiscount) {
                $iDiscount = new Discount($orderDiscount['id_discount']);
                $usedCount = $iDiscount->usedByCustomer($iOrder->id_customer);
                if ($iDiscount->value ==  $productPriceWithTax && $usedCount > 0) {
                    $newDiscount = Discount::createDiscountForOrder($iOrder, Mail::l('Refund Voucher.'), $iDiscount->id_discount_type , $productPriceWithTax);
                    $totalRefundAmount -= $productPriceWithTax;

                    if (!$newDiscount) {
                        $errors[] = Tools::displayError('Credit voucher creation failed.');
                        return $errors;
                    }

                    $customer = new Customer((int)($iOrder->id_customer));
                    $params['{firstname}'] = $customer->firstname;
                    $currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
                    $params['{voucher_amount}'] = Tools::displayPrice($newDiscount->value, $currency, false, false);
                    $params['{voucher_num}'] = $newDiscount->name;
					
					/*Sending the Exchange/Refund/Cancel Voucher details to the Sailthru*/
					if(Module::isInstalled('sailthru')){
						$voucherDetail = array('customerEmail' =>  $customer->email,
											  'customerFirstName' => $customer->firstname,
											  'customerLastName' => $customer->lastname,
											  'voucherAmount' => Tools::displayPrice($newDiscount->value, $currency, false, false),
											  'voucherNumber' => $newDiscount->name);
						Module::hookExec('sailThruMailSend', array(
							'sailThruEmailTemplate' => 'voucher',
							'voucherDetail' => $voucherDetail
						));
					}else{
						Mail::Send(intval($iOrder->id_lang), 'voucher', Mail::l('New voucher regarding your order'), $params, $customer->email, $customer->firstname.' '.$customer->lastname);
					}
						
                    array_push($createdDiscounts, array(
                        'id' => $newDiscount->id,
                        'id_discount_type' => $newDiscount->id_discount_type,
                        'value' => $newDiscount->value
                    ));
                    break;
                }
            }

            if ($totalRefundAmount > 0) {
                $newDiscount = Discount::createDiscountForOrder($iOrder, Mail::l('Exchange Voucher'), _EXCHANGE_VOUCHER_TYPE_ID_, $totalRefundAmount);

                $customer = new Customer((int)($iOrder->id_customer));
                $params['{firstname}'] = $customer->firstname;
                $currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
                $params['{voucher_amount}'] = Tools::displayPrice($newDiscount->value, $currency, false, false);
                $params['{voucher_num}'] = $newDiscount->name;
				
				/*Sending the Exchange/Refund/Cancel Voucher details to the Sailthru*/
				if(Module::isInstalled('sailthru')){
					$voucherDetail = array('customerEmail' =>  $customer->email,
										  'customerFirstName' => $customer->firstname,
										  'customerLastName' => $customer->lastname,
										  'voucherAmount' => Tools::displayPrice($newDiscount->value, $currency, false, false),
										  'voucherNumber' => $newDiscount->name);
					Module::hookExec('sailThruMailSend', array(
						'sailThruEmailTemplate' => 'voucher',
						'voucherDetail' => $voucherDetail
					));
				}else{
					Mail::Send(intval($iOrder->id_lang), 'voucher', Mail::l('New voucher regarding your order'), $params, $customer->email, $customer->firstname.' '.$customer->lastname);
				}
				
                array_push($createdDiscounts, array(
                    'id' => $newDiscount->id,
                    'id_discount_type' => $newDiscount->id_discount_type,
                    'value' => $newDiscount->value,
                    'message' => 'Exchange voucher'
                ));
            }

            if ($newDiscount) {
                $details->id_discount = $newDiscount->id;
                $details->save();

                if(!$details->save()) {
                    $errors[] = Tools::displayError('Inserting Order History Details Failed.');
                    return $errors;
                }
            } else {
                $errors[] = Tools::displayError('Cannot generate discount of type voucher code');
                return $errors;
            }


            $this->id_employee = $cookie->id_employee;
            $this->id_order_history = $history->id;
            $this->date_moderated = date("Y-m-d H:i:s", time());
            $this->was_moderated = true;

            if ($createdDiscounts) {
                $this->discounts_json = json_encode($createdDiscounts);
            }

            $this->save();
            $this->commitTransaction();
            return true;

        } catch (Exception $e) {
            $this->rollbackTransaction();

            $this->log->error("Error occurred during exchange:" . $e);

            return false;
        }
    }

    public function manualRefund() {
        global $cookie;

        $iOrder = new Order($this->id_order);
        $iOrderDetail = new OrderDetail($this->id_order_detail);
        $manualRefundAmountWithTax = $this->message; // Keeping amount in moderation message field
        $manualRefundAmountWithoutTax = $manualRefundAmountWithTax / (1 + ($iOrderDetail->tax_rate / 100));

        if ($manualRefundAmountWithTax > $iOrder->total_paid_real) {
            $errors[] = Tools::displayError('Price big than total  products\' price');
            return $errors;
        }

        $this->beginTransaction();

        try {
            // Get the response from the bank on refund
            $bank_response = Hook::getBankResponseOnOrderStatusChange(_PS_OS_MANUALREFUND_, (int)($iOrder->id), $manualRefundAmountWithTax);
            $this->bank_refund_amount = $manualRefundAmountWithTax;

            /**
             * Hook returns string (ie default working of hook in prestashop). Split the string to form an array.
             * $array[0] is the 'Response(approved or not) xml tag' in the xml bank response.
             * $array[1] is the 'OrderId(id_cart) xml tag' in the xml bank response.
             * $array[2] is the 'ErrMsg(the error message from bank) xml' tag in the xml bank response.
             * Note: OrderId, ErrMsg are not given by some banks in the xml response.
             */
            if (! $bank_response) {
                $this->log->error("No response from bank, \t id-order: " . $iOrder->id);
                $errors[] = Tools::displayError('There is no response from the bank');
                return $errors;
            }

            $this->log->debug("State: Manual Refund, \t Bank response AdminOrders: $bank_response,
                \t id-order: $id_order, \t manual refund amount: $manualRefundAmountWithTax");

            $response_list = explode('|', $bank_response);

            if ($response_list[0] != 'Approved' AND $response_list[0] != 1) {
                $err_msg = isset($response_list[2]) && $response_list[2]  ? $response_list[2]: '';
                $errors[] = Tools::displayError('The Response from the bank is ' . $response_list[0] . ', ' . $err_msg . ' Please try again later');
                return $errors;
            }

            $iOrder->total_paid -= $manualRefundAmountWithTax;
            $iOrder->total_paid_real -= $manualRefundAmountWithTax;
            $iOrder->total_products -= $manualRefundAmountWithoutTax;
            $iOrder->total_products = ($iOrder->total_products > 0 ? $iOrder->total_products : 0);
            $iOrder->total_products_wt -= $manualRefundAmountWithTax;
            $iOrder->total_products_wt = ($iOrder->total_products_wt > 0 ? $iOrder->total_products_wt : 0);
            $iOrderDetail->product_quantity -= $this->quantity;

            if (! $iOrder->update() || ! $iOrderDetail->update()) {
                $errors[] = Tools::displayError('Error during update order');

                $this->log->error('Error during update order. Was trying to refund: ' . $manualRefundAmountWithTax);
                $this->log->error(mysql_error());

                return $errors;
            }

            $details = new OrderHistoryDetails();
            $details->id_order =  $iOrder->id;
            $details->id_order_detail =  $this->id_order_detail;

            $details->id_order_state = _PS_OS_MANUALREFUND_;
            $details->amount = floatval($manualRefundAmountWithTax);
            $details->current_refund_amt = floatval($manualRefundAmountWithTax);
            $details->id_employee = intval($cookie->id_employee);

            if (! $details->save()) {
                $msg =  'Inserting manual refund failed.';
                $errors[] =  Tools::displayError($msg);
                $this->log->error($msg);
                $this->log->error(mysql_error());
                return $errors;
            }

            $history = new OrderHistory();
            $history->id_order = $iOrder->id;
            $history->id_employee = intval($cookie->id_employee);
            $history->changeIdOrderState(_PS_OS_MANUALREFUND_ , intval($iOrder->id));

            $carrier = new Carrier(intval($iOrder->id_carrier), intval($iOrder->id_lang));
            $templateVars = array('{manual_refund_amt}' => floatval($manualRefundAmountWithTax));

            if (! $history->addWithemail(true, $templateVars)) {
                $errors[] = Tools::displayError('Inserting Order History Details Failed.');
                return $errors;
            }

            $this->id_employee = $cookie->id_employee;
            $this->id_order_history = $history->id;
            $this->date_moderated = date("Y-m-d H:i:s", time());
            $this->was_moderated = true;

            if ($createdDiscounts) {
                $this->discounts_json = json_encode($createdDiscounts);
            }

            $this->save();
            $this->commitTransaction();

            return true;
        } catch(Exception $e) {
            $this->rollbackTransaction();

            $this->log->error("Error occurred during manual refund.", $e);

            return false;
        }
    }

    public function cancelExchange () {
        /**
         * Money not refunding to customer here because, CS team refund money with manual refund.
         */
        global $cookie;

        $this->beginTransaction();

        try {
            // disabling exchange voucher if there are any..
            if (! empty($this->message2)) {
                $this->log->info('Disabling exchange voucher: ' . $this->message2);

                $discount = new Discount($this->message2);
                $discount->active = false;
                $discount->save();

                // also detaching it from order..
                $arrOrderDiscount = OrderDiscount::getByDiscountId($this->message2);
                $oDiscount = new OrderDiscount($arrOrderDiscount['id_order_discount']);
                $oDiscount->delete();
            } else {
                $this->log->info('No vouchers have been entered during moderation, skipping voucher disabling..');
            }

            $iOrder = new Order($this->id_order);
            $result = $iOrder->cancelProduct($this->id_order_detail, $this->quantity, $this->shipping, _PS_OS_REFUND_);

            if (! $result) {
                $errors[] = Tools::displayError('Canceling product failed.');
                return $errors;
            }

            $details = new OrderHistoryDetails();
            $details->id_order =  $this->id_order;
            $details->id_order_detail =  $this->id_order_detail;
            $details->id_order_state = _PS_OS_EXCHANGE_CANCELLATION_;
            $details->quantity =  $this->quantity;
            $details->id_employee = intval($cookie->id_employee);

            if (! $details->save()) {
                $errors[] = Tools::displayError('Adding history detail record failed.');
                return $errors;
            }

            $history = new OrderHistory();
            $history->id_order = (int) $iOrder->id;
            $history->id_employee = (int) ($cookie->id_employee);
            $history->changeIdOrderState(_PS_OS_EXCHANGE_CANCELLATION_, $this->id_order);

            if (!$history->save()) {
                $errors[] = Tools::displayError('Adding history record failed.');
                return $errors;
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

            $this->log->error("Error occurred during cancelling order.", $e);

            return false;
        }
    }
}

