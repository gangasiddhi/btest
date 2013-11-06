<?php

if (!defined('_CAN_LOAD_FILES_'))
	exit;

class BlockSocialMedia extends Module
{
	function __construct()
	{
		$this->name = 'blocksocialmedia';
		$this->tab = 'front_office_features';
		$this->version = 1.4;

		parent::__construct();

		$this->displayName = $this->l('The Social Network');
		$this->description = $this->l('Displays links to facebook , twitter etc. in the footer.');
	}

	function install()
	{
		if (!parent::install())
			return false;
		if (!$this->registerHook('footer') OR !$this->registerHook('header'))
			return false;

		return true;
	}
	
	public function uninstall() {
		return (parent::uninstall());
	}
	
        function hookfooter($params)
	{
		return $this->display(__FILE__, 'blocksocialmedia.tpl');
	}
	public function hookHeader($params)
	{
		Tools::addCSS(($this->_path).'blocksocialmedia.css', 'all');
	}

}
?>