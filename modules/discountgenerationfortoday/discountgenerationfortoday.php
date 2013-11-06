<?php

if (!defined('_CAN_LOAD_FILES_'))
    exit;

class DiscountGenerationForToday extends Module {
    private $voucherDescription;

    public function __construct() {
        $this->name = 'discountgenerationfortoday';
        $this->tab = 'front office features';
        $this->version = 1.4;
        $this->author = 'PrestaShop';

        parent::__construct();

        $this->displayName = $this->l('Discount Generation For Today');
        $this->description = $this->l('This module must be enabled if you want to use generate discount.');

        $this->voucherDescription = $this->l('Special 25% Discount For Today');

        $this->hookHeader();
    }

    public function install() {
        if (!parent::install()
            OR !$this->registerHook('header')
            OR !$this->registerHook('todayDiscount')
            OR !$this->registerHook('updateOrderStatus'))
            return false;

        return true;
    }
	
	public function uninstall() {
		return (parent::uninstall());
	}

    public function hookHeader() {
        Tools::addCSS(_THEME_CSS_DIR_."modules/discountgenerationfortoday/discountgenerationfortoday.css");
    }

    public function hooktodayDiscount($params)
    {
        global $cookie, $smarty;
        if(!$this->todayDiscountApplliedForOrder( $params['id_order']))
        {
            if($voucherCodeExists = $this->discountExistsForOrder($params['id_order']))
                $voucherCode = $voucherCodeExists;
            else
                $voucherCode = $this->createDiscountForCustomerOnOrderConfirmation($params['id_customer'], $params['id_order']);

            $smarty->assign('voucherCode', $voucherCode);
            return $this->display(__FILE__, 'today-discount.tpl');
        }
        return false;
    }

    public function hookUpdateOrderStatus($params)
    {
        $newOS = $params['newOrderStatus'];
//        echo $newOS->id; exit;
        if($newOS->id == Configuration::get('PS_OS_CANCELED') || $newOS->id == Configuration::get('PS_OS_REFUND') || $newOS->id == Configuration::get('PS_OS_PARTIALREFUND') || $newOS->id == Configuration::get('PS_OS_MANUALREFUND') || $newOS->id == Configuration::get('PS_OS_EXCHANGE') || $newOS->id == Configuration::get('PS_OS_PARTIALEXCHANGE')) {
            if($this->discountExistsForOrder($params['id_order'])) {
//                $status = DB::getInstance()->delete(_DB_PREFIX_.'discount', 'id_order = '.(int)($params['id_order']));
               $status = Db::getInstance()->Execute('
                         UPDATE `'._DB_PREFIX_.'discount`
                         SET `active`= 0
                         WHERE `id_order` = '.(int)($params['id_order']).'');



//              echo 'Discounted Deleted..'; exit;
                if($status)
                    return true;
                return false;
            }
        }
    }

    public function createDiscountForCustomerOnOrderConfirmation($id_customer, $id_order)
    {
        $voucherCode = Tools::voucherGen();;
        $languages = Language::getLanguages(false);
        // create discount
        $discount = new Discount();
        $discount->id_discount_type = 1;
        $discount->behavior_not_exhausted = 0;

        foreach ($languages as $language) {
            $discount->description[$language['id_lang']] = $this->voucherDescription;
        }

        $discount->value = 25.00;
        $discount->name = $voucherCode ;
        $discount->id_customer = $id_customer;
        $discount->id_currency = 4;
        $discount->quantity = 1;
        $discount->quantity_per_user = 1;
        $discount->cumulable = 0;
        $discount->cumulable_reduction = 1;
        $discount->minimal = 0.00;
        $discount->active = 1;
        $now = time();
        $discount->date_from = date('Y-m-d H:i:s', $now);
        $discount->date_to = date('Y-m-d H:i:s', $now + (30*24*60*60));
        if (!$discount->validateFieldsLang(false) OR !$discount->add())
        {
            return false;
        }
        if($this->insertOrderID($discount->id, $id_order))
        {
           return $voucherCode;
        }
        return false;
    }

    public function insertOrderID($id_discount, $id_order)
    {
        return Db::getInstance()->Execute('
            UPDATE `'._DB_PREFIX_.'discount`
            SET `id_order`= '.($id_order).'
            WHERE `id_discount` = '.$id_discount.'');
    }

    public function discountExistsForOrder($id_order)
    {
        $result = Db::getInstance()->getValue('
		SELECT `name`
		FROM `'._DB_PREFIX_.'discount`
		WHERE `id_order` = '.(int)($id_order).'
		');
        if($result)
            return $result;
        return false;
    }

    public function todayDiscountApplliedForOrder($id_order)
    {
        $result = Db::getInstance()->getRow('
	        SELECT od.`id_discount`, d.`id_order`, d.`value`
		FROM `'._DB_PREFIX_.'order_discount` od
                LEFT JOIN `'._DB_PREFIX_.'discount` d ON (od.`id_discount` = d.`id_discount` AND d.`id_order` IS NOT NULL)
		WHERE od.`id_order` = '.(int)($id_order).'
		');
        if($result['id_order'] && $result['value'] == 25.00)
            return $result;
        return false;
    }
}
?>
