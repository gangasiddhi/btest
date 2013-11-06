<?php

class FaqsFooterControllerCore extends FrontController
{
	public $php_self = 'faqs.php';

	public function preProcess()
	{
		global $cookie;
		parent::preProcess();
		$faq = new Faq();
		$faqs = $faq->getFaqsOnly(intval($cookie->id_lang));
		self::$smarty->assign(array(
			'faqs' => $faqs,
			'id_cms' => 16
		));

		self::$smarty->assign( 'has_title', 1 );
	}

	public function setMedia()
	{
		parent::setMedia();
	if(strpos($_SERVER['PHP_SELF'], 'faqs')!== false)
		Tools::addJS(_THEME_JS_DIR_.'faq.js');
	}


	/*public function process()
	{
		parent::process();
	}*/

	public function displayContent()
	{
		parent::displayContent();
		self::$smarty->display(_PS_THEME_DIR_.'faqs.tpl');
	}
}
?>
