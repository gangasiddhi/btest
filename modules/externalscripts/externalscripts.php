<?php

class ExternalScripts extends Module
{

	public function __construct()
	{
		$this->name = 'externalscripts';
		$this->tab = 'Front_office';
		$this->version = 1.0;
		$this->author = 'Gangadhar K.M';

		parent::__construct();

		$this->displayName = $this->l('External Scripts');
		$this->description = $this->l('This module must be enabled to include the External Scripts(Third party scripts) at the footer, Order-Confirmation Pages and any other pages if needed.');
	}

	public function install()
	{
		if (!parent::install() OR !$this->registerHook('footerExternalScripts') OR !$this->registerHook('headerExternalScripts')) {
			return false;
		}

		return true;
	}

	public function uninstall()
	{
		parent::uninstall();
	}

	public function hookHeaderExternalScripts($params)
	{
		return $this->display(__FILE__, 'external-header-scripts.tpl');
	}

	public function hookFooterExternalScripts($params)
	{
		global $smarty;
		
		$smarty->assign(
			array('HOOK_FOOTER_BOTTOM' => Module::hookExec('footerBottom'))
		);
		return $this->display(__FILE__, 'external-footer-scripts.tpl');
	}

}

?>
