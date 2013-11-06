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

class BlockFavourites extends Module
{
	const INSTALL_SQL_FILE = 'install.sql';

    private $_html = '';
    private $_postErrors = array();

	public function __construct()
	{
		$this->name = 'blockfavourites';
		$this->tab = 'front_office_features';
		$this->version = 0.2;
		$this->author = 'PrestaShop';
		$this->need_instance = 0;
		
		parent::__construct();
		
		$this->displayName = $this->l('Favourites block');
		$this->description = $this->l('Adds a block containing the customer\'s Favourite Products List.');
		$this->_default_favlist_name = $this->l('My Favourites');
	}
	
	public function install()
	{
		if (!file_exists(dirname(__FILE__).'/'.self::INSTALL_SQL_FILE))
			return (false);
		elseif (!$sql = file_get_contents(dirname(__FILE__).'/'.self::INSTALL_SQL_FILE))
			return (false);
		$sql = str_replace(array('PREFIX_', 'ENGINE_TYPE'), array(_DB_PREFIX_, _MYSQL_ENGINE_), $sql);
		Configuration::updateValue('PS_MY_FAV_ACTIVE', 1);
		$sql = preg_split("/;\s*[\r\n]+/", $sql);
		foreach ($sql AS $query)
			if ($query)
				if (!Db::getInstance()->Execute(trim($query)))
					return false;
		if (!parent::install() OR
//						!$this->registerHook('rightColumn') OR
						!$this->registerHook('productActions') OR
//						!$this->registerHook('cart') OR
//						!$this->registerHook('customerAccount') OR
						!$this->registerHook('header') OR
						!$this->registerHook('adminCustomers') OR
						!$this->registerHook('myFavourites')
					)
			return false;
		/* This hook is optional */
//		$this->registerHook('myAccountBlock');
		return true;
	}
	
	public function uninstall()
	{
		Configuration::updateValue('PS_MY_FAV_ACTIVE', 0);
		//Configuration::deleteByName('PS_MY_FAV_ACTIVE');
		return (
			Db::getInstance()->Execute('DROP TABLE '._DB_PREFIX_.'favlist') AND
			//Db::getInstance()->Execute('DROP TABLE '._DB_PREFIX_.'favlist_email') AND
			Db::getInstance()->Execute('DROP TABLE '._DB_PREFIX_.'favlist_product') AND
			//Db::getInstance()->Execute('DROP TABLE '._DB_PREFIX_.'favlist_product_cart') AND
			parent::uninstall()
		);
	}

	public function getContent()
	{
		$this->_html = '<h2>'.$this->displayName.'</h2>';
		if (Tools::isSubmit('submitstate'))
		{  
			$status = Tools::getValue('mod_state');
			Configuration::updateValue('PS_MY_FAV_ACTIVE', $status);
			$this->_html .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('Confirmation').'" />'.$this->l('Settings updated').'</div>';
		}
		if (Tools::isSubmit('submitSettings'))
		{
			$activated = Tools::getValue('activated');
			if ($activated != 0 AND $activated != 1)
				$this->_html .= '<div class="alert error">'.$this->l('Activate module : Invalid choice.').'</div>';
			$this->_html .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('Confirmation').'" />'.$this->l('Settings updated').'</div>';
		}
		$this->_displayForm();
		return ($this->_html);
	}

	private function _displayForm()
	{
		$this->_displayFormView();
	}
	
	private function _displayFormView()
	{
		global $cookie;

		//$customers = Customer::getCustomers();
		//if (!sizeof($customers))
		//	return;
		//$id_customer = (int)(Tools::getValue('id_customer'));
		//if (!$id_customer)
		//	$id_customer = $customers[0]['id_customer'];
		
		$this->_html .='
			<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
		<fieldset>
			<legend><img src="'.$this->_path.'img/icon/package_go.png" alt="" title="" />'.$this->l('Enable/Disable').'</legend>
			<div style="margin: auto; width: 300px;">
			<div style="float:left;width:100px" id="enable">
				<input type="radio" name="mod_state" value="1" '.(Configuration::get('PS_MY_FAV_ACTIVE') == 1 ? 'checked="checked"': '').' />
				<span>Enabled</span></div>
			<div style="float:left;width:100px" id="disable">
				<input type="radio" name="mod_state" value="0" '.(Configuration::get('PS_MY_FAV_ACTIVE') == 0 ? 'checked="checked"': '').' />
				<span>Disabled</span>
			</div>
		<input type="submit" name="submitstate" value="'.$this->l('Save').'" class="button" /></div></fieldset></form>';

		/*$this->_html .= '<br />
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post" id="listing">
			<fieldset>
				<legend><img src="'.$this->_path.'img/icon/package_go.png" alt="" title="" />'.$this->l('Listing').'</legend>

				<label>'.$this->l('Customers').'</label>
				<div class="margin-form">
					<select name="id_customer" onchange="$(\'#listing\').submit();">';
		foreach ($customers as $customer)
		{
			$this->_html .= '<option value="'.(int)($customer['id_customer']).'"';
			if ($customer['id_customer'] == $id_customer)
				$this->_html .= ' selected="selected"';
			$this->_html .= '>'.htmlentities($customer['firstname'], ENT_COMPAT, 'UTF-8').' '.htmlentities($customer['lastname'], ENT_COMPAT, 'UTF-8').'</option>';
		}
		$this->_html .= '
					</select>
				</div>';

		require_once(dirname(__FILE__).'/Favourite.php');
		$favlists = Favourite::getByIdCustomer($id_customer);
		if (!sizeof($favlists))
			return ($this->_html .= '</fieldset></form>');
		$id_favlist = false;
		foreach ($favlists AS $row)
			if ($row['id_favlist'] == Tools::getValue('id_favlist'))
			{
				$id_favlist = (int)(Tools::getValue('id_favlist'));
				break;
			}
		if (!$id_favlist)
			$id_favlist = $favlists[0]['id_favlist'];
		$this->_html .= '
				<label>'.$this->l('Favourites').'</label>
				<div class="margin-form">
					<select name="id_favlist" onchange="$(\'#listing\').submit();">';
		foreach ($favlists as $favlist)
		{
			$this->_html .= '<option value="'.(int)($favlist['id_favlist']).'"';
			if ($favlist['id_favlist'] == $id_favlist)
			{
				$this->_html .= ' selected="selected"';
				$counter = $favlist['counter'];
			}
			$this->_html .= '>'.htmlentities($favlist['name'], ENT_COMPAT, 'UTF-8').'</option>';
		}
		$this->_html .= '
					</select>
				</div>';
		$this->_displayProducts((int)($id_favlist));*/
		$this->_html .= 	'</fieldset>
		</form>';
	}
	
	public function hookHeader($params)
	{
		Tools::addCSS(($this->_path).'blockfavourites.css', 'all');
		return $this->display(__FILE__, 'blockfavourites-header.tpl');
	}

	/*public function hookRightColumn($params)
	{
		global $smarty, $errors;

		require_once(dirname(__FILE__).'/Favourite.php');
		if ($params['cookie']->isLogged())
		{
			$favlists = Favourite::getByIdCustomer($params['cookie']->id_customer);
			if (empty($params['cookie']->id_favlist) === true ||
				Favourite::exists($params['cookie']->id_favlist, $params['cookie']->id_customer) === false)
			{
				if (!sizeof($favlists))
					$id_favlist = false;
				else
				{
					$id_favlist = (int)($favlists[0]['id_favlist']);
					$params['cookie']->id_favlist = (int)($id_favlist);
				}
			}
			else
				$id_favlist = $params['cookie']->id_favlist;
			$smarty->assign(array(
				'id_favlist' => $id_favlist,
				'isLogged' => true,
				'favlist_products' => ($id_favlist == false ? false : Favourite::getProductByIdCustomer($id_favlist, $params['cookie']->id_customer, $params['cookie']->id_lang, null, true)),
				'favlists' => $favlists,
				'ptoken' => Tools::getToken(false)));
		}
		else
			$smarty->assign(array('favlist_products' => false, 'favlists' => false));
		return ($this->display(__FILE__, 'blockfavourites.tpl'));
	}

	public function hookLeftColumn($params)
	{
		return $this->hookRightColumn($params);
	}*/

	public function hookmyFavourites()
	{
		global $smarty, $errors, $cookie;
		require_once(dirname(__FILE__).'/Favourite.php');
		
		if(Favourite::getByIdCustomer((int)($cookie->id_customer)))
		{
				$action = Tools::getValue('action');
				if($cus_fav_list_details = Favourite::getByIdCustomer((int)($cookie->id_customer)))
				{
					$id_favlist = $cus_fav_list_details[0]['id_favlist'];
				}
				else
				{
					$errors[] = Tools::displayError('Customer does not have favlisst');
				}

			$favlist = new Favourite((int)($id_favlist));

			if (empty($id_favlist) === false)
			{
					$products = Favourite::getProductByIdCustomer($id_favlist, $cookie->id_customer, $cookie->id_lang);
					$bought = Favourite::getBoughtProduct($id_favlist);

					for ($i = 0; $i < sizeof($products); ++$i)
					{
						$obj = new Product((int)($products[$i]['id_product']), false, (int)($cookie->id_lang));
						if (!Validate::isLoadedObject($obj))
							continue;
						else
						{
							if ($products[$i]['id_product_attribute'] != 0)
							{
								$combination_imgs = $obj->getCombinationImages((int)($cookie->id_lang));
//									echo "<pre>";print_r($combination_imgs);echo "</pre>";
								if(isset($combination_imgs[$products[$i]['id_product_attribute']]))
								{
									if(isset($combination_imgs[$products[$i]['id_product_attribute']]['coverimage']))
										$products[$i]['cover']  = $obj->id.'-'.$combination_imgs[$products[$i]['id_product_attribute']]['coverimage'][0];
									else
										$products[$i]['cover'] = $obj->id.'-'.$combination_imgs[$products[$i]['id_product_attribute']][0]['id_image'];
									$products[$i]['product_combination'] = 1;
								}
								else
								{
									$images = $obj->getImages((int)($cookie->id_lang));
									foreach ($images AS $k => $image)
										if ($image['cover'])
										{
											$products[$i]['cover'] = $obj->id.'-'.$image['id_image'];
											break;
										}
										$products[$i]['product_combination'] = 0;
								}
							}
							else
							{
								$images = $obj->getImages((int)($cookie->id_lang));
								foreach ($images AS $k => $image)
									if ($image['cover'])
									{
										$products[$i]['cover'] = $obj->id.'-'.$image['id_image'];
										break;
									}
							}
							if (!isset($products[$i]['cover']))
								$products[$i]['cover'] = Language::getIsoById($cookie->id_lang).'-default';
						}
						$products[$i]['bought'] = false;
						for ($j = 0, $k = 0; $j < sizeof($bought); ++$j)
						{
							if ($bought[$j]['id_product'] == $products[$i]['id_product'] AND
								$bought[$j]['id_product_attribute'] == $products[$i]['id_product_attribute'])
								$products[$i]['bought'][$k++] = $bought[$j];
						}
					}

					$productBoughts = array();

					foreach ($products as $product)
						if (sizeof($product['bought']))
							$productBoughts[] = $product;
					$smarty->assign(array(
						'products' => $products,
						'productsBoughts' => $productBoughts,
						'id_favlist' => $id_favlist,
						'token_fav' => $favlist->token
					));
			}
		}
		else
		{
			$smarty->assign('products' , false);
		}
		
		return ($this->display(__FILE__, 'managefavlist.tpl'));

	}

	public function hookProductActions($params)
	{
		global $smarty, $cookie;
		
			require_once(dirname(__FILE__).'/Favourite.php');
		if ($params['cookie']->isLogged())
		{
			$id_product = (int)(Tools::getValue('id_product'));
			if(Tools::getValue('id_product_attribute'))
				$id_product_attribute = (int)(Tools::getValue('id_product_attribute'));
			else
				$id_product_attribute = Product::getDefaultAttribute($id_product);
			$in_myfavorite = Favourite::productExistsInCustomerFavlist($id_product, $id_product_attribute, $params['cookie']->id_customer);

			$smarty->assign( array('id_product' =>  $id_product ,
			'in_myfavorite' => $in_myfavorite,
			'id_product_attribute' =>  $id_product_attribute));

			return ($this->display(__FILE__, 'blockfavourites-extra.tpl'));
		}
	}
	
	/*public function hookCustomerAccount($params)
	{
		global $smarty;
		return $this->display(__FILE__, 'my-account.tpl');
	}
	
	public function hookMyAccountBlock($params)
	{
		return $this->hookCustomerAccount($params);
	}*/
	
	private function _displayProducts($id_favlist)
	{
		global $cookie, $link;
		//include_once(dirname(__FILE__).'/WishList.php');
		include_once(dirname(__FILE__).'/Favourite.php');
		$favlist = new Favourite((int)($id_favlist));
		$products = Favourite::getProductByIdCustomer((int)($id_favlist), (int)($favlist->id_customer), (int)($cookie->id_lang));
		for ($i = 0; $i < sizeof($products); ++$i)
		{
			$obj = new Product((int)($products[$i]['id_product']), false, (int)($cookie->id_lang));
			if (!Validate::isLoadedObject($obj))
				continue;
			else
			{
				if ($products[$i]['id_product_attribute'] != 0)
				{
					$combination_imgs = $obj->getCombinationImages((int)($cookie->id_lang));
					if(isset($combination_imgs[$products[$i]['id_product_attribute']]))
					{
						if(isset($combination_imgs[$products[$i]['id_product_attribute']]['coverimage']))
							$products[$i]['cover']  = $obj->id.'-'.$combination_imgs[$products[$i]['id_product_attribute']]['coverimage'][0];
						else
							$products[$i]['cover'] = $obj->id.'-'.$combination_imgs[$products[$i]['id_product_attribute']][0]['id_image'];
					}
					else
					{
						$images = $obj->getImages((int)($cookie->id_lang));
						foreach ($images AS $k => $image)
							if ($image['cover'])
							{
								$products[$i]['cover'] = $obj->id.'-'.$image['id_image'];
								break;
							}
					}
				}
				else
				{
					$images = $obj->getImages((int)($cookie->id_lang));
					foreach ($images AS $k => $image)
						if ($image['cover'])
						{
							$products[$i]['cover'] = $obj->id.'-'.$image['id_image'];
							break;
						}
				}
				if (!isset($products[$i]['cover']))
					$products[$i]['cover'] = Language::getIsoById($cookie->id_lang).'-default';
			}
		}
		$this->_html .= '
		<table class="table">
			<thead>
				<tr>
					<th class="first_item" style="width:600px;">'.$this->l('Product').'</th>
					<th class="item" style="text-align:center;width:150px;">'.$this->l('Quantity').'</th>
					<th class="item" style="text-align:center;width:150px;">'.$this->l('Priority').'</th>
				</tr>
			</thead>
			<tbody>';
			$priority = array($this->l('High'), $this->l('Medium'), $this->l('Low'));
			foreach ($products as $product)
			{
				$this->_html .= '
				<tr>
					<td class="first_item">
						<img src="'.$link->getImageLink($product['link_rewrite'], $product['cover'], 'small').'" alt="'.htmlentities($product['name'], ENT_COMPAT, 'UTF-8').'" style="float:left;" />
						'.$product['name'];
				if (isset($product['attributes_small']))
					$this->_html .= '<br /><i>'.htmlentities($product['attributes_small'], ENT_COMPAT, 'UTF-8').'</i>';
				$this->_html .= '
					</td>
					<td class="item" style="text-align:center;">'.(int)($product['quantity']).'</td>
					<td class="item" style="text-align:center;">'.$priority[(int)($product['priority']) % 3].'</td>
				</tr>';
			}
		$this->_html .= '</tbody></table>';
	}
	
	public function hookAdminCustomers($params)
	{
		//require_once(dirname(__FILE__).'/WishList.php');
		require_once(dirname(__FILE__).'/Favourite.php');
		$customer = new Customer((int)($params['id_customer']));
		if (!Validate::isLoadedObject($customer))
			die (Tools::displayError());

		$this->_html = '<h2>'.$this->l('Favlists').'</h2>';
		
		$favlists = Favourite::getByIdCustomer((int)($customer->id));
		if (!sizeof($favlists))
			$this->_html .= $customer->lastname.' '.$customer->firstname.' '.$this->l('No favlist.');
		else
		{
			$this->_html .= '<form action="'.$_SERVER['REQUEST_URI'].'" method="post" id="listing">';
	
			$id_favlist = (int)(Tools::getValue('id_favlist'));
			if (!$id_favlist)
					$id_favlist = $favlists[0]['id_favlist'];
			
			$this->_html .= '<span>'.$this->l('Favlist').': </span> <select name="id_favlist" onchange="$(\'#listing\').submit();">';
			foreach ($favlists as $favlist)
			{
				$this->_html .= '<option value="'.(int)($favlist['id_favlist']).'"';
				if ($favlist['id_favlist'] == $id_favlist)
				{
					$this->_html .= ' selected="selected"';
					$counter = $favlist['counter'];
				}
				$this->_html .= '>'.htmlentities($favlist['name'], ENT_COMPAT, 'UTF-8').'</option>';
			}		
			$this->_html .= '</select>';
			
			$this->_displayProducts((int)($id_favlist));
						
			$this->_html .= '</form><br />';
			
			return $this->_html;
		}
	}
	/*
	* Display Error from controler
	*/
	public function errorLogged()
	{
		return $this->l('You must be logged in to manage your favlists.');
	}
}

