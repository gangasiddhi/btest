<?php

class GoogleLandingControllerCore extends FrontController {
    public function __construct() {
        $this->php_self = 'google_landing.php';

        parent::__construct();
    }

    public function preProcess() {
        parent::preProcess();

        if (self::$cookie->isLogged()) {
            Tools::redirect('lookbook.php');
        }
    }

    public function process() {
        parent::process();
        self::$smarty->assign('HOOK_GOOGLE_LANDING', Module::hookExec('googleLanding'));
    }

    public function displayContent() {
        parent::displayContent();
        self::$smarty->display(_PS_THEME_DIR_ . 'google_landing.tpl');
    }

    public function displayHeader() {
        global $css_files, $js_files;

        if (! self::$initialized) {
            $this->init();
        }

        // P3P Policies (http://www.w3.org/TR/2002/REC-P3P-20020416/#compact_policies)
        header('P3P: CP="IDC DSP COR CURa ADMa OUR IND PHY ONL COM STA"');

        /* Hooks are volontary out the initialize array (need those variables already assigned) */
        self::$smarty->assign(array(
            'time' => time(),
            'img_update_time' => Configuration::get('PS_IMG_UPDATE_TIME'),
            'static_token' => Tools::getToken(false),
            'token' => Tools::getToken(),
            'logo_image_width' => Configuration::get('SHOP_LOGO_WIDTH'),
            'logo_image_height' => Configuration::get('SHOP_LOGO_HEIGHT'),
            'priceDisplayPrecision' => _PS_PRICE_DISPLAY_PRECISION_,
            'content_only' => (int) Tools::getValue('content_only'),
            'BLOG_ENABLED' => false
        ));

        self::$smarty->assign(array(
            'HOOK_HEADER' => Module::hookExec('header'),
            'HOOK_TOP' => Module::hookExec('googleLandingTop'),
            // 'HOOK_SHOWROOM_NAVIGATION' => Module::hookExec('showroomNavigation'),
            'HOOK_LEFT_COLUMN' => Module::hookExec('leftColumn')
        ));

        if ((Configuration::get('PS_CSS_THEME_CACHE') OR Configuration::get('PS_JS_THEME_CACHE')) AND is_writable(_PS_THEME_DIR_ . 'cache')) {
            // CSS compressor management
            if (Configuration::get('PS_CSS_THEME_CACHE')) {
                Tools::cccCss();
            }

            //JS compressor management
            if (Configuration::get('PS_JS_THEME_CACHE')) {
                Tools::cccJs();
            }
        }

        self::$smarty->assign('css_files', $css_files);
        self::$smarty->assign('js_files', array_unique($js_files));
        self::$smarty->display(_PS_THEME_DIR_ . 'header.tpl');
    }
}

?>
