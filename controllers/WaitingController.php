<?php

class WaitingControllerCore extends FrontController {
	public $ssl = false;
	public $php_self = 'waiting.php';

	public function preProcess() {
		parent::preProcess();

		$from_sailthru_spider = $_SERVER["HTTP_USER_AGENT"] == "Sailthru Content Spider [Butigo/7960c2582bec87e53771387ab15dd345]" ? true : false;
		
		if (!$from_sailthru_spider) {
			if (! self::$cookie->isLogged()) {
				Tools::redirect('authentication.php?back=waiting.php');
			}

			if (intval(self::$cookie->id_customer)) {
				$customer = new Customer(intval(self::$cookie->id_customer));
			} else {
				Tools::redirect('authentication.php?back=waiting.php');
			}

			if (! Validate::isLoadedObject($customer)) {
				Tools::redirect('authentication.php?back=waiting.php');
			}
		}

		$waiting_products_limit = 10;
		$more_products_limit = 25;

		$styleSurvey = CustomerStyleSurvey::getByCustomerId($customer->id);
        $completion_time = strtotime($styleSurvey['date_add']);
        $waiting_time = $completion_time + (10 * 60 * 60); // Made as 10 hours
        $now = time();

		if ($now < $waiting_time) {
			$waiting_room = true;
		} else {
			$waiting_room = false;
		}

		if ($waiting_room == true) {
			$waiting_caegory_search = Category::searchByNameAndParentCategoryId(1, 'Waiting', 1);
			$waiting_category = new Category($waiting_caegory_search['id_category']);
			$waiting_products = $waiting_category->getProducts(intval(self::$cookie->id_lang), 1, $waiting_products_limit);

			self::$smarty->assign(array(
				'waiting_products' => $waiting_products,
				'prodsmallSize' => Image::getSize('prodsmall')
			));
		} else {
			Tools::redirect(self::$link->getPageLink('showroom.php', false), '');
		}

		//product low stock
		$configs = Configuration::getMultiple(array('PS_ORDER_OUT_OF_STOCK', 'PS_LAST_QTIES'));

		self::$smarty->assign(array(
//			'link_hiw_slideshow' => $link_hiw_slideshow,
//			'collections_links' => $collections_links,
			'last_qties' => intval($configs['PS_LAST_QTIES']),
			'prodsmallSize' => Image::getSize('prodsmall'),
			'HOOK_BEFORE_FOOTER' => Module::hookExec('beforeFooterBlock')
		));

		self::$smarty->assign('errors', $this->errors);
		Tools::safePostVars();
	}

	public function setMedia() {
		parent::setMedia();
		Tools::addCSS(_THEME_CSS_DIR_ . 'waiting.css');
		Tools::addJS(array(
			_THEME_JS_DIR_ . 'waiting.js',
			_THEME_JS_DIR_ . 'hiw.js'
		));
	}

	public function process() {
		parent::process();

		$back = Tools::getValue('back');
		$key = Tools::safeOutput(Tools::getValue('key'));

		if (! empty($key)) {
			$back .= (strpos($back, '?') !== false ? '&' : '?') . 'key=' . $key;
		}

		if (! empty($back)) {
			self::$smarty->assign('back', Tools::safeOutput($back));

			if (strpos($back, 'order.php') !== false) {
				$countries = Country::getCountries((int)(self::$cookie->id_lang), true);

				self::$smarty->assign(array(
					'inOrderProcess' => true,
					'PS_GUEST_CHECKOUT_ENABLED' => Configuration::get('PS_GUEST_CHECKOUT_ENABLED'),
					'sl_country' => (int)Tools::getValue('id_country', Configuration::get('PS_COUNTRY_DEFAULT')),
					'countries' => $countries
				));
			}
		}
	}

	public function displayContent() {
		parent::displayContent();
		self::$smarty->display(_PS_THEME_DIR_.'waiting.tpl');
	}
}

?>
