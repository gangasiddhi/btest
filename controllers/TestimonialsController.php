<?php

class TestimonialsControllerCore extends FrontController
{
	public $php_self = 'testimonials.php';

	public function preProcess()
	{
		parent::preProcess();
		self::$smarty->assign( 'has_title', 1 );
		self::$smarty->assign(array('record' => Tools::getValue('record')));
	}
	
	public function setMedia()
	{
		parent::setMedia();
		Tools::addCSS(_THEME_CSS_DIR_.'testimonials.css');
	}

	public function displayContent()
	{
		parent::displayContent();
		self::$smarty->display(_PS_THEME_DIR_.'testimonials.tpl');
	}
}
?>
