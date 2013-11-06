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

class DealControllerCore extends FrontController
{
	public $ssl = false;
	public $php_self = 'deal.php';

	public function preProcess()
	{
		parent::preProcess();
            
		$from_sailthru_spider = $_SERVER["HTTP_USER_AGENT"] == "Sailthru Content Spider [Butigo/7960c2582bec87e53771387ab15dd345]" ? true : false;
		
		if (!$from_sailthru_spider) {
			if (!self::$cookie->isLogged())
				Tools::redirect('authentication.php?deal=1');

			if (intval(self::$cookie->id_customer))
				$customer = new Customer(intval(self::$cookie->id_customer));
			else
				Tools::redirect('authentication.php?deal=1');

			if (!Validate::isLoadedObject($customer))
				Tools::redirect('authentication.php?deal=1');
		}
		
		unset($product);unset($combination_product);
		unset($customer_products);unset($comb);
		unset($result);

		$category_details = Category::searchByNameAndParentCategoryId((int)(self::$cookie->id_lang),'DEAL', 1);//searching category details by name lkbk
		$category_id = $category_details['id_category']; //getting all the details about products based on id
		$category = new Category($category_id);
		$nb = 3; //limiting the products for 10 (each color combination is considared as 1 product)
		$deal_products = $category->getProducts((int)(self::$cookie->id_lang), 1, $nb, 'position',NULL, false,true,false,1,true,true);
//echo "<pre>";print_r($deal_products);echo "</pre>";

		foreach($deal_products as $deal_prod)
		{
			if($deal_prod['default_combination']>0)
				$deal_ipa = $deal_prod['default_combination'];
		}
		if($deal_products[0]['specific_time'] > 0)
		{
			//$product_link = self::$link->getProductLink($deal_products[0]['id_product'], $deal_products[0]['id_product_attribute']);
			//Tools::redirect($product_link);
			Tools::redirect('product.php?id_product='.$deal_products[0]['id_product'].'&id_product_attribute='.$deal_ipa);
		}
	}
	public function displayContent()
	{
		parent::displayContent();
		self::$smarty->display(_PS_THEME_DIR_.'deal.tpl');
	}

}

?>