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
    
    echo'Running..';
        
    function createSpecificPrice($id_product)
    { 
        $id_shop = 0;
        $id_currency = 0;
        $id_country = 0;
        $id_group = 0;
        $price = 0;
        $from_quantity = 1;
        $reduction = 15.000000;
        $reduction_type = 'percentage';
        $strike_out = 1;
        $from = '2013-10-14 00:00:00';
        $to = '2013-10-14 23:59:59';

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
    $orderBy = 'date_add';
	$orderWay  = 'ASC';
    
    $all_products = Product::getProducts(4, 0, 0, $orderBy, $orderWay);
    
    foreach($all_products as $product)
    {	
        createSpecificPrice($product['id_product']);
    }
    
    echo'Executed Sucessfully..';
	
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
