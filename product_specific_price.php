<?php

require_once(dirname(__FILE__).'/config/config.inc.php');
require_once(dirname(__FILE__).'/init.php');

$cron_user = Tools::getValue('crnu');
$cron_pass = Tools::getValue('crnp');
if(!$cron_user)
	$cron_user = $argv[1];
if(!$cron_pass)
	$cron_pass = $argv[2];
$cron_pass = Tools::encrypt($cron_pass);
//Group->getGroups();
//if($cron_user == _CRON_USER_ && $cron_pass == _CRON_PASSWD_)
//{
	global $cookie;
    
    function getGroups($id_lang)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT g.`id_group`, g.`reduction`, g.`price_display_method`, gl.`name`
		FROM `'._DB_PREFIX_.'group` g
		LEFT JOIN `'._DB_PREFIX_.'group_lang` AS gl ON (g.`id_group` = gl.`id_group` AND gl.`id_lang` = '.(int)($id_lang).')
        WHERE g.`id_group` in(64,65,66,67)
		ORDER BY g.`id_group` ASC');
	}
    
    function getExistingGroups($id_lang)
    {
        $existing_grps = array();
        $groups =getGroups($id_lang);

        foreach($groups AS $group)
        {
            $existing_grps[$group['name']]  = $group['id_group'];
            //$existing_grps['names'][]  = $group['name'];
        }

        return $existing_grps;
    }
    
    function getPriceVariation($group_name)
    {
        $price_array=array('PriceTestGroup0TL'=>0,'PriceTestGroupP5TL'=>5,'PriceTestGroupP10TL'=>10,'PriceTestGroupM5TL'=>-5);
        $price_variation = $price_array[$group_name];
        return $price_variation;
    }
    
    function createSpecificPrice($id_product, $id_group , $price, $strike_out)
    { 
        $id_shop = 0;
        $id_currency = 0;
        $id_country = 0;
        $from_quantity = 1;
        $reduction = 0.000000;
        $reduction_type = 'amount';
        $from = '0000-00-00 00:00:00';
        $to = '0000-00-00 00:00:00';

        $specificPrice = new SpecificPrice();
        $specificPrice->id_product = $id_product;
        $specificPrice->id_shop = (int)($id_shop);
        $specificPrice->id_currency = (int)($id_currency);
        $specificPrice->id_country = (int)($id_country);
        $specificPrice->id_group = (int)($id_group);
        $specificPrice->price = (float)($price);
        $specificPrice->from_quantity = (int)($from_quantity);
        $specificPrice->reduction = (float)($reduction_type == 'percentage' ? $reduction / 100 : $reduction);
        $specificPrice->reduction_type = $reduction_type;
        $specificPrice->strike_out = $strike_out;
        $specificPrice->from = !$from ? '0000-00-00 00:00:00' : $from;
        $specificPrice->to = !$to ? '0000-00-00 00:00:00' : $to;
        if (!$specificPrice->add())
            return false;
        else
            return true;
    }
    $group_ids = getExistingGroups(4);
    //echo '<pre>';print_r($group_ids );echo'</pre>';    
    $orderBy = 'date_add';
	$orderWay  = 'ASC';
    
    $all_products = Product::getProducts(4, 0, 0, $orderBy, $orderWay);
    
    foreach($all_products as $product)
		{			
            foreach($group_ids as $key => $grp_id)
            {
                if($product['price']>10){
                    $price_variation = (getPriceVariation($key));
                    //echo'<br>key='.$key.'==grp_pd=='.$grp_id;
                    $sp_price = $product['price'];
                    $tax_rate = Tax::getProductTaxRate($product['id_product']);
                    $price_variation_base = $price_variation / (1 + ($tax_rate / 100));
                    //$final_product_price =  $sp_price * (1 + ($tax_rate / 100));
                    $final_product_price = $price_variation_base + $sp_price;
                    $final_sp_price = Tools::ps_round($final_product_price, 3);
                    $strike_out = 0;
                    createSpecificPrice($product['id_product'], $grp_id,$final_sp_price, $strike_out);
                }
            }
		}
	
//}
//else
//{
//	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
//	header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
//
//	header("Cache-Control: no-store, no-cache, must-revalidate");
//	header("Cache-Control: post-check=0, pre-check=0", false);
//	header("Pragma: no-cache");
//
//	header("Location: ../");
//	exit;
//}
?>