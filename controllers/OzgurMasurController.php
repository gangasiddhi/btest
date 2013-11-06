<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of OzgurMasurController
 *
 * @author gangadhar
 */
class OzgurMasurControllerCore extends FrontController
{
	public $ssl = false;
	public $php_self = 'ozgur-masur.php';

	public function preProcess() {
		parent::preProcess();
       // setcookie("bu_bcpath", 'koleksiyon');

		$showSite = self::$cookie->show_site;

		if (!$showSite) {
	        // if user is notlogged redirect to authentication page
			// after signin directly redirect to lookbook page
            if (! self::$cookie->isLogged()) {
				Tools::redirect('authentication.php?back=ozgur-masur.php');
			}

	        // get id of customer if already a user
			if (intval(self::$cookie->id_customer)) {
				// get id if customer is 1st time logged
				$customer = new Customer(intval(self::$cookie->id_customer));
			} else {
				Tools::redirect('authentication.php?back=ozgur-masur.php');
			}

			if (! Validate::isLoadedObject($customer)) {
				// if customer is not validated redirect to authentication page
				Tools::redirect('authentication.php?back=ozgur-masur.php');
			}
		}
	}
	
	public function setMedia() {
		parent::setMedia();

		Tools::addCSS(_THEME_CSS_DIR_.'ozgur-masur.css');
      
	}
	
	/*displays content of lookbook.tpl*/
	public function displayContent() {
			parent::displayContent();

			self::$smarty->display(_PS_THEME_DIR_ . 'ozgur-masur.tpl');
	}
}

?>
