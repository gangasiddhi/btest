<?php

class crosssellingmanager extends Module
{
	function __construct()
	{
		$this->name = 'crosssellingmanager';
		$this->tab = ($this->getPSVersion() >= 1.4) ? 'front_office_features' : 'Products';
		$this->version = 1.1;

		parent::__construct();
		
		$this->displayName = $this->l('Cross selling manager');
		$this->description = $this->l('Customers who bought this product also bought...');	
	}

	function install()
	{
		if (parent::install() == false 
			OR !$this->registerHook('productFooter')
			OR !$this->registerHook('header')
			OR !$this->registerHook('shoppingCart')
			OR !Configuration::updateValue('CROSSSELLING_M_DISPLAY_PRICE', 0)
			OR !$this->_installDB()	)
			return false;
		return true;
	}
	
	public function uninstall()
	{
		if (!parent::uninstall() OR 
			!Configuration::deleteByName('CROSSSELLING_M_DISPLAY_PRICE'))
			return false;
		return true;
	}
	
	private function _installDB()
	{
		// check if table crosssellingmanager exists
		$sql = 'SELECT `table_name`
			FROM information_schema.tables
			WHERE table_schema = \''._DB_NAME_.'\'
				AND table_name = \''._DB_PREFIX_.'crosssellingmanager\'';		
		if (!Db::getInstance()->getRow($sql))		
			$return =  Db::getInstance()->Execute('			
			CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'crosssellingmanager` (
			  `id_product_custom` int(10) unsigned NOT NULL,
			  `id_product` int(10) unsigned NOT NULL
			) DEFAULT CHARSET=utf8;');
		else
			$return = true;						
		return $return;		
	}
	
	public function getLError(){
		return ' : '.$this->l('The product is already in the list');
	}
	
	public function getPSVersion(){
		return floatval(substr(_PS_VERSION_,0,3));
	}
	
	
	/************************************************************/
	/******************** ADMIN CONFIGURATION ********************/
	/************************************************************/
	
	public function getContent()
	{
		$this->_html .= '<h2>'.$this->displayName.'</h2>';		
		
		if (Tools::isSubmit('submitCross') AND Tools::getValue('displayPrice') != 0 AND Tools::getValue('displayPrice') != 1)
			$this->_html .= '<div id="error" class="alert error">'.$this->l('Invalid displayPrice').'</div>';
		elseif (Tools::isSubmit('submitCross'))
		{
			Configuration::updateValue('CROSSSELLING_M_DISPLAY_PRICE', Tools::getValue('displayPrice'));
			$this->_html .= '
			<div id="conf" class="conf confirm">
				<img src="../img/admin/ok.gif" alt="'.$this->l('Confirmation').'" />
				'.$this->l('Settings updated').'
			</div>';
		}	
		$this->_displayForm();
		return $this->_html;
	}	
	
	
	public function _displayForm()
	{
		global $cookie;
		
		$this->_html.= '		
			<link rel="stylesheet" type="text/css" href="'.__PS_BASE_URI__.'css/jquery.autocomplete.css" />
			<script type="text/javascript" src="'.__PS_BASE_URI__.'js/jquery/jquery.hotkeys-0.7.8-packed.js"></script>
			<script type="text/javascript" src="'.__PS_BASE_URI__.'js/jquery/jquery.autocomplete.js"></script>			
			<script type="text/javascript" src="'.__PS_BASE_URI__.'modules/crosssellingmanager/admin_crosssellingmanager.js"></script>
		
			<div id="conf" class="conf confirm" style="display:none;">
				<img src="../img/admin/ok.gif" alt="'.$this->l('Confirmation').'" />
				'.$this->l('Settings updated').'
			</div>
			
			<div id="error" class="alert error" style="display:none;"></div>';
		
			if($this->getPSVersion() >= 1.4){
				$this->_html.= '
				<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
				<fieldset><legend><img src="'.$this->_path.'logo.gif" alt="" title="" />'.$this->l('Settings').'</legend>
					<label>'.$this->l('Display price on products').'</label>
					<div class="margin-form">
						<input type="radio" name="displayPrice" id="display_on" value="1" '.(Configuration::get('CROSSSELLING_M_DISPLAY_PRICE') ? 'checked="checked" ' : '').'/>
						<label class="t" for="display_on"> <img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" /></label>
						<input type="radio" name="displayPrice" id="display_off" value="0" '.(!Configuration::get('CROSSSELLING_M_DISPLAY_PRICE') ? 'checked="checked" ' : '').'/>
						<label class="t" for="display_off"> <img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" /></label>
						<p class="clear">'.$this->l('Show the price on the products in the block.').'</p>
					</div>
					<center><input type="submit" name="submitCross" value="'.$this->l('Save').'" class="button" /></center>
				</fieldset>
				</form>
				
				<br/><br/>';
			}
			
			$this->_html.= '
			<form action="'.$_SERVER['REQUEST_URI'].'" method="post" style="clear: both;">
			<fieldset>
				<legend><img src="../img/admin/tab-products.gif" />'.$this->l('Product').'</legend>
				<label>'.$this->l('Product :').'</label>
				<div class="margin-form">		
					<div id="ajax_choose_product" style="padding:6px; padding-top:2px; width:600px;">
						'.$this->l('Begin typing the first letters of the product name, then select the product from the drop-down list:').'<br />
						<input type="text" value="" id="product_autocomplete_input" name="_input" /> 
						<input type="hidden" value="" name="id_product_custom" id="id_product_custom" />
						<input type="hidden" value="'.$cookie->id_lang.'" name="id_lang" id="id_lang" />						
						<input type="hidden" class="button" value="'.$this->l('OK').'" onclick="showDetail()" />
					</div>
				</div>				
			</fieldset>
			</form>
			
			<br/><br/>
			
			<form id="liste" action="'.$_SERVER['REQUEST_URI'].'" method="post" style="display:none;clear: both;">
			<fieldset>
				<legend><img src="../img/admin/asterisk.gif" />'.$this->l('List').'</legend>
				<br/>
				<h3 id="name_product_custom"></h3>
				<label>'.$this->l('Product link :').'</label>
				<div class="margin-form">		
					<div id="ajax_choose_product_bis" style="padding:6px; padding-top:2px; width:600px;">
						'.$this->l('Begin typing the first letters of the product name, then select the product from the drop-down list:').'<br />
						<input type="text" value="" id="product_autocomplete_input_add" name="_input_add" />
						<input type="hidden" value="" name="id_product" id="id_product" /> 
						<input type="hidden" class="button" value="'.$this->l('Add').'" onclick="addProduct()"/>
					</div>
				</div>				
			
				<div id=\'out\'></div>	
			</fieldset>
			</form>	
		';
	}
	
	public function hookHeader()
	{
		if($this->getPSVersion() >= 1.4)
			Tools::addCSS(($this->_path).'crosssellingmanager.css', 'all');
		else
			return '<link href="'.$this->_path.'crosssellingmanager.css" rel="stylesheet" type="text/css" media="screen" />';

	if(strpos($_SERVER['PHP_SELF'], 'product') !== false)
		{
			Tools::addCSS($this->_path.'css/crosssellingmanager.css');
		}
	}
	function _displayProductList($products_id_list)
	{
		global $smarty, $cookie, $link;

		//echo $products_id_list.'<br/>';

		$products = Db::getInstance()->ExecuteS('
		SELECT `id_product`
		FROM '._DB_PREFIX_.'crosssellingmanager
		WHERE `id_product_custom` IN ('.$products_id_list.')');

		$list = '';

		/*echo '<pre>';print_r($products);echo '</pre>';
		foreach($id_product_list as $product_id)
			foreach ($products AS $product)
				if($product_id == intval($product['id_product']))
					unset($products[$product_id]);*/

		foreach ($products AS $product)
			$list .= intval($product['id_product']).',';
		$list = rtrim($list, ',');


		if ($list != '')
		{
//			$orderProducts = Db::getInstance()->ExecuteS('
//				SELECT p.id_product, pa.`id_product_attribute` , pl.name, pl.link_rewrite, p.reference, IF(pa.default_image, pa.`default_image` ,i.`id_image`) as id_image '.( $this->getPSVersion() >= 1.4 ? ', p.show_price' : '' ).'
//				FROM '._DB_PREFIX_.'product p
//				LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa ON (pa.`id_product` = p.`id_product` AND pa.`default_on` = 1 AND p.`id_color_default` = 2)
//				LEFT JOIN '._DB_PREFIX_.'product_lang pl ON (pl.id_product = p.id_product)
//				LEFT JOIN '._DB_PREFIX_.'image i ON (i.id_product = p.id_product AND IF(pa.default_image ,\'  \', i.`cover` = 1))
//				WHERE p.id_product IN ('.$list.') AND pl.id_lang = '.intval($cookie->id_lang).' AND i.cover = 1
//				ORDER BY RAND()
//				LIMIT 10');
			$orderProducts = Db::getInstance()->ExecuteS('
				SELECT p.id_product, pa.`id_product_attribute` , pl.name, pl.link_rewrite, p.reference, IF(pa.default_image, pa.`default_image` ,i.`id_image`) as id_image '.( $this->getPSVersion() >= 1.4 ? ', p.show_price' : '' ).'
				FROM '._DB_PREFIX_.'product p
				LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa ON (pa.`id_product` = p.`id_product` AND pa.`default_on` = 1 AND p.`id_color_default` = 2)
				LEFT JOIN '._DB_PREFIX_.'product_lang pl ON (pl.id_product = p.id_product)
				LEFT JOIN '._DB_PREFIX_.'image i ON (i.id_product = p.id_product)
				WHERE p.`active` = 1 AND p.id_product IN ('.$list.') AND pl.id_lang = '.intval($cookie->id_lang).' AND i.cover = 1
				ORDER BY RAND()
				LIMIT 5');
//			echo '
//				SELECT p.id_product, pl.name, pl.link_rewrite, p.reference, i.id_image '.( $this->getPSVersion() >= 1.4 ? ', p.show_price' : '' ).'
//				FROM '._DB_PREFIX_.'product p
//				LEFT JOIN '._DB_PREFIX_.'product_lang pl ON (pl.id_product = p.id_product)
//				LEFT JOIN '._DB_PREFIX_.'image i ON (i.id_product = p.id_product)
//				WHERE p.id_product IN ('.$list.') AND pl.id_lang = '.intval($cookie->id_lang).' AND i.cover = 1
//				ORDER BY RAND()
//				LIMIT 10';

//print_r($orderProducts);
			$nb_products = Db::getInstance()->NumRows();

			if($nb_products > 0)
			{
				$taxCalc = Product::getTaxCalculationMethod();
				foreach ($orderProducts AS &$orderProduct)
				{
					$orderProduct['image'] = $link->getImageLink($orderProduct['link_rewrite'], intval($orderProduct['id_product']).'-'.intval($orderProduct['id_image']), 'medium');
					$orderProduct['link'] = $link->getProductLink(intval($orderProduct['id_product']),$orderProduct['id_product_attribute'], $orderProduct['link_rewrite']);
					if (Configuration::get('CROSSSELLING_M_DISPLAY_PRICE') AND ($taxCalc == 0 OR $taxCalc == 2))
						$orderProduct['displayed_price'] = Product::getPriceStatic((int)($orderProduct['id_product']), true, NULL);
					elseif (Configuration::get('CROSSSELLING_M_DISPLAY_PRICE') AND $taxCalc == 1)
						$orderProduct['displayed_price'] = Product::getPriceStatic((int)($orderProduct['id_product']), false, NULL);
				}
				$smarty->assign(array(
					'orderProducts' => $orderProducts,
					'middlePosition_crosssellingmanager' => round(sizeof($orderProducts) / 2, 0),
					'crossMDisplayPrice' => Configuration::get('CROSSSELLING_M_DISPLAY_PRICE')
				));
			}
			else
			{
				$list = '';
			}
		}

		// Revert to default behaviour - intelligent method of suggesting products
		/*if ($list == '')
		{
			$orders = Db::getInstance()->ExecuteS('
			SELECT o.id_order
			FROM '._DB_PREFIX_.'orders o
			LEFT JOIN '._DB_PREFIX_.'order_detail od ON (od.id_order = o.id_order)
			WHERE o.valid = 1 AND od.product_id = '.intval($params['product']->id));

			$list = '';
			foreach ($orders AS $order)
				$list .= intval($order['id_order']).',';
			$list = rtrim($list, ',');

			if ($list != '')
			{
				$orderProducts = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
				SELECT DISTINCT od.product_id, pl.name, pl.link_rewrite, p.reference, i.id_image, p.show_price
				FROM '._DB_PREFIX_.'order_detail od
				LEFT JOIN '._DB_PREFIX_.'product p ON (p.id_product = od.product_id)
				LEFT JOIN '._DB_PREFIX_.'product_lang pl ON (pl.id_product = od.product_id)
				LEFT JOIN '._DB_PREFIX_.'image i ON (i.id_product = od.product_id)
				WHERE od.id_order IN ('.$list.') AND pl.id_lang = '.(int)($cookie->id_lang).' AND od.product_id != '.(int)($params['product']->id).' AND i.cover = 1 AND p.active = 1
				ORDER BY RAND()
				LIMIT 10');

				$taxCalc = Product::getTaxCalculationMethod();
				foreach ($orderProducts AS &$orderProduct)
				{
					$orderProduct['image'] = $link->getImageLink($orderProduct['link_rewrite'], (int)($orderProduct['product_id']).'-'.(int)($orderProduct['id_image']), 'medium');
					$orderProduct['link'] = $link->getProductLink((int)($orderProduct['product_id']), $orderProduct['link_rewrite']);
					if (Configuration::get('CROSSSELLING_M_DISPLAY_PRICE') AND ($taxCalc == 0 OR $taxCalc == 2))
						$orderProduct['displayed_price'] = Product::getPriceStatic((int)($orderProduct['product_id']), true, NULL);
					elseif (Configuration::get('CROSSSELLING_M_DISPLAY_PRICE') AND $taxCalc == 1)
						$orderProduct['displayed_price'] = Product::getPriceStatic((int)($orderProduct['product_id']), false, NULL);
				}

				$smarty->assign(array(
					'orderProducts' => $orderProducts,
					'middlePosition_crosssellingmanager' => round(sizeof($orderProducts) / 2, 0),
					'crossMDisplayPrice' => Configuration::get('CROSSSELLING_M_DISPLAY_PRICE')
				));
			}
		}*/

		//echo($list);
		//echo '<pre>';print_r($orderProducts);echo '<pre>';
		return $this->display(__FILE__, 'crosssellingmanager.tpl');
	}

	/**
	* Returns module content for left column
	*/
	function hookProductFooter($params)
	{
		return $this->_displayProductList($params['product']->id);
	}

//	function hookShoppingCart($params)
//	{
//		/*Getting all the product Ids in the cart and make list */
//		$id_product_list = array();
//		foreach($params['products'] as $product)
//			$id_product_list[] = $product['id_product'];
//
//		$product_id_list = '';
//
//		foreach($id_product_list AS $product)
//			$product_id_list.= intval($product).',';
//		$product_id_list= rtrim($product_id_list,',');
//
//		return $this->_displayProductList($product_id_list);
//	}
}
