<?php

if (!defined('_CAN_LOAD_FILES_'))
    exit;

class ReferralProgram extends Module {
    protected $log;

    public function __construct() {
        $this->log = Logger::getLogger(get_class($this));

        $this->name = 'referralprogram';
        $this->tab = 'advertising_marketing';
        $this->version = '1.5';
        $this->author = 'PrestaShop';

        parent::__construct();

        $this->confirmUninstall = $this->l('All sponsors and friends will be deleted. Are you sure you want to uninstall this module?');
        $this->displayName = $this->l('Customer referral program');
        $this->description = $this->l('Integrate a referral program system into your shop.');
        if (Configuration::get('REFERRAL_DISCOUNT_TYPE') == 1 AND !Configuration::get('REFERRAL_PERCENTAGE'))
            $this->warning = $this->l('Please specify an amount for referral program vouchers.');

        if ($this->id) {
            $this->_configuration = Configuration::getMultiple(array('REFERRAL_NB_FRIENDS', 'REFERRAL_ORDER_QUANTITY', 'REFERRAL_DISCOUNT_TYPE', 'REFERRAL_DISCOUNT_VALUE'));
            $this->_configuration['REFERRAL_DISCOUNT_DESCRIPTION'] = Configuration::getInt('REFERRAL_DISCOUNT_DESCRIPTION');
            $this->_xmlFile = dirname(__FILE__).'/referralprogram.xml';
        }
    }

    private function instanceDefaultStates()
    {
        include_once(dirname(__FILE__).'/LoyaltyStateModule.php');

        $this->loyaltyStateDefault = new LoyaltyStateModule(LoyaltyStateModule::getDefaultId());
        $this->loyaltyStateValidation = new LoyaltyStateModule(LoyaltyStateModule::getValidationId());
        $this->loyaltyStateCancel = new LoyaltyStateModule(LoyaltyStateModule::getCancelId());
        $this->loyaltyStateConvert = new LoyaltyStateModule(LoyaltyStateModule::getConvertId());
        $this->loyaltyStateNoneAward = new LoyaltyStateModule(LoyaltyStateModule::getNoneAwardId());
        $this->loyaltyStateRefund = new LoyaltyStateModule(LoyaltyStateModule::getRefundId());
        $this->loyaltyStatePartialRefund = new LoyaltyStateModule(LoyaltyStateModule::getPartialRefundId());
        $this->loyaltyStateFullExchange = new LoyaltyStateModule(LoyaltyStateModule::getFullExchangeId());
        $this->loyaltyStatePartialExchange = new LoyaltyStateModule(LoyaltyStateModule::getPartialExchangeId());
    }

    public function install()
    {
        include_once(dirname(__FILE__).'/LoyaltyStateModule.php');
        $defaultTranslations = array('en' => 'Referral reward', 'fr' => 'Récompense parrainage');
        $desc = array((int)Configuration::get('PS_LANG_DEFAULT') => $this->l('Referral reward'));
        foreach (Language::getLanguages() AS $language)
            if (isset($defaultTranslations[$language['iso_code']]))
                $desc[(int)$language['id_lang']] = $defaultTranslations[$language['iso_code']];

        $defaultLTranslations = array('en' => 'Loyalty reward', 'fr' => 'Récompense fidélité');
        $conf = array((int)Configuration::get('PS_LANG_DEFAULT') => $this->l('Loyalty reward'));
        foreach (Language::getLanguages() AS $language)
            if (isset($defaultLTranslations[$language['iso_code']]))
                $conf[(int)$language['id_lang']] = $defaultTranslations[$language['iso_code']];
        Configuration::updateValue('PS_LOYALTY_VOUCHER_DETAILS', $conf);

        $category_config = '';
        $categories = Category::getSimpleCategories((int)(Configuration::get('PS_LANG_DEFAULT')));
        foreach ($categories AS $category)
            $category_config .= (int)$category['id_category'].',';
        $category_config = rtrim($category_config, ',');
        Configuration::updateValue('PS_LOYALTY_VOUCHER_CATEGORY', $category_config);

        if (! parent::install()
                OR ! $this->installDB()
                OR ! Configuration::updateValue('REFERRAL_DISCOUNT_DESCRIPTION', $desc)
                OR ! Configuration::updateValue('REFERRAL_ORDER_QUANTITY', 1)
                OR ! Configuration::updateValue('REFERRAL_DISCOUNT_TYPE', 2)
                OR ! Configuration::updateValue('REFERRAL_NB_FRIENDS', 5)
                OR ! Configuration::updateValue('PS_LOYALTY_POINT_VALUE', '0.20')
                OR ! Configuration::updateValue('PS_LOYALTY_MINIMAL', 0)
                OR ! Configuration::updateValue('PS_LOYALTY_POINT_RATE', '10')
                OR ! Configuration::updateValue('PS_LOYALTY_NONE_AWARD', '1')
                OR ! $this->registerHook('shoppingCart')
                OR ! $this->registerHook('orderConfirmation')
                OR ! $this->registerHook('updateOrderStatus')
                OR ! $this->registerHook('adminCustomers')
                OR ! $this->registerHook('createAccount')
                OR ! $this->registerHook('createAccountForm')
                OR ! $this->registerHook('customerAccount')
                OR ! $this->registerHook('newOrder')
                OR ! $this->registerHook('cancelProduct')
                OR ! $this->registerHook('referralProgram')
                OR ! $this->registerHook('referralStylepoint'))
            return false;

        /* Define a default value for fixed amount vouchers, for each currency */
        foreach (Currency::getCurrencies() AS $currency)
            Configuration::updateValue('REFERRAL_DISCOUNT_VALUE_'.(int)($currency['id_currency']), 5);

        /* Define a default value for the percentage vouchers */
        Configuration::updateValue('REFERRAL_PERCENTAGE', 5);

        /* This hook is optional */
        $this->registerHook('myAccountBlock');
        if (!LoyaltyStateModule::insertDefaultData())
            return false;
        return true;
    }

    public function installDB()
    {
        Db::getInstance()->Execute('
        CREATE TABLE `'._DB_PREFIX_.'referralprogram` (
            `id_referralprogram` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_sponsor` INT UNSIGNED NOT NULL,
            `email` VARCHAR(255) NOT NULL,
            `lastname` VARCHAR(128) NOT NULL,
            `firstname` VARCHAR(128) NOT NULL,
            `id_customer` INT UNSIGNED DEFAULT NULL,
            `refer_type` INT UNSIGNED DEFAULT NULL,
            `id_discount` INT UNSIGNED DEFAULT NULL,
            `id_discount_sponsor` INT UNSIGNED DEFAULT NULL,
            `date_add` DATETIME NOT NULL,
            `date_upd` DATETIME NOT NULL,
            PRIMARY KEY (`id_referralprogram`),
            UNIQUE KEY `index_unique_referralprogram_email` (`email`)
        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 ;');

        Db::getInstance()->Execute('
        CREATE TABLE `' . _DB_PREFIX_ . 'loyalty` (
            `id_loyalty` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_loyalty_state` INT UNSIGNED NOT NULL DEFAULT 1,
            `id_customer` INT UNSIGNED NOT NULL,
            `id_order` INT UNSIGNED DEFAULT NULL,
            `id_discount` INT UNSIGNED DEFAULT NULL,
            `points` INT NOT NULL DEFAULT 0,
            `id_referralprogram` INT UNSIGNED DEFAULT NULL,
            `date_add` DATETIME NOT NULL,
            `date_upd` DATETIME NOT NULL,
            PRIMARY KEY (`id_loyalty`),
            INDEX index_loyalty_loyalty_state (`id_loyalty_state`),
            INDEX index_loyalty_order (`id_order`),
            INDEX index_loyalty_discount (`id_discount`),
            INDEX index_loyalty_customer (`id_customer`)
        ) DEFAULT CHARSET=utf8 ;');

        Db::getInstance()->Execute('
        CREATE TABLE `' . _DB_PREFIX_ . 'loyalty_history` (
            `id_loyalty_history` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_loyalty` INT UNSIGNED DEFAULT NULL,
            `id_loyalty_state` INT UNSIGNED NOT NULL DEFAULT 1,
            `points` INT NOT NULL DEFAULT 0,
            `date_add` DATETIME NOT NULL,
            PRIMARY KEY (`id_loyalty_history`),
            INDEX `index_loyalty_history_loyalty` (`id_loyalty`),
            INDEX `index_loyalty_history_loyalty_state` (`id_loyalty_state`)
        ) DEFAULT CHARSET=utf8 ;');

        Db::getInstance()->Execute('
        CREATE TABLE `' . _DB_PREFIX_ . 'loyalty_state` (
            `id_loyalty_state` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_order_state` INT UNSIGNED DEFAULT NULL,
            PRIMARY KEY (`id_loyalty_state`),
            INDEX index_loyalty_state_order_state (`id_order_state`)
        ) DEFAULT CHARSET=utf8 ;');

        Db::getInstance()->Execute('
        CREATE TABLE `' . _DB_PREFIX_ . 'loyalty_state_lang` (
            `id_loyalty_state` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_lang` INT UNSIGNED NOT NULL,
            `name` varchar(64) NOT NULL,
            UNIQUE KEY `index_unique_loyalty_state_lang` (`id_loyalty_state`,`id_lang`)
        ) DEFAULT CHARSET=utf8 ;');

        return true;

    }

    public function uninstall()
    {
        $result = true;
        foreach (Currency::getCurrencies() AS $currency)
            $result = $result AND Configuration::deleteByName('REFERRAL_DISCOUNT_VALUE_'.(int)($currency['id_currency']));
        if (!parent::uninstall()
                OR !$this->uninstallDB()
                OR !$this->removeMail()
                OR !$result
                OR !Configuration::deleteByName('REFERRAL_PERCENTAGE')
                OR !Configuration::deleteByName('REFERRAL_ORDER_QUANTITY')
                OR !Configuration::deleteByName('REFERRAL_DISCOUNT_TYPE')
                OR !Configuration::deleteByName('REFERRAL_NB_FRIENDS')
                OR !Configuration::deleteByName('REFERRAL_DISCOUNT_DESCRIPTION')
                OR !Configuration::deleteByName('PS_LOYALTY_POINT_VALUE')
                OR !Configuration::deleteByName('PS_LOYALTY_POINT_RATE')
                OR !Configuration::deleteByName('PS_LOYALTY_NONE_AWARD')
                OR !Configuration::deleteByName('PS_LOYALTY_VOUCHER_DETAILS')
                OR !Configuration::deleteByName('PS_LOYALTY_MINIMAL')
                OR !Configuration::deleteByName('PS_LOYALTY_VOUCHER_CATEGORY'))
            return false;
        return true;
    }

    public function uninstallDB()
    {
        Db::getInstance()->Execute('DROP TABLE `'._DB_PREFIX_.'referralprogram`;');
        Db::getInstance()->Execute('DROP TABLE `' . _DB_PREFIX_ . 'loyalty`;');
        Db::getInstance()->Execute('DROP TABLE `' . _DB_PREFIX_ . 'loyalty_state`;');
        Db::getInstance()->Execute('DROP TABLE `' . _DB_PREFIX_ . 'loyalty_state_lang`;');
        Db::getInstance()->Execute('DROP TABLE `' . _DB_PREFIX_ . 'loyalty_history`;');
        return true;
    }

    public function removeMail() {
        $langs = Language::getLanguages(false);

        foreach ($langs AS $lang) {
            foreach ($this->_mails['name'] AS $name) {
                foreach ($this->_mails['ext'] AS $ext) {
                    $file = _PS_MAIL_DIR_.$lang['iso_code'].'/'.$name.'.'.$ext;
                    if (file_exists($file) AND !@unlink($file))
                        $this->_errors[] = $this->l('Cannot delete this file:').' '.$file;
                }
            }
        }

        return true;
    }

    public function convertToCredits($id_customer, $id_currency/*$no_of_credits*/) {
        $languages = Language::getLanguages();
        $name = $this->l('Converted Style Points');
        $credit = new Discount();
        $credit->name = ReferralProgramModule::getDiscountPrefix() . Tools::passwdGen(6);
        $credit->id_discount_type = 4;
        $credit->behavior_not_exhausted = 0;

        foreach ($languages as $language) {
            $credit->description[$language['id_lang']] = strval($name);
        }

        $credit->id_customer = intval($id_customer);
        $credit->id_currency = intval($id_currency);
        $credit->value = LoyaltyModule::getCreditValue(intval($id_customer), intval($id_currency));
        $credit->quantity = 1;
        $credit->quantity_per_user = 1;
        $credit->cumulable = 0;
        $credit->cumulable_reduction = 1;
        $credit->date_from = date('Y-m-d H:i:s', time());
        $credit->date_to = date('Y-m-d H:i:s', time() + 31536000); // + 1 year
        $credit->minimal = 0;
        $credit->active = 1;
        $credit->save();

        LoyaltyModule::registerDiscount($credit);
    }

    private function _postProcess() {
        $this->instanceDefaultStates();

        $defaultLanguage = (int)(Configuration::get('PS_LANG_DEFAULT'));
        $languages = Language::getLanguages();
        $this->_errors = array();

        if (! is_array(Tools::getValue('categoryBox')) OR !sizeof(Tools::getValue('categoryBox')))
            $this->_errors[] = $this->l('You must choose at least one category for voucher\'s action');

        if (! sizeof($this->_errors)) {
            Configuration::updateValue('REFERRAL_ORDER_QUANTITY', (int)(Tools::getValue('order_quantity')));

            foreach (Tools::getValue('discount_value') AS $id_currency => $discount_value) {
                Configuration::updateValue('REFERRAL_DISCOUNT_VALUE_'.(int)($id_currency), (float)($discount_value));
            }

            Configuration::updateValue('REFERRAL_DISCOUNT_TYPE', (int)(Tools::getValue('discount_type')));
            Configuration::updateValue('REFERRAL_NB_FRIENDS', (int)(Tools::getValue('nb_friends')));
            Configuration::updateValue('REFERRAL_PERCENTAGE', (int)(Tools::getValue('discount_value_percentage')));
            Configuration::updateValue('REFERRAL_DISCOUNT_DESCRIPTION', Tools::getValue('discount_description'));

            Configuration::updateValue('PS_LOYALTY_VOUCHER_CATEGORY', $this->voucherCategories(Tools::getValue('categoryBox')));
            Configuration::updateValue('PS_LOYALTY_POINT_VALUE', (float)(Tools::getValue('point_value')));
            Configuration::updateValue('PS_LOYALTY_POINT_RATE', (float)(Tools::getValue('point_rate')));
            Configuration::updateValue('PS_LOYALTY_NONE_AWARD', (int)(Tools::getValue('PS_LOYALTY_NONE_AWARD')));
            Configuration::updateValue('PS_LOYALTY_MINIMAL', (float)(Tools::getValue('minimal')));

            $this->loyaltyStateValidation->id_order_state = (int)(Tools::getValue('id_order_state_validation'));
            $this->loyaltyStateCancel->id_order_state = (int)(Tools::getValue('id_order_state_cancel'));
            $this->loyaltyStateRefund->id_order_state = (int)(Tools::getValue('id_order_state_refund'));
            $this->loyaltyStatePartialRefund->id_order_state = (int)(Tools::getValue('id_order_state_partialrefund'));
            $this->loyaltyStatePartialExchange->id_order_state = (int)(Tools::getValue('id_order_state_partialexchange'));
            $this->loyaltyStateFullExchange->id_order_state = (int)(Tools::getValue('id_order_state_fullexchange'));

            $arrayVoucherDetails = array();

            foreach ($languages AS $language) {
                $arrayVoucherDetails[(int)($language['id_lang'])] = Tools::getValue('voucher_details_'.(int)($language['id_lang']));
                $this->loyaltyStateDefault->name[(int)($language['id_lang'])] = Tools::getValue('default_loyalty_state_'.(int)($language['id_lang']));
                $this->loyaltyStateValidation->name[(int)($language['id_lang'])] = Tools::getValue('validation_loyalty_state_'.(int)($language['id_lang']));
                $this->loyaltyStateCancel->name[(int)($language['id_lang'])] = Tools::getValue('cancel_loyalty_state_'.(int)($language['id_lang']));
                $this->loyaltyStateConvert->name[(int)($language['id_lang'])] = Tools::getValue('convert_loyalty_state_'.(int)($language['id_lang']));
                $this->loyaltyStateNoneAward->name[(int)($language['id_lang'])] = Tools::getValue('none_award_loyalty_state_'.(int)($language['id_lang']));
                $this->loyaltyStateRefund->name[(int)($language['id_lang'])] = Tools::getValue('refund_loyalty_state_' .(int)($language['id_lang']));
                $this->loyaltyStatePartialRefund->name[(int)($language['id_lang'])] = Tools::getValue('partialrefund_loyalty_state_' .(int)($language['id_lang']));
                $this->loyaltyStateFullExchange->name[(int)($language['id_lang'])] = Tools::getValue('fullexchange_loyalty_state_' .(int)($language['id_lang']));
                $this->loyaltyStatePartialExchange->name[(int)($language['id_lang'])] = Tools::getValue('partialexchange_loyalty_state_' .(int)($language['id_lang']));
            }

            if (empty($arrayVoucherDetails[$defaultLanguage])) {
                $arrayVoucherDetails[$defaultLanguage] = ' ';
            }

            Configuration::updateValue('PS_LOYALTY_VOUCHER_DETAILS', $arrayVoucherDetails);

            if (empty($this->loyaltyStateDefault->name[$defaultLanguage])) {
                $this->loyaltyStateDefault->name[$defaultLanguage] = ' ';
            }

            $this->loyaltyStateDefault->save();

            if (empty($this->loyaltyStateValidation->name[$defaultLanguage])) {
                $this->loyaltyStateValidation->name[$defaultLanguage] = ' ';
            }

            $this->loyaltyStateValidation->save();

            if (empty($this->loyaltyStateCancel->name[$defaultLanguage])) {
                $this->loyaltyStateCancel->name[$defaultLanguage] = ' ';
            }

            $this->loyaltyStateCancel->save();

            if (empty($this->loyaltyStateConvert->name[$defaultLanguage])) {
                $this->loyaltyStateConvert->name[$defaultLanguage] = ' ';
            }

            $this->loyaltyStateConvert->save();

            if (empty($this->loyaltyStateNoneAward->name[$defaultLanguage]))
                $this->loyaltyStateNoneAward->name[$defaultLanguage] = ' ';
            $this->loyaltyStateNoneAward->save();

            if (empty($this->loyaltyStateRefund->name[$defaultLanguage])) {
                $this->loyaltyStateRefund->name[$defaultLanguage] = ' ';
            }

            $this->loyaltyStateRefund->save();

            if (empty($this->loyaltyStatePartialExchange->name[$defaultLanguage])) {
                $this->loyaltyStatePartialExchange->name[$defaultLanguage] = ' ';
            }

            $this->loyaltyStatePartialExchange->save();

            if (empty($this->loyaltyStateFullExchange->name[$defaultLanguage])) {
                $this->loyaltyStateFullExchange->name[$defaultLanguage] = ' ';
            }

            $this->loyaltyStateFullExchange->save();
            $this->_html .= $this->displayConfirmation($this->l('Configuration updated.'));
        } else {
            $errors = '';

            foreach ($this->_errors AS $error) {
                $errors .= $error.'<br />';
            }

            echo $this->displayError($errors);
        }

    }

    private function voucherCategories($categories)
    {
        $cat = '';
        if ($categories)
            foreach ($categories AS $category)
                $cat .= $category.',';
        return rtrim($cat, ',');
    }

    private function _postValidation()
    {
        $this->_errors = array();
        if (!(int)(Tools::getValue('order_quantity')) OR Tools::getValue('order_quantity') < 0)
            $this->_errors[] = $this->displayError($this->l('Order quantity is required/invalid.'));
        if (!is_array(Tools::getValue('discount_value')))
            $this->_errors[] = $this->displayError($this->l('Discount value is invalid.'));
        foreach (Tools::getValue('discount_value') AS $id_currency => $discount_value)
            if ($discount_value == '')
                $this->_errors[] = $this->displayError($this->l('Discount value for the currency #').$id_currency.$this->l(' is empty.'));
            elseif (!Validate::isUnsignedFloat($discount_value))
                $this->_errors[] = $this->displayError($this->l('Discount value for the currency #').$id_currency.$this->l(' is invalid.'));
        if (!(int)(Tools::getValue('discount_type')) OR Tools::getValue('discount_type') < 1 OR Tools::getValue('discount_type') > 2)
            $this->_errors[] = $this->displayError($this->l('Discount type is required/invalid.'));
        if (!(int)(Tools::getValue('nb_friends')) OR Tools::getValue('nb_friends') < 0)
            $this->_errors[] = $this->displayError($this->l('Number of friends is required/invalid.'));
        if (!(int)(Tools::getValue('discount_value_percentage')) OR (int)(Tools::getValue('discount_value_percentage')) < 0 OR (int)(Tools::getValue('discount_value_percentage')) > 100)
            $this->_errors[] = $this->displayError($this->l('Discount percentage is required/invalid.'));
    }

    private function _writeXml()
    {
        $forbiddenKey = array('submitUpdate'); // Forbidden key

        // Generate new XML data
        $newXml = '<'.'?xml version=\'1.0\' encoding=\'utf-8\' ?>'."\n";
        $newXml .= '<referralprogram>'."\n";
        $newXml .= "\t".'<body>';
        // Making body data
        foreach ($_POST AS $key => $field)
            if ($line = $this->putContent($newXml, $key, $field, $forbiddenKey, 'body'))
                $newXml .= $line;
        $newXml .= "\n\t".'</body>'."\n";
        $newXml .= '</referralprogram>'."\n";

        /* write it into the editorial xml file */
        if ($fd = @fopen($this->_xmlFile, 'w'))
        {
            if (!@fwrite($fd, $newXml))
                $this->_html .= $this->displayError($this->l('Unable to write to the xml file.'));
            if (!@fclose($fd))
                $this->_html .= $this->displayError($this->l('Cannot close the xml file.'));
        }
        else
            $this->_html .= $this->displayError($this->l('Unable to update the xml file. Please check the xml file\'s writing permissions.'));
    }

    public function putContent($xml_data, $key, $field, $forbidden, $section)
    {
        foreach ($forbidden AS $line)
            if ($key == $line)
                return 0;
        if (!preg_match('/^'.$section.'_/i', $key))
            return 0;
        $key = preg_replace('/^'.$section.'_/i', '', $key);
        $field = Tools::htmlentitiesDecodeUTF8(htmlspecialchars($field));
        if (!$field)
            return 0;
        return ("\n\t\t".'<'.$key.'><![CDATA['.$field.']]></'.$key.'>');
    }

    public function getContent()
    {
        if (Tools::isSubmit('submitReferralProgram'))
        {
            $this->_postValidation();
            if (!sizeof($this->_errors))
                $this->_postProcess();
            else
                foreach ($this->_errors AS $err)
                    $this->_html .= '<div class="errmsg">'.$err.'</div>';
        }
        elseif (Tools::isSubmit('submitText'))
        {
            foreach ($_POST AS $key => $value)
                if (!is_array(Tools::getValue($key)) && !Validate::isString(Tools::getValue($key)))
                {
                    $this->_html .= $this->displayError($this->l('Invalid html field, javascript is forbidden'));
                    $this->_displayForm();
                    return $this->_html;
                }
            $this->_writeXml();
        }

        $this->_html .= '<h2>'.$this->displayName.'</h2>';
        $this->_displayForm();
        $this->_displayFormRules();
        return $this->_html;
    }

    private function _displayForm()
    {
        global $cookie;
        $divLangName = 'cpara¤dd';
        $currencies = Currency::getCurrencies();

        $this->instanceDefaultStates();
        //$this->_postProcess();
        $categories = Category::getCategories((int)($cookie->id_lang));
        $order_states = OrderState::getOrderStates((int)$cookie->id_lang);
        $currencytr = new Currency((int)(Configuration::get('PS_CURRENCY_DEFAULT')));
        $defaultLanguage = (int)(Configuration::get('PS_LANG_DEFAULT'));
        $languages = Language::getLanguages(true);
        $languageIds = 'voucher_details¤default_loyalty_state¤none_award_loyalty_state¤convert_loyalty_state¤validation_loyalty_state¤cancel_loyalty_state';

        include_once(dirname(__FILE__).'/ReferralProgramModule.php');
        //$sponsors = ReferralProgramModule::sponsorDetails();
        $this->_html .= '
            <fieldset class="width3">
                <legend>'.$this->l('Top Referrers').'</legend>

                <form method="post" action="'.$_SERVER['REQUEST_URI'].'">
                <label>'.$this->l('Select the month').'</label>
                <div class="margin-form" style="margin-top:10px">
                <select id="sponsor_month" name="sponsor_month">
                    <option value="1">Jan</option>
                    <option value="2">Feb</option>
                    <option value="3">Mar</option>
                    <option value="4">Apr</option>
                    <option value="5">May</option>
                    <option value="6">Jun</option>
                    <option value="7">Jul</option>
                    <option value="8">Aug</option>
                    <option value="9">Sep</option>
                    <option value="10">Oct</option>
                    <option value="11">Nov</option>
                    <option value="12">Dec</option>
                </select>
                <input class="button" type="submit"  value="Submit Month" name="submitMonth">
                </div>
                </form>';
        if(Tools::getValue('sponsor_month'))
            $month = Tools::getValue('sponsor_month');
        else
            $month = date("n");

        if($month == 1)
            $month_name = "Jan";
        else if($month == 2)
            $month_name = "Feb";
        else if($month == 3)
            $month_name = "Mar";
        else if($month == 4)
            $month_name = "Apr";
        else if($month == 5)
            $month_name = "May";
        else if($month == 6)
            $month_name = "Jun";
        else if($month == 7)
            $month_name = "Jul";
        else if($month == 8)
            $month_name = "Aug";
        else if($month == 9)
            $month_name = "Sep";
        else if($month == 10)
            $month_name = "Oct";
        else if($month == 11)
            $month_name = "Nov";
        else if($month == 12)
            $month_name = "Dec";

        $sponsors = ReferralProgramModule::sponsorDetails($month);
        $this->_html .= '<h3>'.$this->l('Top referrer for the month '.$month_name).'</h3>
            <table class="table tableDnD"><tr class="nodrag nodrop"><th> Sponsor </th><th> No.of person referred </th><th> Facebook </th><th> Twitter </th><th> Direct </th></tr>';
        foreach ($sponsors as $sponsordetail){
            $sponsors_facebook = 0; $sponsors_twitter = 0; $sponsors_direct = 0;
            $sponsors_facebook = ReferralProgramModule::sponsorDetailsCount($sponsordetail["id_sponsor"],'1',$month);
            $sponsors_twitter = ReferralProgramModule::sponsorDetailsCount($sponsordetail["id_sponsor"],'2',$month);
            $sponsors_direct = ReferralProgramModule::sponsorDetailsCount($sponsordetail["id_sponsor"],'3',$month);
        $this->_html .= '
            <tr><td>'.$sponsordetail["email"].'</td><td>'.$sponsordetail["sponsorcount"].'</td><td>'.$sponsors_facebook["individual_count"].'</td><td>'.$sponsors_twitter["individual_count"].'</td><td>'.$sponsors_direct["individual_count"].'</td></tr>';
        }
        $this->_html .= '
                </table>
            </fieldset><br/>    ';
        $this->_html .= '
        <form action="'.$_SERVER['REQUEST_URI'].'" method="post">
        <fieldset class="width3">
            <legend><img src="'._PS_ADMIN_IMG_.'prefs.gif" alt="'.$this->l('Settings').'" />'.$this->l('Settings').'</legend>
            <p>
                <label class="t" for="order_quantity">'.$this->l('Minimum number of orders a sponsored friend must place to get their voucher:').'</label>
                <input type="text" name="order_quantity" id="order_quantity" value="'.Tools::getValue('order_quantity', Configuration::get('REFERRAL_ORDER_QUANTITY')).'" style="width: 50px; text-align: right;" />
            </p>
            <p>
                <label class="t" for="nb_friends">'.$this->l('Number of friends in the referral program invitation form (customer account, referral program section):').'</label>
                <input type="text" name="nb_friends" id="nb_friends" value="'.Tools::getValue('nb_friends', Configuration::get('REFERRAL_NB_FRIENDS')).'" style="width: 50px; text-align: right;" />
            </p>
            <p>
                <label class="t">'.$this->l('Voucher type:').'</label>
                <input type="radio" name="discount_type" id="discount_type1" value="1" onclick="$(\'#voucherbycurrency\').hide(); $(\'#voucherbypercentage\').show();" '.(Tools::getValue('discount_type', Configuration::get('REFERRAL_DISCOUNT_TYPE')) == 1 ? 'checked="checked"' : '').' />
                <label class="t" for="discount_type1">'.$this->l('Voucher offering a percentage').'</label>
                &nbsp;
                <input type="radio" name="discount_type" id="discount_type2" value="2" onclick="$(\'#voucherbycurrency\').show(); $(\'#voucherbypercentage\').hide();" '.(Tools::getValue('discount_type', Configuration::get('REFERRAL_DISCOUNT_TYPE')) == 2 ? 'checked="checked"' : '').' />
                <label class="t" for="discount_type2">'.$this->l('Voucher offering a fixed amount (by currency)').'</label>
            </p>
            <p id="voucherbypercentage"'.(Configuration::get('REFERRAL_DISCOUNT_TYPE') == 2 ? ' style="display: none;"' : '').'><label class="t">'.$this->l('Percentage:').'</label> <input type="text" id="discount_value_percentage" name="discount_value_percentage" value="'.Tools::getValue('discount_value_percentage', Configuration::get('REFERRAL_PERCENTAGE')).'" style="width: 50px; text-align: right;" /> %</p>
            <table id="voucherbycurrency" cellpadding="5" style="border: 1px solid #BBB;'.(Configuration::get('REFERRAL_DISCOUNT_TYPE') == 1 ? ' display: none;' : '').'" border="0">
                <tr>
                    <th style="width: 80px;">'.$this->l('Currency').'</th>
                    <th>'.$this->l('Voucher amount').'</th>
                </tr>';

        foreach ($currencies AS $currency)
            $this->_html .= '
            <tr>
                <td>'.(Configuration::get('PS_CURRENCY_DEFAULT') == $currency['id_currency'] ? '<span style="font-weight: bold;">' : '').htmlentities($currency['name'], ENT_NOQUOTES, 'utf-8').(Configuration::get('PS_CURRENCY_DEFAULT') == $currency['id_currency'] ? '<span style="font-weight: bold;">' : '').'</td>
                <td><input type="text" name="discount_value['.(int)($currency['id_currency']).']" id="discount_value['.(int)($currency['id_currency']).']" value="'.Tools::getValue('discount_value['.(int)($currency['id_currency']).']', Configuration::get('REFERRAL_DISCOUNT_VALUE_'.(int)($currency['id_currency']))).'" style="width: 50px; text-align: right;" /> '.$currency['sign'].'</td>
            </tr>';

        $this->_html .= '
        </table>
            <p>
                 <div style="float: left"><label class="t" for="discount_description">'.$this->l('Voucher description:').'</label></div>';
            //$defaultLanguage = (int)(Configuration::get('PS_LANG_DEFAULT'));
            //$languages = Language::getLanguages(false);

            foreach ($languages AS $language)
                $this->_html .= '
                <div id="dd_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left; margin-left: 4px;">
                    <input type="text" name="discount_description['.$language['id_lang'].']" id="discount_description['.$language['id_lang'].']" value="'.(isset($_POST['discount_description'][(int)($language['id_lang'])]) ? $_POST['discount_description'][(int)($language['id_lang'])] : $this->_configuration['REFERRAL_DISCOUNT_DESCRIPTION'][(int)($language['id_lang'])]).'" style="width: 200px;" />
                </div>';
            $this->_html .= $this->displayFlags($languages, $defaultLanguage, $divLangName, 'dd', true).'</p>';
            $this->_html .= '
            <script type="text/javascript">
                id_language = Number('.$defaultLanguage.');
            </script>
            <div class="clear"></div>
            <h2>'.$this->l('Loyalty Program').'</h2>
            <fieldset>
                <legend>'.$this->l('Settings').'</legend>

                <label>'.$this->l('Ratio').'</label>
                <div class="margin-form">
                    <input type="text" size="2" id="point_rate" name="point_rate" value="'.(float)(Configuration::get('PS_LOYALTY_POINT_RATE')).'" /> '.$currencytr->sign.'
                    <label for="point_rate" class="t"> = '.$this->l('1 reward point').'.</label>
                    <br />
                    <label for="point_value" class="t">'.$this->l('1 point = ').'</label>
                    <input type="text" size="2" name="point_value" id="point_value" value="'.(float)(Configuration::get('PS_LOYALTY_POINT_VALUE')).'" /> '.$currencytr->sign.'
                    <label for="point_value" class="t">'.$this->l('for the discount').'.</label>
                </div>
                <div class="clear"></div>
                <label>'.$this->l('Voucher details').'</label>
                <div class="margin-form">';
        foreach ($languages as $language)
        $this->_html .= '
                    <div id="voucher_details_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">
                        <input size="33" type="text" name="voucher_details_'.$language['id_lang'].'" value="'.Configuration::get('PS_LOYALTY_VOUCHER_DETAILS', (int)($language['id_lang'])).'" />
                    </div>';
        $this->_html .= $this->displayFlags($languages, $defaultLanguage, $languageIds, 'voucher_details', true);
        $this->_html .= '   </div>
                <div class="clear" style="margin-top: 20px"></div>
                <label>'.$this->l('Minimum amount in which the voucher can be used').'</label>
                <div class="margin-form">
                    <input type="text" size="2" name="minimal" value="'.(float)(Configuration::get('PS_LOYALTY_MINIMAL')).'" /> '.$currencytr->sign.'
                </div>
                <div class="clear" style="margin-top: 20px"></div>
                <label>'.$this->l('Give points on discounted products').' </label>
                <div class="margin-form">
                    <input type="radio" name="PS_LOYALTY_NONE_AWARD" id="PS_LOYALTY_NONE_AWARD_on" value="1" '.(Configuration::get('PS_LOYALTY_NONE_AWARD') ? 'checked="checked" ' : '').'/>
                    <label class="t" for="PS_LOYALTY_NONE_AWARD_on"><img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Yes').'" /></label>
                    <input type="radio" name="PS_LOYALTY_NONE_AWARD" id="PS_LOYALTY_NONE_AWARD_off" value="0" '.(!Configuration::get('PS_LOYALTY_NONE_AWARD') ? 'checked="checked" ' : '').'/>
                    <label class="t" for="PS_LOYALTY_NONE_AWARD_off"><img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('No').'" /></label>
                    </div>
                <div class="clear"></div>
                <label>'.$this->l('Points are awarded when the order is').'</label>
                <div class="margin-form" style="margin-top:10px">
                    <select id="id_order_state_validation" name="id_order_state_validation">';
        foreach ($order_states AS $order_state)
        {
            $this->_html .= '<option value="' . $order_state['id_order_state'] . '" style="background-color:' . $order_state['color'] . ';"';
            if ((int)($this->loyaltyStateValidation->id_order_state) == $order_state['id_order_state'] )
                $this->_html .= ' selected="selected"';
            $this->_html .= '>' . $order_state['name'] . '</option>';
        }
        $this->_html .= '</select>
                </div>
                <div class="clear"></div>
                <label>'.$this->l('Points are cancelled when the order is(Cancel)').'</label>
                <div class="margin-form" style="margin-top:10px">
                    <select id="id_order_state_cancel" name="id_order_state_cancel">';
        foreach ($order_states AS $order_state)
        {
            $this->_html .= '<option value="' . $order_state['id_order_state'] . '" style="background-color:' . $order_state['color'] . ';"';
            if ((int)($this->loyaltyStateCancel->id_order_state) == $order_state['id_order_state'] )
                $this->_html .= ' selected="selected"';
            $this->_html .= '>' . $order_state['name'] . '</option>';
        }

        $this->_html .= '</select>
                </div>
                <div class="clear"></div>
                <label>' . $this->l('Points are canceled when the order is(Refund)') . '</label>
                <div class="margin-form" style="margin-top:10px">
                <select id="id_order_state_refund" name="id_order_state_refund">';
        foreach ($order_states as $order_state)
        {
            $this->_html .= '<option value="' . $order_state['id_order_state'] . '" style="background-color:' . $order_state['color'] . ';"';
            if (intval($this->loyaltyStateRefund->id_order_state) == $order_state['id_order_state'])
                $this->_html .= ' selected="selected"';
            $this->_html .= '>' . $order_state['name'] . '</option>';
        }

        $this->_html .= '</select>
                </div>
                <div class="clear"></div>
                <label>' . $this->l('Points are canceled when the order is(Partial Refund)') . '</label>
                <div class="margin-form" style="margin-top:10px">
                <select id="id_order_state_partialrefund" name="id_order_state_partialrefund">';
        foreach ($order_states as $order_state)
        {
            $this->_html .= '<option value="' . $order_state['id_order_state'] . '" style="background-color:' . $order_state['color'] . ';"';
            if (intval($this->loyaltyStatePartialRefund->id_order_state) == $order_state['id_order_state'])
                $this->_html .= ' selected="selected"';
            $this->_html .= '>' . $order_state['name'] . '</option>';
        }
                $this->_html .= '</select>
                </div>
                <div class="clear"></div>
                <label>' . $this->l('Points are canceled when the order is(Partial Exchange)') . '</label>
                <div class="margin-form" style="margin-top:10px">
                <select id="id_order_state_partialexchange" name="id_order_state_partialexchange">';
        foreach ($order_states as $order_state)
        {
            $this->_html .= '<option value="' . $order_state['id_order_state'] . '" style="background-color:' . $order_state['color'] . ';"';
            if (intval($this->loyaltyStatePartialExchange->id_order_state) == $order_state['id_order_state'])
                $this->_html .= ' selected="selected"';
            $this->_html .= '>' . $order_state['name'] . '</option>';
        }
                $this->_html .= '</select>
                </div>
                <div class="clear"></div>
                <label>' . $this->l('Points are canceled when the order is(Full Exchange)') . '</label>
                <div class="margin-form" style="margin-top:10px">
                <select id="id_order_state_fullexchange" name="id_order_state_fullexchange">';
        foreach ($order_states as $order_state)
        {
            $this->_html .= '<option value="' . $order_state['id_order_state'] . '" style="background-color:' . $order_state['color'] . ';"';
            if (intval($this->loyaltyStateFullExchange->id_order_state) == $order_state['id_order_state'])
                $this->_html .= ' selected="selected"';
            $this->_html .= '>' . $order_state['name'] . '</option>';
        }

        $this->_html .= '</select>
                </div>
                <div class="clear"></div>
                <label>'.$this->l('Vouchers created by the loyalty system can be used in the following categories :').'</label>';
        $index = explode(',', Configuration::get('PS_LOYALTY_VOUCHER_CATEGORY'));
        $indexedCategories =  isset($_POST['categoryBox']) ? $_POST['categoryBox'] : $index;
        // Translations are not automatic for the moment ;)
        $trads = array(
             'Home' => $this->l('Home'),
             'selected' => $this->l('selected'),
             'Collapse All' => $this->l('Collapse All'),
             'Expand All' => $this->l('Expand All'),
             'Check All' => $this->l('Check All'),
             'Uncheck All'  => $this->l('Uncheck All')
        );
        $this->_html .= '<div class="margin-form">'.Helper::renderAdminCategorieTree($trads, $indexedCategories).'</div>';
         $this->_html .= '
                <p style="padding-left:200px;">'.$this->l('Mark the box(es) of categories in which loyalty vouchers are usable.').'</p>
                <div class="clear"></div>
                <h3 style="margin-top:20px">'.$this->l('Loyalty points progression').'</h3>
                <label>'.$this->l('Initial').'</label>
                <div class="margin-form">';
        foreach ($languages as $language)
            $this->_html .= '
                    <div id="default_loyalty_state_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">
                        <input size="33" type="text" name="default_loyalty_state_'.$language['id_lang'].'" value="'.(isset($this->loyaltyStateDefault->name[(int)($language['id_lang'])]) ? $this->loyaltyStateDefault->name[(int)($language['id_lang'])] : $this->loyaltyStateDefault->name[(int)$defaultLanguage]).'" />
                    </div>';
        $this->_html .= $this->displayFlags($languages, $defaultLanguage, $languageIds, 'default_loyalty_state', true);
        $this->_html .= '   </div>
                <div class="clear"></div>
                <label>'.$this->l('Unavailable').'</label>
                <div class="margin-form">';
        foreach ($languages as $language)
            $this->_html .= '
                    <div id="none_award_loyalty_state_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">
                        <input size="33" type="text" name="none_award_loyalty_state_'.$language['id_lang'].'" value="'.(isset($this->loyaltyStateNoneAward->name[(int)($language['id_lang'])]) ? $this->loyaltyStateNoneAward->name[(int)($language['id_lang'])] : $this->loyaltyStateNoneAward->name[(int)$defaultLanguage]).'" />
                    </div>';
        $this->_html .= $this->displayFlags($languages, $defaultLanguage, $languageIds, 'none_award_loyalty_state', true);
        $this->_html .= '   </div>
                <div class="clear"></div>
                <label>'.$this->l('Converted').'</label>
                <div class="margin-form">';
        foreach ($languages as $language)
            $this->_html .= '
                    <div id="convert_loyalty_state_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">
                        <input size="33" type="text" name="convert_loyalty_state_'.$language['id_lang'].'" value="'.(isset($this->loyaltyStateConvert->name[(int)($language['id_lang'])]) ? $this->loyaltyStateConvert->name[(int)($language['id_lang'])] : $this->loyaltyStateConvert->name[(int)$defaultLanguage]).'" />
                    </div>';
        $this->_html .= $this->displayFlags($languages, $defaultLanguage, $languageIds, 'convert_loyalty_state', true);
        $this->_html .= '   </div>
                <div class="clear"></div>
                <label>'.$this->l('Validation').'</label>
                <div class="margin-form">';
        foreach ($languages as $language)
            $this->_html .= '
                    <div id="validation_loyalty_state_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">
                        <input size="33" type="text" name="validation_loyalty_state_'.$language['id_lang'].'" value="'.(isset($this->loyaltyStateValidation->name[(int)($language['id_lang'])]) ? $this->loyaltyStateValidation->name[(int)($language['id_lang'])] : $this->loyaltyStateValidation->name[(int)$defaultLanguage]).'" />
                    </div>';
        $this->_html .= $this->displayFlags($languages, $defaultLanguage, $languageIds, 'validation_loyalty_state', true);
        $this->_html .= '   </div>
                <div class="clear"></div>
                <label>'.$this->l('Cancelled').'</label>
                <div class="margin-form">';
        foreach ($languages as $language)
            $this->_html .= '
                    <div id="cancel_loyalty_state_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">
                        <input size="33" type="text" name="cancel_loyalty_state_'.$language['id_lang'].'" value="'.(isset($this->loyaltyStateCancel->name[(int)($language['id_lang'])]) ? $this->loyaltyStateCancel->name[(int)($language['id_lang'])] : $this->loyaltyStateCancel->name[(int)$defaultLanguage]).'" />
                    </div>';
        $this->_html .= $this->displayFlags($languages, $defaultLanguage, $languageIds, 'cancel_loyalty_state', true);

        $this->_html .= '   </div>
                <div class="clear"></div>
                <label>' . $this->l('Refund') . '</label>
                <div class="margin-form">';
        foreach ($languages as $language)
            $this->_html.= '
                    <div id="refund_loyalty_state_' . $language['id_lang'] . '" style="display: ' . ($language['id_lang'] == $defaultLanguage ? 'block' : 'none') . '; float: left;">
                        <input size="33" type="text" name="refund_loyalty_state_' . $language['id_lang'] . '" value="' . $this->loyaltyStateRefund->name[intval($language['id_lang'])] . '" />
                    </div>';
        $this->_html.= $this->displayFlags($languages, $defaultLanguage, $languageIds, 'refund_loyalty_state', true);

        $this->_html .= '   </div>
                <div class="clear"></div>
                <label>' . $this->l('Partial Refund') . '</label>
                <div class="margin-form">';
        foreach ($languages as $language)
            $this->_html.= '
                    <div id="partialrefund_loyalty_state_' . $language['id_lang'] . '" style="display: ' . ($language['id_lang'] == $defaultLanguage ? 'block' : 'none') . '; float: left;">
                        <input size="33" type="text" name="partialrefund_loyalty_state_' . $language['id_lang'] . '" value="' . $this->loyaltyStatePartialRefund->name[intval($language['id_lang'])] . '" />
                    </div>';
        $this->_html.= $this->displayFlags($languages, $defaultLanguage, $languageIds, 'partialrefund_loyalty_state', true);


            $this->_html .= '
            <div class="clear center"><input class="button" style="margin-top: 10px" name="submitReferralProgram" id="submitReferralProgram" value="'.$this->l('Update settings').'" type="submit" /></div>
        </fieldset></form>
        <br/>';
    }

    public static function recurseCategoryForInclude($id_obj, $indexedCategories, $categories, $current, $id_category = 1, $id_category_default = NULL, $has_suite = array())
    {
        global $done;
        static $irow;
        $html = '';

        if (!isset($done[$current['infos']['id_parent']]))
            $done[$current['infos']['id_parent']] = 0;
        $done[$current['infos']['id_parent']] += 1;

        $todo = sizeof($categories[$current['infos']['id_parent']]);
        $doneC = $done[$current['infos']['id_parent']];

        $level = $current['infos']['level_depth'] + 1;

        $html .= '
        <tr class="'.($irow++ % 2 ? 'alt_row' : '').'">
            <td>
                <input type="checkbox" name="categoryBox[]" class="categoryBox'.($id_category_default == $id_category ? ' id_category_default' : '').'" id="categoryBox_'.$id_category.'" value="'.$id_category.'"'.((in_array($id_category, $indexedCategories) OR ((int)(Tools::getValue('id_category')) == $id_category AND !(int)($id_obj))) ? ' checked="checked"' : '').' />
            </td>
            <td>
                '.$id_category.'
            </td>
            <td>';
            for ($i = 2; $i < $level; $i++)
                $html .= '<img src="../img/admin/lvl_'.$has_suite[$i - 2].'.gif" alt="" style="vertical-align: middle;"/>';
            $html .= '<img src="../img/admin/'.($level == 1 ? 'lv1.gif' : 'lv2_'.($todo == $doneC ? 'f' : 'b').'.gif').'" alt="" style="vertical-align: middle;"/> &nbsp;
            <label for="categoryBox_'.$id_category.'" class="t">'.stripslashes($current['infos']['name']).'</label></td>
        </tr>';

        if ($level > 1)
            $has_suite[] = ($todo == $doneC ? 0 : 1);
        if (isset($categories[$id_category]))
            foreach ($categories[$id_category] AS $key => $row)
                if ($key != 'infos')
                    $html .= self::recurseCategoryForInclude($id_obj, $indexedCategories, $categories, $categories[$id_category][$key], $key, $id_category_default, $has_suite);
        return $html;
    }

    private function _displayFormRules()
    {
        global $cookie;

        // Languages preliminaries
        $defaultLanguage = (int)(Configuration::get('PS_LANG_DEFAULT'));
        $languages = Language::getLanguages();
        $iso = Language::getIsoById($defaultLanguage);
        $divLangName = 'cpara¤dd';

        // xml loading
        $xml = false;
        if (file_exists($this->_xmlFile))
            if (!$xml = @simplexml_load_file($this->_xmlFile))
                $this->_html .= $this->displayError($this->l('Your text is empty.'));

        // TinyMCE
        global $cookie;
        $iso = Language::getIsoById((int)($cookie->id_lang));
        $isoTinyMCE = (file_exists(_PS_ROOT_DIR_.'/js/tiny_mce/langs/'.$iso.'.js') ? $iso : 'en');
        $ad = dirname($_SERVER["PHP_SELF"]);
        echo '
            <script type="text/javascript">
            var iso = \''.$isoTinyMCE.'\' ;
            var pathCSS = \''._THEME_CSS_DIR_.'\' ;
            var ad = \''.$ad.'\' ;
            </script>
            <script type="text/javascript" src="'.__PS_BASE_URI__.'js/tiny_mce/tiny_mce.js"></script>
            <script type="text/javascript" src="'.__PS_BASE_URI__.'js/tinymce.inc.js"></script>
            <script language="javascript">id_language = Number('.$defaultLanguage.');</script>
        <form method="post" action="'.$_SERVER['REQUEST_URI'].'" enctype="multipart/form-data">
            <fieldset>
                <legend><img src="'.$this->_path.'logo.gif" alt="" title="" /> '.$this->l('Referral program rules').'</legend>';
        foreach ($languages AS $language)
        {
            $this->_html .= '
            <div id="cpara_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').';float: left;">
                <textarea class="rte" cols="120" rows="25" id="body_paragraph_'.$language['id_lang'].'" name="body_paragraph_'.$language['id_lang'].'">'.($xml ? stripslashes(htmlspecialchars($xml->body->{'paragraph_'.$language['id_lang']})) : '').'</textarea>
            </div>';
        }
        $this->_html .= $this->displayFlags($languages, $defaultLanguage, $divLangName, 'cpara', true);

        $this->_html .= '
                <div class="clear center"><input type="submit" name="submitText" value="'.$this->l('Update text').'" class="button" style="margin-top: 10px" /></div>
            </fieldset>
        </form>';
    }

    public function sponsor($month)
    {
        $sponsors = ReferralProgramModule::sponsorDetails($month);
        $sponsors_email = array();
        $sponsors_facebook = array();
        $sponsors_twitter = array();
        $sponsors_direct = array();
        foreach ($sponsors as $sponsordetail)
        {
            $sponsor_values[$sponsordetail['id_sponsor']]['email'] = $sponsordetail['email'];
            $sponsor_values[$sponsordetail['id_sponsor']]['name'] = $sponsordetail['firstname'].' '.$sponsordetail['lastname'];
            $sponsor_values[$sponsordetail['id_sponsor']]['total'] = $sponsordetail['sponsorcount'];
            $sponsor_values[$sponsordetail['id_sponsor']]['facebook'] = ReferralProgramModule::sponsorDetailsCount($sponsordetail['id_sponsor'],'1',$month);
            $sponsor_values[$sponsordetail['id_sponsor']]['twitter'] = ReferralProgramModule::sponsorDetailsCount($sponsordetail['id_sponsor'],'2',$month);
            $sponsor_values[$sponsordetail['id_sponsor']]['direct'] = ReferralProgramModule::sponsorDetailsCount($sponsordetail['id_sponsor'],'3',$month);
        }
        return $sponsor_values;
    }
    public function hookReferralProgram()
    {
        include_once(dirname(__FILE__).'/ReferralProgramModule.php');
        global $cookie,$smarty;

        if (!$cookie->isLogged())
            Tools::redirect('authentication.php?back=identity.php');

        $activeTab = 'sponsor';
        $error = false;

        /* top referrer - start */

        if($cookie->id_lang == 4)
            $month_name = array('Ocak','Şubat','Mart','Nisan','Mayıs','Haziran','Temmuz','Ağustos','Eylül','Ekim','Kasım','Aralık');
        else
            $month_name = array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');

        $month = date("n");
//      $month_name = date("M");
        $year = date("Y");
        $smarty->assign(array(
                    'sponsor_values' => self::sponsor($month),
                    'month'          => $month_name[$month-1],
                    'year'           => $year
            ));


        if(date("n") == 1)
            $previous_month = 12;
        else
            $previous_month = date("n")-1;

//      if($previous_month == 1)
//          $month_name = "Jan";
//      else if($previous_month == 2)
//          $month_name = "Feb";
//      else if($previous_month == 3)
//          $month_name = "Mar";
//      else if($previous_month == 4)
//          $month_name = "Apr";
//      else if($previous_month == 5)
//          $month_name = "May";
//      else if($previous_month == 6)
//          $month_name = "Jun";
//      else if($previous_month == 7)
//          $month_name = "Jul";
//      else if($previous_month == 8)
//          $month_name = "Aug";
//      else if($previous_month == 9)
//          $month_name = "Sep";
//      else if($previous_month == 10)
//          $month_name = "Oct";
//      else if($previous_month == 11)
//          $month_name = "Nov";
//      else if($previous_month == 12)
//          $month_name = "Dec";

        $smarty->assign(array(
                    'previous_sponsor_values' => self::sponsor($previous_month),
                    'previous_month'             => $month_name[$previous_month-1]
            ));


        /* top referrer - end */

        // Mailing invitation to friend sponsor
        $invitation_sent = false;
        $nbInvitation = 0;
        if (Configuration::get('PS_CIPHER_ALGORITHM'))
            $cipherTool = new Rijndael(_RIJNDAEL_KEY_, _RIJNDAEL_IV_);
        else
            $cipherTool = new Blowfish(_COOKIE_KEY_, _COOKIE_IV_);
        //$blowfish = new Blowfish(_COOKIE_KEY_, _COOKIE_IV_);
        $ref_id = urlencode($cipherTool->encrypt($cookie->id_customer));
        setcookie('bu_refid', $ref_id, time()+(3600), '/', '', 0); //expires in one hour
        setcookie('bu_refname', strval($cookie->customer_firstname).' '.strval($cookie->customer_lastname), time()+(3600), '/', '', 0);

        if (Tools::isSubmit('submitSponsorFriends') AND Tools::getValue('friendsEmail') AND sizeof($friendsEmail = Tools::getValue('friendsEmail')) >= 1)
        {
//          print_r(Tools::getValue('friendsEmail'));
//      print_r(Tools::getValue('friendsFirstName'));
            $activeTab = 'sponsor';
            if (Tools::getValue('conditionsValided'))
            {
                $error = 'conditions not valided';
            }
            else
            {
                $friendsFirstName = Tools::getValue('friendsFirstName');
                $friendMessage = Tools::getValue('friendMessage'); //$_POST['friendMessage'];
                $mails_exists = array();
                        $counter = 0;
                foreach ($friendsEmail as $key => $friendEmail)
                {
                    $friendEmail = strval($friendEmail);
                    $friendFirstName = strval($friendsFirstName[$key]);
                    if (empty($friendEmail) AND empty($friendFirstName))
                    {
                        $counter++;
                        continue;

                    }
                    elseif (empty($friendEmail) OR !Validate::isEmail($friendEmail))
                        $error = 'email invalid';
                    elseif (empty($friendFirstName) OR !Validate::isName($friendFirstName))
                        $error = 'name invalid';
                    elseif (ReferralProgramModule::isEmailExists($friendEmail) OR Customer::customerExists($friendEmail))
                    {
                        //$error = 'email exists';
                        $mails_exists[] = $friendEmail;
                    }
                    else
                    {
                        //creation of records in referralprogram database
                        $referralprogram = new ReferralProgramModule();
                        $referralprogram->id_sponsor = (int)($cookie->id_customer);
                        $referralprogram->firstname = $friendFirstName;
                        $referralprogram->lastname = 'NULL';
                        $referralprogram->email = $friendEmail;
                        if (!$referralprogram->validateFields(false))
                           $error = 'name invalid';
                        else
                        {
                            if ($referralprogram->save())
                            {   //echo "bb";
                                $vars = array(
                                        '{email}' => strval($cookie->email),
                                        '{firstname}' => strval($cookie->customer_firstname),
                                        '{email_friend}' => $friendEmail,
                                        '{firstname_friend}' => $friendFirstName,
                                        '{link}' => '?ref_by='.$ref_id.'&utm_campaign=invitefriendsvariation1&utm_medium=viral&utm_source=invitefriendssingleemail&utm_content='.$ref_id,
                                        '{message_friend}'=> strval($friendMessage)
                                        //'{discount}' => $discount,
                                );

                                Mail::Send((int)($cookie->id_lang), 'referralprogram-invitation', Mail::l('Referral Program'), $vars, $friendEmail, $friendFirstName, "invite@butigo.com" ,strval($cookie->customer_firstname).' '.strval($cookie->customer_lastname), NULL, NULL, dirname(__FILE__).'/mails/',false);
                                $invitation_sent = true;
                                $nbInvitation++;
                                $activeTab = 'pending';
                                //Tools::redirect('friends.php?invited='.$nbInvitation);
                            }
                            else
                            {
                                $error = 'cannot add friends';
                                //Tools::redirect('friends.php?error=1');
                            }
                        }
                    }
                    if ($error)
                        break;
                }
                if ($nbInvitation > 0)
                    unset($_POST);

                //Not to stop the sending of e-mails in case of doubloon
                if (sizeof($mails_exists))
                    $error = 'email exists';
            }
        }
        if($counter == 5)
        {
            $error = 'no details';
        }
//echo $str;exit;
        $customer = new Customer(intval($cookie->id_customer));
        $stats = $customer->getStats();

        $orderQuantity = intval(Configuration::get('REFERRAL_ORDER_QUANTITY'));
        //$canSendInvitations = false;
        //if (intval($stats['nb_orders']) < $orderQuantity)
        //  $canSendInvitations = true;

        // Smarty display
        $smarty->assign(array(
            'activeTab' => $activeTab,
            //'discount' => $discount
            'orderQuantity' => $orderQuantity,
            //'canSendInvitations' => $canSendInvitations,
            'nbFriends' => intval(Configuration::get('REFERRAL_NB_FRIENDS')),
            'error' => $error,
            'invitation_sent' => $invitation_sent,
            'nbInvitation' => $nbInvitation,
            'refer_id' => $ref_id,
            //'refer_link' => '?ref_by='.$ref_id,
            'refer_link' => '?ref_by='.$ref_id,
            //'has_title' =>  false ,
            'subscribeFriends' => ReferralProgramModule::getSponsorFriend(intval($cookie->id_customer), 'subscribed'),
            'mails_exists' => (isset($mails_exists) ? $mails_exists : array())
        ));

        return $this->display(__FILE__, 'referralprogram-friends.tpl');
    }

    public function hookReferralStylepoint($params)
    {
        include_once(dirname(__FILE__).'/ReferralProgramModule.php');
        include_once(dirname(__FILE__).'/LoyaltyModule.php');
        include_once(dirname(__FILE__).'/LoyaltyStateModule.php');
        global $cookie,$smarty, $currency;
        if (!$cookie->isLogged())
            Tools::redirect('authentication.php?back=identity.php');

        //Tools::addCSS(_THEME_CSS_DIR_.'modules/referralProgram/referral-invite.css' ,'all');

        $smarty->assign('page_name' , 'referralprogram-stylepoints');
        //include(dirname(__FILE__).'/../../header.php');
        $paginationParams = array();

        // get discount value (ready to display)
        //$discount = Discount::display(floatval(Configuration::get('REFERRAL_DISCOUNT_VALUE_'.intval($cookie->id_currency))), intval(Configuration::get('REFERRAL_DISCOUNT_TYPE')), new Currency($cookie->id_currency));

        $activeTab = 'pending';
        $error = false;
        // Mailing revive
        $revive_sent = false;
        $nbRevive = 0;
        if (Tools::isSubmit('revive'))
        {
            $key = Tools::getValue('friendChecked');
            //if($key==Tools::getValue('friendChecked'))
            if (Configuration::get('PS_CIPHER_ALGORITHM'))
                $cipherTool = new Rijndael(_RIJNDAEL_KEY_, _RIJNDAEL_IV_);
            else
                $cipherTool = new Blowfish(_COOKIE_KEY_, _COOKIE_IV_);
            $referralprogram = new ReferralProgramModule((int)($key));
            $vars = array(
                '{email}' => $cookie->email,
                '{lastname}' => $cookie->customer_lastname,
                '{firstname}' => $cookie->customer_firstname,
                '{email_friend}' => $referralprogram->email,
                '{lastname_friend}' => $referralprogram->lastname,
                '{firstname_friend}' => $referralprogram->firstname,
                '{link}' => '?ref_by='.urlencode($cipherTool->encrypt($referralprogram->id_sponsor))
                //'{discount}' => $discount
            );
            $referralprogram->save();
            Mail::Send((int)($cookie->id_lang), 'referralprogram-reinvite', Mail::l('Referral Program Re-Invite'), $vars, $referralprogram->email, $referralprogram->firstname.' '.$referralprogram->lastname, "invite@butigo.com",'' /*strval(Configuration::get('PS_SHOP_NAME'))*/, NULL, NULL, dirname(__FILE__).'/mails/', false);
            $revive_sent = true;
            //$nbRevive++;
        }
        $customer = new Customer(intval($cookie->id_customer));
        $stats = $customer->getStats();
        $discounts = Discount::getCustomerDiscounts(intval($cookie->id_lang), intval($cookie->id_customer), true, false);
        $nbDiscounts = 0;
        foreach ($discounts AS $discount) {
            if ($discount['quantity_for_user']) {
                $nbDiscounts++;
            }
        }

        $smarty->assign(array(
                'nbDiscounts' => intval($nbDiscounts),
                'discounts' => $discounts
            )
        );

        $smarty->assign(array(
            'totalPoints' => intval(LoyaltyModule::getValidPointsByCustomer(intval($cookie->id_customer))),
            'pendingPoints'=> intval(LoyaltyModule::getPendingPointsByCustomer(intval($cookie->id_customer))),
            'referralEmails' => ReferralProgramModule::getRefferalEmails(intval($cookie->id_customer))
        ));

        $orders = LoyaltyModule::getAllByIdCustomer(intval($cookie->id_customer), intval($cookie->id_lang), false,
            true, $params[pagination][order][itemPerPage], $params[pagination][order][pageNo]);

        $paginationParams['order'] = array(
            pageNo => $params[pagination][order][pageNo],
            itemPerPage => $params[pagination][order][itemPerPage],
            totalItem => $orders[totalItem]
        );

        unset($orders[totalItem]);

        /*Survey Vs Register*/
        $showSite = isset($cookie->show_site) AND $cookie->show_site === 1;
        if($customer->date_add >= '2012-09-17 00:00:00') {
            if (! $customer->hasCompletedSurvey() AND !$showSite) {
                $smarty->assign('no_butigim_link' , 1);
            }
        }
        /*Survey Vs Register*/

        $pendingFriends = ReferralProgramModule::getSponsorFriend(intval($cookie->id_customer), 'pending',
            true, $params[pagination][pendingFriends][itemPerPage], $params[pagination][pendingFriends][pageNo]);

        $paginationParams['pendingFriends'] = array(
            pageNo => $params[pagination][pendingFriends][pageNo],
            itemPerPage => $params[pagination][pendingFriends][itemPerPage],
            totalItem => $pendingFriends[totalItem]
        );
        unset($pendingFriends[totalItem]);


        $subscribedFriends = ReferralProgramModule::getSponsorFriend(intval($cookie->id_customer), 'subscribed',
            true, $params[pagination][subscribedFriends][itemPerPage], $params[pagination][subscribedFriends][pageNo]);

        $paginationParams['subscribedFriends'] = array(
            pageNo => $params[pagination][subscribedFriends][pageNo],
            itemPerPage => $params[pagination][subscribedFriends][itemPerPage],
            totalItem => $subscribedFriends[totalItem]
        );
        unset($subscribedFriends[totalItem]);

        // Smarty display
        $smarty->assign(array(
            'orders' => $orders,
            'activeTab' => $activeTab,
            'nbFriends' => intval(Configuration::get('REFERRAL_NB_FRIENDS')),
            'error' => $error,
            'pendingFriends' => $pendingFriends,
            'subscribeFriends' => $subscribedFriends,
            paginationParams => $paginationParams,
            'revive_sent' => $revive_sent,
            'mails_exists' => (isset($mails_exists) ? $mails_exists : array()),
			'currency_sign' => $currency->getSign()
        ));

        return Module::display(dirname(__FILE__).'/referralprogram.php', 'referralprogram-stylepoints.tpl');
    }
    /**
    * Hook call when cart created and updated
    * Display the discount name if the sponsor friend have one
    */
    public function hookShoppingCart($params)
    {
        /*include_once(dirname(__FILE__).'/ReferralProgramModule.php');

        if (!isset($params['cart']->id_customer))
            return false;
        if (!($id_referralprogram = ReferralProgramModule::isSponsorised((int)($params['cart']->id_customer), true)))
            return false;
        $referralprogram = new ReferralProgramModule($id_referralprogram);
        if (!Validate::isLoadedObject($referralprogram))
            return false;
        $discount = new Discount($referralprogram->id_discount);
        if (!Validate::isLoadedObject($discount))
            return false;
        if ($params['cart']->checkDiscountValidity($discount, $params['cart']->getDiscounts(), $params['cart']->getOrderTotal(true, Cart::ONLY_PRODUCTS), $params['cart']->getProducts())===false)
        {
            global $smarty;
            $smarty->assign(array('discount_display' => Discount::display($discount->value, $discount->id_discount_type, new Currency($params['cookie']->id_currency)), 'discount' => $discount));
            return $this->display(__FILE__, 'shopping-cart.tpl');
        }
        return false;*/
        return true;
    }

    /**
    * Hook display on customer account page
    * Display an additional link on my-account and block my-account
    */
    public function hookCustomerAccount($params)
    {
        return $this->display(__FILE__, 'my-account.tpl');
    }

    public function hookMyAccountBlock($params)
    {
        return $this->hookCustomerAccount($params);
    }

    /**
    * Hook display on form create account
    * Add an additional input on bottom for fill the sponsor's e-mail address
    */
    public function hookCreateAccountForm($params)
    {
        global $cookie;
        if($cookie->ref_by)
        {
            include_once(dirname(__FILE__).'/ReferralProgramModule.php');

            if (Configuration::get('PS_CIPHER_ALGORITHM'))
                $cipherTool = new Rijndael(_RIJNDAEL_KEY_, _RIJNDAEL_IV_);
            else
                $cipherTool = new Blowfish(_COOKIE_KEY_, _COOKIE_IV_);

            //id of the customer who sponsers
            $explodeResult = $cipherTool->decrypt(urldecode(urlencode($cookie->ref_by)));

            if ($explodeResult)
            {
                $sponsor = new Customer($explodeResult);
                $_POST['referralprogram'] = $sponsor->email;
                //post the email id of the sponser to the reffered when the reffered is about to create an account
                return $this->display(__FILE__, 'authentication.tpl');
            }
        }
        else if(Tools::getIsset('referralprogram'))
        {
            return $this->display(__FILE__, 'authentication.tpl');
        }
    }

    /**
    * Hook called on creation customer account
    * Create a discount for the customer if sponsorised
    */
    public function hookCreateAccount($params) {
        global $cookie;

        include_once(dirname(__FILE__).'/ReferralProgramModule.php');

        $cipherTool = Tools::getCipherTool();
        $refCode = $cookie->ref_by;
        $newCustomerEmail = trim(Tools::getValue('newemail'));
        $newCustomer = new Customer();
        $newCustomer = $newCustomer->getByEmail($newCustomerEmail);

        if (! $refCode) {
            return false;
        }

        if (! Validate::isLoadedObject($newCustomer)) {
            return false;
        }

        $id_sponsor = $cipherTool->decrypt($refCode);
        $sponsor = new Customer($id_sponsor);

        if (! Validate::isLoadedObject($sponsor)) {
            return false;
        }

        if (! Validate::isEmail($newCustomerEmail) OR $sponsor->email == $newCustomerEmail) {
            return false;
        }

        if ($id_referralprogram = ReferralProgramModule::isEmailExists($newCustomerEmail, true, false)) {
            $referralprogram = new ReferralProgramModule($id_referralprogram);

            if ($referralprogram->id_sponsor == $sponsor->id) {
                $referralprogram->id_customer = $newCustomer->id;
                $referralprogram->refer_type = $cookie->refer_type;
                $referralprogram->save();

                unset($cookie->ref_by);

                return true;
            }
        } else { // if a customer registers using the shared link of a customer,then a record is created referralprogram table
            $referralprogram = new ReferralProgramModule();
            $referralprogram->id_sponsor = intval($sponsor->id);
            $referralprogram->id_customer = $newCustomer->id; //intval($explodeResult);
            $referralprogram->firstname = $newCustomer->firstname;
            $referralprogram->lastname = $newCustomer->lastname;
            $referralprogram->email = $newCustomer->email; // $friendEmail;
            $referralprogram->refer_type = $cookie->refer_type;
            $referralprogram->save();

            unset($cookie->ref_by);

            return true;
        }

        return false;
    }

    /* Hook called when a new order is created */
    public function hookNewOrder($params) {
        $id_cart = $params['cart']->id;

        $this->log->debug('[' . $id_cart . '] NewOrder hook is called with params: ' . print_r($params, true));

        include_once(dirname(__FILE__) . '/LoyaltyStateModule.php');
        include_once(dirname(__FILE__) . '/LoyaltyModule.php');

        /**
         * if an order is  placed by the customer,then points  are awarded to the customer.
         * which is still not available to the customer.The state of loyalty points is '2'
         */
        if (! Validate::isLoadedObject($params['customer']) OR ! Validate::isLoadedObject($params['order'])) {
            $msg = 'Customer and Order in params should be real objects!';

            $this->log->fatal($msg);

            die(Tools::displayError($msg));
        }

        // Do not award points on products which have credit discount.
        if ($params['order']->total_discounts <= 0 || ($params['order']->total_discounts > 0 AND ! LoyaltyModule::creditDiscountExists((int)$params['order']->id))) {
            $this->log->info(sprintf('[%d] Seems like order is suitable for awarding the customer, continuing..', $id_cart));

            $loyalty = new LoyaltyModule();
            $loyalty->id_customer = (int)$params['customer']->id;
            $loyalty->id_order = (int)$params['order']->id;
            $loyalty->points = LoyaltyModule:: getOrderPurchasePoints($params['cart'], $params['order']);

            if ((int) Configuration::get('PS_LOYALTY_NONE_AWARD') AND (int) $loyalty->points == 0) {
                $loyalty->id_loyalty_state = LoyaltyStateModule::getNoneAwardId();
            } else {
                $loyalty->id_loyalty_state = LoyaltyStateModule::getDefaultId();
            }

            $this->log->debug(sprintf('[%d] Awarding customer %d with %d points for order %d. Loyalty state is: %d',
                $id_cart, $loyalty->id_customer, $loyalty->points, $loyalty->id_order, $loyalty->id_loyalty_state));

            // the above fields are saved in the loyalty table in database
            return $loyalty->save();
         }

         $this->log->info(sprintf('[%d] Seems like order is not suitable for awarding the customer. Continuing without awarding..', $id_cart));
    }

    /* Hook called when an order change its status */
    public function hookUpdateOrderStatus($params) {
        include_once(dirname(__FILE__).'/LoyaltyStateModule.php');
        include_once(dirname(__FILE__).'/LoyaltyModule.php');
        include_once(dirname(__FILE__).'/ReferralProgramModule.php');

        if (! Validate::isLoadedObject($params['newOrderStatus'])) {
            die(Tools::displayError('Some parameters are missing.'));
        }

        $newOrder = $params['newOrderStatus'];
        $order = new Order((int)($params['id_order']));

        if ($order AND ! Validate::isLoadedObject($order)) {
            die(Tools::displayError('Incorrect object Order.'));
        }

        //get the state's available for points
        $this->instanceDefaultStates();
        $convertCustomerPoints = false;
        $convertReferralPoints = false;

        // Checking the state of the loyalty points
        if ($newOrder->id == $this->loyaltyStateValidation->id_order_state
            OR $newOrder->id == $this->loyaltyStateRefund->id_order_state
            OR $newOrder->id == $this->loyaltyStatePartialRefund->id_order_state
            OR $newOrder->id == $this->loyaltyStateCancel->id_order_state
            OR $newOrder->id == $this->loyaltyStateFullExchange->id_order_state
            OR $newOrder->id == $this->loyaltyStatePartialExchange->id_order_state) {

            if (! Validate::isLoadedObject($loyalty = new LoyaltyModule(LoyaltyModule::getByOrderId($order->id)))) {
                return false;
            }

            if (intval(Configuration::get('PS_LOYALTY_NONE_AWARD')) AND $loyalty->id_loyalty_state == LoyaltyStateModule::getNoneAwardId()) {
                return true;
            }

            /**
             * if the order placed is confirmed,then the state of loyalty points is '2'
             * which means the loyalty points are awarded to the customer
             */
            if ($newOrder->id == $this->loyaltyStateValidation->id_order_state) {
                $loyalty->id_loyalty_state = LoyaltyStateModule::getValidationId();

                if (intval($loyalty->points) < 0) {
                    $loyalty->points = abs(intval($loyalty->points));
                }
            } else if ($newOrder->id == $this->loyaltyStateCancel->id_order_state) {
                /**
                 * if the order placed is canceled,then the state of loyalty points is '3'
                 * which means the loyalty points are not awarded to the customer
                 */
                $loyalty->id_loyalty_state = LoyaltyStateModule::getCancelId();
                $loyalty->points = intval($loyalty->points) * -1;
            } else if ($newOrder->id == $this->loyaltyStateRefund->id_order_state) {
                $loyalty->id_loyalty_state = LoyaltyStateModule::getRefundId();
                $loyalty->points = intval($loyalty->points) * -1;
            } else if ($newOrder->id == $this->loyaltyStateFullExchange->id_order_state) {
                $loyalty->id_loyalty_state = LoyaltyStateModule::getFullExchangeId();
                $loyalty->points = intval($loyalty->points) * -1;
            } else if ($newOrder->id == $this->loyaltyStatePartialExchange->id_order_state) {
                $order_detail = new OrderDetail($params['id_order_detail']);
                $points_deducted = $order_detail->product_quantity_exchanged * intval(Configuration::get('PS_PRODUCT_PURCHASE_POINTS'));
                $points_after_deduction = $loyalty->points - $points_deducted;

                if ($points_after_deduction > 0) {
                    $partialExchange = new LoyaltyModule();
                    $partialExchange->id_customer = (int)$order->id_customer;
                    $partialExchange->id_order = (int)$order->id;
                    $partialExchange->points = $points_after_deduction;
                    $partialExchange->id_loyalty_state = LoyaltyStateModule::getValidationId();
                    $partialExchange->save();
                }

                $loyalty->id_loyalty_state = LoyaltyStateModule::getPartialExchangeId();
                $loyalty->points = intval($loyalty->points) * -1;
            } else if ($newOrder->id == $this->loyaltyStatePartialRefund->id_order_state) {
                $order_detail = new OrderDetail($params['id_order_detail']);
                $points_deducted = $order_detail->product_quantity_refunded * intval(Configuration::get('PS_PRODUCT_PURCHASE_POINTS'));
                $points_after_deduction = $loyalty->points - $points_deducted;

                if ($points_after_deduction > 0) {
                    $loyalty->id_loyalty_state = LoyaltyStateModule::getPartialRefundId();
                    $loyalty->points = $points_deducted * -1;

                    $partialrefund_loyalty = new LoyaltyModule();
                    $partialrefund_loyalty->id_customer = (int)$order->id_customer;
                    $partialrefund_loyalty->id_order = (int)$order->id;
                    $partialrefund_loyalty->points = $points_after_deduction;
                    $partialrefund_loyalty->id_loyalty_state = LoyaltyStateModule::getValidationId();
                    $partialrefund_loyalty->save();
                } else {
                    $loyalty->id_loyalty_state = LoyaltyStateModule::getPartialRefundId();
                    $loyalty->points = $loyalty->points * -1;
                }
            }

            // if there is a negative impact on the order, we should restore
            // style points back and cancel any credit vouchers..
            if (! empty($loyalty->id_discount) AND in_array($newOrder->id, array(
                $this->loyaltyStateCancel->id_order_state,
                $this->loyaltyStateRefund->id_order_state,
                $this->loyaltyStateFullExchange->id_order_state,
                $this->loyaltyStatePartialExchange->id_order_state,
                $this->loyaltyStatePartialRefund->id_order_state)
            )) {
                // disabling voucher
                $voucher = new Discount($loyalty->id_discount);
                $voucher->active = false;
                $voucher->update();

                // restoring points
                LoyaltyModule::restoreConvertedLoyaltiesByDiscountId($loyalty->id_discount);
            }

            //save the above values in the loyalty table in database
            $loyalty->save();

            //Set the flag to true.Convert points to credits for customer
            $convertCustomerPoints = true;
        }
        else
            return true;
        /* here in this block ,it is checked if the customer who has plced an order is reffered by an existing customer */
        $customer = new Customer($order->id_customer);
        $stats = $customer->getStats();
        $nbOrdersCustomer = intval($stats['nb_orders']) + 1; // hack to count current order
        //returns the sponser id
        $refer_id = ReferralProgramModule::isSponsorised(intval($customer->id), true);
        $referralprogram = new ReferralProgramModule($refer_id);
        if (Validate::isLoadedObject($referralprogram))
        {
            /* if the referred customer has placed an order ,the sponser who had sent an email invite to join the shop is
              awarded points which is still not available to the sponser. The state of loyalty points is '1'. */
            $id_exists = LoyaltyModule::referralIdExists(intval($referralprogram->id_sponsor), intval($refer_id));
            if (intval($newOrder->logable) AND $nbOrdersCustomer >= intval($this->_configuration['REFERRAL_ORDER_QUANTITY']) AND $refer_id != $id_exists)
            {
                $referrer = new LoyaltyModule();
                $referrer->id_customer = $referralprogram->id_sponsor;
                $referrer->points = intval(Configuration::get('REFERRAL_PROGRAM_POINTS'));
                $referrer->id_referralprogram = intval($refer_id);
                if (intval(Configuration::get('PS_LOYALTY_NONE_AWARD')) AND intval($referrer->points) == 0)
                    $referrer->id_loyalty_state = LoyaltyStateModule::getNoneAwardId();
                else
                    $referrer->id_loyalty_state = LoyaltyStateModule::getDefaultId();
                $referrer->save();

            }
            if (Validate::isLoadedObject($reffered = new LoyaltyModule(LoyaltyModule::getByOrderId($order->id))))
            {
                /* if the referred customer confirms an order(sponser is awarded points only for one purchase by the customer)
                  The sponser is awarded points. the state of loyalty points is '2'. */
                if (Validate::isLoadedObject($referrer) AND $reffered->id_loyalty_state == LoyaltyStateModule::getValidationId())
                {
                    $referrer->id_loyalty_state = LoyaltyStateModule::getValidationId();
                    $referrer->points = abs(intval($referrer->points));
                    $referrer->save();
                }

                if(Validate::isLoadedObject($referr = new LoyaltyModule(LoyaltyModule::getByreferralId((int)$id_exists))))
                {   /* if the referred customer cancels an order, the  points awarded to the sponser is also canceled.
                      The state of loyalty points is '3'.*/
                    if ($reffered->id_loyalty_state == LoyaltyStateModule::getCancelId())
                    {
                        $referr->id_loyalty_state = LoyaltyStateModule::getCancelId();
                        $referr->points = intval($referr->points) * -1;
                    }
                    elseif($reffered->id_loyalty_state == LoyaltyStateModule::getRefundId())
                    {
                        $referr->id_loyalty_state = LoyaltyStateModule::getRefundId();
                        $referr->points = intval($referr->points) * -1;
                    }
                    $referr->save();
                }
                //Set the flag to true.Convert points to credits for the sponser
                $convertReferralPoints = true;
            }

        }
        //else
        //  return true;
        if ($convertCustomerPoints == true)
        {
            //get the total points available for a customer
            $customerPoints = intval(LoyaltyModule::getValidPointsByCustomer(intval($order->id_customer)));
            // value set in the database as per the wish of the shop owner
            $creditPoints = intval(Configuration::get('PS_LOYALTY_CREDIT_POINTS'));
            //convert customer points to credits only if customer points available is greater thna 1000(set by the shop owner)
            if ($customerPoints >= $creditPoints)
            {
                //get the balance($carry_over_lpoints) of customer points if customer points is greater than 1000.
                $carry_over_lpoints = intval(intval($customerPoints) % intval($creditPoints));
                $no_of_credits = intval((intval($customerPoints) - intval($carry_over_lpoints)) / intval($creditPoints));
                //register discount for each 1000 points
                for ($i = 1; $i <= $no_of_credits; $i++)
                    $this->convertToCredits($order->id_customer, $order->id_currency);
                //$this->convertToCredits($order->id_customer, $no_of_credits);
                //if balance($carry_over_lpoints) is greater than zero ,then save it in the loyalty table
                if ($carry_over_lpoints > 0)
                {
                    $carry_over = new LoyaltyModule();
                    $carry_over->id_customer = intval($order->id_customer);
                    $carry_over->points = $carry_over_lpoints;
                    $carry_over->id_loyalty_state = LoyaltyStateModule::getValidationId();
                    $carry_over->save();
                }
            }
        }

        if ($convertReferralPoints == true)
        {
            //get the total points available for a customer(sponser)
            $referralPoints = intval(LoyaltyModule::getValidPointsByCustomer(intval($referralprogram->id_sponsor)));
            // value set in the database as per the wish of the shop owner
            $credit_points = intval(Configuration::get('PS_LOYALTY_CREDIT_POINTS'));
            if ($referralPoints >= $credit_points)
            {
                //get the balance($carry_over_lpoints) of customer(sponser) points if customer(sponser) points is greater than 1000.
                $carry_over_points = intval(intval($referralPoints) % intval($credit_points));
                $no_of_credits = intval((intval($referralPoints) - intval($carry_over_points)) / intval($credit_points));
                //register discount for each 1000 points
                for ($i = 1; $i <= $no_of_credits; $i++)
                    $this->convertToCredits($referralprogram->id_sponsor, $order->id_currency);
                    //$this->convertToCredits($order->id_customer, $no_of_credits);
                //if balance($carry_over_lpoints) is greater than zero ,then save it in the loyalty table
                if ($carry_over_points > 0)
                {
                    $carry_over = new LoyaltyModule();
                    $carry_over->id_customer = intval($referralprogram->id_sponsor);
                    $carry_over->points = $carry_over_points;
                    $carry_over->id_loyalty_state = LoyaltyStateModule::getValidationId();
                    $carry_over->save();
                }
            }
        }
        //Mail::Send(intval($order->id_lang), 'referralprogram-congratulations', $this->l('Congratulations!'), $data, $sponsor->email, $sponsor->firstname.' '.$sponsor->lastname, strval(Configuration::get('PS_SHOP_EMAIL')), strval(Configuration::get('PS_SHOP_NAME')), NULL, NULL, dirname(__FILE__).'/mails/');*/
        // return true;
    }

    /**
    * Hook display in tab AdminCustomers on BO
    * Data table with all sponsors informations for a customer
    */
    public function hookAdminCustomers($params)
    {
        include_once(dirname(__FILE__).'/ReferralProgramModule.php');

        $customer = new Customer((int)$params['id_customer']);
        if (!Validate::isLoadedObject($customer))
            die (Tools::displayError('Incorrect object Customer.'));

        global $cookie;
        $friends = ReferralProgramModule::getSponsorFriend((int)$customer->id, false,
         isset($params[pagination]), $params[pagination][itemPerPage], $params[pagination][pageNo]);
        $totalItem = $friends['totalItem'];
        unset($friends['totalItem']);

        if ($id_referralprogram = ReferralProgramModule::isSponsorised((int)$customer->id, true)) {
            $referralprogram = new ReferralProgramModule((int)$id_referralprogram);
            $sponsor = new Customer((int)$referralprogram->id_sponsor);
        }

        $html .= '
        <h2>'.$this->l('Referral program').'</h2>
        <h3>'.(isset($sponsor) ? $this->l('Customer\'s sponsor:').' <a href="index.php?tab=AdminCustomers&id_customer='.(int)$sponsor->id.'&viewcustomer&token='.Tools::getAdminToken('AdminCustomers'.(int)(Tab::getIdFromClassName('AdminCustomers')).(int)($cookie->id_employee)).'">'.$sponsor->firstname.' '.$sponsor->lastname.'</a>' : $this->l('No one has sponsored this customer.')).'</h3>';

        if ($friends AND sizeof($friends))
        {
            $html.= '<h3>'.sizeof($friends).' '.(sizeof($friends) > 1 ? $this->l('Sponsored customers:') : $this->l('Sponsored customer:')).'</h3>';
            $html.= '
            <table cellspacing="0" cellpadding="0" class="table">
                <tr>
                    <th class="center">'.$this->l('ID').'</th>
                    <th class="center">'.$this->l('Name').'</th>
                    <th class="center">'.$this->l('Email').'</th>
                    <th class="center">'.$this->l('Registration date').'</th>
                    <th class="center">'.$this->l('Customers sponsored by this friend').'</th>
                    <th class="center">'.$this->l('Placed orders').'</th>
                    <th class="center">'.$this->l('Customer account created').'</th>
                </tr>';
                foreach ($friends AS $key => $friend)
                {
                    $orders = Order::getCustomerOrders($friend['id_customer']);
                    $html.= '
                    <tr '.($key++ % 2 ? 'class="alt_row"' : '').' '.((int)($friend['id_customer']) ? 'style="cursor: pointer" onclick="document.location = \'?tab=AdminCustomers&id_customer='.$friend['id_customer'].'&viewcustomer&token='.Tools::getAdminToken('AdminCustomers'.(int)(Tab::getIdFromClassName('AdminCustomers')).(int)($cookie->id_employee)).'\'"' : '').'>
                        <td class="center">'.((int)($friend['id_customer']) ? $friend['id_customer'] : '--').'</td>
                        <td>'.$friend['firstname'].' '.$friend['lastname'].'</td>
                        <td>'.$friend['email'].'</td>
                        <td>'.Tools::displayDate($friend['date_add'], (int)($cookie->id_lang), true).'</td>
                        <td align="right">'.sizeof(ReferralProgramModule::getSponsorFriend($friend['id_customer'])).'</td>
                        <td align="right">'.($orders ? sizeof($orders) : 0).'</td>
                        <td align="center">'.((int)$friend['id_customer'] ? '<img src="'._PS_ADMIN_IMG_.'enabled.gif" />' : '<img src="'._PS_ADMIN_IMG_.'disabled.gif" />').'</td>
                    </tr>';
                }
            $html.= '</table>';

            if ($params[pagination] AND $totalItem > $params[pagination][itemPerPage]) {
                $html .='
                    <div id="friends-pagination" class="pagination butigo-pagination green-pagination"></div>
                    <script src="'. _THEME_JS_DIR_.'pagination/jquery.pagination.js" type="text/javascript"></script>
                    <link type="text/css" rel="stylesheet" href="'._THEME_JS_DIR_.'pagination/pagination.css">';

                $html .='<script type="text/javascript">
                    $(function(){
                        $("#friends-pagination").pagination('.$totalItem.', {
                            prev_text: "'.$this->l("Prev").'"
                            , next_text: "'.$this->l("Next").'"
                            , items_per_page: '.$params[pagination][itemPerPage].'
                            , num_display_entries: 10
                            , num_edge_entries: 2
                            , current_page : '.($params[pagination][pageNo] - 1).'
                            , callback: function(pageNo, $pagination) {
                                if (pageNo == '.$params[pagination][pageNo].' - 1) return;
                                pageNo = parseInt(pageNo) + 1;
                                var urlParams =  getQueryString();
                                urlParams["pendingFPagination"] = pageNo;
                                goToUrl(location.pathname + "?" + $.param(urlParams));
                                return false;
                            }
                        });
                    });
                </script>';
            }
        }
        else
            $html.= $customer->firstname.' '.$customer->lastname.' '.$this->l('has not sponsored any friends yet.');
        return $html.'<br/><br/>';
    }

    /**
    * Hook called when a order is confimed
    * display a message to customer about sponsor discount
    */
    public function hookOrderConfirmation($params)
    {
        if ($params['objOrder'] AND !Validate::isLoadedObject($params['objOrder']))
            return die(Tools::displayError('Incorrect object Order.'));

        include_once(dirname(__FILE__).'/ReferralProgramModule.php');

        $customer = new Customer((int)$params['objOrder']->id_customer);
        $stats = $customer->getStats();
        $nbOrdersCustomer = (int)$stats['nb_orders'] + 1; // hack to count current order
        $referralprogram = new ReferralProgramModule(ReferralProgramModule::isSponsorised((int)$customer->id, true));
        if (!Validate::isLoadedObject($referralprogram))
            return false;
        $sponsor = new Customer((int)$referralprogram->id_sponsor);
        if ((int)$nbOrdersCustomer == (int)$this->_configuration['REFERRAL_ORDER_QUANTITY'])
        {
            $discount = new Discount((int)$referralprogram->id_discount_sponsor);
            if (!Validate::isLoadedObject($discount))
                return false;
            global $smarty;
            $smarty->assign(array('discount' => $discount->display($discount->value, (int)$discount->id_discount_type, new Currency((int)$params['objOrder']->id_currency)), 'sponsor_firstname' => $sponsor->firstname, 'sponsor_lastname' => $sponsor->lastname));
            return $this->display(__FILE__, 'order-confirmation.tpl');
        }
        return false;
    }

    public function getL($key)
    {
        $translations = array(
        'Awaiting validation' => $this->l('Awaiting validation'),
        'Available' => $this->l('Available'),
        'Cancelled' => $this->l('Cancelled'),
        'Already converted' => $this->l('Already converted'),
        'Unavailable on discounts' => $this->l('Unavailable on discounts'),
        'Not available on discounts' => $this->l('Not available on discounts'),
        'Refund' => $this->l('Unavailable on refund.'),
        'Partial Refund' => $this->l('Unavailable on partail refund.'));

        return (array_key_exists($key, $translations)) ? $translations[$key] : $key;
    }
}
