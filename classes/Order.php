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
*  @version  Release: $Revision: 7630 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class OrderCore extends ObjectModel
{
    /** @var integer Delivery address id */
    public      $id_address_delivery;

    /** @var integer Invoice address id */
    public      $id_address_invoice;

    /** @var integer Cart id */
    public      $id_cart;

    /** @var integer Currency id */
    public      $id_currency;

    /** @var integer Language id */
    public      $id_lang;

    /** @var integer Customer id */
    public      $id_customer;

    /** @var integer Carrier id */
    public      $id_carrier;

    /** @var string Secure key */
    public      $secure_key;

    /** @var string Payment method id */
    public      $payment;

    /** @var string Payment module */
    public      $module;

    /** @var float Currency conversion rate */
    public      $conversion_rate;

    /** @var boolean Customer is ok for a recyclable package */
    public      $recyclable = 1;

    /** @var boolean True if the customer wants a gift wrapping */
    public      $gift = 0;

    /** @var string Gift message if specified */
    public      $gift_message;

    /** @var string Shipping number */
    public      $shipping_number;

    /** @var float Discounts total */
    public      $total_discounts;

    /** @var float Total to pay */
    public      $total_paid;

    /** @var float Total really paid */
    public      $total_paid_real;

    /** @var float interest to paid if installments chosen by  customer*/
    public      $installment_interest;

    /** @var int number of installments chosen by  customer*/
    public      $installment_count;

    /** @var float each installment amount */
    public      $installment_amount;

    /** @var float Products total */
    public      $total_products;

    /** @var float Products total tax excluded */
    public      $total_products_wt;

    /** @var float Shipping total */
    public      $total_shipping;

    /** @var float initial amount of total products with tax and shipping */
    public 		$initial_product_shipping;

    /** @var float Shipping tax rate */
    public      $carrier_tax_rate;

    /** @var float Wrapping total */
    public      $total_wrapping;

    /** @var integer Invoice number */
    public      $invoice_number;

    /** @var integer Delivery number */
    public      $delivery_number;

    /** @var string Invoice creation date */
    public      $invoice_date;

    /** @var string Delivery creation date */
    public      $delivery_date;

    /** @var boolean Order validity (paid and not canceled) */
    public      $valid;

    /** @var string Object creation date */
    public      $date_add;

    /** @var string Object last modification date */
    public      $date_upd;

    /** @var string Object tracking_number that come from cargo componies */
    public     $tracking_number;

    protected $tables = array ('orders');

    protected   $fieldsRequired = array('conversion_rate', 'id_address_delivery', 'id_address_invoice', 'id_cart', 'id_currency', 'id_lang', 'id_customer', 'id_carrier', 'payment', 'total_paid', 'total_paid_real', 'total_products', 'total_products_wt');
    protected   $fieldsValidate = array(
        'id_address_delivery' => 'isUnsignedId',
        'id_address_invoice' => 'isUnsignedId',
        'id_cart' => 'isUnsignedId',
        'id_currency' => 'isUnsignedId',
        'id_lang' => 'isUnsignedId',
        'id_customer' => 'isUnsignedId',
        'id_carrier' => 'isUnsignedId',
        'secure_key' => 'isMd5',
        'payment' => 'isGenericName',
        'recyclable' => 'isBool',
        'gift' => 'isBool',
        'gift_message' => 'isMessage',
        'total_discounts' => 'isPrice',
        'total_paid' => 'isPrice',
        'total_paid_real' => 'isPrice',
        'total_products' => 'isPrice',
        'total_products_wt' => 'isPrice',
        'total_shipping' => 'isPrice',
        'initial_product_shipping' => 'isPrice',
        'carrier_tax_rate' => 'isFloat',
        'total_wrapping' => 'isPrice',
        'shipping_number' => 'isUrl',
        'conversion_rate' => 'isFloat'
    );

    protected   $webserviceParameters = array(
        'objectMethods' => array('add' => 'addWs'),
        'objectNodeName' => 'order',
        'objectsNodeName' => 'orders',
        'fields' => array(
            'id_address_delivery' => array('xlink_resource'=> 'addresses'),
            'id_address_invoice' => array('xlink_resource'=> 'addresses'),
            'id_cart' => array('xlink_resource'=> 'carts'),
            'id_currency' => array('xlink_resource'=> 'currencies'),
            'id_lang' => array('xlink_resource'=> 'languages'),
            'id_customer' => array('xlink_resource'=> 'customers'),
            'id_carrier' => array('xlink_resource'=> 'carriers'),
            'module' => array('required' => true),
            'invoice_number' => array(),
            'invoice_date' => array(),
            'delivery_number' => array(),
            'delivery_date' => array(),
            'valid' => array(),
            'current_state' => array('getter' => 'getCurrentState', 'setter' => 'setCurrentState', 'xlink_resource'=> 'order_states'),
            'date_add' => array(),
            'date_upd' => array(),
        ),
        'associations' => array(
            'order_rows' => array('resource' => 'order_row', 'setter' => false, 'virtual_entity' => true,
                'fields' => array(
                    'id' =>  array(),
                    'product_id' => array('required' => true),
                    'product_attribute_id' => array('required' => true),
                    'product_quantity' => array('required' => true),
                    'product_name' => array('setter' => false),
                    'product_price' => array('setter' => false),
            )),
        ),

    );

    /* MySQL does not allow 'order' for a table name */
    protected   $table = 'orders';
    protected   $identifier = 'id_order';
    protected       $_taxCalculationMethod = PS_TAX_EXC;

    protected static $_historyCache = array();

    public function getFields()
    {
        parent::validateFields();

        $fields['id_address_delivery'] = (int)($this->id_address_delivery);
        $fields['id_address_invoice'] = (int)($this->id_address_invoice);
        $fields['id_cart'] = (int)($this->id_cart);
        $fields['id_currency'] = (int)($this->id_currency);
        $fields['id_lang'] = (int)($this->id_lang);
        $fields['id_customer'] = (int)($this->id_customer);
        $fields['id_carrier'] = (int)($this->id_carrier);
        $fields['secure_key'] = pSQL($this->secure_key);
        $fields['payment'] = pSQL($this->payment);
        $fields['module'] = pSQL($this->module);
        $fields['conversion_rate'] = (float)($this->conversion_rate);
        $fields['recyclable'] = (int)($this->recyclable);
        $fields['gift'] = (int)($this->gift);
        $fields['gift_message'] = pSQL($this->gift_message);
        $fields['shipping_number'] = pSQL($this->shipping_number);
        $fields['total_discounts'] = (float)($this->total_discounts);
        $fields['total_paid'] = (float)($this->total_paid);
        $fields['total_paid_real'] = (float)($this->total_paid_real);
        $fields['installment_interest'] = floatval($this->installment_interest);
        $fields['installment_count'] = intval($this->installment_count);
        $fields['installment_amount'] = floatval($this->installment_amount);
        $fields['total_products'] = (float)($this->total_products);
        $fields['total_products_wt'] = (float)($this->total_products_wt);
        $fields['total_shipping'] = (float)($this->total_shipping);
        $fields['initial_product_shipping'] = floatval($this->initial_product_shipping);
        $fields['carrier_tax_rate'] = (float)($this->carrier_tax_rate);
        $fields['total_wrapping'] = (float)($this->total_wrapping);
        $fields['invoice_number'] = $this->invoice_number;
        $fields['delivery_number'] = $this->delivery_number;
        $fields['invoice_date'] = pSQL($this->invoice_date);
        $fields['delivery_date'] = pSQL($this->delivery_date);
        $fields['valid'] = (int)($this->valid) ? 1 : 0;
        $fields['date_add'] = pSQL($this->date_add);
        $fields['date_upd'] = pSQL($this->date_upd);
        $fields['tracking_number'] = pSQL($this->tracking_number);

        return $fields;
    }

    public function __construct($id = NULL, $id_lang = NULL)
    {
        parent::__construct($id, $id_lang);
        if ($this->id_customer)
        {
            $customer = new Customer((int)($this->id_customer));
            $this->_taxCalculationMethod = Group::getPriceDisplayMethod((int)($customer->id_default_group));
        }
        else
            $this->_taxCalculationMethod = Group::getDefaultPriceDisplayMethod();
    }

    public function getTaxCalculationMethod()
    {
        return (int)($this->_taxCalculationMethod);
    }

    /* Does NOT delete a product but "cancel" it (which means return/refund/delete it depending of the case) */
    public function deleteProduct($order, $orderDetail, $quantity)
    {
        if (! (int)($this->getCurrentState())) {
            return false;
        }

        if ($this->hasBeenDelivered()) {
            if (! Configuration::get('PS_ORDER_RETURN')) {
                die(Tools::displayError('Error: 201306051501'));
            }

            $orderDetail->product_quantity_return += (int)($quantity);

            return $orderDetail->update();
        } else if ($this->hasBeenPaid()) {
            $orderDetail->product_quantity_refunded += (int)($quantity);

            return $orderDetail->update();
        }

        return $this->_deleteProduct($orderDetail, (int)($quantity));
    }


    /* DOES delete the product */
    protected function _deleteProduct($orderDetail, $quantity)
    {
        $price = $orderDetail->product_price * (1 + $orderDetail->tax_rate * 0.01);
        if ($orderDetail->reduction_percent != 0.00)
            $reduction_amount = $price * $orderDetail->reduction_percent / 100;
        elseif ($orderDetail->reduction_amount != '0.000000')
            $reduction_amount = Tools::ps_round($orderDetail->reduction_amount, 2);
        if (isset($reduction_amount) AND $reduction_amount)
            $price = Tools::ps_round($price - $reduction_amount, 2);
        $productPriceWithoutTax = number_format($price / (1 + $orderDetail->tax_rate * 0.01), 2, '.', '');
        $price += Tools::ps_round($orderDetail->ecotax * (1 + $orderDetail->ecotax_tax_rate / 100), 2);
        $productPrice = number_format($quantity * $price, 2, '.', '');
        /* Update cart */
        $cart = new Cart($this->id_cart);
        $cart->updateQty($quantity, $orderDetail->product_id, $orderDetail->product_attribute_id, false, 'down'); // customization are deleted in deleteCustomization
        $cart->update();

        /* Update order */
        $shippingDiff = $this->total_shipping - $cart->getOrderShippingCost();
        $this->total_products -= $productPriceWithoutTax;

        // After upgrading from old version
        // total_products_wt is null
        // removing a product made order total negative
        // and don't recalculating totals (on getTotalProductsWithTaxes)
        if ($this->total_products_wt != 0)
            $this->total_products_wt -= $productPrice;
        $this->total_shipping = $cart->getOrderShippingCost();

        /* It's temporary fix for 1.3 version... */
        //if ($orderDetail->product_quantity_discount != '0.000000')
            $this->total_paid -= ($productPrice + $shippingDiff);
        //else
            //$this->total_paid = $cart->getOrderTotal();

        $this->total_paid_real -= ($productPrice + $shippingDiff);
        /* Prevent from floating precision issues (total_products has only 2 decimals) */
        if ($this->total_products < 0)
            $this->total_products = 0;

        if ($this->total_paid < 0)
            $this->total_paid = 0;

        if ($this->total_paid_real < 0)
            $this->total_paid_real = 0;

        /* Prevent from floating precision issues */
        $this->total_paid = number_format($this->total_paid, 2, '.', '');
        $this->total_paid_real = number_format($this->total_paid_real, 2, '.', '');
        $this->total_products = number_format($this->total_products, 2, '.', '');
        $this->total_products_wt = number_format($this->total_products_wt, 2, '.', '');

        /* Update order detail */
        $orderDetail->product_quantity -= (int)($quantity);

        /*if (!$orderDetail->product_quantity)
        {
            if (!$orderDetail->delete())
                return false;
            if (count($this->getProductsDetail()) == 0)
            {
                $history = new OrderHistory();
                $history->id_order = (int)($this->id);
                $history->changeIdOrderState(Configuration::get('PS_OS_CANCELED'), (int)($this->id));
                if (!$history->addWithemail())
                    return false;
            }
            return $this->update();
        }*/
        return $orderDetail->update() AND $this->update();
    }

    /**
     * Disregarding shipping flag as we let indefinite refund and exchange..
     */
    public function customDeleteProduct($order, $orderDetail, $quantity = 0, $shipping, $state = 0) {
        $log = true;

        if (in_array($state, array(_PS_OS_CREDITED_, _PS_OS_EXCHANGE_))) {
            $orderDetail->product_quantity -= (int)($quantity);
        } else {
            $totalProductPriceWithTax = 0;

             // Update order detail
            $orderDetail->product_quantity -= (int)($quantity);
            $orderDetail->save();

            // workaround for getting all calculated data..
            $orderCalculations = Tools::generateXml($order, true);

            // Update cart
            $cart = new Cart($this->id_cart);
            $cart->updateQty($quantity, $orderDetail->product_id, $orderDetail->product_attribute_id, false, 'down'); // customization are deleted in deleteCustomization
            $cart->update();

            if ($state == _PS_OS_REFUND_) {
                $orderDetail->product_quantity_refunded = (int) ($quantity);
            } else if ($state == _PS_OS_EXCHANGE_) {
                $orderDetail->product_quantity_exchanged = (int) ($quantity);
            }

            foreach ($orderCalculations['products'] as $pid => $values) {
                $totalProductPriceWithTax += $values['FINAL_PRICE'];
            }

            $this->total_paid = $orderCalculations['paymentValue'];
            $this->total_paid_real = $orderCalculations['paymentValue'];
            $this->total_products = $orderCalculations['totalProductPriceWithoutTax'];
            $this->total_products_wt = $totalProductPriceWithTax;
            $this->total_shipping = $orderCalculations['shippingFinalPrice'];
            $this->total_discounts = $orderCalculations['totalDiscount'];
        }

        return $orderDetail->update() AND $this->update();
    }



    public function deleteCustomization($id_customization, $quantity, $orderDetail)
    {
        if (!(int)($this->getCurrentState()))
            return false;

        if ($this->hasBeenDelivered())
            return Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'customization` SET `quantity_returned` = `quantity_returned` + '.(int)($quantity).' WHERE `id_customization` = '.(int)($id_customization).' AND `id_cart` = '.(int)($this->id_cart).' AND `id_product` = '.(int)($orderDetail->product_id));
        elseif ($this->hasBeenPaid())
            return Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'customization` SET `quantity_refunded` = `quantity_refunded` + '.(int)($quantity).' WHERE `id_customization` = '.(int)($id_customization).' AND `id_cart` = '.(int)($this->id_cart).' AND `id_product` = '.(int)($orderDetail->product_id));
        if (!Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'customization` SET `quantity` = `quantity` - '.(int)($quantity).' WHERE `id_customization` = '.(int)($id_customization).' AND `id_cart` = '.(int)($this->id_cart).' AND `id_product` = '.(int)($orderDetail->product_id)))
            return false;
        if (!Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'customization` WHERE `quantity` = 0'))
            return false;
        return $this->_deleteProduct($orderDetail, (int)($quantity));
    }

    /**
     * Get order history
     *
     * @param integer $id_lang Language id
     *
     * @return array History entries ordered by date DESC
     */
    public function getHistory($id_lang, $id_order_state = false, $no_hidden = false) {
        if (! $id_order_state) {
            $id_order_state = 0;
        }

        if (! isset(self::$_historyCache[$id_order_state]) OR $no_hidden) {
            $id_lang = $id_lang ? (int)($id_lang) : 'o.`id_lang`';
            $result = Db::getInstance()->ExecuteS('
                SELECT oh.*, e.`firstname` AS employee_firstname, e.`lastname` AS employee_lastname, osl.`name` AS ostate_name
                FROM `' . _DB_PREFIX_ . 'orders` o
                LEFT JOIN `' . _DB_PREFIX_ . 'order_history` oh ON o.`id_order` = oh.`id_order`
                LEFT JOIN `' . _DB_PREFIX_ . 'order_state` os ON os.`id_order_state` = oh.`id_order_state`
                LEFT JOIN `' . _DB_PREFIX_ . 'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state` AND osl.`id_lang` = ' . (int)($id_lang) . ')
                LEFT JOIN `' . _DB_PREFIX_ . 'employee` e ON e.`id_employee` = oh.`id_employee`
                WHERE oh.id_order = ' . (int)($this->id) . '
                ' . ($no_hidden ? ' AND os.hidden = 0' : '') . '
                ' . ((int)($id_order_state) ? ' AND oh.`id_order_state` = ' . (int)($id_order_state) : '') . '
                ORDER BY oh.date_add DESC, oh.id_order_history DESC');

            if ($no_hidden) {
                return $result;
            }

            self::$_historyCache[$id_order_state] = $result;
        }

        return self::$_historyCache[$id_order_state];
    }

    public function getProductsDetail() {
        return Db::getInstance()->ExecuteS('
            SELECT od.*, pl.link_rewrite, pl.id_product, IF(pa.default_image,pa.default_image,pi.id_image) AS id_image
            FROM `' . _DB_PREFIX_ . 'order_detail` od
            LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (od.`product_id` = pl.`id_product`)
            LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute` pa ON (od.`product_attribute_id` = pa.`id_product_attribute`)
            LEFT JOIN `' . _DB_PREFIX_ . 'image` pi ON (od.`product_id` = pi.`id_product`  AND IF(pa.default_image,\' \',pi.`cover` = 1))
            WHERE od.`id_order` = ' . intval($this->id) . ' AND pl.`id_lang` = 1');
    }


    /**
     * @return string
     * @deprecated
     */
    public function getLastMessage()
    {
        Tools::displayAsDeprecated();
        $sql = 'SELECT `message` FROM `'._DB_PREFIX_.'message` WHERE `id_order` = '.(int)($this->id).' ORDER BY `id_message` desc';
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
        return $result['message'];
    }

    public function getFirstMessage()
    {
        $sql = 'SELECT `message` FROM `'._DB_PREFIX_.'message` WHERE `id_order` = '.(int)($this->id).' ORDER BY `id_message` asc';
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
        return $result['message'];
    }

    public function setProductPrices(&$row)
    {
        if ($this->_taxCalculationMethod == PS_TAX_EXC)
            $row['product_price'] = Tools::ps_round($row['product_price'], 2);
        else
            $row['product_price_wt'] = Tools::ps_round($row['product_price'] * (1 + $row['tax_rate'] / 100), 2);

        $group_reduction = 1;
        if ($row['group_reduction'] > 0)
            $group_reduction =  $row['group_reduction'] / 100;

        if ($row['reduction_percent'])
        {
            if ($this->_taxCalculationMethod == PS_TAX_EXC)
                $row['product_price'] = $row['product_price'] - $row['product_price'] * ($row['reduction_percent'] * 0.01 * $group_reduction);
            else
                $row['product_price_wt'] = Tools::ps_round($row['product_price_wt'] - $row['product_price_wt'] * ($row['reduction_percent'] * 0.01 * $group_reduction), 2);
        }

        if ($row['reduction_amount'])
        {
            if ($this->_taxCalculationMethod == PS_TAX_EXC)
                $row['product_price'] = $row['product_price'] - ($row['reduction_amount'] / (1 + $row['tax_rate'] / 100))  * $group_reduction;
            else
                $row['product_price_wt'] = Tools::ps_round($row['product_price_wt'] - $row['reduction_amount'] * $group_reduction, 2);
        }

        if (
($row['reduction_percent'] OR $row['reduction_amount'] OR $row['group_reduction']) AND $this->_taxCalculationMethod == PS_TAX_EXC)
            $row['product_price'] = Tools::ps_round($row['product_price'], 2);

        if ($this->_taxCalculationMethod == PS_TAX_EXC)
            $row['product_price_wt'] = Tools::ps_round($row['product_price'] * (1 + ($row['tax_rate'] * 0.01)), 2) + Tools::ps_round($row['ecotax'] * (1 + $row['ecotax_tax_rate'] / 100), 2);
        else
        {
            $row['product_price_wt_but_ecotax'] = $row['product_price_wt'];
            $row['product_price_wt'] = Tools::ps_round($row['product_price_wt'] + $row['ecotax'] * (1 + $row['ecotax_tax_rate'] / 100), 2);
        }

        $row['total_wt'] = $row['product_quantity'] * $row['product_price_wt'];
        $row['total_price'] = $row['product_quantity'] * $row['product_price'];
    }

    /**
     * Get order products
     *
     * @return array Products with price, quantity (with taxe and without)
     */
    public function getProducts($products = false, $selectedProducts = false, $selectedQty = false)
    {
        global $cookie;
        if (!$products)
            $products = $this->getProductsDetail();
        $resultArray = array();

        $credit_aplied_to = array();
        $discount_ids = array();
        $credit_qty = 0;
        $discounts = $this->getDiscounts(true);
        $num_of_order_detail = sizeof($products);

        if($discounts)
        {
            foreach($discounts as $discount)
            {
                //print_r($credit_discount);
                if($discount['id_discount_type'] == _PS_OS_CREDIT_ID_TYPE_)
                {
                    $credit_discount[] = $discount;
                }
            }
        }

        if(isset($credit_discount))
        {
            $credit_qty = sizeof($credit_discount);
            foreach($credit_discount as $discount)
            {
                foreach ($products AS $row)
                {
                    $this->setProductPrices($row);
                    if($discount['id_discount_type'] == _PS_OS_CREDIT_ID_TYPE_)
                    {
                        if($row['product_price_wt'] == $discount['value'])
                        {
                            if(!in_array($discount['id_discount'] ,$discount_ids ) && !in_array($row['id_order_detail'] ,$credit_aplied_to ))
                            {
                                    $credit_aplied_to[$row['id_order_detail']] = $row['id_order_detail'];
                                    $discount_ids[] = $discount['id_discount'];
                            }
                        }
                    }
                }

            }
        }

        foreach ($products AS $row)
        {
            // Change qty if selected
            if ($selectedQty)
            {
                $row['product_quantity'] = 0;
                foreach ($selectedProducts AS $key => $id_product)
                    if ($row['id_order_detail'] == $id_product)
                        $row['product_quantity'] = (int)($selectedQty[$key]);
                if (!$row['product_quantity'])
                    continue ;
            }
            $this->setProductPrices($row);
            if(isset($credit_aplied_to[$row['id_order_detail']]))
            {
                    $row['credit_discount_'.$row['id_order_detail']] = $row['id_order_detail'];
                    if($credit_qty == sizeof($credit_aplied_to))
                        $row['credit_quantity_'.$credit_aplied_to[$row['id_order_detail']]] = 1;
                    elseif(sizeof($credit_aplied_to) == 1)
                        $row['credit_quantity_'.$credit_aplied_to[$row['id_order_detail']]] = $credit_qty;
                    elseif($credit_qty != sizeof($credit_aplied_to))
                    {
                        if($row['product_quantity'] > 1)
                        {
                            $row['credit_quantity_'.$credit_aplied_to[$row['id_order_detail']]] = 1 + ($credit_qty - sizeof($credit_aplied_to));
                        }
                        else
                        {
                            $row['credit_quantity_'.$credit_aplied_to[$row['id_order_detail']]] = 1;
                        }
                    }
            }

            /* Add information for virtual product */
            if ($row['download_hash'] AND !empty($row['download_hash']))
                $row['filename'] = ProductDownload::getFilenameFromIdProduct($row['product_id']);
            /*$row['id_image'] = Product::defineProductImage($row , $cookie->id_lang);*/
            $row['id_image'] = $this->getImageOrder($row);
            /* Stock product */
            $resultArray[(int)($row['id_order_detail'])] = $row;
        }

        return $resultArray;
    }

    public function getImageOrder($row)
    {
        if($multiple_color = $this->getMultipleColorProduct($row['product_id']))
        {
            $result2 = Db::getInstance()->ExecuteS('SELECT pa2.`id_product_attribute`,pa2.`default_image`,pac2.`id_attribute`
                        FROM `'._DB_PREFIX_.'product_attribute` pa2
                        LEFT JOIN `'._DB_PREFIX_.'product_attribute_combination` pac2 ON (pac2.`id_product_attribute` = pa2.`id_product_attribute`)
                        LEFT JOIN `'._DB_PREFIX_.'attribute` a2 ON (pac2.`id_attribute`= a2.`id_attribute` )
                        WHERE pa2.`id_product` = '.$row['product_id'].' AND  a2.`id_attribute_group` = 2');
            $test = array();
            foreach($result2 as $res)
            {
                $test[$res['id_attribute']][]= $res['id_product_attribute'];
                if($res['default_image'] != 0)
                $test[$res['id_attribute']]['default_image']=$res['default_image'];
            }

            foreach($test as $key => $t)
            {
                if(in_array($row['product_attribute_id'],$t))
                {
                    $id_image=$t['default_image'];
                }
            }
        }
        else
            unset($test);

        $row2 = Db::getInstance()->getRow('
        SELECT i.`id_image` as id_image, il.`legend`
        FROM `'._DB_PREFIX_.'image` i
        LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = '.(int)$this->id_lang.')
        WHERE '.((isset($test))
            ? 'i.`id_image` = '.($id_image?$id_image:0)
            : 'i.`id_product` = '.(int)$row['product_id'].' AND i.`cover` = 1').'
        ');
        $order_image = $row['product_id']."-".$row2['id_image'];
        return $order_image;
    }

    public function getMultipleColorProduct($id_product)
    {
        $sql_product = Db::getInstance()->getRow('
                SELECT p.`id_product`
                FROM `'._DB_PREFIX_.'product` p
                LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa ON (pa.`id_product` = p.`id_product`)
                    WHERE p.`id_product` = '.(int)$id_product.'  AND pa.`default_image` > 0');
        if($sql_product['id_product'])
            return true;
        else
            return false;

    }




    public function getTaxesAverageUsed()
    {
        return Cart::getTaxesAverageUsed((int)($this->id_cart));
    }

    /**
     * Count virtual products in order
     *
     * @return int number of virtual products
     */
    public function getVirtualProducts()
    {
        $sql = '
            SELECT `product_id`, `download_hash`, `download_deadline`
            FROM `'._DB_PREFIX_.'order_detail` od
            WHERE od.`id_order` = '.(int)($this->id).'
                AND `download_hash` <> \'\'';
        return Db::getInstance()->ExecuteS($sql);
    }

    /**
    * Check if order contains (only) virtual products
    * @return boolean true if is a virtual order or false
    *
    */
    public function isVirtual($strict = true)
    {
        $products = $this->getProducts();
        if (count($products) < 1)
            return false;
        $virtual = false;

        foreach ($products AS $product) {
            $pd = ProductDownload::getIdFromIdProduct((int)($product['product_id']));

            if ($pd AND Validate::isUnsignedInt($pd) AND $product['download_hash']) {
                if ($strict === false) {
                    return true;
                }

                $virtual &= true;
            }
        }

        return $virtual;
    }

    /**
     * Get order discounts
     *
     * @return array Discounts with price and quantity
     */
    public function getDiscounts($details = false, $forXML = false) {
        $sql = '
            SELECT *
            FROM `' . _DB_PREFIX_ . 'order_discount` od '
            . ($details ? 'LEFT JOIN `' . _DB_PREFIX_ . 'discount` d ON (d.`id_discount` = od.`id_discount`) ' : '')
            . 'WHERE od.`id_order` = ' . (int) ($this->id);

        if ($forXML) {
            $sql = "
                SELECT d.`name`,
                    dl.`description`,
                    d.`id_discount_type`,
                    dtl.`name` AS 'discountTypeName',
                    od.`value`,
                    IF(
                        d.`id_discount_type` = 1,
                        CONCAT('%', d.`value`),
                        CONCAT_WS(' ', d.`value`, 'TL')
                    ) AS 'valueTextNotation',
                    d.`origin`
                FROM `bu_order_discount` od
                JOIN `bu_discount` d ON d.`id_discount` = od.`id_discount`
                JOIN `bu_discount_lang` dl ON dl.`id_discount` = od.`id_discount` AND dl.`id_lang` = 4
                JOIN `bu_discount_type_lang` dtl ON dtl.`id_discount_type` = d.`id_discount_type` AND dtl.`id_lang` = 4
                WHERE od.`id_order` = " . (int) $this->id;
        }

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql);
    }

    public static function getDiscountsCustomer($id_customer, $id_discount) {
        return Db::getInstance()->getValue('
            SELECT COUNT(*) FROM `'._DB_PREFIX_.'orders` o
            LEFT JOIN '._DB_PREFIX_.'order_discount od ON (od.id_order = o.id_order)
            WHERE o.id_customer = '.(int)($id_customer).'
            AND od.id_discount = '.(int)($id_discount));
    }

    /**
     * Get current order state (eg. Awaiting payment, Delivered...)
     *
     * @return array Order state details
     */
    public function getCurrentState()
    {
        $orderHistory = OrderHistory::getLastOrderState($this->id);
        if (!isset($orderHistory) OR !$orderHistory)
            return false;
        return $orderHistory->id;
    }

    /**
     * Get current order state name (eg. Awaiting payment, Delivered...)
     *
     * @return array Order state details
     */
    public function getCurrentStateFull($id_lang)
    {
        return Db::getInstance()->getRow('
        SELECT oh.`id_order_state`, osl.`name`, os.`logable`
        FROM `'._DB_PREFIX_.'order_history` oh
        LEFT JOIN `'._DB_PREFIX_.'order_state_lang` osl ON (osl.`id_order_state` = oh.`id_order_state`)
        LEFT JOIN `'._DB_PREFIX_.'order_state` os ON (os.`id_order_state` = oh.`id_order_state`)
        WHERE osl.`id_lang` = '.(int)($id_lang).' AND oh.`id_order` = '.(int)($this->id).'
        ORDER BY `date_add` DESC, `id_order_history` DESC');
    }

    /**
     * @deprecated
     */
    public function isLogable()
    {
        Tools::displayAsDeprecated();
        return $this->valid;
    }

    public function hasBeenDelivered()
    {
        return sizeof($this->getHistory((int)($this->id_lang), Configuration::get('PS_OS_DELIVERED')));
    }

    public function hasBeenPaid()
    {
        return sizeof($this->getHistory((int)($this->id_lang), Configuration::get('PS_OS_PAYMENT')));
    }

    public function hasBeenShipped()
    {
        return sizeof($this->getHistory((int)($this->id_lang), Configuration::get('PS_OS_SHIPPING')));
    }

    public function isInPreparation()
    {
        return sizeof($this->getHistory((int)($this->id_lang), Configuration::get('PS_OS_PREPARATION')));
    }

    /**
     * Get customer orders
     *
     * @param integer $id_customer Customer id
     * @param boolean $showHiddenStatus Display or not hidden order statuses
     * @return array Customer orders
     */
    public static function getCustomerOrders($id_customer, $showHiddenStatus = false,  $pagination = false, $limit = 10, $page = 1)
    {
        global $cookie;
        $sql = 'SELECT '.($pagination ? 'SQL_CALC_FOUND_ROWS ' : '').' o.*, (SELECT SUM(od.`product_quantity`) FROM `'._DB_PREFIX_.'order_detail` od WHERE od.`id_order` = o.`id_order`) nb_products
        FROM `'._DB_PREFIX_.'orders` o
        WHERE o.`id_customer` = '.(int)$id_customer.'
        GROUP BY o.`id_order`
        ORDER BY o.`date_add` DESC '
        .($pagination ? 'LIMIT '.(((int)($page) - 1) * (int)($limit)).', '.(int)($limit) : '');

        // echo $sql;
        $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql);
        if (!$res) {
            return array();
        }

        if ($pagination) {
            $res['totalItem'] = (int) Db::getInstance()->getValue('SELECT FOUND_ROWS() as rowCount');
        }

        foreach ($res AS $key => $val) {
            $res2 = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
            SELECT os.`id_order_state`, osl.`name` AS order_state, os.`invoice`
            FROM `'._DB_PREFIX_.'order_history` oh
            LEFT JOIN `'._DB_PREFIX_.'order_state` os ON (os.`id_order_state` = oh.`id_order_state`)
            INNER JOIN `'._DB_PREFIX_.'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state` AND osl.`id_lang` = '.(int)($cookie->id_lang).')
            WHERE oh.`id_order` = '.(int)($val['id_order']).(!$showHiddenStatus ? ' AND os.`hidden` != 1' : '').'
            ORDER BY oh.`date_add` DESC, oh.`id_order_history` DESC
            LIMIT 1');
            if ($res2) {
                $res[$key] = array_merge($res[$key], $res2[0]);
            }
        }

        return $res;
    }

    public static function getOrdersIdByDate($date_from, $date_to, $id_customer = NULL, $type = NULL)
    {
        $sql = '
        SELECT `id_order`
        FROM `'._DB_PREFIX_.'orders`
        WHERE DATE_ADD(date_upd, INTERVAL -1 DAY) <= \''.pSQL($date_to).'\' AND date_upd >= \''.pSQL($date_from).'\''
        .($type ? ' AND '.pSQL(strval($type)).'_number != 0' : '')
        .($id_customer ? ' AND id_customer = '.(int)($id_customer) : '');
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql);

        $orders = array();
        foreach ($result AS $order)
            $orders[] = (int)($order['id_order']);
        return $orders;
    }

    /*
    * @deprecated
    */
    public static function getOrders($limit = NULL)
    {
        Tools::displayAsDeprecated();
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
            SELECT *
            FROM `'._DB_PREFIX_.'orders`
            ORDER BY `date_add`
            '.((int)$limit ? 'LIMIT 0, '.(int)$limit : ''));
    }

    public static function getOrdersWithInformations($limit = NULL)
    {
        global $cookie;

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
            SELECT *, (
                SELECT `name`
                FROM `'._DB_PREFIX_.'order_history` oh
                LEFT JOIN `'._DB_PREFIX_.'order_state_lang` osl ON (osl.`id_order_state` = oh.`id_order_state`)
                WHERE oh.`id_order` = o.`id_order`
                AND osl.`id_lang` = '.(int)$cookie->id_lang.'
                ORDER BY oh.`date_add` DESC
                LIMIT 1
            ) AS `state_name`
            FROM `'._DB_PREFIX_.'orders` o
            LEFT JOIN `'._DB_PREFIX_.'customer` c ON (c.`id_customer` = o.`id_customer`)
            ORDER BY o.`date_add` DESC
            '.((int)$limit ? 'LIMIT 0, '.(int)$limit : ''));
    }

    public static function getInvoiceDetailsByDate($date_from, $date_to, $id_customer = NULL, $type = NULL)
    {
        $sql = '
        SELECT `invoice_number`,`invoice_date`,`total_products`,`total_shipping`,`carrier_tax_rate`
        FROM `'._DB_PREFIX_.'orders`
        WHERE DATE_ADD(invoice_date, INTERVAL -1 DAY) <= \''.pSQL($date_to).'\' AND invoice_date >= \''.pSQL($date_from).'\''
        .($type ? ' AND '.pSQL(strval($type)).'_number != 0' : '')
        .($id_customer ? ' AND id_customer = '.(int)($id_customer) : '');

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql);

        return $result;
    }

    public static function getOrdersIdInvoiceByDate($date_from, $date_to, $id_customer = NULL, $type = NULL)
    {
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
        SELECT `id_order`
        FROM `'._DB_PREFIX_.'orders`
        WHERE DATE_ADD(invoice_date, INTERVAL -1 DAY) <= \''.pSQL($date_to).'\' AND invoice_date >= \''.pSQL($date_from).'\''
        .($type ? ' AND '.pSQL(strval($type)).'_number != 0' : '')
        .($id_customer ? ' AND id_customer = '.(int)($id_customer) : '').
        ' ORDER BY invoice_date ASC');

        $orders = array();
        foreach ($result AS $order)
            $orders[] = (int)($order['id_order']);
        return $orders;
    }

    public static function getOrderByInvoiceNumber($invoiceNumber) {
        $sql = "
            SELECT bo.*
            FROM `" . _DB_PREFIX_ . "orders` bo
            WHERE bo.`invoice_number` = '%s'";

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(sprintf($sql, pSQL($invoiceNumber)));

        if (! empty($result)) {
            return new Order($result['id_order']);
        }

        return null;
    }

    public static function getOrdersIdByInvoiceNumbers($invoice_from, $invoice_to)
    {
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
        SELECT `id_order`
        FROM `'._DB_PREFIX_.'orders`
        WHERE invoice_number BETWEEN '.$invoice_from.' AND '.$invoice_to.'
        ');

        $orders = array();
        foreach ($result AS $order)
            $orders[] = (int)($order['id_order']);
        return $orders;
    }

    public static function getOrderIdsByStatus($id_order_state) {
        $orders = array();
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
            SELECT id_order
            FROM ' . _DB_PREFIX_ . 'orders o
            WHERE ' . (int) $id_order_state . ' = (
                SELECT id_order_state
                FROM ' . _DB_PREFIX_ . 'order_history oh
                WHERE oh.id_order = o.id_order
                ORDER BY date_add DESC, id_order_history DESC
                LIMIT 1
            )
            ORDER BY invoice_date ASC
        ');

        foreach ($result AS $order) {
            $orders[] = (int) $order['id_order'];
        }

        return $orders;
    }

    /**
     * Get product total without taxes
     *
     * @return Product total with taxes
     */
    public function getTotalProductsWithoutTaxes($products = false)
    {
        return $this->total_products;
    }

    /**
     * Get product total with taxes
     *
     * @return Product total with taxes
     */
    public function getTotalProductsWithTaxes($products = false)
    {
        if ($this->total_products_wt != '0.00' AND !$products)
            return $this->total_products_wt;
        /* Retro-compatibility (now set directly on the validateOrder() method) */
        if (!$products)
            $products = $this->getProductsDetail();

        $return = 0;
        foreach ($products AS $row)
        {
            $price = Tools::ps_round($row['product_price'] * (1 + $row['tax_rate'] / 100), 2);
            if ($row['reduction_percent'])
                $price -= $price * ($row['reduction_percent'] * 0.01);
            if ($row['reduction_amount'])
                $price -= $row['reduction_amount'] * (1 + ($row['tax_rate'] * 0.01));
            if ($row['group_reduction'])
                $price -= $price * ($row['group_reduction'] * 0.01);
            $price += $row['ecotax'] * (1 + $row['ecotax_tax_rate'] / 100);
            $return += Tools::ps_round($price, 2) * $row['product_quantity'];
        }
        if (!$products)
        {
            $this->total_products_wt = $return;
            $this->update();
        }
        return $return;
    }

    /**
     * Get customer orders number
     *
     * @param integer $id_customer Customer id
     * @return array Customer orders number
     */
    public static function getCustomerNbOrders($id_customer)
    {
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
        SELECT COUNT(`id_order`) AS nb
        FROM `'._DB_PREFIX_.'orders`
        WHERE `id_customer` = '.(int)($id_customer));

        return isset($result['nb']) ? $result['nb'] : 0;
    }

    /**
     * Get an order by its cart id
     *
     * @param integer $id_cart Cart id
     * @return array Order details
     */
    public static function getOrderByCartId($id_cart)
    {
        $result = Db::getInstance()->getRow('
        SELECT `id_order`
        FROM `'._DB_PREFIX_.'orders`
        WHERE `id_cart` = '.(int)($id_cart));

        return isset($result['id_order']) ? $result['id_order'] : false;
    }

    /**
     * Add a discount to order
     *
     * @param integer $id_discount Discount id
     * @param string $name Discount name
     * @param float $value Discount value
     * @return boolean Query sucess or not
     */
    public function addDiscount($id_discount, $name, $value)
    {
        return Db::getInstance()->AutoExecute(_DB_PREFIX_.'order_discount', array('id_order' => (int)($this->id), 'id_discount' => (int)($id_discount), 'name' => pSQL($name), 'value' => (float)($value)), 'INSERT');
    }

    /**
    * @param $discount Discount object
    * @return true | false
    */
    public function sendDiscountMailToCustomer(Discount $discount){

        $customer = new Customer((int)($this->id_customer));
        $currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));

        $params = array(
            '{firstname}' => $customer->firstname,
            '{voucher_amount}' => Tools::displayPrice($discount->value, $currency, false, false),
            '{voucher_num}' => $discount->name
        );

		/*Sending the Exchange/Refund/Cancel Voucher details to the Sailthru*/
		if(Module::isInstalled('sailthru')){
			$voucherDetail = array('customerEmail' =>  $customer->email,
								  'customerFirstName' => $customer->firstname,
								  'customerLastName' => $customer->lastname,
								  'voucherAmount' => Tools::displayPrice($discount->value, $currency, false, false),
								  'voucherNumber' => $discount->name);
			Module::hookExec('sailThruMailSend', array(
				'sailThruEmailTemplate' => 'voucher',
				'voucherDetail' => $voucherDetail
			));
		}else{
			Mail::Send(intval($this->id_lang), 'voucher', Mail::l('New voucher regarding your order'), $params,
                    $customer->email, $customer->firstname.' '.$customer->lastname);
		}

        return true;
    }

    /**
     * Get orders number last week
     *
     * @return integer Orders number last week
     * @deprecated
     */
    public static function getWeeklyOrders()
    {
        Tools::displayAsDeprecated();
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
        SELECT COUNT(`id_order`) as nb
        FROM `'._DB_PREFIX_.'orders`
        WHERE YEARWEEK(`date_add`) = YEARWEEK(NOW())');

        return isset($result['nb']) ? $result['nb'] : 0;
    }

    /**
     * Get sales amount last month
     *
     * @return float Sales amount last month
     * @deprecated
     */
    public static function getMonthlySales()
    {
        Tools::displayAsDeprecated();
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
        SELECT SUM(`total_paid`) as nb
        FROM `'._DB_PREFIX_.'orders`
        WHERE MONTH(`date_add`) = MONTH(NOW())
        AND YEAR(`date_add`) = YEAR(NOW())');

        return isset($result['nb']) ? $result['nb'] : 0;
    }

    public function getNumberOfDays()
    {
        $nbReturnDays = (int)(Configuration::get('PS_ORDER_RETURN_NB_DAYS'));
        if (!$nbReturnDays)
            return true;
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
        SELECT TO_DAYS(NOW()) - TO_DAYS(`delivery_date`)  AS days FROM `'._DB_PREFIX_.'orders`
        WHERE `id_order` = '.(int)($this->id));
        if ($result['days'] <= $nbReturnDays)
            return true;
        return false;
    }


    public function isReturnable()
    {
        $payment = $this->getHistory((int)($this->id_lang), Configuration::get('PS_OS_PAYMENT'));
        $delivred = $this->getHistory((int)($this->id_lang), Configuration::get('PS_OS_DELIVERED'));
        if ($payment AND $delivred AND strtotime($delivred[0]['date_add']) < strtotime($payment[0]['date_add']))
            return ((int)(Configuration::get('PS_ORDER_RETURN')) == 1 AND $this->getNumberOfDays());
        else
            return ((int)(Configuration::get('PS_ORDER_RETURN')) == 1 AND (int)($this->getCurrentState()) == Configuration::get('PS_OS_DELIVERED') AND $this->getNumberOfDays());
    }

    /**
     * DEPRECATED!
     */
    public static function getLastInvoiceNumber() {
        return Db::getInstance()->getValue('
            SELECT MAX(`invoice_number`) AS `invoice_number`
            FROM `' . _DB_PREFIX_ . 'orders`
            WHERE `invoice_number` > 9179831 AND `invoice_number` < 9184952
        ');
    }

    public function setInvoice() {
        throw new Exception('DEPRECATED!');
    }

    public function setDelivery()
    {
        // Set delivery number
        $number = (int)(Configuration::get('PS_DELIVERY_NUMBER'));
        if (!(int)($number))
            die(Tools::displayError('Invalid delivery number'));
        $this->delivery_number = $number;
        Configuration::updateValue('PS_DELIVERY_NUMBER', $number + 1);

        // Set delivery date
        $this->delivery_date = date('Y-m-d H:i:s');

        // Update object
        $this->update();
    }



    public static function printPDFIcons($id_order, $tr)
    {
        $order = new Order($id_order);
        $orderState = OrderHistory::getLastOrderState($id_order);
        if (!Validate::isLoadedObject($orderState) OR !Validate::isLoadedObject($order))
            die(Tools::displayError('Invalid objects'));
        echo '<span style="width:20px; margin-right:5px;">';
        if ((/*$orderState->invoice AND*/ $order->invoice_number) AND (int)($tr['product_number']))
        {
            echo '<a href="pdf.php?id_order='.(int)($order->id).'&pdf"><img src="../img/admin/tab-invoice.gif" alt="invoice" /></a>';
            echo '<a href="xml.php?id_order='.intval($order->id).'&order_xml"><img src="../img/admin/export.gif" alt="XML" /></a>';
        }
        else
            echo '&nbsp;';
        echo '</span>';
        echo '<span style="width:20px;">';
        if ($orderState->delivery AND $order->delivery_number)
            echo '<a href="pdf.php?id_delivery='.(int)($order->delivery_number).'"><img src="../img/admin/delivery.gif" alt="delivery" /></a>';
        else
            echo '&nbsp;';
        echo '</span>';
    }

    public static function getByDelivery($id_delivery)
    {
        $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
        SELECT id_order
        FROM `'._DB_PREFIX_.'orders`
        WHERE `delivery_number` = '.(int)($id_delivery));
        return new Order((int)($res['id_order']));
    }

    public function getTotalWeight()
    {
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
        SELECT SUM(product_weight * product_quantity) weight
        FROM '._DB_PREFIX_.'order_detail
        WHERE id_order = '.(int)($this->id));

        return (float)($result['weight']);
    }

    public static function getInvoice($id_invoice)
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
        SELECT `invoice_number`, `id_order`
        FROM `'._DB_PREFIX_.'orders`
        WHERE invoice_number = '.(int)($id_invoice));
    }

    public function isAssociatedAtGuest($email)
    {
        if (!$email)
            return false;
        return (bool)Db::getInstance()->getValue('
            SELECT COUNT(*)
            FROM `'._DB_PREFIX_.'orders` o
            LEFT JOIN `'._DB_PREFIX_.'customer` c ON (c.`id_customer` = o.`id_customer`)
            WHERE o.`id_order` = '.(int)$this->id.'
            AND c.`email` = \''.pSQL($email).'\'
            AND c.`is_guest` = 1
        ');
    }

    /**
     * @param int $id_order
     * @param int $id_customer optionnal
     * @return int id_cart
     */
    public static function getCartIdStatic($id_order, $id_customer = 0)
    {
        return (int)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
            SELECT `id_cart`
            FROM `'._DB_PREFIX_.'orders`
            WHERE `id_order` = '.(int)$id_order.'
            '.($id_customer ? 'AND `id_customer` = '.(int)$id_customer : ''));
    }

    public function getWsOrderRows()
    {
        $query = 'SELECT id_order_detail as `id`, `product_id`, `product_price`, `id_order`, `product_attribute_id`, `product_quantity`, `product_name`
        FROM `'._DB_PREFIX_.'order_detail`
        WHERE id_order = '.(int)$this->id;
        $result = Db::getInstance()->executeS($query);
        return $result;
    }

    public function setCurrentState($id_order_state) {
        $this->log->info('Setting order state to: ' . $id_order_state);

        if (empty($id_order_state)) {
            $this->log->error('No new state is given, giving up..');

            return false;
        }

        $history = new OrderHistory();
        $history->id_order = (int)($this->id);
        $history->changeIdOrderState((int) $id_order_state, (int) $this->id);
        $history->addWithemail();

        $this->log->info('Order is set the new state successfully!');
    }

    public function addWs($autodate = true, $nullValues = false)
    {
        $paymentModule = Module::getInstanceByName($this->module);
        $customer = new Customer($this->id_customer);
        $paymentModule->validateOrder($this->id_cart, Configuration::get('PS_OS_WS_PAYMENT'), $this->total_paid, $this->payment, NULL, array(), null, false, $customer->secure_key);
        $this->id = $paymentModule->currentOrder;
        return true;
    }

    public function deleteAssociations()
    {
        return (Db::getInstance()->Execute('
                DELETE FROM `'._DB_PREFIX_.'order_detail`
                WHERE `id_order` = '.(int)($this->id)) !== false);
    }

    public function getOrderDiscounttype()
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
        SELECT d.`id_discount_type`
        FROM `'._DB_PREFIX_.'order_discount` od
        LEFT JOIN `'._DB_PREFIX_.'discount` d ON (d.`id_discount` = od.`id_discount`)
        WHERE od.`id_order` = '.(int)($this->id));
    }

    public function createOrderxmlDaily($id_order)
    {
        global $cookie;
        //generating the XML for an order
        $log = false;
        $folder = _PS_DOWNLOAD_DIR_."orders_xml/".date("Y-m-d");
        $log_file = _PS_LOG_DIR_."order_xml_logs/log_".date("Y-m-d");
        if(!file_exists($log_file))
        {
            $fh = fopen($log_file, 'w');
            fclose($fh);
        }
        if(!is_dir($folder))
            mkdir($folder);

        $order = new Order($id_order);
        $output = Tools::generateXml($order);

        $file_name = _PS_DOWNLOAD_DIR_ . "orders_xml/all/" . sprintf('%s', $order->invoice_number) . ".xml";
        $file_name1 = $folder . "/" . sprintf('%s', $order->invoice_number) . ".xml";
        $mikroFileName = _PS_DOWNLOAD_DIR_ . "orders_xml/mikro/" . sprintf('%s', $order->invoice_number) . ".xml";

        if ($log) {
            $fh = fopen($log_file, 'a') or die("can't open file");
            fwrite($fh,"************************ Start of " . $order->invoice_number . " ******************************\n");
            fclose($fh);
        }

        $fp = fopen($file_name, 'w');

        if ($log) {
            $fh = fopen($log_file, 'a') or die("can't open file");
            fwrite($fh, "File pointer: $fp \n");
            fclose($fh);
        }

        if ($fp == NULL) {
            if ($log) {
                $fh = fopen($log_file, 'a') or die("can't open file");
                fwrite($fh, "$file_name is not created");
                fclose($fh);
            }
        }

        if ($log) {
            $fh = fopen($log_file, 'a') or die("can't open file");
            fwrite($fh,"$file_name Created Sucessfully \n");
            fclose($fh);
        }

        fwrite($fp, $output);
        fclose($fp);

        // STARTING FILE_NAME1

        $fp = fopen($file_name1, 'w');

        if ($fp == NULL) {
            if ($log) {
                $fh = fopen($log_file, 'a') or die("can't open file");
                fwrite($fh, "$file_name1 is not created \r\n");
                fclose($fh);
            }
        }

        if ($log) {
            $fh = fopen($log_file, 'a') or die("can't open file");
            fwrite($fh, "$file_name1 Created Sucessfully \n");
            fclose($fh);
        }

        fwrite($fp, $output);
        fclose($fp);

        // ENDING FILE_NAME1

        // STARTING MIKRO FILENAME

        $fp = fopen($mikroFileName, 'w');

        if ($fp == NULL) {
            if ($log) {
                $fh = fopen($log_file, 'a') or die("can't open file");
                fwrite($fh, "$mikroFileName is not created \r\n");
                fclose($fh);
            }
        }

        if ($log) {
            $fh = fopen($log_file, 'a') or die("can't open file");
            fwrite($fh,"$mikroFileName Created Sucessfully \n");
            fclose($fh);
        }

        fwrite($fp, $output);
        fclose($fp);

        chmod($mikroFileName, 777);

        // ENDING MIKRO FILENAME

        if ($log) {
            $fh = fopen($log_file, 'a') or die("can't open file");
            fwrite($fh,$output);
            fclose($fh);
        }

        if ($log) {
            $fh = fopen($log_file, 'a') or die("can't open file");
            fwrite($fh,"************************ End of " . $order->invoice_number . " ******************************\n\n");
            fclose($fh);
        }

        return;
    }

	public static function isExist($id) {
		$id_order = pSQL($id);
		$sql = "Select id_order from `bu_orders` where id_order=$id";
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
		return ($result) ? true : false;
	}

	public function setTrackingNumber($tracking_number) {
        $this->log->info('Setting order tracking number to: ' . $tracking_number);

		$this->tracking_number = $tracking_number;
	}

    public function getTrackingLink() {
        $moduleFile = _PS_MODULE_DIR_.'araskargo/araskargo.php';
        include_once($moduleFile);

        return ArasKargo::getTrackingLinkByOrder($this);
    }

    /**
     * Cancel Product
     * @param $idOrderDetail
     * @param $quantity
     * @param $shipping
     * @param $state
     * @param $updateQty Update quantity
     * return true | error array
     */
    public function cancelProduct($id_order_detail, $qty, $shipping, $state = 0 ,$updateqty = false ) {
        global $cookie;

        $error = array('error' => true);
        if (!$id_order_detail) {
             $error['message'] = Tools::displayError('No product or quantity selected.');
            return $error;
        }

        $qtyCancelProduct = abs($qty);
        if (!$qtyCancelProduct) {
            $error['message'] = Tools::displayError('No quantity selected for product.');
            return $error;
        }

        $orderDetail = new OrderDetail((int)($id_order_detail));

        // check actionable quantity
        if($orderDetail->product_quantity == 0) {
            $error['message'] = Tools::displayError('Invalid quantity selected for product.');
            return $error;
        }

        // Reinject product
        if($qtyCancelProduct && $updateqty == true) {
            $reinjectableQuantity = (int)($orderDetail->product_quantity) - (int)($orderDetail->product_quantity_reinjected);
            $quantityToReinject = $qtyCancelProduct;//$qtyCancelProduct > $reinjectableQuantity ? $reinjectableQuantity : $qtyCancelProduct;
            if (!Product::reinjectQuantities($orderDetail, $quantityToReinject)) {
                $error['message'] = Tools::displayError('Cannot re-stock product') . ' <b>'.$orderDetail->product_name.'</b>';
                return $error;
            } else {
                $updProductAttributeID = !empty($orderDetail->product_attribute_id) ? (int)($orderDetail->product_attribute_id) : NULL;
                $newProductQty = Product::getQuantity((int)($orderDetail->product_id), $updProductAttributeID);
                $product = get_object_vars(new Product((int)($orderDetail->product_id), false, (int)($cookie->id_lang)));

                if (!empty($orderDetail->product_attribute_id)) {
                    $updProduct['quantity_attribute'] = (int)($newProductQty);
                    $product['quantity_attribute'] = $updProduct['quantity_attribute'];
                } else {
                    $updProduct['stock_quantity'] = (int)($newProductQty);
                    $product['stock_quantity'] = $updProduct['stock_quantity'];
                }

                Hook::updateQuantity($product, $this);
            }
        }

        if ($state == _PS_OS_REFUND_ || $state == _PS_OS_EXCHANGE_ || $state == _PS_OS_CREDITED_ ) {
            // Delete product
            if (! $this->customDeleteProduct($this, $orderDetail, $qtyCancelProduct, $shipping, $state)) {
                switch ($state) {
                    case _PS_OS_REFUND_:
                        $errorMessageText = 'An error occurred during refund of the product.';
                        break;
                    case _PS_OS_EXCHANGE_ :
                        $errorMessageText = 'An error occurred during exchange of the product.';
                        break;
                    case _PS_OS_CREDITED_ :
                        $errorMessageText = 'An error occurred during generating credit of the product.';
                        break;
                    default:
                        break;
                }

                $error['message'] = Tools::displayError($errorMessageText) . ' <b>' . $orderDetail->product_name . '</b>';
                return $error;
            }
        } elseif ($state == _PS_OS_CANCELED_) {
            if (!$this->deleteProduct($this, $orderDetail, $qtyCancelProduct)) {
                $error['message'] = Tools::displayError('An error occurred during cancel of the product.').' <b>'.$orderDetail->product_name.'</b>';
                return $error;
            }
        }

        Module::hookExec('cancelProduct', array('order' => $this, 'id_order_detail' => $id_order_detail));
        return true;
    }

    /**
     * @return id_order_detail
     */
    public function getOrderDetailIds() {
        return OrderDetail::getDetailIdsByOrderId($this->id);
    }

    /**
     *  Check bank is accept cancel payment of today
     *  @param $order  Order instance
     */

    public function isCancellable() {
        if ($this->module == 'freeorder') return true;

        $moduleFile = _PS_MODULE_DIR_.$this->module.'/'.$this->module.'.php';
        require_once($moduleFile);
        $className = strtoupper($this->module); // PGG, PGY etc

        return $className::isOrderCancellable($this);
    }

    public function isRefundable() {
        $moduleFile = _PS_MODULE_DIR_.$this->module.'/'.$this->module.'.php';
        require_once($moduleFile);
        $className = strtoupper($this->module); // PGG, PGY etc
        return $className::isOrderRefundable($this);
    }


    public static function getCustomerIdStatic($id) {
        $sql = "Select id_customer from `bu_orders` where id_order=$id";
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
    }
}

