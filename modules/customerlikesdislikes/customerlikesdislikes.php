<?php

/**
 * Description of likedislike
 *
 * @author gangadhar
 */
class CustomerLikesDislikes extends Module
{

	public function __construct()
	{
		$this->name = 'customerlikesdislikes';
		$this->tab = 'front_office_features';
		$this->version = 1.0;
		$this->author = 'Gangadhar';

		parent::__construct();

		$this->displayName = $this->l('Customer Likes Dislikes');
		$this->description = $this->l('This module must be enabled if you want to use Customer Likes & Dislikes of products.');
	}

	public function install()
	{
		if (!parent::install() OR
				!$this->registerHook('header')/* OR
				!$this->registerHook('customerLikesDislikes')*/) {
			return false;
		}

		return true;
	}

	public function uninstall()
	{
		return (parent::uninstall());
	}

	public function hookHeader()
	{
		Tools::addCSS(($this->_path) . 'css/customerlikesdislikes.css', 'all');
		Tools::addJs(($this->_path) . 'js/customerlikesdislikes.js');
	}

}

?>
