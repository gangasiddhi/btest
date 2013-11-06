<?php

class LandingControllerCore extends FrontController {
	public $php_self = 'landing.php';

	public function preProcess() {
        global $smarty;

        parent::preProcess();

        if (self::$cookie->isLogged()) {
            Tools::redirect(self::$link->getPageLink('showroom.php', false), '');
        }

        /**
         * TODO: Remove the below code or comment out when Ettikett module is disabled
         *
         * Ettikett, When the customer visit the site by clicking the Facebook/Twitter shared link.
         * Customer can see a banner in the landing page, saying that he/she have some discounts.
         */
        if (Tools::getValue('etkt_ref') == 1) {
            setcookie("etkt", 1, null);
            $smarty->assign('etkt_refer', 1);
        } else {
            $smarty->assign('etkt_refer', 0);
        }
	}

	public function process() {
        parent::process();

        self::$smarty->assign('HOOK_LANDING', Module::hookExec('landing'));
	}

	public function displayContent() {
        parent::displayContent();

        self::$smarty->display(_PS_THEME_DIR_ . 'landing.tpl');
	}
}

?>
