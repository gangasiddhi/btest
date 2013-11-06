<?php
/**
 * Description of ReturnController
 * @author Avish WebSoft Pvt Ltd
 */

class ReturnControllerCore extends FrontController 
{
	public $php_self = 'return.php';
	
	public function preProcess() 
	{
		parent::preProcess();
	}
	
	public function process() 
	{
		parent::process();
		
		if(Tools::getValue('etkt') == 1)
		{
			self::$smarty->assign(array('HOOK_ORDER_CONFIRMATION_RETURN'=>Module::hookExec('OrderConfirmationReturn'),
										'etkt_shared'=>1));
		}
		
	}
	
	public function setMedia() {
		parent::setMedia();
	}
	
	public function displayContent() 
	{
		parent::displayContent();
		self::$smarty->display(_PS_THEME_DIR_.'return.tpl');
	}
}

?>
