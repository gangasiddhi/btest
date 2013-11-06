<?php

if (!defined('_CAN_LOAD_FILES_'))
	exit;

class BlockFeaturedhome extends Module
{
	function __construct()
	{
		$this->name = 'blockfeaturedhome';
		$this->tab = 'front_office_features';
		$this->version = 1.4;

		parent::__construct();

		$this->displayName = $this->l('Home page bottom blocks');
		$this->description = $this->l('Displays the 3 bottom blocks on the home page.');
	}

	function install()
	{
		if (!parent::install())
			return false;

		if (!$this->registerHook('home') OR
			!$this->registerHook('header') OR
			!$this->registerHook('landing') OR
			!$this->registerHook('googleLanding'))
			return false;

		return true;
	}
	
	public function uninstall() {
		return (parent::uninstall());
	}

	function hookHome($params)
	{
		global $smarty,$cookie;

		// CMS links
		/*$cms_ids = array(16, 19);
		$cms_links = CMS::getLinks($cookie->id_lang, $cms_ids);
		$collections_links = array();
		$index = 0;
		foreach($cms_links as $cms_link)
		{
			$cms_links_arr[] = $cms_link['link'];

			if($index === 1)
			{
				$stylists_link = $cms_link['link'];
			}
			elseif($index == 0)
			{
				$link_hiw_slideshow = $cms_link['link'];
				if (!strpos($link_hiw_slideshow, '?'))
					$link_hiw_slideshow .= '?content_only=1&slides=1';
				else
					$link_hiw_slideshow .= '&content_only=1&slides=1';
			}
			$index++;
		}

		$smarty->assign( array(
		'stylists'=> $stylists_link,
		'link_hiw_slideshow'=> $link_hiw_slideshow,
		//'last_qties' => intval($configs['PS_LAST_QTIES']),
		//'prodsmallSize'=> Image::getSize('prodsmall'),
		//'hasSeenPopup'=> $popup
		));*/

		return $this->display(__FILE__, 'blockfeaturedhome.tpl');
	}

	public function hookLanding($params)
	{
		return $this->hookhome($params);
	}

	public function hookGoogleLanding($params)
	{
		return $this->hookhome($params);
	}

	public function hookHeader($params)
	{
		if(strpos($_SERVER['PHP_SELF'], 'index') !== false || strpos($_SERVER['PHP_SELF'], 'landing') !== false || strpos($_SERVER['PHP_SELF'], 'google_landing') !== false)
		{
			Tools::addCSS(($this->_path).'blockfeaturedhome.css', 'all');
			Tools::addJS(_THEME_JS_DIR_.'hiw.js', 'all');
		}
	}
}

?>
