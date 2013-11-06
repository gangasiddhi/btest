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
*  @version  Release: $Revision: 7551 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class AdminOrders extends AdminTab
{
    public function __construct()
    {
        global $cookie;

        $this->table = 'order';
        $this->className = 'Order';
        $this->view = true;
        $this->status = true;
        $this->colorOnBackground = true;
        $this->_select = '
            a.id_order AS id_pdf,
            CONCAT(c.`firstname`, \'. \', c.`lastname`) AS `customer`,
            osl.`name` AS `osname`,
            os.`color`,
            IF((SELECT COUNT(so.id_order) FROM `'._DB_PREFIX_.'orders` so WHERE so.id_customer = a.id_customer) > 1, 0, 1) as new,
            (SELECT COUNT(od.`id_order`) FROM `'._DB_PREFIX_.'order_detail` od WHERE od.`id_order` = a.`id_order` GROUP BY `id_order`) AS product_number';
        $this->_join = 'LEFT JOIN `'._DB_PREFIX_.'customer` c ON (c.`id_customer` = a.`id_customer`)
        LEFT JOIN `'._DB_PREFIX_.'order_history` oh ON (oh.`id_order` = a.`id_order`)
        LEFT JOIN `'._DB_PREFIX_.'order_state` os ON (os.`id_order_state` = oh.`id_order_state`)
        LEFT JOIN `'._DB_PREFIX_.'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state` AND osl.`id_lang` = '.(int)($cookie->id_lang).')';
        $this->_where = 'AND oh.`id_order_history` = (SELECT MAX(`id_order_history`) FROM `'._DB_PREFIX_.'order_history` moh WHERE moh.`id_order` = a.`id_order` GROUP BY moh.`id_order`)';

        $statesArray = array();
        $states = OrderState::getOrderStates((int)($cookie->id_lang));

        foreach ($states AS $state)
            $statesArray[$state['id_order_state']] = $state['name'];
        $this->fieldsDisplay = array(
        'id_order' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25),
        'new' => array('title' => $this->l('New'), 'width' => 25, 'align' => 'center', 'type' => 'bool', 'filter_key' => 'new', 'tmpTableFilter' => true, 'icon' => array(0 => 'blank.gif', 1 => 'news-new.gif'), 'orderby' => false),
        'customer' => array('title' => $this->l('Customer'), 'widthColumn' => 160, 'width' => 140, 'filter_key' => 'customer', 'tmpTableFilter' => true),
        'total_paid' => array('title' => $this->l('Total'), 'width' => 70, 'align' => 'right', 'prefix' => '<b>', 'suffix' => '</b>', 'price' => true, 'currency' => true),
        'payment' => array('title' => $this->l('Payment'), 'width' => 100),
        'osname' => array('title' => $this->l('Status'), 'widthColumn' => 230, 'type' => 'select', 'select' => $statesArray, 'filter_key' => 'os!id_order_state', 'filter_type' => 'int', 'width' => 200),
        'date_add' => array('title' => $this->l('Date'), 'width' => 35, 'align' => 'right', 'type' => 'datetime', 'filter_key' => 'a!date_add'),
        'id_pdf' => array('title' => $this->l('PDF'), 'callback' => 'printPDFIcons', 'orderby' => false, 'search' => false));
        parent::__construct();
    }

    /**
      * @global object $cookie Employee cookie necessary to keep trace of his/her actions
      */
    public function postProcess()
    {
        global $currentIndex, $cookie;

        /* Update shipping number */
        if (Tools::isSubmit('submitShippingNumber') AND ($id_order = (int)(Tools::getValue('id_order'))) AND Validate::isLoadedObject($order = new Order($id_order)))
        {
            if ($this->tabAccess['edit'] === '1')
            {
                if (!$order->hasBeenShipped())
                    die(Tools::displayError('The shipping number can only be set once the order has been shipped.'));
                $_GET['view'.$this->table] = true;

                $shipping_number = pSQL(Tools::getValue('shipping_number'));
                $order->shipping_number = $shipping_number;
                $order->update();
                if ($shipping_number)
                {
                    global $_LANGMAIL;
                    $customer = new Customer((int)($order->id_customer));
                    $carrier = new Carrier((int)($order->id_carrier));
                    if (!Validate::isLoadedObject($customer) OR !Validate::isLoadedObject($carrier))
                        die(Tools::displayError());
                    $templateVars = array(
                        '{followup}' => str_replace('@', $order->shipping_number, $carrier->url),
                        '{firstname}' => $customer->firstname,
                        '{lastname}' => $customer->lastname,
                        '{id_order}' => (int)($order->id)
                    );
                    @Mail::Send((int)($order->id_lang), 'in_transit', Mail::l('Package in transit'), $templateVars,
                        $customer->email, $customer->firstname.' '.$customer->lastname, NULL, NULL, NULL, NULL,
                        _PS_MAIL_DIR_, true);
                }
            } else {
                $this->_errors[] = Tools::displayError('You do not have permission to edit here.');
            }
        } else if (Tools::isSubmit('submitStateForMultipleOrders')) {
            $orderids = Tools::getValue('OrderId');

            foreach ($orderids as $id_order) {
                if (! $newOrderStatusId = intval(Tools::getValue('id_order_state'))) {
                    $this->_errors[] = Tools::displayError('Invalid new order status!');
                } else {
                    ObjectModel::beginTransaction();

                    try {
                        $order = new Order($id_order);
                        $history = new OrderHistory();
                        $history->id_order = $id_order;
                        $history->id_employee = intval($cookie->id_employee);
                        $history->changeIdOrderState(intval($newOrderStatusId), intval($id_order));
                        $carrier = new Carrier(intval($order->id_carrier), intval($order->id_lang));
                        $templateVars = array();

                        if ($history->id_order_state == _PS_OS_SHIPPING_) {
                            $shipment_tracking_link = $order->getTrackingLink();
                            error_log('[AdminOrders][postProcess][submitStateForMultipleOrders][PS_OS_SHIPPING] ' . $shipment_tracking_link);

                            $templateVars = array(
                                '{followup}' => str_replace('@', $order->shipping_number, $carrier->url),
                                '{shipment_tracking_link}' => $shipment_tracking_link
                            );
                        } else if ($history->id_order_state == _PS_OS_CHEQUE_) {
                            $templateVars = array(
                                '{cheque_name}' => (Configuration::get('CHEQUE_NAME') ? Configuration::get('CHEQUE_NAME') : ''),
                                '{cheque_address_html}' => (Configuration::get('CHEQUE_ADDRESS') ? nl2br(Configuration::get('CHEQUE_ADDRESS')) : '')
                            );
                        } else if ($history->id_order_state == _PS_OS_BANKWIRE_) {
                            $templateVars = array(
                                '{bankwire_owner}' => (Configuration::get('BANK_WIRE_OWNER') ? Configuration::get('BANK_WIRE_OWNER') : ''),
                                '{bankwire_details}' => (Configuration::get('BANK_WIRE_DETAILS') ? nl2br(Configuration::get('BANK_WIRE_DETAILS')) : ''),
                                '{bankwire_address}' => (Configuration::get('BANK_WIRE_ADDRESS') ? nl2br(Configuration::get('BANK_WIRE_ADDRESS')) : '')
                            );
                        }

                        if (! $history->addWithemail(true, $templateVars)) {
                            throw new Exception(Tools::displayError('an error occurred while changing status or was unable to send e-mail to the customer'));
                        }

                        ObjectModel::commitTransaction();
                    } catch(Exception $e) {
                        ObjectModel::rollbackTransaction();
                        $msg = 'Error during chance state of order:<br>'. $e->getMessage();
                        $this->_errors[] = Tools::displayError($msg);
                        error_log("[AdminOrders][submitStateForMultipleOrders] ".$msg);
                    }
                }
            }

            if (!$this->_errors) {
                Tools::redirectAdmin($currentIndex . '&token=' . $this->token);
            }
        } else if (Tools::isSubmit('dispatchToWarehouse')
            AND ($id_order = (int) Tools::getValue('id_order'))
            AND Validate::isLoadedObject($order = new Order($id_order))
            ) {

            if ($this->tabAccess['edit'] === '1') {
                require_once(_PS_MODULE_DIR_ . 'warehouse/lib/OGLI.php');

                Module::hookExec('dispatchOrder', array(
                    'id_order' => $id_order
                ));

                $this->displayConf(28);
            } else {
                $this->_errors[] = Tools::displayError('You do not have permission to edit here.');
            }
        }

        /* Change order state, add a new entry in order history and send an e-mail to the customer if needed */
        elseif (Tools::isSubmit('submitState')
            AND ($id_order = (int)(Tools::getValue('id_order')))
            AND Validate::isLoadedObject($order = new Order($id_order))) {

            if ($this->tabAccess['edit'] === '1') {

                ObjectModel::beginTransaction();
                try {
                    $_GET['view' . $this->table] = true;

                    if (! $newOrderStatusId = (int) (Tools::getValue('id_order_state'))) {
                        $this->_errors[] = Tools::displayError('Invalid new order status');
                    } else {
                        $history = new OrderHistory();
                        $history->id_order = (int) $id_order;
                        $history->id_employee = (int) ($cookie->id_employee);
                        $history->changeIdOrderState((int) ($newOrderStatusId), (int) ($id_order));
                        $carrier = new Carrier((int) ($order->id_carrier), (int) ($order->id_lang));
                        $templateVars = array();

                        if ($history->id_order_state == Configuration::get('PS_OS_PREPARATION') ) {
                            $pre_sales_agreement_link = Tools::getShopDomain(true) . __PS_BASE_URI__
                                . 'agreements-general.php?id_cms=20&id_order=' . $history->id_order . '&s_key=' . md5($history->id_order . _COOKIE_KEY_);
                            $non_members_agreement_link = Tools::getShopDomain(true) . __PS_BASE_URI__
                                . 'agreements-general.php?id_cms=21&id_order=' . $history->id_order . '&s_key=' . md5($history->id_order . _COOKIE_KEY_);
                            $templateVars = array(
                                '{pre_sales_agreement_link}' => $pre_sales_agreement_link,
                                '{non_members_agreement_link}' => $non_members_agreement_link
                            );
                        } else if ($history->id_order_state == Configuration::get('PS_OS_SHIPPING')) {
                            $shipment_tracking_link = $order->getTrackingLink();
                            error_log('[AdminOrders][postProcess][submitState][PS_OS_SHIPPING] ' . $shipment_tracking_link);

                            $templateVars = array(
                                '{shipment_tracking_link}' => $shipment_tracking_link
                            );
                        } else if ($history->id_order_state == Configuration::get('PS_OS_CHEQUE')) {
                            $templateVars = array(
                                '{cheque_name}' => (Configuration::get('CHEQUE_NAME') ? Configuration::get('CHEQUE_NAME') : ''),
                                '{cheque_address_html}' => (Configuration::get('CHEQUE_ADDRESS') ? nl2br(Configuration::get('CHEQUE_ADDRESS')) : ''));
                        } else if ($history->id_order_state == Configuration::get('PS_OS_BANKWIRE')) {
                            $templateVars = array(
                                '{bankwire_owner}' => (Configuration::get('BANK_WIRE_OWNER') ? Configuration::get('BANK_WIRE_OWNER') : ''),
                                '{bankwire_details}' => (Configuration::get('BANK_WIRE_DETAILS') ? nl2br(Configuration::get('BANK_WIRE_DETAILS')) : ''),
                                '{bankwire_address}' => (Configuration::get('BANK_WIRE_ADDRESS') ? nl2br(Configuration::get('BANK_WIRE_ADDRESS')) : ''));
                        }

                        if (! $history->addWithemail(true, $templateVars)) {
                            throw new Exception(Tools::displayError('An error occurred while changing the status or was unable to send e-mail to the customer.'));
                        }

                        ObjectModel::commitTransaction();
                        Tools::redirectAdmin($currentIndex . '&id_order=' . $id_order . '&vieworder' . '&token=' . $this->token);
                    }
                } catch(Exception $e) {
                    ObjectModel::rollbackTransaction();
                    $msg = "Error during chance state of order:<br>". $e->getMessage();
                    $this->_errors[] = Tools::displayError($msg);
                    error_log("[AdminOrders][submitState] ".$msg);
                }
            } else {
                $this->_errors[] = Tools::displayError('You do not have permission to edit here.');
            }
        }

        /* Add a new message for the current order and send an e-mail to the customer if needed */
        elseif (isset($_POST['submitMessage'])) {
            $_GET['view'.$this->table] = true;
            if ($this->tabAccess['edit'] === '1')
            {
                if (!($id_order = (int)(Tools::getValue('id_order'))) OR !($id_customer = (int)(Tools::getValue('id_customer'))))
                    $this->_errors[] = Tools::displayError('An error occurred before sending message');
                elseif (!Tools::getValue('message'))
                    $this->_errors[] = Tools::displayError('Message cannot be blank');
                else
                {
                    /* Get message rules and and check fields validity */
                    $rules = call_user_func(array('Message', 'getValidationRules'), 'Message');
                    foreach ($rules['required'] AS $field)
                        if (($value = Tools::getValue($field)) == false AND (string)$value != '0')
                            if (!Tools::getValue('id_'.$this->table) OR $field != 'passwd')
                                $this->_errors[] = Tools::displayError('field').' <b>'.$field.'</b> '.Tools::displayError('is required.');
                    foreach ($rules['size'] AS $field => $maxLength)
                        if (Tools::getValue($field) AND Tools::strlen(Tools::getValue($field)) > $maxLength)
                            $this->_errors[] = Tools::displayError('field').' <b>'.$field.'</b> '.Tools::displayError('is too long.').' ('.$maxLength.' '.Tools::displayError('chars max').')';
                    foreach ($rules['validate'] AS $field => $function)
                        if (Tools::getValue($field))
                            if (!Validate::$function(htmlentities(Tools::getValue($field), ENT_COMPAT, 'UTF-8')))
                                $this->_errors[] = Tools::displayError('field').' <b>'.$field.'</b> '.Tools::displayError('is invalid.');
                    if (!sizeof($this->_errors))
                    {
                        $message = new Message();
                        $message->id_employee = (int)($cookie->id_employee);
                        $message->message = htmlentities(Tools::getValue('message'), ENT_COMPAT, 'UTF-8');
                        $message->id_order = $id_order;
                        $message->private = Tools::getValue('visibility');
                        if (!$message->add())
                            $this->_errors[] = Tools::displayError('An error occurred while sending message.');
                        elseif ($message->private)
                            Tools::redirectAdmin($currentIndex.'&id_order='.$id_order.'&vieworder&conf=11'.'&token='.$this->token);
                        elseif (Validate::isLoadedObject($customer = new Customer($id_customer)))
                        {
                            $order = new Order((int)($message->id_order));
                            if (Validate::isLoadedObject($order))
                            {
                                $varsTpl = array('{lastname}' => $customer->lastname, '{firstname}' => $customer->firstname, '{id_order}' => $message->id_order, '{message}' => (Configuration::get('PS_MAIL_TYPE') == 2 ? $message->message : nl2br2($message->message)));
                                if (@Mail::Send((int)($order->id_lang), 'order_merchant_comment',
                                    Mail::l('New message regarding your order'), $varsTpl, $customer->email,
                                    $customer->firstname.' '.$customer->lastname, NULL, NULL, NULL, NULL, _PS_MAIL_DIR_, true))
                                    Tools::redirectAdmin($currentIndex.'&id_order='.$id_order.'&vieworder&conf=11'.'&token='.$this->token);
                            }
                        }
                        $this->_errors[] = Tools::displayError('An error occurred while sending e-mail to customer.');
                    }
                }
            }
            else
                $this->_errors[] = Tools::displayError('You do not have permission to delete here.');
        } elseif (Tools::isSubmit('returnProduct') AND ($id_order = intval(Tools::getValue('id_order'))) AND Validate::isLoadedObject($order = new Order($id_order))) {
            if ($this->tabAccess['delete'] === '1')
            {
                $productList = Tools::getValue('id_order_detail');
                $customizationList = Tools::getValue('id_customization');
                $qtyList = Tools::getValue('cancelQuantity');
                $customizationQtyList = Tools::getValue('cancelCustomizationQuantity');
                if ($productList OR $customizationList)
                {
                    $result = $this->cancelProducts($order, $productList, $qtyList, $customizationList, $customizationQtyList);
                    if ($result !== true) {
                        $this->_errors[] = $result['message'];
                    }
                }
                // Redirect if no errors
                if (!sizeof($this->_errors))
                    Tools::redirectLink($currentIndex . '&id_order=' . $order->id . '&vieworder&conf=21&token=' . $this->token);
            }
            else
                $this->_errors[] = Tools::displayError('You do not have permission to delete here.');
        }
        /* Cancel product from order */
        elseif (Tools::isSubmit('cancelProducts')AND ($id_order = intval(Tools::getValue('id_order')))  AND Validate::isLoadedObject($iOrder = new Order((int)(Tools::getValue('id_order'))))) {
            $idModerationType = Configuration::get('MODERATION_TYPE_CANCEL');
            $orderDetail = Tools::getValue('orderDetail');
            $shipping = Tools::getValue('shipping');

            // S**** For determine order moderation or product moderation
            $notChangedProducts = array();
            $selectedProducts = array();
            foreach ($orderDetail as $id_order_detail => $product) {
                $iOrderDetail = new OrderDetail($id_order_detail);
                if ($product['quantity'] <= 1) {
                    $notChangedProducts[] = $product;
                }

                if ($product['process'] == 1) {
                    $selectedProducts[] = true;
                }
            }

            $cancelOrder = true;
            if (count($notChangedProducts) != count($orderDetail)
                OR (count($selectedProducts) > 0 AND count($selectedProducts) < count($orderDetail))) {
                $cancelOrder = false;
            }
            // F**** For determine order moderation or product moderation


            // If all selected or none of them
            if ($cancelOrder) {
                if (OrderModerationDetail::isExistByOrderId($id_order)) {
                    $this->_errors[] = Tools::displayError("Moderation already exist for this order");
                    return;
                }

                $iOrderModeration = new OrderModerationDetail();
                $iOrderModeration->id_moderation_type = $idModerationType;
                $iOrderModeration->id_order = (int) $id_order;
                $iOrderModeration->id_reason = (int) Tools::getValue('orderModerationReason');
                $iOrderModeration->shipping = $shipping ? 1 : 0;

                $messageOfOrder = Tools::getValue('orderModerationMessage');
                if ($messageOfOrder) {
                    $iOrderModeration->message = $messageOfOrder;
                } elseif (count($orderDetail) == 1) {
                    // There is only one product in order and employe write message to on it
                    $firstProduct = array_shift(array_values($orderDetail));
                    $iOrderModeration->message = $firstProduct['message'];
                    $iOrderModeration->id_reason = $firstProduct['id_reason'];
                }

                if (!$iOrderModeration->add()) {
                    $this->_errors[] = Tools::displayError("An error occurred during adding order to moderation");
                    return;
                }

                $history = new OrderHistory();
                $history->id_order = (int) $id_order;
                $history->id_employee = (int) ($cookie->id_employee);
                $history->changeIdOrderState(_PS_OS_WAITING_MANAGER_APPROVAL_, $id_order);

                if (! $history->add()) {
                    $this->_errors[] = Tools::displayError('An error occurred while changing the status or was unable to send e-mail to the customer.');
                    return;
                }

                Tools::redirectAdmin('index.php?tab=AdminOrders&id_order='.$id_order.'&vieworder&token=' . Tools::getAdminTokenLite('AdminOrders').(!$this->_errors ? '&conf=26':''));
            }

            foreach ($orderDetail as $id_order_detail => $product) {
                if ($product['process'] != 1) {
                    continue;
                }

                $iProductModeration = new ProductModerationDetail();
                $iProductModeration->id_moderation_type = $idModerationType;
                $iProductModeration->id_order = (int) $id_order;
                $iProductModeration->id_order_detail = (int) $id_order_detail;
                $iProductModeration->id_reason = (int) $product['id_reason'];
                $iProductModeration->quantity = (int) $product['quantity'];
                $iProductModeration->message = $product['message'];
                $iProductModeration->shipping = Tools::getValue("shipping") ? true :  false;
                $result = $iProductModeration->add();

                if (! $result) {
                    $this->_errors[] = Tools::displayError("An error [2013061901] occurred during adding product to moderation");
                    return;
                }
            }

            if (!$this->_errors) {
                Tools::redirectAdmin('index.php?tab=AdminOrders&id_order='.$id_order.'&vieworder&conf=27&token=' . Tools::getAdminTokenLite('AdminOrders'));
            }

        } elseif (Tools::isSubmit('exchangeProducts') AND ($id_order = intval(Tools::getValue('id_order'))) AND Validate::isLoadedObject($iOrder = new Order($id_order))) {

            $idModerationType = Configuration::get('MODERATION_TYPE_EXCHANGE');
            $orderDetail = Tools::getValue('orderDetail');
            $shipping = Tools::getValue('shipping');

            // S**** For determine order moderation or product moderation
            $notChangedProducts = array();
            $selectedProducts = array();
            foreach ($orderDetail as $id_order_detail => $product) {
                $iOrderDetail = new OrderDetail($id_order_detail);
                if ($product['quantity'] <= 1) {
                    $notChangedProducts[] = $product;
                }

                if ($product['process'] == 1) {
                    $selectedProducts[] = true;
                }
            }

            $exchangeOrder = true;
            if (count($notChangedProducts) != count($orderDetail)
                OR (count($selectedProducts) > 0 AND count($selectedProducts) < count($orderDetail))) {
                $exchangeOrder = false;
            }

            // TODO: this is workaround.
            if (count($orderDetail) == 1) {
                $exchangeOrder = false;
            }
            // F**** For determine order moderation or product moderation


            // check count for use which message
            if ($exchangeOrder) {
                if (OrderModerationDetail::isExistByOrderId($id_order)) {
                    $this->_errors[] = Tools::displayError("Moderation already exist for this order");
                    return;
                }

                $iOrderModeration = new OrderModerationDetail();
                $iOrderModeration->id_moderation_type = $idModerationType;
                $iOrderModeration->id_order = (int) $id_order;
                $iOrderModeration->id_reason = (int) Tools::getValue('orderModerationReason');
                $iOrderModeration->shipping = $shipping ? 1 : 0;

                $messageOfOrder = Tools::getValue('orderModerationMessage');
                if ($messageOfOrder) {
                    $iOrderModeration->message = $messageOfOrder;
                } elseif (count($orderDetail) == 1) {
                    // There is only one product in order and employe write message to on it
                    $firstProduct = array_shift(array_values($orderDetail));
                    $iOrderModeration->message = $firstProduct['message'];
                }

                if (! $iOrderModeration->add()) {
                    $this->_errors[] = Tools::displayError("An error occurred during adding order to moderation");
                    return;
                }

                $history = new OrderHistory();
                $history->id_order = (int) $id_order;
                $history->id_employee = (int) ($cookie->id_employee);
                $history->changeIdOrderState(_PS_OS_WAITING_MANAGER_APPROVAL_, $id_order);

                if (! $history->add()) {
                    $this->_errors[] = Tools::displayError('An error occurred while changing the status.');
                    return;
                }

                Tools::redirectAdmin('index.php?tab=AdminOrders&id_order='.$id_order.'&vieworder&token=' . Tools::getAdminTokenLite('AdminOrders').(!$this->_errors ? '&conf=26':''));
            }

            foreach ($orderDetail as $id_order_detail => $product) {
                if ($product['process'] != 1) {
                    continue;
                }

                if (!$product['quantity']) {
                    $this->_errors[] = Tools::displayError("Product quantity must be at least one");
                    continue;
                }

                $iProductModeration = new ProductModerationDetail();

                $iProductModeration->id_moderation_type = $idModerationType;
                $iProductModeration->id_order = (int) $id_order;
                $iProductModeration->id_order_detail = (int) $id_order_detail;
                $iProductModeration->id_reason = (int) $product['id_reason'];
                $iProductModeration->quantity = (int) $product['quantity'];
                $iProductModeration->message = $product['message'];
                $iProductModeration->shipping = Tools::getValue("shipping") ? true :  false;

                if ($product['shoe_reference']) {
                    $iProductModeration->message2 = $product['shoe_reference'];
                    $iProductModeration->message = $product['shoe_size'];
                }

                if (!$iProductModeration->add()) {
                    $this->_errors[] = Tools::displayError("An error [2013061902] occurred during adding product to moderation");
                    return;
                }
            }

            if (!$this->_errors) {
                Tools::redirectAdmin('index.php?tab=AdminOrders&id_order='.$id_order.'&vieworder&conf=27&token=' . Tools::getAdminTokenLite('AdminOrders'));
            }

        } elseif (Tools::isSubmit('btnCancelExchange') AND ($id_order = intval(Tools::getValue('id_order'))) AND Validate::isLoadedObject($iOrder = new Order($id_order))) {
            $orderDetail = Tools::getValue('orderDetail');
            $discountId = Tools::getValue('exchangeVoucherToBeCancelled');

            foreach ($orderDetail as $id_order_detail => $product) {
                if ($product['process'] != 1) {
                    continue;
                }

                $moderationModule = Module::getInstanceByName('moderation');

                $iProductModeration = new ProductModerationDetail();
                $iProductModeration->id_moderation_type = $moderationModule::PROD_MOD_TYPE_CANCEL_EXCHANGE;
                $iProductModeration->id_order = (int) $id_order;
                $iProductModeration->id_order_detail = (int) $id_order_detail;
                $iProductModeration->shipping = (bool) Tools::getValue("shipping");
                $iProductModeration->quantity =  $product['quantity'] ? (int) $product['quantity'] : 1;

                if ($discountId) {
                    $iProductModeration->message2 = $discountId; // store exchange voucher id to be cancelled
                }

                if (!$iProductModeration->add()) {
                    $this->_errors[] = Tools::displayError("An error [2013061903] occurred during adding product to moderation");
                    return;
                }

                $details = new OrderHistoryDetails();
                $details->id_order =  $iOrder->id;
                $details->id_order_detail =  $id_order_detail;
                $details->id_order_state = _PS_OS_WAITING_MANAGER_APPROVAL_;
                $details->quantity =  $product['quantity'];
                $details->id_employee = intval($cookie->id_employee);

                if (! $details->save()) {
                    $this->_errors[] = Tools::displayError('Inserting record to history failed.');
                    return;
                }

                $history = new OrderHistory();
                $history->id_order = (int) $id_order;
                $history->id_employee = (int) ($cookie->id_employee);
                $history->changeIdOrderState(_PS_OS_WAITING_MANAGER_APPROVAL_, $id_order);

                if (!$history->save()) {
                    return false;
                }

                break;
            }

            Tools::redirectAdmin('index.php?tab=AdminOrders&id_order='.$id_order.'&vieworder&conf=27&token=' . Tools::getAdminTokenLite('AdminOrders'));

        } elseif(Tools::isSubmit('manualGenerateCredit') AND ($id_order = intval(Tools::getValue('id_order'))) AND Validate::isLoadedObject($order = new Order($id_order))) {
            $credit_qty = Tools::getValue('credit_qty');
            $details = new OrderHistoryDetails();
            $details->id_order =  $order->id;
            $details->id_order_state = _PS_OS_CREDITSGIVEN_;
            $details->quantity = abs($credit_qty);
            $details->id_employee = intval($cookie->id_employee);
            if(!$details->save())
                    $this->_errors[] = Tools::displayError('Inserting credits failed.');

            $history = new OrderHistory();
            $history->id_order = $order->id;
            $history->id_employee = intval($cookie->id_employee);
            $history->changeIdOrderState(_PS_OS_CREDITSGIVEN_ , intval($order->id));

            for($i=1;$i<=$credit_qty;$i++)
                if (!$exchangeCredit = Discount::createDiscountForOrder($order, $this->l('Credit Discount'), _PS_OS_CREDIT_ID_TYPE_, $amt=20.00))
                            $this->_errors[] = Tools::displayError('Cannot generate discount of type credit');

            $carrier = new Carrier(intval($order->id_carrier), intval($order->id_lang));
            $templateVars = array();

            if ($history->id_order_state == _PS_OS_SHIPPING_) {
                    $shipment_tracking_link = $order->getTrackingLink();
                    error_log('[AdminOrders][postProcess][manualGenerateCredit][PS_OS_SHIPPING] ' . $shipment_tracking_link);

                $templateVars = array(
                    '{followup}' => str_replace('@', $order->shipping_number, $carrier->url),
                    '{shipment_tracking_link}' => $shipment_tracking_link
                );
            }

            if ($history->addWithemail(true, $templateVars)) {
                Tools::redirectAdmin($currentIndex . '&id_order=' . $id_order . '&vieworder&conf=21' . '&token=' . $this->token);
            }
        } elseif(Tools::isSubmit('manualRefund') AND ($id_order = intval(Tools::getValue('id_order'))) AND Validate::isLoadedObject($iOrder = new Order($id_order))) {

            if ($iOrder->module == 'cashondelivery' || $iOrder->module == 'freeorder') {
                $this->_errors[] = Tools::displayError("Manual refund not permitted with COD and Freeorder");
                return;
            }

            $iModeration = Module::getInstanceByName('moderation');

            $manualRefundAmount = (float) Tools::getValue('manualRefundAmount');
            $orderDetail = Tools::getValue('orderDetail');

            foreach($orderDetail as $idOrderDetail => $product) {
                if ($product['process'] != 1) continue;

                $iProductModeration = new ProductModerationDetail();
                $iProductModeration->id_moderation_type = $iModeration::PROD_MOD_TYPE_MANUAL_REFUND;
                $iProductModeration->id_order = $id_order;
                $iProductModeration->id_order_detail = $idOrderDetail;
                $iProductModeration->id_reason = $product['id_reason'];
                $iProductModeration->quantity = $product['quantity'] ? $product['quantity'] : 1; // For exchange cancellation
                $iProductModeration->message = "$manualRefundAmount";
                $iProductModeration->message2 = $product['message'];

                if (! $iProductModeration->add()) {
                    $this->_errors[] = Tools::displayError("An error [2013061904] occurred during adding product to moderation");
                    $this->_errors[] = Tools::displayError(mysql_error());

                    return;
                }

                break; // Only one product can send to moderation with manual refund.
            }

            Tools::redirectAdmin($currentIndex . '&id_order=' . $id_order . '&vieworder&conf=21' . '&token=' . $this->token);

        } elseif (isset($_GET['messageReaded'])) {
            Message::markAsReaded((int)($_GET['messageReaded']), (int)($cookie->id_employee));
        }

        parent::postProcess();
    }

    public function cancelProducts($order, $orderDetailIds, $qtyList, $state, $updateqty = false) {
        if ($orderDetailIds) {
            foreach ($orderDetailIds AS $idOrderDetail) {
                $qtyCancelProduct = abs($qtyList[$idOrderDetail]);
                if (!$qtyCancelProduct) {
                    $this->_errors[] = Tools::displayError('No quantity selected for product.');
                    continue;
                }

                $result = $order->cancelProduct($idOrderDetail, $qtyList[$idOrderDetail],
                    null /*shipping: For only argument order. Not using already when state _PS_OS_CANCELED_*/,
                    $state,$updateqty);

                if ($result !== true) {
                    $this->_errors[] = $result['message'];
                }
            }
        }
    }

    private function displayCustomizedDatas(&$customizedDatas, &$product, &$currency, &$image, $tokenCatalog, $id_order_detail)
    {
        if (!($order = $this->loadObject()))
            return;

        if (is_array($customizedDatas) AND isset($customizedDatas[(int)($product['product_id'])][(int)($product['product_attribute_id'])]))
        {
            $imageObj = new Image($image['id_image']);
            echo '
            <tr>
                <td align="center">'.(isset($image['id_image']) ? cacheImage(_PS_IMG_DIR_.'p/'.$imageObj->getExistingImgPath().'.jpg',
                'product_mini_'.(int)($product['product_id']).(isset($product['product_attribute_id']) ? '_'.(int)($product['product_attribute_id']) : '').'.jpg', 45, 'jpg') : '--').'</td>
                <td><a href="index.php?tab=AdminCatalog&id_product='.$product['product_id'].'&updateproduct&token='.$tokenCatalog.'">
                    <span class="productName">'.$product['product_name'].' - '.$this->l('customized').'</span><br />
                    '.($product['product_reference'] ? $this->l('Ref:').' '.$product['product_reference'].'<br />' : '')
                    .($product['product_supplier_reference'] ? $this->l('Ref Supplier:').' '.$product['product_supplier_reference'] : '')
                    .'</a></td>
                <td align="center">'.Tools::displayPrice($product['product_price_wt'], $currency, false).'</td>
                <td align="center" class="productQuantity">'.$product['customizationQuantityTotal'].'</td>
                '.($order->hasBeenPaid() ? '<td align="center" class="productQuantity">'.$product['customizationQuantityRefunded'].'</td>' : '').'
                '.($order->hasBeenDelivered() ? '<td align="center" class="productQuantity">'.$product['customizationQuantityReturned'].'</td>' : '').'
                <td align="center" class="productQuantity"> - </td>
                <td align="center">'.Tools::displayPrice(Tools::ps_round($order->getTaxCalculationMethod() == PS_TAX_EXC ? $product['product_price'] : $product['product_price_wt'], 2) * $product['customizationQuantityTotal'], $currency, false).'</td>
                <td align="center" class="cancelCheck">--</td>
            </tr>';
            foreach ($customizedDatas[(int)($product['product_id'])][(int)($product['product_attribute_id'])] AS $customizationId => $customization)
            {
                echo '
                <tr>
                    <td colspan="2">';
                foreach ($customization['datas'] AS $type => $datas)
                    if ($type == _CUSTOMIZE_FILE_)
                    {
                        $i = 0;
                        echo '<ul style="margin: 4px 0px 4px 0px; padding: 0px; list-style-type: none;">';
                        foreach ($datas AS $data)
                            echo '<li style="display: inline; margin: 2px;">
                                    <a href="displayImage.php?img='.$data['value'].'&name='.(int)($order->id).'-file'.++$i.'" target="_blank"><img src="'._THEME_PROD_PIC_DIR_.$data['value'].'_small" alt="" /></a>
                                </li>';
                        echo '</ul>';
                    }
                    elseif ($type == _CUSTOMIZE_TEXTFIELD_)
                    {
                        $i = 0;
                        echo '<ul style="margin: 0px 0px 4px 0px; padding: 0px 0px 0px 6px; list-style-type: none;">';
                        foreach ($datas AS $data)
                            echo '<li>'.($data['name'] ? $data['name'] : $this->l('Text #').++$i).$this->l(':').' '.$data['value'].'</li>';
                        echo '</ul>';
                    }
                echo '</td>
                    <td align="center">-</td>
                    <td align="center" class="productQuantity">'.$customization['quantity'].'</td>
                    '.($order->hasBeenPaid() ? '<td align="center">'.$customization['quantity_refunded'].'</td>' : '').'
                    '.($order->hasBeenDelivered() ? '<td align="center">'.$customization['quantity_returned'].'</td>' : '').'
                    <td align="center">-</td>
                    <td align="center">'.Tools::displayPrice(Tools::ps_round($order->getTaxCalculationMethod() == PS_TAX_EXC ? $product['product_price'] : $product['product_price_wt'], 2) * $customization['quantity'], $currency, false).'</td>
                    <td align="center" class="cancelCheck">
                        <input type="hidden" name="totalQtyReturn" id="totalQtyReturn" value="'.(int)($customization['quantity_returned']).'" />
                        <input type="hidden" name="totalQty" id="totalQty" value="'.(int)($customization['quantity']).'" />
                        <input type="hidden" name="productName" id="productName" value="'.$product['product_name'].'" />';
                if ((!$order->hasBeenDelivered() OR Configuration::get('PS_ORDER_RETURN')) AND (int)(($customization['quantity_returned']) < (int)($customization['quantity'])))
                    echo '
                        <input type="checkbox" name="id_customization['.$customizationId.']" id="id_customization['.$customizationId.']" value="'.$id_order_detail.'" onchange="setCancelQuantity(this, \''.$customizationId.'\', \''.$customization['quantity'].'\')" '.(((int)($customization['quantity_returned'] + $customization['quantity_refunded']) >= (int)($customization['quantity'])) ? 'disabled="disabled" ' : '').'/>';
                else
                    echo '--';
                echo '
                    </td>
                    <td class="cancelQuantity">';
                if ((int)($customization['quantity_returned'] + $customization['quantity_refunded']) >= (int)($customization['quantity']))
                    echo '<input type="hidden" name="cancelCustomizationQuantity['.$customizationId.']" value="0" />';
                elseif (!$order->hasBeenDelivered() OR Configuration::get('PS_ORDER_RETURN'))
                    echo '
                        <input type="text" id="cancelQuantity_'.$customizationId.'" name="cancelCustomizationQuantity['.$customizationId.']" size="2" onclick="selectCheckbox(this);" value="" /> ';
                echo ($order->hasBeenDelivered() ? (int)($customization['quantity_returned']).'/'.((int)($customization['quantity']) - (int)($customization['quantity_refunded'])) : ($order->hasBeenPaid() ? (int)($customization['quantity_refunded']).'/'.(int)($customization['quantity']) : '')).'
                    </td>';
                echo '
                </tr>';
            }
        }
    }

    private function getCancelledProductNumber(&$order, &$product)
    {
        $productQuantity = array_key_exists('customizationQuantityTotal', $product) ? $product['product_quantity'] - $product['customizationQuantityTotal'] : $product['product_quantity'];
        $productRefunded = $product['product_quantity_refunded'];
        $productReturned = $product['product_quantity_return'];
        $content = '0/'.$productQuantity;
        if ($order->hasBeenDelivered())
            $content = $productReturned.'/'.($productQuantity - $productRefunded);
        elseif ($order->hasBeenPaid())
            $content = $productRefunded.'/'.$productQuantity;
        return $content;
    }

    public function viewDetails()
    {
        global $currentIndex, $cookie, $link;
        $irow = 0;
        if (!($order = $this->loadObject()))
            return;

        $customer = new Customer($order->id_customer);
        $customerStats = $customer->getStats();
        $addressInvoice = new Address($order->id_address_invoice, (int)($cookie->id_lang));
        if (Validate::isLoadedObject($addressInvoice) AND $addressInvoice->id_state)
            $invoiceState = new State((int)($addressInvoice->id_state));
        $addressDelivery = new Address($order->id_address_delivery, (int)($cookie->id_lang));
        if (Validate::isLoadedObject($addressDelivery) AND $addressDelivery->id_state)
            $deliveryState = new State((int)($addressDelivery->id_state));
        $carrier = new Carrier($order->id_carrier);
        $history = $order->getHistory($cookie->id_lang);
        $products = $order->getProducts();
        $customizedDatas = Product::getAllCustomizedDatas((int)($order->id_cart));
        Product::addCustomizationPrice($products, $customizedDatas);
        $discounts = $order->getDiscounts(true);
        $messages = Message::getMessagesByOrderId($order->id, true);
        $states = OrderState::getOrderStates((int)($cookie->id_lang));
        $currency = new Currency($order->id_currency);
        $currentLanguage = new Language((int)($cookie->id_lang));
        $currentState = OrderHistory::getLastOrderState($order->id);
        $sources = ConnectionsSource::getOrderSources($order->id);
        $cart = Cart::getCartByOrderId($order->id);
        $sendToCarrier = Tools::getValue('sendToCarrier');

        if ($sendToCarrier) {
            Module::hookExec('sendOrderDataToCarrier', array(
                'orderId' => $order->id,
                'IsExchangedOrder' => (OrderHistory::isOrderStateExist($orderId, _PS_OS_PROCESSED_) ? 1 : 0)
            ));

            $this->displayConf(28);
        }

        $row = array_shift($history);

        if ($prevOrder = Db::getInstance()->getValue('SELECT id_order FROM '._DB_PREFIX_.'orders WHERE id_order < '.(int)$order->id.' ORDER BY id_order DESC'))
            $prevOrder = '<a href="'.$currentIndex.'&token='.Tools::getValue('token').'&vieworder&id_order='.$prevOrder.'"><img style="width:24px;height:24px" src="../img/admin/arrow-left.png" /></a>';
        if ($nextOrder = Db::getInstance()->getValue('SELECT id_order FROM '._DB_PREFIX_.'orders WHERE id_order > '.(int)$order->id.' ORDER BY id_order ASC'))
            $nextOrder = '<a href="'.$currentIndex.'&token='.Tools::getValue('token').'&vieworder&id_order='.$nextOrder.'"><img style="width:24px;height:24px" src="../img/admin/arrow-right.png" /></a>';


        if ($order->total_paid != $order->total_paid_real)
            echo '<center><span class="warning" style="font-size: 16px">'.$this->l('Warning:').' '.Tools::displayPrice($order->total_paid_real, $currency, false).' '.$this->l('paid instead of').' '.Tools::displayPrice($order->total_paid, $currency, false).' !</span></center><div class="clear"><br /><br /></div>';

        // display bar code if module enabled
        $hook = Module::hookExec('invoice', array('id_order' => $order->id));
        if ($hook !== false)
        {
            echo '<div style="float: right; margin: -40px 40px 10px 0;">';
            echo $hook;
            echo '</div><br class="clear" />';
        }

        // display order header
        echo '
        <div style="float:left" style="width:440px">';
        echo '<h2>
                '.$prevOrder.'
                '.(Validate::isLoadedObject($customer) ? $customer->firstname.' '.$customer->lastname.' - ' : '').$this->l('Order #').sprintf('%06d', $order->id).'
                '.$nextOrder.'
            </h2>
            <div style="width:429px">
                '.((($currentState->invoice OR $order->invoice_number) AND count($products))
                    ? '<a href="pdf.php?id_order='.$order->id.'&pdf"><img src="../img/admin/charged_ok.gif" alt="'.$this->l('View invoice').'" /> '.$this->l('View invoice').'</a>'
                    : '<img src="../img/admin/charged_ko.gif" alt="'.$this->l('No invoice').'" /> '.$this->l('No invoice')).' -
                '.(($currentState->delivery OR $order->delivery_number)
                    ? '<a href="pdf.php?id_delivery='.$order->delivery_number.'"><img src="../img/admin/delivery.gif" alt="'.$this->l('View delivery slip').'" /> '.$this->l('View delivery slip').'</a>'
                    : '<img src="../img/admin/delivery_ko.gif" alt="'.$this->l('No delivery slip').'" /> '.$this->l('No delivery slip')).' -
                '.(($order->invoice_number AND count($products))
                    ? '<a href="xml.php?id_order=' . $order->id . '&order_xml"><img src="../img/admin/export.gif" alt="' . $this->l('Get order XML') . '" title="' . $this->l('Get order XML') . '" /> '.$this->l('Get order XML').'</a><br/><br/>' : '') .' -
                  <a href="javascript:window.print()"><img src="../img/admin/printer.gif" alt="'.$this->l('Print order').'" title="'.$this->l('Print order').'" /> '.$this->l('Print page').'</a>
            </div>
            <div class="clear">&nbsp;</div>';

        /* Display current status */
        echo '
            <table cellspacing="0" cellpadding="0" class="table" style="width: 429px">
                <tr>
                    <th>'.Tools::displayDate($row['date_add'], (int)($cookie->id_lang), true).'</th>
                    <th><img src="../img/os/'.$row['id_order_state'].'.gif" /></th>
                    <th>'.stripslashes($row['ostate_name']).'</th>
                    <th>'.((!empty($row['employee_lastname'])) ? '('.stripslashes(Tools::substr($row['employee_firstname'], 0, 1)).'. '.stripslashes($row['employee_lastname']).')' : '').'</th>
                </tr>';
            /* Display previous status */
            foreach ($history AS $row)
            {
                echo '
                <tr class="'.($irow++ % 2 ? 'alt_row' : '').'">
                    <td>'.Tools::displayDate($row['date_add'], (int)($cookie->id_lang), true).'</td>
                    <td><img src="../img/os/'.$row['id_order_state'].'.gif" /></td>
                    <td>'.stripslashes($row['ostate_name']).'</td>
                    <td>'.((!empty($row['employee_lastname'])) ? '('.stripslashes(Tools::substr($row['employee_firstname'], 0, 1)).'. '.stripslashes($row['employee_lastname']).')' : '').'</td>
                </tr>';
            }
        echo '
            </table>
            <br />
            <div style="text-align: center">';

        /* Display status form */
        echo '
            <form action="'.$currentIndex.'&view'.$this->table.'&token='.$this->token.'" method="post" style="text-align:center; display: inline-block;">
                <select name="id_order_state">';
        $currentStateTab = $order->getCurrentStateFull($cookie->id_lang);
        foreach ($states AS $state)
            echo '<option value="'.$state['id_order_state'].'"'.(($state['id_order_state'] == $currentStateTab['id_order_state']) ? ' selected="selected"' : '').'>'.stripslashes($state['name']).'</option>';
        echo '
                </select>
                <input type="hidden" name="id_order" value="'.$order->id.'" />
                <input type="submit" name="submitState" value="'.$this->l('Change').'" class="button" />';

        if ($currentStateTab['id_order_state'] == _PS_OS_PREPARATION_) {
            echo ' | <input type="submit" name="dispatchToWarehouse" value="' . $this->l('Dispatch to Warehouse') . '" class="button" />';
        }

        echo '
                </form>
            </div>';

        /* Display customer information */
        if (Validate::isLoadedObject($customer))
        {
            echo '<br />
            <fieldset style="width: 400px">
                <legend><img src="../img/admin/tab-customers.gif" /> '.$this->l('Customer information').'</legend>
                <span style="font-weight: bold; font-size: 14px;"><a href="?tab=AdminCustomers&id_customer='.$customer->id.'&viewcustomer&token='.Tools::getAdminToken('AdminCustomers'.(int)(Tab::getIdFromClassName('AdminCustomers')).(int)($cookie->id_employee)).'"> '.$customer->firstname.' '.$customer->lastname.'</a></span> ('.$this->l('#').$customer->id.')<br />
                (<a href="mailto:'.$customer->email.'">'.$customer->email.'</a>)<br /><br />';
            if ($customer->isGuest())
            {
                echo '
                '.$this->l('This order has been placed by a').' <b>'.$this->l('guest').'</b>';
                if (!Customer::customerExists($customer->email))
                {
                    echo '<form method="POST" action="index.php?tab=AdminCustomers&id_customer='.(int)$customer->id.'&token='.Tools::getAdminTokenLite('AdminCustomers').'">
                        <input type="hidden" name="id_lang" value="'.(int)$order->id_lang.'" />
                        <p class="center"><input class="button" type="submit" name="submitGuestToCustomer" value="'.$this->l('Transform to customer').'" /></p>
                        '.$this->l('This feature will generate a random password and send an e-mail to the customer').'
                    </form>';
                }
                else
                    echo '<div><b style="color:red;">'.$this->l('A registered customer account exists with the same email address').'</b></div>';
            }
            else
            {
                echo $this->l('Account registered:').' '.Tools::displayDate($customer->date_add, (int)($cookie->id_lang), true).'<br />
                '.$this->l('Valid orders placed:').' <b>'.$customerStats['nb_orders'].'</b><br />
                '.$this->l('Total paid since registration:').' <b>'.Tools::displayPrice(Tools::ps_round(Tools::convertPrice($customerStats['total_orders'], $currency), 2), $currency, false).'</b><br />';
            }
            echo '</fieldset>';
        }

        /* Display sources */
        if (sizeof($sources))
        {
            echo '<br />
            <fieldset style="width: 400px;"><legend><img src="../img/admin/tab-stats.gif" /> '.$this->l('Sources').'</legend><ul '.(sizeof($sources) > 3 ? 'style="height: 200px; overflow-y: scroll; width: 360px;"' : 'style="width: 360px;overflow-x: scroll;"').'>';
            foreach ($sources as $source)
                echo '<li>
                        '.Tools::displayDate($source['date_add'], (int)($cookie->id_lang), true).'<br />
                        <b>'.$this->l('From:').'</b> <a href="'.$source['http_referer'].'">'.preg_replace('/^www./', '', parse_url($source['http_referer'], PHP_URL_HOST)).'</a><br />
                        <b>'.$this->l('To:').'</b> '.$source['request_uri'].'<br />
                        '.($source['keywords'] ? '<b>'.$this->l('Keywords:').'</b> '.$source['keywords'].'<br />' : '').'<br />
                    </li>';
            echo '</ul></fieldset>';
        }
        // display hook specified to this page : AdminOrder
        if (($hook = Module::hookExec('adminOrder', array('id_order' => $order->id))) !== false)
            echo $hook;

        echo '
        </div>
        <div style="float: left; margin-left: 40px">';

        /* Display invoice information */
        echo '<fieldset style="width: 400px">';
        if (($currentState->invoice OR $order->invoice_number) AND count($products))
            echo '<legend><a href="pdf.php?id_order='.$order->id.'&pdf"><img src="../img/admin/charged_ok.gif" /> '.$this->l('Invoice').'</a></legend>
                <a href="pdf.php?id_order='.$order->id.'&pdf">'.$this->l('Invoice #').'<b>'.Configuration::get('PS_INVOICE_PREFIX', (int)($cookie->id_lang)). $order->invoice_number . '</b></a>
                <br />'.$this->l('Created on:').' '.Tools::displayDate($order->invoice_date, (int)$cookie->id_lang, true);
        else
            echo '<legend><img src="../img/admin/charged_ko.gif" />'.$this->l('Invoice').'</legend>
                '.$this->l('No invoice yet.');
        echo '</fieldset><br />';

        /* Display shipping infos */
        echo '
        <fieldset style="width:400px;">
            <legend><img src="../img/admin/delivery.gif" /> '.$this->l('Shipping information').'</legend>
            '.$this->l('Total weight:').' <b>'.number_format($order->getTotalWeight(), 3).' '.Configuration::get('PS_WEIGHT_UNIT').'</b><br />
            '.$this->l('Carrier:').' <b>'.($carrier->name == '0' ? Configuration::get('PS_SHOP_NAME') : $carrier->name).'</b><br />
            '.(($currentState->delivery OR $order->delivery_number) ? '<br />' . $this->l('Delivery slip #') . '<b>' . Configuration::get('PS_DELIVERY_PREFIX', intval($cookie->id_lang)) . $order->delivery_number . '</b><br />' : '').
            $this->l('Tracking number :'). '
                <input type="text" name="trancking_number" id="it-tracking-number" value="'. ((! empty($order->tracking_number)) ? $order->tracking_number : '' ) .'" placeholder="'.((int)$order->tracking_number > 0 ? $order->tracking_number : Configuration::get('PS_INVOICE_PREFIX', (int)($cookie->id_lang)) . $order->invoice_number).'" style="margin:0 5px 0 7px;">
                <input type="button" id="btn-save-tracking-number" class="button" value="'.$this->l('Save').'" style="height:20px;">
                <span></span>
                <script type="text/javascript">
                    $(function(){
                        $("#btn-save-tracking-number").click(function(){
                            var $btn = $(this)
                                , $trackingNum = $("#it-tracking-number")
                                , trackingNumber = $trackingNum.val();

                            if (! trackingNumber) {
                                alert("' . $this->l("Invalid Tracking Number") . '");
                                return;
                            }

                            $.ajax({
                                type: "GET" /* PUT not allowed */
                                , url: "ajax.php"
                                , dataType : "json"
                                , data : {
                                    "id_order":' . $order->id . '
                                    , "tracking_number": trackingNumber
                                }
                                , success : function(res) {
                                    if (res.success) {
                                        $btn.siblings("span").text("'.$this->l("Saved").'");
                                        $trackingNum.attr("placeholder", $trackingNum.val());
                                    } else {
                                        $btn.siblings("span").text(res.error.message);
                                    }
                                }
                            });

                        });

                    });

                </script>

                <br/>'.

            ($order->hasBeenShipped() ? '<br /><a href="' . $order->getTrackingLink() . '" target="_blank">'
                . $this->l('Click Here For Order Tracking') . '<b>'. '</b></a><br />' : '')

            . '<br><br>

            <div style="float: right"><input type="button" class="button" id="btnSendToCarrier" value="' . $this->l('Send to Carrier') . '"></div>

            <script type="text/javascript">
                $("#btnSendToCarrier").click(function(e) {
                    var url = "' . $currentIndex . '&id_order=' . $order->id . '&vieworder&token='
                        . $this->token . '&sendToCarrier=1";

                    window.location = url;
                });
            </script>

            ';

            if ($order->shipping_number) {
                echo $this->l('Tracking number:') . ' <b>' . ((! empty($order->tracking_number)) ? $order->tracking_number : $order->shipping_number)
                    . '</b> ' . (! empty($carrier->url) ? '(<a href="'.str_replace('@', $order->shipping_number, $carrier->url)
                    . '" target="_blank">' . $this->l('Track the shipment') . '</a>)' : '');
            }

            /* Carrier module */
            if ($carrier->is_module == 1)
            {
                $module = Module::getInstanceByName($carrier->external_module_name);
                if (method_exists($module, 'displayInfoByCart'))
                    echo call_user_func(array($module, 'displayInfoByCart'), $order->id_cart);
            }

            /* Display shipping number field */
            if ($carrier->url && $order->hasBeenShipped())
             echo '
                <form action="'.$currentIndex.'&view'.$this->table.'&token='.$this->token.'" method="post" style="margin-top:10px;">
                    <input type="text" name="shipping_number" value="'. $order->shipping_number.'" />
                    <input type="hidden" name="id_order" value="'.$order->id.'" />
                    <input type="submit" name="submitShippingNumber" value="'.$this->l('Set shipping number').'" class="button" />
                </form>';
            echo '
        </fieldset>';

        /* Display summary order */
        echo '
        <br />
        <fieldset style="width: 400px">
            <legend><img src="../img/admin/details.gif" /> '.$this->l('Order details').'</legend>
            <label>'.$this->l('Original cart:').' </label>
            <div style="margin: 2px 0 1em 190px;"><a href="?tab=AdminCarts&id_cart='.$cart->id.'&viewcart&token='.Tools::getAdminToken('AdminCarts'.(int)(Tab::getIdFromClassName('AdminCarts')).(int)($cookie->id_employee)).'">'.$this->l('Cart No.').sprintf('%06d', $cart->id).'</a></div>
            <label>'.$this->l('Payment mode:').' </label>
            <div style="margin: 2px 0 1em 190px;">'.Tools::substr($order->payment, 0, 32).' '.($order->module ? '('.$order->module.')' : '').'</div>
                        '.($order->installment_count > 1 ?'<label>'.$this->l('Installments:').' </label><div style="margin: 2px 0 1em 190px;">'.$order->installment_count.'</div><label>'.$this->l('Installment Interest:').' </label><div style="margin: 2px 0 1em 190px;">'.$order->installment_interest.'%</div>' :'').'
                        <div style="margin: 2px 0 1em 50px;">
                <table class="table" width="300px;" cellspacing="0" cellpadding="0">
                    <tr><td width="150px;">'.$this->l('Products').'</td><td align="right">'.Tools::displayPrice($order->getTotalProductsWithTaxes(), $currency, false).'</td></tr>
                    '.($order->total_discounts > 0 ? '<tr><td>'.$this->l('Discounts').'</td><td align="right">-'.Tools::displayPrice($order->total_discounts, $currency, false).'</td></tr>' : '').'
                    '.($order->total_wrapping > 0 ? '<tr><td>'.$this->l('Wrapping').'</td><td align="right">'.Tools::displayPrice($order->total_wrapping, $currency, false).'</td></tr>' : '').'
                    <tr><td>'.$this->l('Shipping').'</td><td align="right">'.Tools::displayPrice($order->total_shipping, $currency, false).'</td></tr>
                                         '.($order->installment_count > 1 ?'<tr><td>'.$this->l('Interest Amount').'</td><td align="right">'.Tools::displayPrice(($order->total_paid_real - ($order->total_products_wt  - $order->total_discounts + $order->total_shipping)) ,$currency, false).'</td></tr>' :'').'
                    <tr style="font-size: 20px"><td>'.$this->l('Total').'</td><td align="right">'.Tools::displayPrice($order->total_paid, $currency, false).($order->total_paid != $order->total_paid_real ? '<br /><font color="red">('.$this->l('Paid:').' '.Tools::displayPrice($order->total_paid_real, $currency, false, false).')</font>' : '').'</td></tr>
                </table>
            </div>
            <div style="float: left; margin-right: 10px; margin-left: 42px;">
                <span class="bold">'.$this->l('Recycled package:').'</span>
                '.($order->recyclable ? '<img src="../img/admin/enabled.gif" />' : '<img src="../img/admin/disabled.gif" />').'
            </div>
            <div style="float: left; margin-right: 10px;">
                <span class="bold">'.$this->l('Gift wrapping:').'</span>
                 '.($order->gift ? '<img src="../img/admin/enabled.gif" />
            </div>
            <div style="clear: left; margin: 0px 42px 0px 42px; padding-top: 2px;">
                '.(!empty($order->gift_message) ? '<div style="border: 1px dashed #999; padding: 5px; margin-top: 8px;"><b>'.$this->l('Message:').'</b><br />'.nl2br2($order->gift_message).'</div>' : '') : '<img src="../img/admin/disabled.gif" />').'
            </div>
        </fieldset>';

        echo '</div>
        <div class="clear">&nbsp;</div>';

        /* Display adresses : delivery & invoice */
        echo '<div class="clear">&nbsp;</div>
        <div style="float: left">
            <fieldset style="width: 400px;">
                <legend><img src="../img/admin/delivery.gif" alt="'.$this->l('Shipping address').'" />'.$this->l('Shipping address').'</legend>
                <div style="float: right">
                    <a href="?tab=AdminAddresses&id_address='.$addressDelivery->id.'&addaddress&realedit=1&id_order='.$order->id.($addressDelivery->id == $addressInvoice->id ? '&address_type=1' : '').'&token='.Tools::getAdminToken('AdminAddresses'.(int)(Tab::getIdFromClassName('AdminAddresses')).(int)($cookie->id_employee)).'&back='.urlencode($_SERVER['REQUEST_URI']).'"><img src="../img/admin/edit.gif" /></a>
                    <a href="http://maps.google.com/maps?f=q&hl='.$currentLanguage->iso_code.'&geocode=&q='.$addressDelivery->address1.' '.$addressDelivery->postcode.' '.$addressDelivery->city.($addressDelivery->id_state ? ' '.$deliveryState->name: '').'" target="_blank"><img src="../img/admin/google.gif" alt="" class="middle" /></a>
                </div>
                '.$this->displayAddressDetail($addressDelivery)
                .(!empty($addressDelivery->other) ? '<hr />'.$addressDelivery->other.'<br />' : '')
            .'</fieldset>
        </div>
        <div style="float: left; margin-left: 40px">
            <fieldset style="width: 400px;">
                <legend><img src="../img/admin/invoice.gif" alt="'.$this->l('Invoice address').'" />'.$this->l('Invoice address').'</legend>
                <div style="float: right"><a href="?tab=AdminAddresses&id_address='.$addressInvoice->id.'&addaddress&realedit=1&id_order='.$order->id.($addressDelivery->id == $addressInvoice->id ? '&address_type=2' : '').'&back='.urlencode($_SERVER['REQUEST_URI']).'&token='.Tools::getAdminToken('AdminAddresses'.(int)(Tab::getIdFromClassName('AdminAddresses')).(int)($cookie->id_employee)).'"><img src="../img/admin/edit.gif" /></a></div>
                '.$this->displayAddressDetail($addressInvoice)
                .(!empty($addressInvoice->other) ? '<hr />'.$addressInvoice->other.'<br />' : '')

            .'</fieldset>
        </div>
        <div class="clear">&nbsp;</div>';

        $immutableStates = array(
            _PS_OS_CANCELED_,
            _PS_OS_REFUND_,
            _PS_OS_EXCHANGE_,
            _PS_OS_PROCESSING_,
            _PS_OS_PROCESSED_,
            _PS_OS_WAITING_MANAGER_APPROVAL_
        );
        $currentState = $order->getCurrentState();
        $preventModification = (in_array($currentState, $immutableStates) ? 'true' : 'false');

        // List of products
        echo
        '<script type="text/javascript">
                    // Checked all products if not any checkbox selected
                    $(function(){
                        $("form input[type=submit]").click(function() {
                            $("input[type=submit]", $(this).parents("form")).removeAttr("data-clicked");
                            $(this).attr("data-clicked", "true");
                        });

                        $("#formProducts").submit(function(e) {
                            if (' . $preventModification . ') {
                                alert("' . $this->l('You cannot make any modifications at this state!') . '");
                                return false;
                            }

                            var $btnClicked = $(this).find("input[type=submit][data-clicked=true]");
                            var only1Product = ["manualRefund", "btnCancelExchange"];

                            if (only1Product.indexOf($btnClicked.attr("id")) != -1) {
                                if ($(".cb_product:checked").length > 1) {
                                    alert("'.$this->l("Please select only one product").'");
                                    return false;
                                }
                            }

                            if (! $(".cb_product:checked").length) {
                                alert("'.$this->l('Please select one or more products').'");
                                return false;
                            }

                            $(".product-shoe-sizes").each(function(i, item) {
                                if ($(item).siblings(".prod-moderation-reason").val() == '.Configuration::get("PROD_MODERATION_REASON_SHOE_SIZE").') {
                                    $(item).next(".shoe-size").val($(item).children("option:selected").text());
                                } else {
                                    $(item).remove();
                                }
                            });

                            return orderDeleteProduct(\''.$this->l('Cannot return this product').'\', \''.$this->l('Quantity to cancel is greater than quantity available').'\');
                        });
                        /*
                        $(".select-all-products").click(function() {
                            $(".cb_product:not(:disabled)").attr("checked", $(this).is(":checked"));

                            setButtonTexts();
                            initButtonVisibility();
                            setModerationChoicesVisibility();

                            if (isOrderModeration()) {
                                showOrderModerationMessageCon();
                            } else {
                                hideOrderModerationMessageCon();
                            }
                        });
                        */

                        $(".cb_product").click(function() {
                            if (isOrderModeration()) {
                                hideAllProdModChoices();
                                showOrderModerationMessageCon();
                            } else {
                                hideOrderModerationMessageCon();
                            }

                            setModerationChoicesVisibility();
                            initButtonVisibility();
                            setButtonTexts();
                        });

                        $(".product-quantity").change(function() {
                            $(this).closest("tr").find(".cb_product").attr("checked", true);
                            $(this).closest("tr").next(".productModerationChoices").show();
                            $("#btnExchangeProducts").parent().show();
                            setButtonTexts();
                            initButtonVisibility();
                            setModerationChoicesVisibility();
                        });

                        $(".prod-moderation-reason").change(function() {
                            var id_reason = $(this).val();
                            if (id_reason == '.Configuration::get("PROD_MODERATION_REASON_SHOE_SIZE").') {
                                $(this).siblings(".prod-moderation-note").hide()
                                $(this).siblings(".product-shoe-sizes").show();
                            } else {
                                $(this).siblings(".prod-moderation-note").show()
                                .siblings(".product-shoe-sizes").hide();
                            }
                        });

                        $(".select-all-products, .cb_product").click(function() {
                            setButtonTexts();
                        });

                    });

                    function isOrderModeration() {
                        var result = true;
                        $(".product-quantity").each(function(i, item) {
                            if ($(item).val() > 1) {
                                result = false;
                                return;
                            }
                        });

                        if ($(".cb_product").length == 1 || $(".cb_product:checked").length < $(".cb_product").length) {
                            result = false;
                        }

                        return result;
                    }

                    function initButtonVisibility() {
                        var $selectedCbx = $(".cb_product:checked");

                        $("#moderation-action-buttons").show()
                            .siblings("input").show();

                        if (isOrderModeration() && $(".cb_product").length > 1) {
                            $("#btnExchangeProducts").hide();
                        } else {
                            $("#btnExchangeProducts").show();
                        }

                        if ($selectedCbx.length == 0 ) {
                            $("#btnExchangeProducts").parent().hide();
                            $(".product-quantity").each(function(i, item) {
                                if ($(item).val() > 1) {
                                   $("#btnExchangeProducts").parent().show();
                                }
                            });
                        }
                    }

                    function setModerationChoicesVisibility() {
                        if (isOrderModeration()) {
                            $("#formProducts").find(".productModerationChoices").hide();
                        } else {
                            if (isAllProductsSelected()) {
                                $("#formProducts").find(".productModerationChoices").show();
                            } else {
                                $(".cb_product").each(function(i, item) {
                                    if ($(item).is(":checked") && $(item).closest("tr").find(".product-quantity").children().length) {
                                       $(item).closest("tr").next(".productModerationChoices").show();
                                    } else {
                                       $(item).closest("tr").next(".productModerationChoices").hide();
                                    }
                                });

                                $(".product-quantity").each(function(i, item) {
                                    if ($(item).val() > 1) {
                                       $(item).closest("tr").next(".productModerationChoices").show();
                                       $(item).closest("tr").find(".cb_product").attr("checked", true);
                                    }
                                });
                            }
                        }
                    }

                    function isAllProductsSelected() {
                        return $(".cb_product:checked").length == $(".cb_product").length;
                    }

                    function setButtonTexts() {
                        if (isOrderModeration()) {
                            $("#btnCancelProducts").val("'.$this->l("Cancel Order").'");
                            $("#btnExchangeProducts").val("'.$this->l("Exchange Order").'");
                        } else {
                            $("#btnCancelProducts").val("'.$this->l("Cancel Product(s)").'");
                            $("#btnExchangeProducts").val("'.$this->l("Exchange Product(s)").'");
                        }
                    }

                    function showOrderModerationMessageCon() {
                        $("#orderModerationMessage").show();
                        $("#orderModerationReason").show();
                    }

                    function hideOrderModerationMessageCon() {
                        $("#orderModerationMessage").hide();
                        $("#orderModerationReason").hide();
                    }

                    function hideAllProdModChoices() {
                        $(".productModerationChoices").hide();
                    }


                    function display_opts(type , order_detail_id, qty, credit_discount, shipping)
                    {
                        $(".display_options").remove();
                        var detail = \'<div class="display_options">\';
                         detail += \'<div id="select_dty"><span>How Many</span>\';
                        if(credit_discount)
                        {
                            detail += \'<select name="orderDetail[\'+order_detail_id+\'][quantity]">\';
                            for(var i=1; i<=qty;i++){
                                detail +=   \'<option value="\'+i+\'">\'+i+\'</option>\';
                            }
                            detail += \'</select>\';
                        }
                        else
                        {   detail +=\'<select name="orderDetail[\'+order_detail_id+\'][quantity]">\';
                            for(var i=1; i<=qty;i++){
                            detail += \'<option value="\'+i+\'">\'+i+\'</option>\';}
                            detail += \'</select>\';
                        }
                        detail += \'</div>\';
                        if(!credit_discount && shipping == 1)
                            detail += \'<div id="sel_shipping"><label for="Shipping" style="float:none; font-weight:normal;">'.$this->l('Shipping').'</label><input type="checkbox" id="shipping" name="shipping" class="button">&nbsp;</div>\';
                        detail +=  \'<input type="hidden" name="id_order_detail" value="\'+order_detail_id+\'" class="action"> <input type="submit" name="\'+type+\'" value="Confirm" id="\'+type+\'" class="action button">\';
                        $("#display_details_"+order_detail_id).append(detail);
                        $("#display_details_"+order_detail_id).fadeIn();
                    }
        </script>';

        $isOrderWaitingForMApproval = OrderModerationDetail::isExistByOrderId($order->id);

        echo'
            <a name="products"><br /></a>
        <form id="formProducts" action="'.$currentIndex.'&submitCreditSlip&vieworder&token='.$this->token.'" method="post">
            <input type="hidden" name="id_order" value="'.$order->id.'" />
            <input type="hidden" name="order_detail_rows"  value="'.count($products).'"/>
            <fieldset style="width: 868px;">
                <legend><img src="../img/admin/cart.gif" alt="'.$this->l('Products').'" />'.$this->l('Products').'</legend>
                <div style="float:left;">
                    <table style="width: 868px;font-size:13px;" cellspacing="0" cellpadding="0" class="table" id="orderProducts">
                        <tr>
                            <th align="center" style="width: 20px">';
                                // echo ($isOrderWaitingForMApproval) ? '' : '<input type="checkbox" class="select-all-products button" style="width:25px">';
                            echo '</th>
                            <th align="center" style="width: 60px">&nbsp;</th>
                            <th style="width: 300px;">'.$this->l('Product').'</th>
                            <th style="width: 60px; text-align: center">'.$this->l('UP').' <sup>*</sup></th>
                            <th style="width: 15px; text-align: center">'.$this->l('Qty').'</th>
                            <th style="width: 17px; text-align: center">'.$this->l('Stock').'</th>
                            <th style="width: 60px; text-align: center">'.$this->l('Total').' <sup>*</sup></th>
                            <th style="width: 100px; text-align: left">'.$this->l('Action').'</th>
                            <th style="width: 110px; text-align: center"></th>';
                        echo '
                        </tr>';
                        $tokenCatalog = Tools::getAdminToken('AdminCatalog'.(int)(Tab::getIdFromClassName('AdminCatalog')).(int)($cookie->id_employee));

                        foreach ($products as $k => $product) {
                            $isProductWaitingForMApproval = false;
                            $id_product = $product['id_product'];
                            if (!$isAnyProductAtModeration && ProductModerationDetail::isExistByIdOrderDetail($k)) {
                                //Quantity of product that waiting for m. approval
                                $productModerationQuantity = ProductModerationDetail::getTotalQuantityOfProductByOrderDetailId($k);
                                if ($productModerationQuantity == $product['product_quantity'] || !$product['product_quantity']) {
                                    $isProductWaitingForMApproval = true;
                                }
                            }

                            $isDisabledProduct = ($isProductWaitingForMApproval || $isOrderWaitingForMApproval || !$product['product_quantity']);

                            if ($order->getTaxCalculationMethod() == PS_TAX_EXC) {
                                $product_price = $product['product_price'] + $product['ecotax'];
                            } else {
                                $product_price = $product['product_price_wt'];
                            }

                            $image = array();
                            if (isset($product['product_attribute_id']) AND (int)($product['product_attribute_id']))
                                $image = Db::getInstance()->getRow('
                                SELECT id_image
                                FROM '._DB_PREFIX_.'product_attribute_image
                                WHERE id_product_attribute = '.(int)($product['product_attribute_id']));
                            if (!isset($image['id_image']) OR !$image['id_image'])
                                $image = Db::getInstance()->getRow('
                                SELECT id_image
                                FROM '._DB_PREFIX_.'image
                                WHERE id_product = '.(int)($product['product_id']).' AND cover = 1');
                            $stock = Db::getInstance()->getRow('
                            SELECT '.($product['product_attribute_id'] ? 'pa' : 'p').'.quantity
                            FROM '._DB_PREFIX_.'product p
                            '.($product['product_attribute_id'] ? 'LEFT JOIN '._DB_PREFIX_.'product_attribute pa ON p.id_product = pa.id_product' : '').'
                            WHERE p.id_product = '.(int)($product['product_id']).'
                            '.($product['product_attribute_id'] ? 'AND pa.id_product_attribute = '.(int)($product['product_attribute_id']) : ''));
                            if (isset($image['id_image']))
                            {
                                $target = _PS_TMP_IMG_DIR_.'product_mini_'.(int)($product['product_id']).(isset($product['product_attribute_id']) ? '_'.(int)($product['product_attribute_id']) : '').'.jpg';
                                if (file_exists($target))
                                    $products[$k]['image_size'] = getimagesize($target);
                            }
                            // Customization display
                            $this->displayCustomizedDatas($customizedDatas, $product, $currency, $image, $tokenCatalog, $k);

                            // Normal display
                            //if ($product['product_quantity'] > $product['customizationQuantityTotal'])
                            {
                                $imageObj = new Image($image['id_image']);
                                $order_details = OrderHistoryDetails::getOrderDetail($k, 0, true, $cookie->id_lang);
                                $backgroundColor = '';
                                if($order_details)
                                {
                                    foreach($order_details as $order_detail)
                                    {
                                        $backgroundColor = $order_detail['color'];
                                    }
                                }

                                if ($isOrderWaitingForMApproval OR $isProductWaitingForMApproval) {
                                    $backgroundColor = '#ebff58';
                                }

                                echo '
                                <tr height= 74px; style="background-color:'.$backgroundColor.';" data-quantity-exchanged="'.$product['product_quantity_exchanged'].'">
                                    <td align="center">
                                        <input type="hidden" name="orderDetail['.$k.'][product_id]" value="'.$product['product_id'].'">
                                        <input type="checkbox" name="orderDetail['.$k.'][process]" value="1" style="width:25px" class="button cb_product">
                                    </td>
                                    <td align="center">'.(isset($image['id_image']) ? cacheImage(_PS_IMG_DIR_.'p/'.$imageObj->getExistingImgPath().'.jpg',
                                    'product_mini_'.(int)($product['product_id']).(isset($product['product_attribute_id']) ? '_'.(int)($product['product_attribute_id']) : '').'.jpg', 45, 'jpg') : '--').'</td>
                                    <td><a href="index.php?tab=AdminCatalog&id_product='.$product['product_id'].'&updateproduct&token='.$tokenCatalog.'">
                                        <span class="productName">'.$product['product_name'].'</span><br />
                                        '.($product['product_reference'] ? $this->l('Ref:').' '.$product['product_reference'].'<br />' : '')
                                        .($product['product_supplier_reference'] ? $this->l('Ref Supplier:').' '.$product['product_supplier_reference'] : '')
                                        .'</a></td>
                                    <td align="center">'.Tools::displayPrice($product_price, $currency, false).'</td>
                                    <td align="center" class="productQuantity">'.((int)($product['product_quantity']) - $product['customizationQuantityTotal']).'</td>
                                    <td align="center" class="productQuantity">'.(int)($stock['quantity']).'</td>
                                    <td align="center">'.Tools::displayPrice(Tools::ps_round($product_price, 2) * ((int)($product['product_quantity']) - $product['customizationQuantityTotal']), $currency, false).'</td>

                                    <input type="hidden" name="order_detail['.$k.']" id="order_detail_'.(int)($product['id_order_detail']).'" value="'.$product['id_order_detail'].'" />
                                    <input type="hidden" name="product_ref['.$k.']" id="product_ref_'.(int)($product['id_order_detail']).'" value="'.$product['product_reference'].'" />';
                                $shipping_flag = 0;
                                if(/*$order->total_paid_real &&
                                        $order->total_paid_real < Configuration::get('PS_SHIPPING_FREE_PRICE') &&*/
                                        $order->total_shipping !=0 ){
                                    $shipping_flag = 1;
                                }

                                echo ' <td class="chooseAction">';
                                    echo '<div class="actions">';
                                            echo '<select class="product-quantity" name="orderDetail['.$k.'][quantity]" '.($isDisabledProduct ? 'disabled="disabled"': '').'>';
                                                if ($productModerationQuantity) {
                                                    $qty = $product['product_quantity'] - $productModerationQuantity;
                                                } else {
                                                    $qty = $product['product_quantity'];
                                                }

                                                for($i=1; $i<=$qty; $i++) {
                                                    echo "<option value=$i>$i</option>";
                                                }
                                            echo '</select>';
                                        if(isset($product['credit_discount_'.$k])) {
                                            $credit_qty = $product['credit_quantity_'.$k] ;
                                            if($order_details) {
                                                foreach($order_details as $order_detail) {
                                                    if($order_detail['quantity'] > 0 && $order_detail['id_order_state'] == _PS_OS_PARTIALCREDITED_) {
                                                        $credit_qty = $product['credit_quantity_'.$k] - $order_detail['quantity'];
                                                    }
                                                }

                                                echo $this->l("Credits Purchase.Credits:") + $credit_qty;
                                            }
                                        }
                                        echo'</div>
                                        <div id="display_details_'.(int)($product['id_order_detail']).'" style="display:none;"></div>';
                                echo '</td>
                                <td class="info_to_customer_rep">
                                <ul style="padding:0;">';

                                    if (OrderModerationDetail::isExistByOrderId($order->id) OR $productModerationQuantity) {
                                        echo '<li>'.sprintf('%s %s %s',$productModerationQuantity, $this->l('product'), $this->l('waiting for manager approval')).'</li>';
                                    }

                                    if(isset($product['credit_discount_'.$k])== $product['product_id'])
                                        echo '<li> Credits Purchase.<br/>Credits:'.$product['credit_quantity_'.$k].'</li>';
                                    if($order_details)
                                    {
                                        foreach($order_details as $order_detail)
                                        {
                                            if($order_detail['shipping_cost'] > 0)
                                                echo '<li> Shipping:'. $order_detail['shipping_cost'].' TL </li>';
                                            if($order_detail['name'])
                                                echo '<li>'.$order_detail['name'].' </li>';
                                            if($order_detail['quantity'] > 0)
                                            {
                                                if($order_detail['id_order_state'] == _PS_OS_PARTIALCREDITED_)
                                                    $qty_type = 'Credit';
                                                else
                                                    $qty_type = 'Product';
                                                echo '<li> '.$qty_type.' Qty: '.$order_detail['quantity'].' </li>';
                                            }
                                            if($order_detail['id_employee'])
                                            {
                                               $name =  Employee::getEmployeeById($order_detail['id_employee']);
                                               echo '<li style="border-bottom:1px solid;">'.$name.'</li> ';
                                            }
                                        }
                                    }
                                echo '</ul></td>
                                </tr>';
                            }

                        echo '<tr class="productModerationChoices" style="display:none; background-color:'.$backgroundColor.';">
                                <td>&nbsp;</td>
                                <td colspan="8">
                                    '.$this->l('Reason :').'
                                    <select class="prod-moderation-reason" name="orderDetail['.$k.'][id_reason]">
                                        '. Module::hookExec('displayProductModerationChoices').'
                                    </select>
                                    <input class="prod-moderation-note" type="text" name="orderDetail['.$k.'][message]" placeholder="'.$this->l('Extra Note(Optional)').'" style="width:400px;height:14px;">';
                                        $sizes = Product::getSingleColorShoeSizes($product['id_product'], $cookie->id_lang);
                                        if ($sizes) {
                                            echo '<select class="product-shoe-sizes" name="orderDetail['.$k.'][shoe_reference]" style="display:none;">';
                                                foreach ($sizes as $attribute) {
                                                    echo '<option value="'.$attribute['reference'].'">'.$attribute['attribute_name'].'</option>';
                                                }
                                            echo '</select>';
                                        } else {
                                            echo '<span class="product-shoe-sizes" style="display:none;">'. $this->l("This product not a shoe").'</span>';
                                        }
                                    echo '<input type="hidden" class="shoe-size" name="orderDetail['.$k.'][shoe_size]">
                                </td>
                            </tr>';
                            unset($productModerationQuantity);
                        }
                    echo '
                    </table>
                    <div style=" float: left;width: 100%;">
                    <div style="float:left; width:280px; margin-top:15px;"><sup>*</sup> '.$this->l('According to the group of this customer, prices are printed:').' '.($order->getTaxCalculationMethod() == PS_TAX_EXC ? $this->l('tax excluded.') : $this->l('tax included.')).(!Configuration::get('PS_ORDER_RETURN') ? '<br /><br />'.$this->l('Merchandise returns are disabled') : '').'</div>';

                    if (sizeof($discounts))
                    {
                        echo '
                    <div style="float:right; width:280px; margin-top:15px;">
                    <table cellspacing="0" cellpadding="0" class="table" style="width:100%;">
                        <tr>
                            <th><img src="../img/admin/coupon.gif" alt="'.$this->l('Discounts').'" />'.$this->l('Discount name').'</th>
                            <th align="center" style="width: 100px">'.$this->l('Value').'</th>
                        </tr>';
                        foreach ($discounts as $discount)
                            echo '
                        <tr>
                            <td>'.$discount['name'].'
                        '.($discount['id_discount_type'] == _PS_OS_CREDIT_ID_TYPE_? $this->l('(Credit Discount)'):'').'
                            </td>
                            <td align="center">'.($discount['value'] != 0.00 ? '- ' : '')
                                .($discount['id_discount_type'] == 1 ? $discount['value'].'%' : Tools::displayPrice($discount['value'], $currency, false)).
                            '</td>
                        </tr>';
                        echo '
                    </table></div>';
                    }
                echo '
                </div>';


                // Cancel product
                echo '
                <div style="clear:both; height:15px;">&nbsp;</div>
                <div style="height:25px;'.($isOrderWaitingForMApproval ? 'display:none;' : '').'" class="cb-shipping-con">
                    <input type="checkbox" name="shipping" class="button" style="width:25px;">'.$this->l('Shipping').
                    '<input type="text" name="orderModerationMessage" id="orderModerationMessage" placeholder="'.$this->l('Extra Note(Optional)').'" style="width:400px;float:right;display:none;">
                       <select name="orderModerationReason" id="orderModerationReason" style="float:right;margin-right:10px;display:none;">
                            '. Module::hookExec('displayOrderModerationChoices').'
                        </select>
                </div>
                <div style="height:25px;">

                </div>
                <div style="float: right; width:480px;">';
                if ($order->hasBeenDelivered() AND Configuration::get('PS_ORDER_RETURN'))
                    echo '
                    <input type="checkbox" id="reinjectQuantities" name="reinjectQuantities" class="button" />&nbsp;<label for="reinjectQuantities" style="float:none; font-weight:normal;">'.$this->l('Re-stock products').'</label><br />';
                if ((!$order->hasBeenDelivered() AND $order->hasBeenPaid()) OR ($order->hasBeenDelivered() AND Configuration::get('PS_ORDER_RETURN')))
                    echo '
                    <span id="spanShippingBack" style="display:none;"><input type="checkbox" id="generateCreditSlip" name="generateCreditSlip" class="button" onclick="toogleShippingCost(this)" />&nbsp;<label for="generateCreditSlip" style="float:none; font-weight:normal;">'.$this->l('Generate a credit slip').'</label><br /></span>
                    <span id="spanShippingBack" style="display:none;"><input type="checkbox" id="generateDiscount" name="generateDiscount" class="button" onclick="toogleShippingCost(this)" />&nbsp;<label for="generateDiscount" style="float:none; font-weight:normal;">'.$this->l('Generate a voucher').'</label><br /></span>
                    <span id="spanShippingBack" style="display:none;"><input type="checkbox" id="shippingBack" name="shippingBack" class="button" />&nbsp;<label for="shippingBack" style="float:none; font-weight:normal;">'.$this->l('Repay shipping costs').'</label><br /></span>';

                echo '<div style="text-align:center; margin: 10px 15px 0 0; float:right;display:none;" id="moderation-action-buttons">';
                        function discountTypeMapper($row) {
                            return $row['id_discount_type'];
                        }

                        $discountTypes = array_map('discountTypeMapper', $order->getOrderDiscounttype());

                        if (in_array(_EXCHANGE_VOUCHER_TYPE_ID_, $discountTypes)) {
                            echo '<select name="exchangeVoucherToBeCancelled" style="vertical-align: -5px; margin-right: 5px;">
                                <option value="0" selected>---</option>';

                            foreach ($discounts as $discount) {
                                if ($discount['active'] AND $discount['id_discount_type'] == _EXCHANGE_VOUCHER_TYPE_ID_) {
                                    echo '<option value="' . $discount['id_discount'] . '">' . $discount['name'] . '</option>';
                                }
                            }

                            echo '</select>';
                            echo '<input type="submit" name="btnCancelExchange" id="btnCancelExchange" value="'
                                . $this->l('Cancel Exchange') . '" class="button" style="margin:8px 15px 0 0 ;" />';
                        }

                        echo '<input type="submit" name="exchangeProducts" id="btnExchangeProducts" value="'.$this->l($isAnyProductAtModeration ? 'Exchange Product(s)' : 'Exchange Order').'" class="button" style="margin-top:8px;" />
                        <input type="submit" name="cancelProducts" id="btnCancelProducts" value="'.$this->l($isAnyProductAtModeration ? 'Cancel Product(s)' :'Cancel Order').'" class="button" style="margin-top:8px;" />
                    </div>';
                echo '
                </div>';
                foreach ($products as $k => $product)
                {
                    if($order_details = OrderHistoryDetails::getOrderDetail($k, 0, true, $cookie->id_lang))
                    {
                        echo '<div class="summary">';
                        foreach($order_details as $state)
                        {
                            echo 'Product with reference number:'.$product['product_reference'].' has the status "'.$state['name'].'"<br/>';

                             if($state['shipping_cost'] > 0)
                                echo '<br/> Shipping cost <span style="color:red">('.Tools::displayPrice($state['shipping_cost'], $currency).')</span> for this order has been refunded back to the customer. <br/>';
                        }
                        echo '</div>';
                    }
                }
                echo '<div style="clear:both; height:15px;">&nbsp;</div></fieldset>';
                    echo'<div style="clear:both; height:15px;">&nbsp;</div></fieldset>
                        <div style="float: left; width: 100%;">
                        <fieldset style="width: 412px;float:left; margin: 0 20px 0 0;">
                        <legend>Manual Refund</legend>';

                    //Show manual refund box If not credit voucher or credit voucher and product count great than 1(credit voucher already cover one product, so not refundable)
                    $manualRefundable = true;
                    if ($order->module == 'cashondelivery' || $order->module == 'freeorder') {
                        $manualRefundable = false;
                    } else {
                        foreach ($order->getDiscounts() as $discount) {
                            $iDiscount = new Discount($discount['id_discount']);
                            if ($iDiscount->id_discount_type == Configuration::get('PS_CREDIT_DISCOUNT_ID_TYPE') && count($products) == 1) {
                                $manualRefundable = false;
                                break;
                            }
                        }
                    }

                    if ($manualRefundable) {
                        echo '<div style="float: left; padding: 20px 0 0;">
                                <input type="text" name="manualRefundAmount" value=" "  id="manualRefundAmount" style=" width: 75px;margin-right: 10px;"/>
                                <input type="submit" name="manualRefund" value="Submit Amount "  id="manualRefund" class="button"/>';
                        if($manual_refund = OrderHistoryDetails::getAllPartialRefundAmounts($order->id, NULL, _PS_OS_MANUALREFUND_)) {
                            echo '<div style="clear:both; height:15px;">&nbsp;</div>
                                <div style="font-weight:bold;color:red;">';
                                    foreach ($manual_refund as $item) {
                                        echo Tools::displayPrice($item, $currency).' has been refunded<br/>';
                                    }
                                echo '</div>
                            </div>';
                        }
                    } else  {
                        echo $this->l('This order not refundable');
                    }

                    echo '</fieldset>
                    <fieldset style="width: 412px;float:left">
                    <legend>Give Credits</legend>
                    <div style="float: left; padding: 20px 0 0;">
                        <input type="text" name="credit_qty" value=" "  id="credit_qty" style=" width: 75px;margin-right: 10px;"/>
                        <input type="submit" name="manualGenerateCredit" value="Submit Credit Quantity"  id="manualGenerateCredit" class="button"/>
                     </div>
                     <div style="clear:both; height:15px;">&nbsp;</div>';
                    if($credit_qty = OrderHistoryDetails::returnOrderStatus($order->id, _PS_OS_CREDITSGIVEN_, 'Mcr'))
                         echo '<div style="font-weight:bold;color:red;">Manually the customer has been given back '.$credit_qty.' '.($credit_qty > 0 ? 'credits' : 'credit').'</div>';
                     echo '</div>';

            echo '
        </fieldset>
        </form>
        <div class="clear" style="height:20px;">&nbsp;</div>';

        /* Display send a message to customer & returns/credit slip*/
        $returns = OrderReturn::getOrdersReturn($order->id_customer, $order->id);
        $slips = OrderSlip::getOrdersSlip($order->id_customer, $order->id);
        echo '
        <div style="float: left">
            <form action="'.$_SERVER['REQUEST_URI'].'&token='.$this->token.'" method="post" onsubmit="if (getE(\'visibility\').checked == true) return confirm(\''.$this->l('Do you want to send this message to the customer?', __CLASS__, true, false).'\');">
            <fieldset style="width: 400px;">
                <legend style="cursor: pointer;" onclick="$(\'#message\').slideToggle();$(\'#message_m\').slideToggle();return false"><img src="../img/admin/email_edit.gif" /> '.$this->l('New message').'</legend>
                <div id="message_m" style="display: '.(Tools::getValue('message') ? 'none' : 'block').'; overflow: auto; width: 400px;">
                    <a href="#" onclick="$(\'#message\').slideToggle();$(\'#message_m\').slideToggle();return false"><b>'.$this->l('Click here').'</b> '.$this->l('to add a comment or send a message to the customer').'</a>
                </div>
                <div id="message" style="display: '.(Tools::getValue('message') ? 'block' : 'none').'">
                    <select name="order_message" id="order_message" onchange="orderOverwriteMessage(this, \''.$this->l('Do you want to overwrite your existing message?').'\')">
                        <option value="0" selected="selected">-- '.$this->l('Choose a standard message').' --</option>';
        $orderMessages = OrderMessage::getOrderMessages((int)($order->id_lang));
        foreach ($orderMessages AS $orderMessage)
            echo '      <option value="'.htmlentities($orderMessage['message'], ENT_COMPAT, 'UTF-8').'">'.$orderMessage['name'].'</option>';
        echo '      </select><br /><br />
                    <b>'.$this->l('Display to consumer?').'</b>
                    <input type="radio" name="visibility" id="visibility" value="0" /> '.$this->l('Yes').'
                    <input type="radio" name="visibility" value="1" checked="checked" /> '.$this->l('No').'
                    <p id="nbchars" style="display:inline;font-size:10px;color:#666;"></p><br /><br />
                    <textarea id="txt_msg" name="message" cols="50" rows="8" onKeyUp="var length = document.getElementById(\'txt_msg\').value.length; if (length > 600) length = \'600+\'; document.getElementById(\'nbchars\').innerHTML = \''.$this->l('600 chars max').' (\' + length + \')\';">'.htmlentities(Tools::getValue('message'), ENT_COMPAT, 'UTF-8').'</textarea><br /><br />
                    <input type="hidden" name="id_order" value="'.(int)($order->id).'" />
                    <input type="hidden" name="id_customer" value="'.(int)($order->id_customer).'" />
                    <input type="submit" class="button" name="submitMessage" value="'.$this->l('Send').'" />
                </div>
            </fieldset>
            </form>';
        /* Display list of messages */
        if (sizeof($messages))
        {
            echo '
            <br />
            <fieldset style="width: 400px;">
            <legend><img src="../img/admin/email.gif" /> '.$this->l('Messages').'</legend>';
            foreach ($messages as $message)
            {
                echo '<div style="overflow:auto; width:400px;" '.($message['is_new_for_me'] ?'class="new_message"':'').'>';
                if ($message['is_new_for_me'])
                    echo '<a class="new_message" title="'.$this->l('Mark this message as \'viewed\'').'" href="'.$_SERVER['REQUEST_URI'].'&token='.$this->token.'&messageReaded='.(int)($message['id_message']).'"><img src="../img/admin/enabled.gif" alt="" /></a>';
                echo $this->l('At').' <i>'.Tools::displayDate($message['date_add'], (int)($cookie->id_lang), true);
                echo '</i> '.$this->l('from').' <b>'.(($message['elastname']) ? ($message['efirstname'].' '.$message['elastname']) : ($message['cfirstname'].' '.$message['clastname'])).'</b>';
                echo ((int)($message['private']) == 1 ? '<span style="color:red; font-weight:bold;">'.$this->l('Private:').'</span>' : '');
                echo '<p>'.nl2br2($message['message']).'</p>';
                echo '</div>';
                echo '<br />';
            }
            echo '<p class="info">'.$this->l('When you read a message, please click on the green check.').'</p>';
            echo '</fieldset>';
        }
        echo '</div>';

        /* Display return product */
        echo '<div style="float: left; margin-left: 40px">
            <fieldset style="width: 400px;">
                <legend><img src="../img/admin/return.gif" alt="'.$this->l('Merchandise returns').'" />'.$this->l('Merchandise returns').'</legend>';
        if (!sizeof($returns))
            echo $this->l('No merchandise return for this order.');
        else
            foreach ($returns as $return)
            {
                $state = new OrderReturnState($return['state']);
                echo '('.Tools::displayDate($return['date_upd'], $cookie->id_lang).') :
                <b><a href="index.php?tab=AdminReturn&id_order_return='.$return['id_order_return'].'&updateorder_return&token='.Tools::getAdminToken('AdminReturn'.(int)(Tab::getIdFromClassName('AdminReturn')).(int)($cookie->id_employee)).'">'.$this->l('#').sprintf('%06d', $return['id_order_return']).'</a></b> -
                '.$state->name[$cookie->id_lang].'<br />';
            }
        echo '</fieldset>';

        /* Display credit slip */
        echo '
                <br />
                <fieldset style="width: 400px;">
                    <legend><img src="../img/admin/slip.gif" alt="'.$this->l('Credit slip').'" />'.$this->l('Credit slip').'</legend>';
        if (!sizeof($slips))
            echo $this->l('No slip for this order.');
        else
            foreach ($slips as $slip)
                echo '('.Tools::displayDate($slip['date_upd'], $cookie->id_lang).') : <b><a href="pdf.php?id_order_slip='.$slip['id_order_slip'].'">'.$this->l('#').sprintf('%06d', $slip['id_order_slip']).'</a></b><br />';
        echo '</fieldset>
        </div>';
        echo '<div class="clear">&nbsp;</div>';
        echo '<br /><br /><a href="'.$currentIndex.'&token='.$this->token.'"><img src="../img/admin/arrow2.gif" /> '.$this->l('Back to list').'</a><br />';
    }

    public function displayAddressDetail($addressDelivery)
    {
        // Allow to add specific rules
        $patternRules = array(
            'avoid' => array()
            //'avoid' => array('address2')
        );
        return AddressFormat::generateAddress($addressDelivery, $patternRules, '<br />');
    }

    public function display()
    {
        global $cookie;

        if (isset($_GET['view'.$this->table]))
            $this->viewDetails();
        else
        {
            $this->getList((int)($cookie->id_lang), !Tools::getValue($this->table.'Orderby') ? 'date_add' : NULL, !Tools::getValue($this->table.'Orderway') ? 'DESC' : NULL);
            $currency = new Currency((int)(Configuration::get('PS_CURRENCY_DEFAULT')));
            $this->displayList();
            echo '<h2 class="space" style="text-align:right; margin-right:44px;">'.$this->l('Total:').' '.Tools::displayPrice($this->getTotal(), $currency).'</h2>';
        }
    }

    public function displayList($token = NULL)
    {
        global $currentIndex;

        /* Display list header (filtering, pagination and column names) */
        $this->displayListHeader($token);

        global $cookie;
        $id_order = intval(Tools::getValue('id_order'));
        $order = new Order($id_order);
        $currentStateTab = $order->getCurrentStateFull($cookie->id_lang);
        $states = OrderState::getOrderStates(intval($cookie->id_lang));
        $order_state = array(
            _PS_OS_PREPARATION_,
            _PS_OS_SHIPPING_,
            _PS_OS_DELIVERED_,
            _PS_OS_PROCESSING_,
            _PS_OS_PROCESSED_
        );

        echo '<select name="id_order_state">';
            foreach ($states AS $state) {
                if (in_array($state['id_order_state'], $order_state)) {
                    echo '<option value="' . $state['id_order_state'] . '"' . (($state['id_order_state'] == $currentStateTab['id_order_state']) ? ' selected="selected"' : '') . '>' . stripslashes($state['name']) . '</option>';
                }
            }
        echo '</select>
              <input type="submit" name="submitStateForMultipleOrders" value="Change" class="button" />';


        /* Show the content of the table */
        $this->displayListContent($token);

        /* Close list table and submit button */
        $this->displayListFooter($token);
    }

    private function getTotal()
    {
        $total = 0;
        foreach($this->_list AS $item)
            if ($item['id_currency'] == Configuration::get('PS_CURRENCY_DEFAULT'))
                $total += (float)($item['total_paid']);
            else
            {
                $currency = new Currency((int)($item['id_currency']));
                $total += Tools::ps_round((float)($item['total_paid']) / (float)($currency->conversion_rate), 2);
            }
        return $total;
    }
}

