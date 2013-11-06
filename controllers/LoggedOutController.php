<?php

class LoggedOutControllerCore extends FrontController {
    public $php_self = 'logged-out.php';

    public function process() {
        parent::process();

        if (self::$cookie->logged) {
            // customer logged in
            Tools::redirect(self::$link->getPageLink('lookbook.php', false), '');
        }
    }

    public function displayHeader() {
        global $css_files, $js_files;

        if (! self::$initialized) {
            $this->init();
        }

        // P3P Policies (http://www.w3.org/TR/2002/REC-P3P-20020416/#compact_policies)
        header('P3P: CP="IDC DSP COR CURa ADMa OUR IND PHY ONL COM STA"');
        self::$smarty->assign('emailerror', urldecode(Tools::getValue('errorMsg')));
        self::$smarty->assign(array(
            'HOOK_HEADER' => Module::hookExec('header'),
            'HOOK_HOME_LOGGED_OUT' => Module::hookExec('HomeLoggedOut'),
            'HOOK_JOIN_NOW' =>  Module::hookExec('joinNow')
        ));

        if ((Configuration::get('PS_CSS_THEME_CACHE')
            OR Configuration::get('PS_JS_THEME_CACHE'))
            AND is_writable(_PS_THEME_DIR_.'cache')) {

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

        self::$smarty->display(_PS_THEME_DIR_ . 'header-logged-out.tpl');
    }

    public function displayFooter()
    {
        global $link;
        if (!self::$initialized)
            $this->init();

        if(isset($id_cms))
            $smarty->assign('id_cms', $id_cms);

        self::$smarty->assign(array(
            'HOOK_FOOTER_TOP' => Module::hookExec('footerTop'),
            'HOOK_FOOTER' => Module::hookExec('footer'),
            'HOOK_FOOTER_BOTTOM' => Module::hookExec('footerBottom'),
            'content_only' => (int)(Tools::getValue('content_only')),
            'copy_year' => '2011-'.date('Y'),
        ));

        self::$smarty->display(_PS_THEME_DIR_.'footer-logged-out.tpl');

        //live edit
        if (Tools::isSubmit('live_edit') AND $ad = Tools::getValue('ad') AND (Tools::getValue('liveToken') == sha1(Tools::getValue('ad')._COOKIE_KEY_)))
        {
            self::$smarty->assign(array('ad' => $ad, 'live_edit' => true));
            self::$smarty->display(_PS_ALL_THEMES_DIR_.'live_edit.tpl');
        }
        else
            Tools::displayError('Error: 201306051530');
    }

    public function setMedia() {
        global $cookie;
        Tools::addCSS(_THEME_CSS_DIR_ . 'logged-out.css', 'all');
        Tools::addCSS(_THEME_CSS_DIR_ . 'errors.css', 'all');
        Tools::addCSS(_PS_CSS_DIR_.'jquery.fancybox.css');
        Tools::addJS(array(
            _PS_JS_DIR_ . 'jquery/jquery-1.9.1.min.js',
            _PS_JS_DIR_ . 'jquery/jquery.easing.1.3.js',
            _PS_JS_DIR_ . 'jquery/jquery.fancybox.pack.js',
            _PS_JS_DIR_ . 'jquery/errors.js',
            _THEME_JS_DIR_ . 'hiw.js'
        ));
    }
}
