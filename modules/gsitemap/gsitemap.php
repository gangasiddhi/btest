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
*  @version  Release: $Revision: 7541 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_CAN_LOAD_FILES_'))
	exit;

class Gsitemap extends Module
{
	private $_html = '';
	private $_postErrors = array();

	public function __construct()
	{
		$this->name = 'gsitemap';
		$this->tab = 'seo';
		$this->version = '1.6';
		$this->author = 'PrestaShop';
		$this->need_instance = 0;
		
		parent::__construct();

		$this->displayName = $this->l('Google sitemap');
		$this->description = $this->l('Generate your Google sitemap file');

		if (!defined('GSITEMAP_FILE'))
			define('GSITEMAP_FILE', dirname(__FILE__).'/../../sitemap.xml');
	}

	public function uninstall()
	{
		file_put_contents(GSITEMAP_FILE, '');
		return parent::uninstall();
	}
	
	private function _postValidation()
	{
		file_put_contents(GSITEMAP_FILE, '');
		if (!($fp = fopen(GSITEMAP_FILE, 'w')))
			$this->_postErrors[] = $this->l('Cannot create').' '.realpath(dirname(__FILE__.'/../..')).'/'.$this->l('sitemap.xml file.');
		else
			fclose($fp);
	}
	
	private function getUrlWith($url, $key, $value)
	{
		if (empty($value))
			return $url;
		if (strpos($url, '?') !== false)
			return $url.'&'.$key.'='.$value;
		return $url.'?'.$key.'='.$value;
	}

	private function _postProcess()
	{
		Configuration::updateValue('GSITEMAP_ALL_CMS', (int)Tools::getValue('GSITEMAP_ALL_CMS'));
		Configuration::updateValue('GSITEMAP_ALL_PRODUCTS', (int)Tools::getValue('GSITEMAP_ALL_PRODUCTS'));
		$link = new Link();
		$langs = Language::getLanguages();
				
		$xmlString = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
</urlset>
XML;
		
		$xml = new SimpleXMLElement($xmlString);

		if (Configuration::get('PS_REWRITING_SETTINGS') AND sizeof($langs) > 1)
			foreach($langs as $lang)
				$this->_addSitemapNode($xml, Tools::getShopDomain(true, true).__PS_BASE_URI__.$lang['iso_code'].'/', '1.00', 'daily', date('Y-m-d'));
		else
			$this->_addSitemapNode($xml, Tools::getShopDomain(true, true).__PS_BASE_URI__, '1.00', 'daily', date('Y-m-d'));
		
		/* CMS Generator */
		if (Configuration::get('GSITEMAP_ALL_CMS') OR !Module::isInstalled('blockcms'))
			$sql_cms = '
			SELECT DISTINCT '.(Configuration::get('PS_REWRITING_SETTINGS') ? 'cl.id_cms, cl.link_rewrite, cl.id_lang' : 'cl.id_cms').
			' FROM '._DB_PREFIX_.'cms_lang cl
			LEFT JOIN '._DB_PREFIX_.'lang l ON (cl.id_lang = l.id_lang)
			WHERE l.`active` = 1
			ORDER BY cl.id_cms, cl.id_lang ASC';
		elseif (Module::isInstalled('blockcms'))
			$sql_cms = '
			SELECT DISTINCT '.(Configuration::get('PS_REWRITING_SETTINGS') ? 'cl.id_cms, cl.link_rewrite, cl.id_lang' : 'cl.id_cms').
			' FROM '._DB_PREFIX_.'cms_block_page b
			LEFT JOIN '._DB_PREFIX_.'cms_lang cl ON (b.id_cms = cl.id_cms)
			LEFT JOIN '._DB_PREFIX_.'lang l ON (cl.id_lang = l.id_lang)
			WHERE l.`active` = 1
			ORDER BY cl.id_cms, cl.id_lang ASC';
		
		$cmss = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql_cms);
		foreach($cmss AS $cms)
		{
			$tmpLink = Configuration::get('PS_REWRITING_SETTINGS') ? $link->getCMSLink((int)$cms['id_cms'], $cms['link_rewrite'], false, (int)$cms['id_lang']) : $link->getCMSLink((int)$cms['id_cms']);
			//$this->_addSitemapNode($xml, $tmpLink, '0.8', 'daily');				
		}
		
		        
        
    /*query for fetching the main category name*/
    $category_sql = 'SELECT c.id_category as category_id,cl.name as category_name,c.level_depth,c.id_parent as parent_id, cl.link_rewrite, DATE_FORMAT(IF(date_upd,date_upd,date_add), \'%Y-%m-%d\') AS date_upd FROM '._DB_PREFIX_.'category as c
          INNER JOIN '._DB_PREFIX_.'category_lang as cl ON cl.id_category = c.id_category WHERE cl.id_lang = 4 AND c.id_parent=1 AND c.active=1 AND c.id_category IN(162,164,171)';
    
    //(104,145,162,164,161)
    //104,145,162,164,171,173,161,158,159,172,174,157,156
    
     $category_result= Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($category_sql);
     $productId_check_array=array();
     foreach($category_result as $category) {
        if (($main_cat_priority = 0.9 - ($category['level_depth'] / 10)) < 0.1)
				$main_cat_priority = 0.1;
        
        $main_cat_tmpLink = Configuration::get('PS_REWRITING_SETTINGS') ? $link->getCategoryLink($category['link_rewrite']) : $link->getCategoryLink($category['link_rewrite']);	
		$this->_addSitemapNode($xml, htmlspecialchars($main_cat_tmpLink), $main_cat_priority, 'weekly', substr($category['date_upd'], 0, 10)); 
        
        /*Subcategory link*/
        $subcategory_result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT c.`level_depth`, cl.link_rewrite, DATE_FORMAT(IF(date_upd,date_upd,date_add), \'%Y-%m-%d\') AS date_upd FROM `'._DB_PREFIX_.'category` c
		LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON (c.`id_category` = cl.`id_category` AND `id_lang` = 4)
		WHERE `id_parent` = '.(int)($category['category_id']).' AND `active` = 1');
        
        foreach ($subcategory_result as $sub_cat_url){
            if (($sub_cat_priority = 0.9 - ($sub_cat_url['level_depth'] / 10)) < 0.1)
				$sub_cat_priority = 0.1;
            
            $tmp_sub_cat_Link =  _PS_BASE_URL_.__PS_BASE_URI__.$sub_cat_url['link_rewrite'];	
            $this->_addSitemapNode($xml, htmlspecialchars($tmp_sub_cat_Link), $sub_cat_priority, 'weekly', substr($sub_cat_url['date_upd'], 0, 10));
        
        }
        
        $categ = new Category((int)$category['category_id'], 4);
        $product_array =$categ->getProducts(4, 1, 10000000, 'position', NULL, false, true, false, 1, true, true);
        //echo'<pre>';print_r($product_array);echo'</pre><br>';
        
        foreach($product_array as $product) {
            if (($priority = 0.9 - ($category['level_depth'] / 10)) < 0.1)
            $priority = 0.1;

            if(!in_array($product['id_product'], $productId_check_array)){

                $tmpLink = _PS_BASE_URL_.__PS_BASE_URI__.$category['link_rewrite'].'/'.$product['id_product'].'-'.$product['link_rewrite'].'.html';
                $sitemap = $this->_addSitemapNode($xml, htmlspecialchars($tmpLink), $priority, 'weekly', substr($product['date_upd'], 0, 10));

            }
        }
      }
       
      
        /*New xml code part ends here*/
	  
		/* Add classic pages (contact, best sales, new products...) */
		$pages = array(
			'authentication' => true, 
			'best-sales' => false, 
			'contact-form' => true, 
			'discount' => false, 
			'index' => false, 
			'manufacturer' => false, 
			'new-products' => false, 
			'prices-drop' => false, 
			'supplier' => false, 
			'store' => false);
		
		// Don't show suppliers and manufacturers if they are disallowed
		if (!Module::getInstanceByName('blockmanufacturer')->id && !Configuration::get('PS_DISPLAY_SUPPLIERS'))
			unset($pages['manufacturer']);
			
		if (!Module::getInstanceByName('blocksupplier')->id && !Configuration::get('PS_DISPLAY_SUPPLIERS'))
			unset($pages['supplier']);

		// Generate nodes for pages
		/*if (Configuration::get('PS_REWRITING_SETTINGS'))		
			foreach ($pages AS $page => $ssl)		
				foreach($langs as $lang)
					$this->_addSitemapNode($xml, $link->getPageLink($page.'.php', $ssl, $lang['id_lang']), '0.5', 'monthly');
		else
			foreach($pages AS $page => $ssl)
				$this->_addSitemapNode($xml, $link->getPageLink($page.'.php', $ssl), '0.5', 'monthly');*/
        
        /*Page Link*/
        $page_array=array('ayakkabi-modelleri', 'dugun-toren-ayakkabi-modelleri', 'butigo-magazin','gorusleriniz','7-iadeler-degisimler','8-gizlilik','9-kullanici-sozlesmesi','11-hakkimizda','12-bize-ulasin','blog','sss','ayakkabi-tasarimcilari');
        
        foreach($page_array as $key => $pageLink){
            $this->_addSitemapNode($xml, $link->getPageLink($pageLink, $ssl, $lang['id_lang']), '0.5', 'monthly');
        }

        $xmlString = $xml->asXML();
		
        $fp = fopen(GSITEMAP_FILE, 'w');
        fwrite($fp, $xmlString);
        fclose($fp);

        $res = file_exists(GSITEMAP_FILE);
        $this->_html .= '<h3 class="'. ($res ? 'conf confirm' : 'alert error') .'" style="margin-bottom: 20px">';
        $this->_html .= $res ? $this->l('Sitemap file generated') : $this->l('Error while creating sitemap file');
        $this->_html .= '</h3>';
    }
	
	private function _addSitemapNode($xml, $loc, $priority, $change_freq, $last_mod = NULL)
	{		
		$sitemap = $xml->addChild('url');
		$sitemap->addChild('loc', $loc);
		$sitemap->addChild('priority',  $priority);
		if ($last_mod)
			$sitemap->addChild('lastmod', $last_mod);
		$sitemap->addChild('changefreq', $change_freq);
		return $sitemap;
	}
	
	private function _addSitemapNodeImage($xml, $product)
	{
		$link = new Link();	
		$image = $xml->addChild('image', null, 'http://www.google.com/schemas/sitemap-image/1.1');
		$image->addChild('loc', $link->getImageLink($product['link_rewrite'], (int)$product['id_product'].'-'.(int)$product['id_image']), 'http://www.google.com/schemas/sitemap-image/1.1');
		
		$legend_image = preg_replace('/(&+)/i', '&amp;', $product['legend_image']);
		$image->addChild('caption', $legend_image, 'http://www.google.com/schemas/sitemap-image/1.1');
		$image->addChild('title', $legend_image, 'http://www.google.com/schemas/sitemap-image/1.1');
	}

    private function _displaySitemap()
    {
        if (file_exists(GSITEMAP_FILE) AND filesize(GSITEMAP_FILE))
        {			
            $fp = fopen(GSITEMAP_FILE, 'r');
            $fstat = fstat($fp);
            fclose($fp);
            $xml = simplexml_load_file(GSITEMAP_FILE);
			
            $nbPages = sizeof($xml->url);

            $this->_html .= '<p>'.$this->l('Your Google sitemap file is online at the following address:').'<br />
            <a href="'.Tools::getShopDomain(true, true).__PS_BASE_URI__.'sitemap.xml" target="_blank"><b>'.Tools::getShopDomain(true, true).__PS_BASE_URI__.'sitemap.xml</b></a></p><br />';

            $this->_html .= $this->l('Update:').' <b>'.utf8_encode(strftime('%A %d %B %Y %H:%M:%S',$fstat['mtime'])).'</b><br />';
            $this->_html .= $this->l('Filesize:').' <b>'.number_format(($fstat['size']*.000001), 3).'MB</b><br />';
            $this->_html .= $this->l('Indexed pages:').' <b>'.$nbPages.'</b><br /><br />';
        }
    }

	private function _displayForm()
	{
		$this->_html .=
		'<form action="'.Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI']).'" method="post">
			<div style="margin:0 0 20px 0;">
				<input type="checkbox" name="GSITEMAP_ALL_PRODUCTS" id="GSITEMAP_ALL_PRODUCTS" style="vertical-align: middle;" value="1" '.(Configuration::get('GSITEMAP_ALL_PRODUCTS') ? 'checked="checked"' : '').' /> <label class="t" for="GSITEMAP_ALL_PRODUCTS">'.$this->l('Sitemap also includes products from inactive categories').'</label>
			</div>
			<div style="margin:0 0 20px 0;">
				<input type="checkbox" name="GSITEMAP_ALL_CMS" id="GSITEMAP_ALL_CMS" style="vertical-align: middle;" value="1" '.(Configuration::get('GSITEMAP_ALL_CMS') ? 'checked="checked"' : '').' /> <label class="t" for="GSITEMAP_ALL_CMS">'.$this->l('Sitemap also includes CMS pages which are not in a CMS block').'</label>
			</div>
			<input name="btnSubmit" class="button" type="submit"
			value="'.((!file_exists(GSITEMAP_FILE)) ? $this->l('Generate sitemap file') : $this->l('Update sitemap file')).'" />
		</form>';
	}
	
	public function getContent()
	{
		$this->_html .= '<h2>'.$this->l('Search Engine Optimization').'</h2>
		'.$this->l('See').' <a href="https://www.google.com/webmasters/tools/docs/en/about.html" style="font-weight:bold;text-decoration:underline;" target="_blank">
		'.$this->l('this page').'</a> '.$this->l('for more information').'<br /><br />';
		if (Tools::isSubmit('btnSubmit'))
		{
			$this->_postValidation();
			if (!sizeof($this->_postErrors))
				$this->_postProcess();
			else
				foreach ($this->_postErrors AS $err)
					$this->_html .= '<div class="alert error">'.$err.'</div>';
		}

		$this->_displaySitemap();
		$this->_displayForm();

		return $this->_html;
	}
}

