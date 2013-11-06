<?php

if (!defined('_CAN_LOAD_FILES_'))
	exit;

class BlockGetStarted extends Module
{
	function __construct()
	{
		$this->name = 'blockgetstarted';
		$this->tab = 'front_office_features';
		$this->version = 1.4;

		parent::__construct();

		$this->displayName = $this->l('Info block / Take StyleSurvey');
		$this->description = $this->l('Adds a block that displays information.');
	}

	function install()
	{
		if (!parent::install())
			return false;
		if (!$this->registerHook('top') OR !$this->registerHook('header'))
			return false;
		return true;
	}
	
	public function uninstall() {
		return (parent::uninstall());
	}

	/**
	* Returns module content for header
	*
	* @param array $params Parameters
	* @return string Content
	*/
	function hookTop($params)
	{
		global $smarty, $cookie;
		$smarty->assign(array('logged' => $cookie->isLogged(),
                    'HOOK_JOIN_NOW' =>  Module::hookExec('joinNow')));
		return $this->display(__FILE__, 'blockgetstarted.tpl');
	}
	public function hookHeader($params)
	{
		Tools::addCSS(($this->_path).'blockgetstarted.css', 'all');
	}
}

?>
