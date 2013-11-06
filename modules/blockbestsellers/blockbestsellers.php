<?php
/*
* 2007-2011 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2011 PrestaShop SA
*  @version  Release: $Revision: 7616 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_CAN_LOAD_FILES_'))
	exit;

class BlockBestSellers extends Module
{
	private $_html = '';
	private $_postErrors = array();

	public function __construct()
	{
		$this->name = 'blockbestsellers';
		$this->tab = 'front_office_features';
		$this->version = '1.1';
		$this->author = 'PrestaShop';
		$this->need_instance = 0;

		parent::__construct();

		$this->displayName = $this->l('Top seller block');
		$this->description = $this->l('Add a block displaying the shop\'s top sellers.');
	}

	/**
	 * @see ModuleCore::install()
	 */
	public function install()
	{
		if (!parent::install() OR
				!$this->registerHook('header') OR
				!$this->registerHook('rightColumn') OR
                !$this->registerHook('topSellers') OR
				!ProductSale::fillProductSales())
			return false;
		return true;
	}
	
	public function uninstall() {
		return (parent::uninstall());
	}

	/**
	 * Called in administration -> module -> configure
	 */
	public function getContent()
	{
		$output = '<h2>'.$this->displayName.'</h2>';
		if (Tools::isSubmit('submitBestSellers'))
		{
			Configuration::updateValue('PS_BLOCK_BESTSELLERS_DISPLAY', (int)(Tools::getValue('always_display')));
			$output .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('Confirmation').'" />'.$this->l('Settings updated').'</div>';
		}
		return $output.$this->displayForm();
	}
	public function getXmlSale()
	{
		if (file_exists(dirname(__FILE__).'/bestsale.xml'))
		{
			$xmlfile = dirname(__FILE__).'/bestsale.xml';
			$xmlparser = xml_parser_create();
			$fp = fopen($xmlfile, 'r');
			$xmldata = fread($fp, filesize($xmlfile));
			xml_parse_into_struct($xmlparser,$xmldata,$values);
			xml_parser_free($xmlparser);
		}
			//print_r($values);
		$data = array();
		$i = 0;
		foreach ($values as $value)
		{
			if($value['type'] == 'complete')
			{
				$data[$i][strtolower($value['tag'])] = $value['value'];
				$i++;
			}
			if($value['tag'] == 'ID_PRODUCT' AND $value['type'] == 'close')
				$i++;
		}
		return $data;
	}

	public function displayForm()
	{
		global $cookie;

		$datahtml = '
			<link rel="stylesheet" type="text/css" href="'.__PS_BASE_URI__.'css/jquery.autocomplete.css" />
			<script type="text/javascript" src="'.__PS_BASE_URI__.'js/jquery/jquery.hotkeys-0.7.8-packed.js"></script>
			<script type="text/javascript" src="'.__PS_BASE_URI__.'js/jquery/jquery.autocomplete.js"></script>
			<script type="text/javascript" src="'.__PS_BASE_URI__.'modules/blockbestsellers/admin_bestsellingmanager.js"></script>
		    <script>
			   window.onload =  showDetail();
		    </script>
			';

		$datahtml .= '
			<form id="liste" action="'.$_SERVER['REQUEST_URI'].'" method="post" style="clear: both;">
			<fieldset>
				<legend><img src="../img/admin/tab-products.gif" />'.$this->l('Product').'</legend>
				<label>'.$this->l('Product :').'</label>
				<div class="margin-form">
					<div id="ajax_choose_product" style="padding:6px; padding-top:2px; width:600px;">
						'.$this->l('Begin typing the first letters of the product name, then select the product from the drop-down list:').'<br />
						<input type="text" value="" id="product_autocomplete_input_add" name="_input" />
						<input type="hidden" value="" name="id_product" id="id_product" />
						<input type="hidden" value="'.$cookie->id_lang.'" name="id_lang" id="id_lang" />
						<input type="hidden" class="button"  name="best_sale" value="'.$this->l('Add').'" onclick="addProduct()"/>
					</div>
				</div>
				<div id=\'out\'></div>
			</fieldset>
			</form>
			<br/><br/>
		';

		return $datahtml;
	}

	public function hookRightColumn($params)
	{
		if (Configuration::get('PS_CATALOG_MODE'))
			return ;

		global $smarty;
		$currency = new Currency((int)($params['cookie']->id_currency));
		$bestsellers = ProductSale::getBestSalesLight((int)($params['cookie']->id_lang), 0, 5);
		if (!$bestsellers AND !Configuration::get('PS_BLOCK_BESTSELLERS_DISPLAY'))
			return;
		$best_sellers = array();

		if($bestsellers)
			foreach ($bestsellers AS $bestseller)
			{
				$bestseller['price'] = Tools::displayPrice(Product::getPriceStatic((int)($bestseller['id_product'])), $currency);
				$best_sellers[] = $bestseller;
			}

		$smarty->assign(array(
			'best_sellers' => $best_sellers,
			'mediumSize' => Image::getSize('medium')));
		return $this->display(__FILE__, 'blockbestsellers.tpl');
	}

	public function hookHeader($params)
	{
		global $cookie;
		if (Configuration::get('PS_CATALOG_MODE'))
			return ;
		if(strpos($_SERVER['PHP_SELF'], 'index')!== false && $cookie->isLogged())
		{
			Tools::addCSS(($this->_path).'blockbestsellers.css', 'all');
			Tools::addJS(_PS_JS_DIR_.'jquery/jquery-ui-1.10.3.custom.min.js');
			Tools::addJS(_THEME_JS_DIR_.'jquery.flip.min.js');
		}

	}

    public function hookTopSellers($params)
    {
        global $smarty, $cookie;

        if (Configuration::get('PS_CATALOG_MODE'))
            return ;

        $currency = new Currency((int)($params['cookie']->id_currency));
        $bestsellers = ProductSale::getTodayBestSales((int)($params['cookie']->id_lang), 0, 5);

        if (!$bestsellers AND !Configuration::get('PS_BLOCK_BESTSELLERS_DISPLAY'))
            return;

        $best_sellers = array();
        $i = 0;
        if($bestsellers){
            foreach ($bestsellers AS $bestseller){
                $bestseller['price'] = Tools::displayPrice(Product::getPriceStatic((int)($bestseller['id_product'])), $currency);
                $best_sellers[] = $bestseller;
            }
        }

        $reverse = array_reverse($best_sellers);

        $smarty->assign(array(
                'best_sellers' => $reverse,
                'homeSize' => Image::getSize('home')));

        // To display today's date
        if($cookie->id_lang == 4)
                $month_name = array('Ocak','Şubat','Mart','Nisan','Mayıs','Haziran','Temmuz','Ağustos','Eylül','Ekim','Kasım','Aralık');
        else
                $month_name = array('January','February','March','April','May','June','July','August','September','October','November','December');

        $month = date("n");
        $today_date = date("j");
        $year = date("Y");
        $smarty->assign(array(
                                'today_date' => $today_date,
                                'month'		 => $month_name[$month-1],
                                'year'		 => $year
                ));
        return $this->display(__FILE__, 'blockbestsellers-home.tpl');
    }

}
