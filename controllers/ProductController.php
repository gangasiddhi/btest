<?php
/*
* 2007-2011 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
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
*  @version  Release: $Revision: 7733 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class ProductControllerCore extends FrontController
{
	protected $product;
	public $php_self = 'product.php';
	protected $canonicalURL;
    protected $new_product_page = 0;
    protected $accesssoriesed_product = 0;


    /**
     * Cache wrapper for getting product for frontend
     */
    private function _getProductById($id)
    {
        $cacheKey = "product_detail_" . $id . "_". self::$cookie->id_lang;
        $productData = BCache::get($cacheKey);
        if (!$productData) {
            $productData = new Product($id, true, self::$cookie->id_lang);
            BCache::set($cacheKey, $productData, 0);
        }

        return $productData;
    }

    public function setMedia()
	{
		parent::setMedia();

        if ($id_product = (int)Tools::getValue('id_product')) {
			$this->product = $this->_getProductById($id_product);
        }


        $category = new Category((int)$this->product->id_category_default);
        $categoryName = $category->getName(self::$cookie->id_lang);
        if($categoryName === "AccessoriesedProducts")
            $this->accesssoriesed_product = 1;

        /*if($this->accesssoriesed_product == 1) {
            Tools::addCSS(_THEME_CSS_DIR_.'product.css');

		    Tools::addJS(_THEME_JS_DIR_.'tools.js');
			Tools::addJS(_PS_JS_DIR_.'main.js');

			$jqZoomEnabled = (Configuration::get('PS_DISPLAY_JQZOOM') == 1);
			if ($jqZoomEnabled)
			{
				Tools::addJS(_PS_JS_DIR_.'jquery/jquery.cloudzoom.js');
				Tools::addCSS(_PS_CSS_DIR_.'cloudzoom.css');
			}

            Tools::addJS(array(
                _PS_JS_DIR_.'jquery/jquery.idTabs.modified.js',
				_PS_JS_DIR_.'jquery/jquery.scrollto.js',
				_PS_JS_DIR_.'jquery/jquery.serialScroll.js',
                _THEME_JS_DIR_.'product.js'
            ));
        } else {*/
            /* BEGIN New Product Page */
            if (self::$cookie->isLogged() && intval(self::$cookie->id_customer)) {
                $customer = new Customer(intval(self::$cookie->id_customer));
                $groups = Group::getGroups(self::$cookie->id_lang);

                foreach($groups AS $group) {
                    if(strpos($group['name'], 'NewProductPage') !== false )
                        $group_id_new_product_page = $group['id_group'];
                }

                if( $customer->isMemberOfGroup($group_id_new_product_page)) {
                    $this->new_product_page = 1;
                    Tools::addCSS(_THEME_CSS_DIR_.'accordion.core.css');
                    Tools::addCSS(_THEME_CSS_DIR_.'product-new.css');
					Tools::addCSS(_THEME_CSS_DIR_.'product-recommend-size.css');
					Tools::addJS(_THEME_JS_DIR_.'tools.js');
					Tools::addJS(_PS_JS_DIR_.'main.js');

					/* jqZoom */
					$jqZoomEnabled = (Configuration::get('PS_DISPLAY_JQZOOM') == 1);
					if ($jqZoomEnabled)
					{
						Tools::addJS(_PS_JS_DIR_.'jquery/jquery.cloudzoom.js');
						Tools::addCSS(_PS_CSS_DIR_.'cloudzoom.css');
					}

                    Tools::addJS(array(
                        _PS_JS_DIR_ . 'jquery/jquery.scrollTo.min.js',
                        _PS_JS_DIR_ . 'jquery/jquery.serialScroll-1.2.2-min.js',
                        _PS_JS_DIR_ . 'jquery/jquery-ui-1.10.3.custom.min.js',
                        _PS_JS_DIR_ . 'jquery/jquery.countdown.js',
                        _PS_JS_DIR_ . 'jquery/jquery.countdown-tr.js',
                        _THEME_JS_DIR_ . 'product-new.js',
						_MODULE_DIR_.'productrecommend/js/product-recommend-size.js'
                    ));
                } else {
                    Tools::addCSS(_THEME_CSS_DIR_.'product.css');
					Tools::addCSS(_THEME_CSS_DIR_.'product-recommend-size.css');
					Tools::addJS(_THEME_JS_DIR_.'tools.js');
					Tools::addJS(_PS_JS_DIR_.'main.js');

					/* jqZoom */
					$jqZoomEnabled = (Configuration::get('PS_DISPLAY_JQZOOM') == 1);
					if ($jqZoomEnabled)
					{
						Tools::addJS(_PS_JS_DIR_.'jquery/jquery.cloudzoom.js');
						Tools::addCSS(_PS_CSS_DIR_.'cloudzoom.css');
					}

                    Tools::addJS(array(
                        _PS_JS_DIR_.'jquery/jquery.idTabs.modified.js',
						_PS_JS_DIR_.'jquery/jquery.scrollto.js',
						_PS_JS_DIR_.'jquery/jquery.serialScroll.js',
                        _THEME_JS_DIR_.'product.js',
						_MODULE_DIR_.'productrecommend/js/product-recommend-size.js'
                    ));
                }
            } else {
                Tools::addCSS(_THEME_CSS_DIR_.'product.css');
				Tools::addCSS(_THEME_CSS_DIR_.'product-recommend-size.css');
				Tools::addJS(_THEME_JS_DIR_.'tools.js');
				Tools::addJS(_PS_JS_DIR_.'main.js');

				/* jqZoom */
				$jqZoomEnabled = (Configuration::get('PS_DISPLAY_JQZOOM') == 1);
				if ($jqZoomEnabled)
				{
					Tools::addJS(_PS_JS_DIR_.'jquery/jquery.cloudzoom.js');
					Tools::addCSS(_PS_CSS_DIR_.'cloudzoom.css');
				}

                Tools::addJS(array(
                    _PS_JS_DIR_.'jquery/jquery.idTabs.modified.js',
					_PS_JS_DIR_.'jquery/jquery.scrollto.js',
					_PS_JS_DIR_.'jquery/jquery.serialScroll.js',
                    _THEME_JS_DIR_.'product.js',
					_MODULE_DIR_.'productrecommend/js/product-recommend-size.js'
                ));
            }
            /* END New Product Page*/
       // }
	}

	public function canonicalRedirection()
	{
		// Automatically redirect to the canonical URL if the current in is the right one
		// $_SERVER['HTTP_HOST'] must be replaced by the real canonical domain
		if (Validate::isLoadedObject($this->product))
		{
			$canonicalURL = self::$link->getProductLink($this->product, Tools::getValue('id_product_attribute'));
			if (!preg_match('/^'.Tools::pRegexp($canonicalURL, '/').'([&?].*)?$/', Tools::getProtocol().$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']))
			{
				header('HTTP/1.0 301 Moved');
				if (defined('_PS_MODE_DEV_') AND _PS_MODE_DEV_)
					die('[Debug] This page has moved<br />Please use the following URL instead: <a href="'.$canonicalURL.'">'.$canonicalURL.'</a>');
				Tools::redirectLink($canonicalURL);
			}
		}
	}


	public function run()
	{
		$this->init();

        $this->preProcess();
        $this->setMedia();
        $this->process();
        $this->displayHeader();
        $this->displayContent();
        $this->displayFooter();
	}

	public function preProcess()
	{
        if ($id_product = (int)Tools::getValue('id_product')) {
			$this->product = $this->_getProductById($id_product);
        }

		if (!Validate::isLoadedObject($this->product))
		{
			header('HTTP/1.1 404 Not Found');
			header('Status: 404 Not Found');
		}
		else
			$this->canonicalRedirection();

		parent::preProcess();

		/* Product videos */
		if( in_array($id_product, array(1807, 1943, 2013, 2071, 1904, 1990)) ) {
			switch($id_product)
			{
				case 1807: self::$smarty->assign('video_link', 'http://www.youtube.com/embed/KXGU3b9_Y_M?rel=0');
							break;
				case 1943: self::$smarty->assign('video_link', 'http://www.youtube.com/embed/EXI14QqzbCE?rel=0');
							break;
				case 2013: self::$smarty->assign('video_link', 'http://www.youtube.com/embed/CcUmAL9gLeg?rel=0');
							break;
				case 2071: self::$smarty->assign('video_link', 'http://www.youtube.com/embed/yqWz-dw9v6A?rel=0');
							break;
				case 1904: self::$smarty->assign('video_link', 'http://www.youtube.com/embed/pQbMC5ANqe8?rel=0');
							break;
				case 1990: self::$smarty->assign('video_link', 'http://www.youtube.com/embed/zyFzuLCGw9U?rel=0');
							break;
			}
        }

		if(intval(Configuration::get('PS_REWRITING_SETTINGS')) === 1)
			$rewrited_url = null;
	}

	public function process()
	{
		global $cart, $currency, $errors, $link;

		parent::process();

        if($_COOKIE['bu_bcpath'] == "handbags" || $_COOKIE['bu_bcpath'] == "bayan-canta-modelleri") {
            self::$smarty->assign('breadcrumb_path', $_COOKIE['bu_bcpath']);
        } elseif($_COOKIE['bu_bcpath'] == "taki-modelleri" || $_COOKIE['bu_bcpath'] == "taki") {
            self::$smarty->assign('breadcrumb_path', $_COOKIE['bu_bcpath']);
        } elseif($_COOKIE['bu_bcpath'] == "kisa-topuklu-ayakkabi-modelleri") {
            self::$smarty->assign('breadcrumb_path', $_COOKIE['bu_bcpath']);
        } elseif($_COOKIE['bu_bcpath'] == "ayakkabi-aksesuarlari") {
            self::$smarty->assign('breadcrumb_path', $_COOKIE['bu_bcpath']);
        } elseif($_COOKIE['bu_bcpath'] == "shoes" && $_COOKIE['bu_bcpath_categories'] == "") {
            self::$smarty->assign('breadcrumb_path', $_COOKIE['bu_bcpath']);
        } else {
            self::$smarty->assign(array(
                'breadcrumb_path' => $_COOKIE['bu_bcpath'],
                /*'breadcrumb_path_categories' =>$category_path*/
                'breadcrumb_path_categories' => $_COOKIE['bu_bcpath_categories']
            ));
        }

        if ($this->new_product_page === 1) {
            if($_COOKIE['bu_bcpath'] == "handbags" || $_COOKIE['bu_bcpath'] == "canta") {
                self::$smarty->assign('breadcrumb_path', $_COOKIE['bu_bcpath']);
            } elseif($_COOKIE['bu_bcpath'] == "jewelry" || $_COOKIE['bu_bcpath'] == "taki") {
                self::$smarty->assign('breadcrumb_path', $_COOKIE['bu_bcpath']);
            } elseif($_COOKIE['bu_bcpath'] == "lowheels") {
                self::$smarty->assign('breadcrumb_path', $_COOKIE['bu_bcpath']);
            } elseif($_COOKIE['bu_bcpath'] == "accesories") {
                self::$smarty->assign('breadcrumb_path', $_COOKIE['bu_bcpath']);
            } elseif($_COOKIE['bu_bcpath'] == "shoes" && $_COOKIE['bu_bcpath_categories'] == "") {
                self::$smarty->assign('breadcrumb_path', $_COOKIE['bu_bcpath']);
            } else {
                self::$smarty->assign(array(
                    'breadcrumb_path' => $_COOKIE['bu_bcpath'],
                    'breadcrumb_path_categories' => $_COOKIE['bu_bcpath_categories']
                ));
            }
            setcookie('bu_bcpath' , "", time() - 3600);
            setcookie('bu_bcpath_categories', "", time() - 3600);

            $day = date('N');
            $current_time = strtotime(date("G:i"));
            $zero_hour = strtotime("00:00");
            $twelve_fifty_nine = strtotime("12:59");
            $thirteen_hundred = strtotime("13:00");
            $twenty_three_fifty_nine = strtotime("23:59");

            $url_product_attribute = Tools::getValue('id_product_attribute');

            if (isset($this->product->out_of_stock) && $this->product->out_of_stock == 1) {
                $delivery_day =  (date('d-m-Y', strtotime('tomorrow +11 days')));
            } else {
                if ($day >= 1 && $day <= 4 && $current_time >= $zero_hour && $current_time <= $twelve_fifty_nine) {
                    $delivery_day = date('d-m-Y', strtotime("tomorrow"));
                    $shipping_time = $twelve_fifty_nine - $current_time;
                } elseif ($day >= 1 && $day <= 4 && $current_time >= $thirteen_hundred && $current_time <= $twenty_three_fifty_nine) {
                    $delivery_day = date('d-m-Y', strtotime('tomorrow +1 day'));
                    $shipping_time = strtotime("12:59 +1 day") - $current_time;
                } elseif (($day == 5 && ($current_time <= $twelve_fifty_nine))) {
                    $delivery_day =  (date('d-m-Y', strtotime('tomorrow +2 days')));
                    $shipping_time = $twelve_fifty_nine - $current_time;
                } elseif (($day == 5 && ($current_time >= $twelve_fifty_nine))) {
                    $delivery_day = (date('d-m-Y', strtotime('tomorrow +3 days')));
                    $shipping_time = strtotime("12:59 +3 days") - $current_time;
                } elseif (($day == 6 && ($current_time <= $twelve_fifty_nine || $current_time >= $twelve_fifty_nine))) {
                    $delivery_day = (date('d-m-Y', strtotime('tomorrow +2 days')));
                    $shipping_time = strtotime("12:59 +2 days") - $current_time;
                } elseif (($day == 7 && ($current_time <= $twelve_fifty_nine || $current_time >= $twelve_fifty_nine))) {
                    $delivery_day = (date('d-m-Y', strtotime('tomorrow +1 day')));
                    $shipping_time = strtotime("12:59 +1 day") - $current_time;
                }

                if ($day >= 1 && $day < 5 && $current_time >= $zero_hour && $current_time <= $twelve_fifty_nine) {
                    $phrase_text = 'BUGÜN';
                } elseif ($day >= 1 && $day < 5 && $current_time >= $thirteen_hundred && $current_time <= $twenty_three_fifty_nine) {
                    $phrase_text = 'YARIN';
                } elseif ($day == 5 && $current_time >= $zero_hour && $current_time <= $twelve_fifty_nine) {
                    $phrase_text = 'BUGÜN';
                }  elseif ($day == 5 && $current_time >= $thirteen_hundred && $current_time <= $twenty_three_fifty_nine) {
                    $phrase_text = 'PAZARTESİ';
                } else {
                    $phrase_text = 'PAZARTESİ';
                }
            }

            self::$smarty->assign( array(
                'current_time' => $current_time,
                'shipping_time' => $shipping_time,
                'delivery_date' => $delivery_day,
                'phrase_text' => $phrase_text
            ));
        }

		// Modules might throw errors into postProcess
		if (!isset($errors))
			$errors = array();

		$jqZoomEnabled = (Configuration::get('PS_DISPLAY_JQZOOM') == 1);

		if (!Validate::isLoadedObject($this->product))
		//if (!$id_product = intval(Tools::getValue('id_product')) OR !Validate::isUnsignedId($id_product))
			$this->errors[] = Tools::displayError('Product not found');
		else
		{
			if ((!$this->product->active AND !$this->product->showing_status AND (Tools::getValue('adtoken') != Tools::encrypt('PreviewProduct'.$this->product->id))
				|| !file_exists(dirname(__FILE__).'/../'.Tools::getValue('ad').'/ajax.php')))
			{
				header('HTTP/1.1 404 page not found');
				$this->errors[] = Tools::displayError('Product is no longer available.');
			}
			elseif (!$this->product->checkAccess((int)self::$cookie->id_customer))
				$this->errors[] = Tools::displayError('You do not have access to this product.');
			else
			{
				/**start
				$default_rewrite = $link->getProductLink($this->product->id, $product->link_rewrite, $product->category, $product->ean13);

				// remove http or https
				$default_rewrite_pattern = substr($default_rewrite, strpos($default_rewrite, '/'));
				if (Configuration::get('PS_REWRITING_SETTINGS') AND !preg_match('!'.quotemeta($default_rewrite_pattern).'!', Tools::getHttpHost(true).$_SERVER['REQUEST_URI']))
				{
					header("Status: 301 Moved Permanently", false, 301);
					header('Location: '.$default_rewrite);
				}
				end**/

				self::$smarty->assign('virtual', ProductDownload::getIdFromIdProduct((int)$this->product->id));

				/**start
				//rewrited url set
				$rewrited_url = $link->getProductLink($product->id, $product->link_rewrite);
				end**/

				if (!$this->product->active)
					self::$smarty->assign('adminActionDisplay', true);

				/* Product pictures management */
				require_once('images.inc.php');

				if ($this->product->customizable)
				{
					self::$smarty->assign('customizationFormTarget', Tools::safeOutput(urldecode($_SERVER['REQUEST_URI'])));

					if (Tools::isSubmit('submitCustomizedDatas'))
					{
//						$this->pictureUpload($this->product, $cart);
//						$this->textRecord($this->product, $cart);
						$this->formTargetFormat();
					}
//					elseif (isset($_GET['deletePicture']) AND !$cart->deletePictureToProduct((int)($this->product->id), (int)(Tools::getValue('deletePicture'))))
//						$this->errors[] = Tools::displayError('An error occurred while deleting the selected picture');

					$files = self::$cookie->getFamily('pictures_'.(int)($this->product->id));
					$textFields = self::$cookie->getFamily('textFields_'.(int)($this->product->id));
					foreach ($textFields as $key => $textField)
						$textFields[$key] = str_replace('<br />', "\n", $textField);
					self::$smarty->assign(array(
						'pictures' => $files,
						'textFields' => $textFields));
				}

				/**start
				$productPriceWithTax = Product::getPriceStatic($id_product, true, NULL, 6);
				if (Product::$_taxCalculationMethod == PS_TAX_INC)
					$productPriceWithTax = Tools::ps_round($productPriceWithTax, 2);

				$productPriceWithoutEcoTax = floatval($productPriceWithTax - $product->ecotax);

				$configs = Configuration::getMultiple(array('PS_ORDER_OUT_OF_STOCK', 'PS_LAST_QTIES'));
				end**/

				/* Features / Values */
//				$features = $this->product->getFrontFeatures((int)self::$cookie->id_lang);
//				$attachments = ($this->product->cache_has_attachments ? $this->product->getAttachments((int)self::$cookie->id_lang) : array());
				$features = $this->product->getFrontFeatures(intval(self::$cookie->id_lang));
				$attachments = $this->product->getAttachments(intval(self::$cookie->id_lang));

				/* Category */
				$category = false;
				if (isset($_SERVER['HTTP_REFERER']) AND preg_match('!^(.*)\/([0-9]+)\-(.*[^\.])|(.*)id_category=([0-9]+)(.*)$!', $_SERVER['HTTP_REFERER'], $regs) AND !strstr($_SERVER['HTTP_REFERER'], '.html'))
				{
					if (isset($regs[2]) AND is_numeric($regs[2]))
					{
						if (Product::idIsOnCategoryId((int)($this->product->id), array('0' => array('id_category' => (int)($regs[2])))))
							$category = new Category((int)($regs[2]), (int)(self::$cookie->id_lang));
					}
					elseif (isset($regs[5]) AND is_numeric($regs[5]))
					{
						if (Product::idIsOnCategoryId((int)($this->product->id), array('0' => array('id_category' => (int)($regs[5])))))
							$category = new Category((int)($regs[5]), (int)(self::$cookie->id_lang));
					}
				}
				if (!$category)
					$category = new Category($this->product->id_category_default, (int)(self::$cookie->id_lang));

				if (isset($category) AND Validate::isLoadedObject($category))
				{
					self::$smarty->assign(array(
						'path' => Tools::getPath((int)$category->id, $this->product->name, true),
						'category' => $category,
						'subCategories' => $category->getSubCategories((int)self::$cookie->id_lang, true),
						'id_category_current' => (int)$category->id,
						'id_category_parent' => (int)$category->id_parent,
						'return_category_name' => Tools::safeOutput($category->name)

					));
				}
				else
					self::$smarty->assign('path', Tools::getPath((int)$this->product->id_category_default, $this->product->name));

				self::$smarty->assign('return_link', (isset($category->id) AND $category->id) ? Tools::safeOutput(self::$link->getCategoryLink($category->link_rewrite)) : 'javascript: history.back();');

				if (Pack::isPack((int)$this->product->id) AND !Pack::isInStock((int)$this->product->id))
					$this->product->quantity = 0;

				$group_reduction = (100 - Group::getReduction((int)self::$cookie->id_customer)) / 100;
				$id_customer = (isset(self::$cookie->id_customer) AND self::$cookie->id_customer) ? (int)(self::$cookie->id_customer) : 0;
				$id_group = $id_customer ? (int)(Customer::getDefaultGroupId($id_customer)) : _PS_DEFAULT_CUSTOMER_GROUP_;
				$id_country = (int)($id_customer ? Customer::getCurrentCountry($id_customer) : Configuration::get('PS_COUNTRY_DEFAULT'));

				// Tax
				/*$tax = (float)(Tax::getProductTaxRate((int)($this->product->id), $cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')}));
				self::$smarty->assign('tax_rate', $tax);

				$productPriceWithTax = Product::getPriceStatic($this->product->id, true, NULL, 6);
				if (Product::$_taxCalculationMethod == PS_TAX_INC)
					$productPriceWithTax = Tools::ps_round($productPriceWithTax, 2);
				$productPriceWithoutEcoTax = (float)($productPriceWithTax - $this->product->ecotax);

				$ecotax_rate = (float) Tax::getProductEcotaxRate($cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')});
				$ecotaxTaxAmount = Tools::ps_round($this->product->ecotax, 2);
				if (Product::$_taxCalculationMethod == PS_TAX_INC && (int)Configuration::get('PS_TAX'))
					$ecotaxTaxAmount = Tools::ps_round($ecotaxTaxAmount * (1 + $ecotax_rate / 100), 2);*/
				$url_product_attribute = Tools::getValue('id_product_attribute');

				self::$smarty->assign(array(
					//'quantity_discounts' => $this->formatQuantityDiscounts(SpecificPrice::getQuantityDiscounts((int)$this->product->id, (int)Shop::getCurrentShop(), (int)self::$cookie->id_currency, $id_country, $id_group), $this->product->getPrice(Product::$_taxCalculationMethod == PS_TAX_INC, false), (float)$tax),
					'product' => $this->product,
					'jqZoomEnabled' => $jqZoomEnabled,
					'url_product_attribute' =>  $url_product_attribute,
//					'ecotax_tax_inc' => $ecotaxTaxAmount,
//					'ecotax_tax_exc' => Tools::ps_round($this->product->ecotax, 2),
//					'ecotaxTax_rate' => $ecotax_rate,
//					'homeSize' => Image::getSize('home'),
//					'product_manufacturer' => new Manufacturer((int)$this->product->id_manufacturer, self::$cookie->id_lang),
					'token' => Tools::getToken(false),
//					'productPriceWithoutEcoTax' => (float)($productPriceWithoutEcoTax),
					'features' => $features,
					'attachments' => $attachments,
					'allow_oosp' => $this->product->isAvailableWhenOutOfStock((int)($this->product->out_of_stock)),
					'last_qties' =>  (int)Configuration::get('PS_LAST_QTIES'),
					'group_reduction' => $group_reduction,
					'col_img_dir' => _PS_COL_IMG_DIR_
				));

				self::$smarty->assign(array(
					'HOOK_EXTRA_LEFT' => Module::hookExec('extraLeft'),
					'HOOK_EXTRA_RIGHT' => Module::hookExec('extraRight'),
					'HOOK_PRODUCT_OOS' => Hook::productOutOfStock($this->product),
					'HOOK_PRODUCT_FOOTER' => Hook::productFooter($this->product, $category),
					'HOOK_PRODUCT_ACTIONS' => Module::hookExec('productActions'),
					'HOOK_PRODUCT_TAB' =>  Module::hookExec('productTab'),
					'HOOK_PRODUCT_TAB_CONTENT' =>  Module::hookExec('productTabContent'),
					'HOOK_PRODUCT_FOOTER_BLOCK' => Module::hookExec('productFooterBlock'),
					'HOOK_PRODUCT_LOGGED_FOOTERBLOCK' => Module::hookExec('productLoggedFooterBlock'),
				));

				/*$images = $this->product->getImages((int)self::$cookie->id_lang);
				$productImages = array();
				foreach ($images AS $k => $image)
				{
					if($url_product_attribute>0)
					{
						$image['id_image']='426';
					}
					if ($image['cover'])
					{
						self::$smarty->assign('mainImage', $images[0]);
						$cover = $image;
						$cover['id_image'] = (Configuration::get('PS_LEGACY_IMAGES') ? ($this->product->id.'-'.$image['id_image']) : $image['id_image']);
						$cover['id_image_only'] = (int)($image['id_image']);
					}
					$productImages[(int)$image['id_image']] = $image;
				}

				if (!isset($cover))
					$cover = array('id_image' => Language::getIsoById(self::$cookie->id_lang).'-default', 'legend' => 'No picture', 'title' => 'No picture');
				$size = Image::getSize('large');
				self::$smarty->assign(array(
					'cover' => $cover,
					'imgWidth' => (int)($size['width']),
					'mediumSize' => Image::getSize('medium'),
					'largeSize' => Image::getSize('large'),
					'accessories' => $this->product->getAccessories((int)self::$cookie->id_lang)
				));
				if (count($productImages))
					self::$smarty->assign('images', $productImages);*/

				/* Attributes / Groups & colors */
				$colors = array();
				$attributesGroups = $this->product->getAttributesGroups((int)(self::$cookie->id_lang));  // @todo (RM) should only get groups and not all declination ?

				if (is_array($attributesGroups) AND $attributesGroups)
				{
					$groups = array();
					$combinationImages = $this->product->getCombinationImages((int)(self::$cookie->id_lang));

					foreach ($attributesGroups AS $k => $row)
					{
						/* Color management */
						if (((isset($row['attribute_color']) AND $row['attribute_color']) OR (file_exists(_PS_COL_IMG_DIR_.$row['id_attribute'].'.jpg'))) AND $row['id_attribute_group'] == $this->product->id_color_default)
						{
							$colors[$row['id_attribute']]['value'] = $row['attribute_color'];
							$colors[$row['id_attribute']]['name'] = $row['attribute_name'];
							if (!isset($colors[$row['id_attribute']]['attributes_quantity']))
								$colors[$row['id_attribute']]['attributes_quantity'] = 0;
							$colors[$row['id_attribute']]['attributes_quantity'] += (int)($row['quantity']);
						}
						if (!isset($groups[$row['id_attribute_group']]))
						{
							$groups[$row['id_attribute_group']] = array(
								'name' =>			$row['public_group_name'],
								'is_color_group' =>	$row['is_color_group'],
//								'default' =>		-1
							);
						}

						$groups[$row['id_attribute_group']]['attributes'][$row['id_attribute']] = $row['attribute_name'];
//						if ($row['default_on'] && $groups[$row['id_attribute_group']]['default'] == -1)
//							$groups[$row['id_attribute_group']]['default'] = intval($row['attribute_name']); //(int)($row['id_attribute']);
						if (!isset($groups[$row['id_attribute_group']]['attributes_quantity'][$row['id_attribute']]))
							$groups[$row['id_attribute_group']]['attributes_quantity'][$row['id_attribute']] = 0;
						$groups[$row['id_attribute_group']]['attributes_quantity'][$row['id_attribute']] += (int)($row['quantity']);


						$combinations[$row['id_product_attribute']]['attributes_values'][$row['id_attribute_group']] = $row['attribute_name'];
						$combinations[$row['id_product_attribute']]['attributes'][] = (int)($row['id_attribute']);
						$combinations[$row['id_product_attribute']]['price'] = (float)($row['price']);
						$combinations[$row['id_product_attribute']]['ecotax'] = (float)($row['ecotax']);
						$combinations[$row['id_product_attribute']]['weight'] = (float)($row['weight']);
						$combinations[$row['id_product_attribute']]['quantity'] = (int)($row['quantity']);
						$combinations[$row['id_product_attribute']]['reference'] = $row['reference'];
						$combinations[$row['id_product_attribute']]['unit_impact'] = $row['unit_price_impact'];
						$combinations[$row['id_product_attribute']]['minimal_quantity'] = $row['minimal_quantity'];
						$combinations[$row['id_product_attribute']]['id_image'] = isset($combinationImages[$row['id_product_attribute']][0]['id_image']) ? $combinationImages[$row['id_product_attribute']][0]['id_image'] : -1;
						if ($row['default_image']){
							$combinations[$row['id_product_attribute']]['default_ipa'] = $row['default_image'];
						}
					}

//					$id_product = (int)Tools::getValue('id_product');
//					$id_attribute_color = Product::getIpaAttribute($id_product);
					//$ipa = Tools::getValue('id_product_attribute');

					if($url_product_attribute > 0)
					{
						$id_attri_color = Product::getIdAttribute($url_product_attribute);
						self::$smarty->assign('id_attribute_color' , $id_attri_color[0]['id_attribute']);
						self::$smarty->assign('color_id_quantity',$colors[$id_attri_color[0]['id_attribute']]['attributes_quantity']);
					}
						$color_combination = array();
						foreach($combinations AS $key => $combination)
						{

							if((isset($combination['attributes_values'][2]) && isset($combination['attributes_values'][4])) || isset($combination['attributes_values'][4]))
							{
								if($url_product_attribute > 0)
								{
									if($url_product_attribute == $key )
									{
										self::$smarty->assign('default_color' , $combination['attributes'][1]);
										if(isset($combination['default_ipa']))
											$color_id_image = $combination['default_ipa'];
									}

	//								if(isset($combination['default_ipa']))
	//									$colors[$combination['attributes'][1]]['id_product_attribute'] = $combination['default_ipa'];

									//$color_combination[$combination['attributes'][1]]['ipa'][] = $key;

									if(isset($combination['default_ipa']))
										$colors[$combination['attributes'][1]]['id_product_attribute'] = $key;
									$color_combination[$combination['attributes'][1]]['shoesize'][$combination['attributes'][0]]['size'] = $combination['attributes_values'][4];
									$color_combination[$combination['attributes'][1]]['shoesize'][$combination['attributes'][0]]['quantity'] = $combination['quantity'];
									$color_combination[$combination['attributes'][1]]['shoesize'][$combination['attributes'][0]]['ipa'] = $key;
								}
								if(isset($combination['attributes_values'][4]))
									self::$smarty->assign('color_shoe_combination' , 1);

							}else if((isset($combination['attributes_values'][2]) && isset($combination['attributes_values'][5])) || isset($combination['attributes_values'][5])){
								if($url_product_attribute > 0)
								{
									if($url_product_attribute == $key )
									{
										self::$smarty->assign('default_color' , $combination['attributes'][1]);
										if(isset($combination['default_ipa']))
											$color_id_image = $combination['default_ipa'];
									}

	//								if(isset($combination['default_ipa']))
	//									$colors[$combination['attributes'][1]]['id_product_attribute'] = $combination['default_ipa'];

									//$color_combination[$combination['attributes'][1]]['ipa'][] = $key;

									if(isset($combination['default_ipa']))
										$colors[$combination['attributes'][1]]['id_product_attribute'] = $key;
									$color_combination[$combination['attributes'][1]]['shoesize'][$combination['attributes'][0]]['size'] = $combination['attributes_values'][5];
									$color_combination[$combination['attributes'][1]]['shoesize'][$combination['attributes'][0]]['quantity'] = $combination['quantity'];
									$color_combination[$combination['attributes'][1]]['shoesize'][$combination['attributes'][0]]['ipa'] = $key;
								}
								if(isset($combination['attributes_values'][5]))
									self::$smarty->assign('accessory_size_combination' , 1);
							}else {
								if($url_product_attribute == $key )
								{
									self::$smarty->assign('default_color' , $combination['attributes'][0]);
									if(isset($combination['default_ipa']))
										$color_id_image = $combination['default_ipa'];
								}
								if(isset($combination['default_ipa']))
									$colors[$combination['attributes'][0]]['id_product_attribute'] = $key;
								//$color_combination[$combination['attributes'][1]]['ipa'][] = $key;
								self::$smarty->assign('color_shoe_combination' , 0);
							}
						}


self::$smarty->assign('color_combination' , $color_combination);
					$images = $this->product->getImages((int)self::$cookie->id_lang);

				$productImages = array();
				foreach ($images AS $k => $image)
				{
					if($url_product_attribute>0)
					{
						$image['fb_image'] = $color_id_image;
					}
					else
					{
						$image['fb_image'] = $image['id_image'];
					}
					if ($image['cover'])
					{
						self::$smarty->assign('mainImage', $images[0]);
						$cover = $image;
						$cover['id_image'] = (Configuration::get('PS_LEGACY_IMAGES') ? ($this->product->id.'-'.$image['id_image']) : $image['id_image']);
						$cover['fb_image'] = (Configuration::get('PS_LEGACY_IMAGES') ? ($this->product->id.'-'.$image['fb_image']) : $image['fb_image']);
						$cover['id_image_only'] = (int)($image['id_image']);
					}
					$productImages[(int)$image['id_image']] = $image;
				}

				if (!isset($cover))
					$cover = array('id_image' => Language::getIsoById(self::$cookie->id_lang).'-default', 'legend' => 'No picture', 'title' => 'No picture');

                                /*facebook like button sharing issue in facebook profile */

                                if($url_product_attribute > 0)
                                    $product_url = $link->getProductLink($this->product, $url_product_attribute);
                                 else
                                    $product_url = $link->getProductLink($this->product);

                                 self::$smarty->assign('product_url' , $product_url);

                                 /*facebook like button sharing issue in facebook profile */

                                 $size = Image::getSize('large');
				self::$smarty->assign(array(
					'cover' => $cover,
					'fb_image' => $cover['id_image'],
					//'fb_image_host' => $content_base_url,
					'thumbSize' => Image::getSize('prodthumb'),
					'imgWidth' => (int)($size['width']),
					'mediumSize' => Image::getSize('medium'),
					'prodSmall' => Image::getSize('prodsmall'),
					'largeSize' => Image::getSize('large'),
					'accessories' => $this->product->getAccessories((int)self::$cookie->id_lang)
				));

				if($this->product->recommendations != null){
					$recommendationsDetails = json_decode($this->product->recommendations);
					$recommendProducts = array();
					$recommendProductString = '';
					$recommendProductLists = explode('|',$recommendationsDetails->product_id_list);
					$recommendProductDetails = array();
					foreach($recommendProductLists as $recommendProductList){
						$recommendProductArray = explode('_', $recommendProductList);
						$recommendProducts[$recommendProductArray[2]] = $recommendProductArray[0];
						$recommendProductString .= $recommendProductArray[0] . ',';
						$recommendProductDetails[] = $this->product->getRecommendProductDetails((int)self::$cookie->id_lang, $recommendProductArray[0]);
					}
					$positionedRecommendProductDetails = array();
					foreach($recommendProductDetails as $recommendProductDetail){
						if(!empty($recommendProductDetail[0]) AND $recommendProductDetail[0]['quantity'] > 0 ){
							$positionedRecommendProductDetails[] = $recommendProductDetail[0];
						}
					}

					/*To check whether the recommend products have more than one combinations*/
					$i = 0;
					foreach($positionedRecommendProductDetails as $positionedRecommendProductDetail){
						$recommendProduct = new Product((int)$positionedRecommendProductDetail['id_product']);
						$positionedRecommendProductDetails[$i]['number_of_combinations'] = count($recommendProduct->getAttributesGroups((int)self::$cookie->id_lang));
						$i++;
					}

					self::$smarty->assign(array(
						'recommendEnable' => $recommendationsDetails->recommend,
						'recommendProductDetails'=>$positionedRecommendProductDetails
					));
				}

				if (count($productImages))
					self::$smarty->assign('images', $productImages);

					$attr_q = array();
					//wash attributes list (if some attributes are unavailables and if allowed to wash it)
                    //commented for Back Order feature
					//if (!Product::isAvailableWhenOutOfStock($this->product->out_of_stock) && Configuration::get('PS_DISP_UNAVAILABLE_ATTR') == 0)
					{
						foreach ($groups AS &$group)
							foreach ($group['attributes_quantity'] AS $key => &$quantity)
								if (!$quantity)
									$attr_q[] = $group['attributes'][$key];
//						foreach ($colors AS $key => $color)
//							if (!$color['attributes_quantity'])
//								unset($colors[$key]);

					}

//					foreach ($groups AS &$group)
//						natcasesort($group['attributes']);

					self::$smarty->assign('attr_q' , $attr_q);
								  //unset($group['attributes'][$key]);
					/*$customer_shoe_size = intval(Customer::getShoeSize(intval(self::$cookie->id_customer)));
					$customerShoeSizeDefault = $customer_shoe_size ;*/
					foreach($groups AS &$group)
					{
							natcasesort($group['attributes']);
							/*$customer_default_shoe_size = false;
							$customer_default_attribute = false;
							if($group['is_color_group'] == 0)
							{
								foreach($group['attributes'] AS $key => &$attribute)
									{
										if($customer_shoe_size == $attribute && (in_array($customer_shoe_size, $attr_q)== false))
										{
											  $customerShoeSizeDefault = $attribute;
											  $customer_default_shoe_size = true;
											  $customer_default_attribute = true;
										}
									}
									if($customer_default_shoe_size == false)
									{
										foreach($group['attributes'] AS $key => &$attribute)
											{
												if($group['default'] == $attribute && (in_array($group['default'], $attr_q)== false))
												{
													  $customerShoeSizeDefault = $attribute;
													  $customer_default_attribute = true;
													  break;
												}
											}
									}
									if($customer_default_attribute == false)
									{
										foreach($group['attributes'] AS $key => &$attribute)
											{
												if(in_array($attribute, $attr_q)== false)
												{
													  $customerShoeSizeDefault = $attribute;
													  break;
												}
											}
									}
									self::$smarty->assign('customerShoeSizeDefault' ,$customerShoeSizeDefault);
							}*/
					}

					foreach ($combinations AS $id_product_attribute => $comb)
					{
						$attributeList = '';
						foreach ($comb['attributes'] AS $id_attribute)
							$attributeList .= '\''.(int)($id_attribute).'\',';
						$attributeList = rtrim($attributeList, ',');
						$combinations[$id_product_attribute]['list'] = $attributeList;
					}


					self::$smarty->assign(array(
						'groups' => $groups,
						'combinaisons' => $combinations, /* Kept for compatibility purpose only */
						'combinations' => $combinations,
						'colors' => (sizeof($colors) AND $this->product->id_color_default) ? $colors : false,
						'combinationImages' => $combinationImages,
						//'customer_shoe_size'=>$customer_shoe_size
						));
				}

				self::$smarty->assign(array(
					//'no_tax' => Tax::excludeTaxeOption() OR !Tax::getProductTaxRate((int)$this->product->id, $cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')}),
					'customizationFields' => ($this->product->customizable ? $this->product->getCustomizationFields((int)self::$cookie->id_lang) : false)
				));

				// Pack management
				self::$smarty->assign('packItems', $this->product->cache_is_pack ? Pack::getItemTable($this->product->id, (int)(self::$cookie->id_lang), true) : array());
				self::$smarty->assign('packs', Pack::getPacksTable($this->product->id, (int)(self::$cookie->id_lang), true, 1));
			}
		}

		$customerLikesDislikes = CustomerLikesAndDislikes::getCustomerRecord(self::$cookie->id_customer);
		
		self::$smarty->assign(array(
			'ENT_NOQUOTES' => ENT_NOQUOTES,
			'outOfStockAllowed' => (int)(Configuration::get('PS_ORDER_OUT_OF_STOCK')),
			'errors' => $this->errors,
			'categories' => Category::getHomeCategories((int)self::$cookie->id_lang),
			'productSubcategory' => Category::getProductSubcategory($this->product->id, $this->product->id_category_default),
			'have_image' => (isset($cover) ? (int)$cover['id_image'] : false),
			//'tax_enabled' => Configuration::get('PS_TAX'),
			'display_qties' => (int)Configuration::get('PS_DISPLAY_QTIES'),
			'display_ht' => !Tax::excludeTaxeOption(),
			'shoeSize' => intval(Customer::getShoeAttributeId(intval(self::$cookie->id_customer),intval(self::$cookie->id_lang))),
			'shoe_size' => intval(Customer::getShoeSize(intval(self::$cookie->id_customer))),
//			'ecotax' => (!sizeof($this->errors) AND $this->product->ecotax > 0 ? Tools::convertPrice((float)($this->product->ecotax)) : 0),
//			'currencySign' => $currency->sign,
//			'currencyRate' => $currency->conversion_rate,
//			'currencyFormat' => $currency->format,
//			'currencyBlank' => $currency->blank,
			'jqZoomEnabled' => Configuration::get('PS_DISPLAY_JQZOOM'),
			'last_qties'	=> (int)Configuration::get('PS_LAST_QTIES'),
			'isAnyofTheProductCombinationIsOutOfStock' => Product::isAnyofTheProductCombinationIsOutOfStock($this->product->id),
			'productLikesCount' => ProductLikesAndDislikes::checkProductRecordExists($this->product->id),
			'customerLikes' => $customerLikesDislikes['likes'][$this->product->id] ? 1 : 0,
			'customerDislikes' => $customerLikesDislikes['dislikes'][$this->product->id] ? 1 : 0
		));

		// utm_source parameter checking for prevent if hash parameters come from another place for another purpose.
		if (Tools::getValue('utm_medium') == 'stilsos' AND Tools::getValue('hash')){
			self::$smarty->assign('stilsos_hash', Tools::getValue('hash'));
		}

		/**start**/
//		if( $fb_image = Product::getCover(intval(Tools::getValue('id_product'))) )
//		{
//			self::$smarty->assign(array(
//				'fb_image' => $fb_image,
//				//'fb_image_host' => $content_base_url
//			));
//		}
		/**end**/
	}

	public function displayContent()
	{
		parent::displayContent();
//		if(Tools::getValue('ajax') == 'true')
//			self::$smarty->display(_PS_THEME_DIR_.'productq.tpl',NULL,NULL,NULL,false);
//		else
            $category = new Category((int)$this->product->id_category_default);
            $categoryName = $category->getName(self::$cookie->id_lang);

		if ($this->new_product_page === 1) {
			if($categoryName === "AccessoriesedProducts")
				 self::$smarty->display(_PS_THEME_DIR_.'product-new-accessory.tpl');
			else
				self::$smarty->display(_PS_THEME_DIR_.'product-new.tpl');
        } else {
			if($categoryName === "AccessoriesedProducts")
				 self::$smarty->display(_PS_THEME_DIR_.'accessoriesed-product-page.tpl');
			else
				self::$smarty->display(_PS_THEME_DIR_.'product.tpl');
        }

	}

	public function pictureUpload(Product $product, Cart $cart)
	{
		if (!$fieldIds = $this->product->getCustomizationFieldIds())
			return false;
		$authorizedFileFields = array();
		foreach ($fieldIds AS $fieldId)
			if ($fieldId['type'] == _CUSTOMIZE_FILE_)
				$authorizedFileFields[(int)($fieldId['id_customization_field'])] = 'file'.(int)($fieldId['id_customization_field']);
		$indexes = array_flip($authorizedFileFields);
		foreach ($_FILES AS $fieldName => $file)
			if (in_array($fieldName, $authorizedFileFields) AND isset($file['tmp_name']) AND !empty($file['tmp_name']))
			{
				$fileName = md5(uniqid(rand(), true));
				if ($error = checkImage($file, (int)(Configuration::get('PS_PRODUCT_PICTURE_MAX_SIZE'))))
					$this->errors[] = $error;

				if ($error OR (!$tmpName = tempnam(_PS_TMP_IMG_DIR_, 'PS') OR !move_uploaded_file($file['tmp_name'], $tmpName)))
					return false;
				/* Original file */
				elseif (!imageResize($tmpName, _PS_UPLOAD_DIR_.$fileName))
					$this->errors[] = Tools::displayError('An error occurred during the image upload.');
				/* A smaller one */
				elseif (!imageResize($tmpName, _PS_UPLOAD_DIR_.$fileName.'_small', (int)(Configuration::get('PS_PRODUCT_PICTURE_WIDTH')), (int)(Configuration::get('PS_PRODUCT_PICTURE_HEIGHT'))))
					$this->errors[] = Tools::displayError('An error occurred during the image upload.');
				elseif (!chmod(_PS_UPLOAD_DIR_.$fileName, 0777) OR !chmod(_PS_UPLOAD_DIR_.$fileName.'_small', 0777))
					$this->errors[] = Tools::displayError('An error occurred during the image upload.');
				else
					$cart->addPictureToProduct((int)($this->product->id), $indexes[$fieldName], $fileName);
				unlink($tmpName);
			}
		return true;
	}

	public function textRecord(Product $product, Cart $cart)
	{
		if (!$fieldIds = $this->product->getCustomizationFieldIds())
			return false;
		$authorizedTextFields = array();
		foreach ($fieldIds AS $fieldId)
			if ($fieldId['type'] == _CUSTOMIZE_TEXTFIELD_)
				$authorizedTextFields[(int)($fieldId['id_customization_field'])] = 'textField'.(int)($fieldId['id_customization_field']);
		$indexes = array_flip($authorizedTextFields);
		foreach ($_POST AS $fieldName => $value)
			if (in_array($fieldName, $authorizedTextFields) AND !empty($value))
			{
				if (!Validate::isMessage($value))
					$this->errors[] = Tools::displayError('Invalid message');
				else
					$cart->addTextFieldToProduct((int)($this->product->id), $indexes[$fieldName], $value);
			}
			elseif (in_array($fieldName, $authorizedTextFields) AND empty($value))
				$cart->deleteTextFieldFromProduct((int)($this->product->id), $indexes[$fieldName]);
	}

	public function formTargetFormat()
	{
		$customizationFormTarget = Tools::safeOutput(urldecode($_SERVER['REQUEST_URI']));
		foreach ($_GET AS $field => $value)
			if (strncmp($field, 'group_', 6) == 0)
				$customizationFormTarget = preg_replace('/&group_([[:digit:]]+)=([[:digit:]]+)/', '', $customizationFormTarget);
		if (isset($_POST['quantityBackup']))
			self::$smarty->assign('quantityBackup', (int)($_POST['quantityBackup']));
		self::$smarty->assign('customizationFormTarget', $customizationFormTarget);
	}

	public function formatQuantityDiscounts($specificPrices, $price, $taxRate)
	{
		foreach ($specificPrices AS $key => &$row)
		{
			$row['quantity'] = &$row['from_quantity'];
			// The price may be directly set
			if ($row['price'] != 0)
			{
			    $cur_price = (Product::$_taxCalculationMethod == PS_TAX_EXC ? $row['price'] : $row['price'] * (1 + $taxRate / 100));
                if ($row['reduction_type'] == 'amount')
			        $cur_price = Product::$_taxCalculationMethod == PS_TAX_INC ? $cur_price - $row['reduction'] : $cur_price - ($row['reduction'] / (1 + $taxRate / 100));
				else
				    $cur_price = $cur_price * ( 1  - ($row['reduction']));
			    $row['real_value'] = $price - $cur_price;
			}
			else
			{
                global $cookie;
                $id_currency = (int)$cookie->id_currency;

			    if ($row['reduction_type'] == 'amount')
			    {
	    	        $reduction_amount = $row['reduction'];
    		        if (!$row['id_currency'])
	    	            $reduction_amount = Tools::convertPrice($reduction_amount, $id_currency);

			        $row['real_value'] = Product::$_taxCalculationMethod == PS_TAX_INC ? $reduction_amount : $reduction_amount / (1 + $taxRate / 100);
                }
			    else
                {
				    $row['real_value'] = $row['reduction'] * 100;
                }
			}
			$row['nextQuantity'] = (isset($specificPrices[$key + 1]) ? (int)($specificPrices[$key + 1]['from_quantity']) : -1);
		}
		return $specificPrices;
	}
}

