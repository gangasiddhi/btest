<?php

require( _PS_MODULE_DIR_ . '/productrating/rating/_drawrating.php');
require( _PS_MODULE_DIR_ . '/productrating/rating/_config-rating.php');

class productrating extends Module
{
	function __construct()
	{
		$this->name 	= 'productrating';
		$this->tab 		= 'advertising_marketing';
		$this->version  =  0.91;
		
		/** Tradução **/
		$this->l_rating	= $this->l('Give your rating now');
		$this->l_rating	= $this->l('Rating');
		$this->l_cast	= $this->l('cast');
		$this->l_votes	= $this->l('votes');
		$this->l_vote	= $this->l('vote');
		$this->l_out	= $this->l('out of');
		//$this->l_thank	= $this->l('Thanks for voting!');

		parent::__construct(); // The parent construct is required for translations

		$this->page = basename(__FILE__, '.php');
		$this->displayName = $this->l('Product Ratings');
		$this->description = $this->l('Allow users to rate products');
	}
	
	function traduz($termo)
	{
		return $this->l($termo);
	}

	function install()
	{
		Configuration::updateValue('RATING_NUMBER', 10);
		Configuration::updateValue('RATING_STAR', '0001.gif');
		Configuration::updateValue('RATING_BGCL', 'f1f2f4');
		Configuration::updateValue('RATING_BDCL', 'd0d3d8');
		
		Db::getInstance()->Execute
			('
			CREATE TABLE `' . _DB_PREFIX_ . 'ratings` (
  			`id` varchar(11) NOT NULL,
  			`total_votes` int(11) NOT NULL default 0,
  			`total_value` int(11) NOT NULL default 0,
  			`used_ips` longtext,
  			PRIMARY KEY  (`id`)
			) TYPE=MyISAM;');
		
		if (!parent::install())
			return false;
		if (!$this->registerHook('extraRight'))
			return false;
		if (!$this->registerHook('header'))
			return false;
		return true;
	}

	public function uninstall()
    {
         if (parent::uninstall() == false)
             return false;
         if (!Db::getInstance()->Execute('DROP TABLE '._DB_PREFIX_.'ratings'))
             return false;
         return true;
    } 
    
	public function getContent()
	{
		$output = '<h2>'.$this->displayName.'</h2>';
		
		if (Tools::isSubmit('submitStar'))
		{
			$star_pic 	= Tools::getValue('star_pic');
			$back_col 	= Tools::getValue('bgcolor');
			$bord_col 	= Tools::getValue('bdcolor');
			
			Configuration::updateValue('RATING_STAR', $star_pic);
			Configuration::updateValue('RATING_BGCL', $back_col);
			Configuration::updateValue('RATING_BDCL', $bord_col);
		}

		if (Tools::isSubmit('submit'))
		{
			$onllog 	= Tools::getValue('onllogg');
			Configuration::updateValue('RATING_ONLG', $onllog);
			
			$nbr = intval(Tools::getValue('nbr'));
			if (!$nbr OR $nbr <= 0 OR !Validate::isInt($nbr))
				$errors[] = $this->l('Invalid number');
			else
				Configuration::updateValue('RATING_NUMBER', $nbr);
			if (isset($errors) AND count($errors))
				$output .= $this->displayError(implode('<br />', $errors));
			else
				$output .= $this->displayConfirmation($this->l('Settings updated'));
		}
		
		return $output.$this->displayForm();
	}
	
	public function displayForm()
	{
		$star_pic	= Tools::getValue('star_pic', Configuration::get('RATING_STAR'));

		$array	= array(1,2,3,4,5,6,7,8,9,10);
		
		$output = '
		<p><b>'.$this->l('Allow visitors vote to products with Star Rating.').'</b></p>
		<div class="clear">
		';
		
		$output .= '
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
			<fieldset><legend><img src="'.$this->_path.'logo.gif" alt="" title="" />'.$this->l('Settings').'</legend>
				<label>'.$this->l('Number of Stars').'</label>
				<div class="margin-form">
					
				<select size="1" name="nbr">';
		
		foreach ( $array as $value )					
		$output .= "<option value=\"$value\" ".( Tools::getValue('nbr', Configuration::get('RATING_NUMBER')) == $value ?  ' SELECTED ' : false )." >$value</option>";
	
		$output .= '
				</select>
				<p class="clear">'.$this->l('The number of stars (default: 10)').'</p>
				</div>';
				
		$output .= '
			<label >'.$this->l('Only Logged').'</label>
			<div class="margin-form">
			<input type="checkbox" name="onllogg" value="1" '.( Tools::getValue('onllogg', Configuration::get('RATING_ONLG')) ? 'checked="checked"' : false ).' />
				<p class="clear">'.$this->l('Only logged user can vote.').'</p></div>';	
				
		$output .= '<center>
					<input type="submit" name="submit" value="'.$this->l('Save').'" class="button" />
				</center>
			</fieldset>
		</form><br />';
		
		/** STAR **/
		$output .= '
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
		<fieldset>
			<legend><img src="../img/admin/themes.gif" />'.$this->l('Star').'</legend>';

		$star_pic	= Tools::getValue('star_pic', Configuration::get('RATING_STAR'));

		foreach ( $this->pegaArquivos() as $id => $value )
		{
			if ($star_pic ==  $value){
				$check = 'checked = "checked"'; 
			}else{
				$check = '';
			}
			
			$output .=  '

			<label style="width: 30px; float: left; margin-right: 7px; margin-bottom: 20px; padding: 0; 
			text-align: center;">
			<input type="radio" name="star_pic" value="'.$value.'" '.$check.' >';

			$output .=  '<img src="'.$this->_mediaServerPath.$this->_path.'/rating/stars/'.$value.'" />';
			$output .=  '</label>';
		}
		
		$output .= '<div class="clear"></div><hr>
			<label class="clear" >'.$this->l('Background Color').'</label>
			<div class="margin-form">
				<input type="text" size="7" name="bgcolor" value="'.
				Tools::getValue('bgcolor', Configuration::get('RATING_BGCL')).'" />
				<p class="clear">'.$this->l('Hexadecimal code color Ex. f1f2f4, or 0 for Transparent.').'</p></div>';
		
		$output .= '<label >'.$this->l('Border Color').'</label>
			<div class="margin-form">
				<input type="text" size="7" name="bdcolor" value="'.
				Tools::getValue('bdcolor', Configuration::get('RATING_BDCL')).'" />
				<p class="clear">'.$this->l('Hexadecimal code color Ex. d0d3d8, or 0 for None.').'</p></div>';

		$output .= '<br /><center><input type="submit" name="submitStar" value="'.$this->l('Save').'" 
			class="button" />
		</center>
		</fieldset>
		</form>';
		
		return $output;
	}

	public function pegaArquivos()
	{
		$diretorio				= dirname(__FILE__) . '/rating/stars/';
		$ponteiro 				= opendir( $diretorio  );

		while ( $nome_itens = readdir($ponteiro) )
		{
			$itens[] = $nome_itens ;
		}
		
		sort( $itens ) ;

		foreach ( $itens as $listar )
		{
			if ( $listar != "." && $listar != ".." && $listar != "Thumbs.db" )
			{
				if ( !is_dir($listar) )
				{
					$arquivos[] = $listar ;
				}
			}
		}
		
		return $arquivos;
	}

	function hookHeader($params)
	{
		global $smarty,$cookie;
		
		$star_pic	= Tools::getValue('star_pic', 	Configuration::get('RATING_STAR'));
		$back_col   = Tools::getValue('bgcolor', 	Configuration::get('RATING_BGCL'));
		$bord_col	= Tools::getValue('bdcolor', 	Configuration::get('RATING_BDCL'));

		$smarty->assign( 'rating_rpc', '..'.__PS_BASE_URI__.'modules/productrating/rating/rpc.php');
		$smarty->assign( 'bdcolor', $bord_col );
		$smarty->assign( 'bgcolor', $back_col );
		$smarty->assign( 'star', 	$star_pic );

		$product = new Product(Tools::getValue('id_product'), true, intval($cookie->id_lang));
		$smarty->assign( 'product', $product );

		if(strpos($_SERVER['PHP_SELF'], 'product')!== false)
		{
			Tools::addJS(($this->_path).'rating/js/behavior.js');
			Tools::addJS(($this->_path).'rating/js/rating.js');
			Tools::addCSS($this->_path.'rating/css/rating.css', 'all');
		}

		return $this->display(__FILE__, 'productrating-header.tpl');
	}

	function hookextraRight($params)
	{
		global $smarty, $cookie, $page_name, $logged;

		$onllog		= Tools::getValue('onllog', Configuration::get('RATING_ONLG'));
		$number		= Tools::getValue('nbr', Configuration::get('RATING_NUMBER'));
		$rating		= rating_bar( Tools::getValue('id_product'), $number);
		$static		= rating_bar( Tools::getValue('id_product'), $number, 'static' );

		$smarty->assign( 'onllog', $onllog );
		$smarty->assign( 'rating', $rating );
		$smarty->assign( 'result', $static );

		return $this->display(__FILE__, 'productrating.tpl');
	}
}

?>
