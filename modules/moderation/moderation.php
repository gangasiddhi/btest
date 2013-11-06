<?php

if (!defined('_CAN_LOAD_FILES_'))
    exit;

class moderation extends Module {

    const MODERATION_TYPE_EXCHANGE = 1;
    const MODERATION_TYPE_CANCEL = 2;

    // Product moderation types
    const PROD_MOD_TYPE_MANUAL_REFUND = 3;
    const PROD_MOD_TYPE_CANCEL_EXCHANGE = 4;

    /**
     * moderation_reasons table reason_type field values
     */
    const ORDER_MODERATION_REASON_TYPE = 1;
    const PROD_MODERATION_REASON_TYPE = 2;

    public function __construct() {
        $this->name = 'moderation';
        $this->tab = 'order';
        $this->version = '0.1';

        parent::__construct();
        $this->displayName = $this->l('Moderation');
        $this->description = $this->l('Moderation');

        $this->hooks = array(
            'exchangeProduct' => 'Fire when product exchange',
            'displayProductModerationChoices' => 'Return model moderation select element options',
            'displayOrderModerationChoices' =>  'Return order moderation select element options'
        );

        $this->adminModerationTabClassName = 'AdminModeration';

    }

    public function install() {
        $this->createHooks();

        if (!parent::install()
            || !$this->installModuleTab($this->adminModerationTabClassName, array(1 => 'Moderations', 4 => 'Moderasyonlar'), 3)
            ){
            return false;
        }

        foreach ($this->hooks as $name => $title) {
            if (!$this->registerHook($name)) {
                return false;
            }
        }

        return true;
    }

    public function uninstall() {
        parent::uninstall();
        $this->deleteHooks();
        $this->uninstallModuleTab($this->adminModerationTabClassName);
    }

    protected function createHooks() {
        $sql = "INSERT INTO " . _DB_PREFIX_ . "hook(name, title) VALUES ";
        $hookItemSqls = array();
        foreach ($this->hooks as $name => $title) {
            $name = sprintf("'%s'", $name);
            $title = sprintf("'%s'", $title);
            $hookItemSqls[] = ' (' .$name. ',' .$title. ')';
        }
        $sql .= implode(",", $hookItemSqls);
        DB::getInstance()->ExecuteS($sql);
    }

    protected function deleteHooks() {
        function addQuotes ($str) {
            return sprintf("'%s'", $str);
        }

        $sql = "DELETE FROM " . _DB_PREFIX_ . "hook where name in ";
        $sql .= '(' . implode(",", array_map('addQuotes', array_keys($this->hooks))) . ')';
        DB::getInstance()->ExecuteS($sql);
    }

    protected function installModuleTab($tabClass, $tabName, $idTabParent) {
        $tab = new Tab();
        $tab->module = $this->name;
        $tab->name = $tabName;
        $tab->class_name = $tabClass;
        $tab->id_parent = $idTabParent;
        return $tab->save() ? true : false;
    }

    protected function uninstallModuleTab($tabClass) {
        $idTab = Tab::getIdFromClassName($tabClass);
        if($idTab == 0) {
            return false;
        }

        $tab = new Tab($idTab);
        $tab->delete();

        return true;
    }

    public function hookDisplayProductModerationChoices($params) {
        // Dropdown list tpl
        global $smarty;

        $smarty->assign('productModerationChoices', self::getModerationChoiceByType(self::PROD_MODERATION_REASON_TYPE));
        return $this->display(__FILE__, 'product_moderation_dropdown_options.tpl');
    }

    public static function getProductModerationReasons() {
        return self::getModerationChoiceByType(self::PROD_MODERATION_REASON_TYPE);
    }

    public function hookDisplayOrderModerationChoices($params) {
        // Dropdown list tpl
        global $smarty;

        $smarty->assign('orderModerationChoices', self::getModerationChoiceByType(self::ORDER_MODERATION_REASON_TYPE));
        return $this->display(__FILE__, 'order_moderation_dropdown_options.tpl');
    }

    public static function getOrderModerationReasons() {
        return self::getModerationChoiceByType(self::ORDER_MODERATION_REASON_TYPE);
    }

    public static function getModerationChoiceByType($reason_type) {
        $sql = "Select id,text from " . _DB_PREFIX_ ."moderation_reason Where reason_type=$reason_type";
        return DB::getInstance()->ExecuteS($sql);
    }
}

?>
