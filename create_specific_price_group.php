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

if($cron_user == _CRON_USER_ && $cron_pass == _CRON_PASSWD_)
{
	global $cookie;

	$group_names = array();
	$group_names = array('bwacgp5' ,'bwacgp10','bwacgp15', 'bwacgm5', 'bwacgm10' , 'bwacgm15');
	
	$logFile = @fopen(_PS_ROOT_DIR_.'/log/specific_price_tracking.txt', "a");
	fwrite($logFile,'START----'.date("D M j G:i:s T Y")."\n");
	//print_r($group_names);
	$group_ids = createGroups($group_names , $cookie->id_lang, $logFile);
	//echo 'vibha';print_r($group_ids );
	
	if($group_ids)
	{
		//echo 'vibha123';
		$orderBy = 'date_add';
		$orderWay  = 'ASC';

		$all_products = Product::getProducts(intval($cookie->id_lang), 0, 0, $orderBy, $orderWay);
		

		$counter = 1;
		foreach($all_products as $product)
		{
			//5th parameter is group id.
			//0 is all groups
			//1 is default group
			$strb = 'BEGIN-----------------';
			fwrite($logFile, sprintf("%s",$strb."\n"));
			if(SpecificPrice::getSpecificPrice($product['id_product'], 0, 0, 0, 0, 1) || SpecificPrice::getSpecificPrice($product['id_product'], 0, 0, 0, 1, 1))
			{
				$str3 =  'Specific Price exists for product id '.$product['id_product'].' for "all groups" or "default group"';
				fwrite($logFile, sprintf("\n%s\n",$str3."\n"));
				echo "<br>";
				echo $str3;
			}
			else
			{
				foreach($group_ids as $key => $grp_id)
				{

					if( $sp_exists = SpecificPrice::getSpecificPrice($product['id_product'], 0, 0, 0, $key, 1))
					{
						$str4 =  'Specific Price exists for product id '.$product['id_product'].', group id '.$key;
						fwrite($logFile, sprintf("\n%s\n",$str4."\n"));
						echo "<br>";
						echo $str4;
                                                
                                                $grp_specific = new SpecificPrice($sp_exists['id_specific_price']);
                                                
                                                if(Tools::getIsset('remove') && Tools::getValue('remove') == 1)
                                                {  
                                                    if($grp_specific->delete())
                                                    {
                                                        $rmv1 =  'Specific Price for product id '.$product['id_product'].', group id '.$key.' has been removed';
                                                    }
                                                    else
                                                    {
                                                       $rmv1 =  'Specific Price for product id '.$product['id_product'].', group id '.$key.'could not be removed'; 
                                                    }
                                                    fwrite($logFile, sprintf("\n%s\n",$rmv1."\n"));
                                                    echo "<br>";
                                                    echo $rmv1;
                                                    
                                                }
                                                elseif(Tools::getIsset('roundType') && Tools::getValue('roundType'))
                                                {
                                                    if(Tools::getValue('roundType')=='ceil')
                                                    {
                                                        $modified_price =  ceil($sp_exists['price']); 
                                                        $round_off = 'ceil';
                                                    }
                                                    elseif(Tools::getValue('roundType')=='floor')
                                                    {
                                                        $modified_price =  floor($sp_exists['price']); 
                                                        $round_off = 'floor';
                                                    }
                                                    $grp_specific->price = $modified_price;
                                                    if($grp_specific->update())
                                                    {   
                                                        $mod1 = 'Specific Price ('.$sp_exists['price'].') for product id '.$product['id_product'].', group id '.$key.' has been modified ('.$round_off .') as '. $modified_price;
                                                        
                                                    }
                                                    else
                                                    {
                                                        $mod1 = 'Specific Price ('.$sp_exists['price'].') for product id '.$product['id_product'].', group id '.$key.'could not be modified as '. $modified_price;
                                                    }
                                                    fwrite($logFile, sprintf("\n%s\n",$mod1."\n"));
                                                    echo "<br>";
                                                    echo $mod1;
                                                }
					}
					else
					{
						$sp_price = $product['price'] + ($product['price'] * ($grp_id /100 ));
                                                $tax_rate = Tax::getProductTaxRate($product['id_product']);
                                                $final_product_price =  $sp_price * (1 + ($tax_rate / 100));
                                                $ceil_price = ceil( $final_product_price);
                                                $final_sp_price = $ceil_price/(1 + ($tax_rate / 100));
                                                $final_sp_price = Tools::ps_round($final_sp_price, 3);
//                                                if(Tools::getIsset('roundType') && Tools::getValue('roundType'))
//                                                {
//                                                    if(Tools::getValue('roundType')=='ceil')
//                                                    {
//                                                        $sp_price =  ceil($sp_price);
//                                                       
//                                                    }
//                                                    elseif(Tools::getValue('roundType')=='floor')
//                                                    {
//                                                         $sp_price = floor($sp_price);
//                                                    }
//                                                }
						if($ceil_price > $product['price'])
							$strike_out = 1;
						else
							$strike_out = 0;
						echo "<br>";
						$str5 = 'The final specific price(tax excl) calculated for product with id ('.$product['id_product'].'), price ('.$product['price'].'), group-id '.$key.' , variation '.$grp_id.'% is '.$final_sp_price;
						fwrite($logFile, sprintf("\n%s\n",$str5."\n"));
						echo $str5;
						echo "<br>";
						if(!createSpecificPrice($product['id_product'], $key,$final_sp_price, $strike_out))
						{
							$str7 = 'Specific Price could not be created for product with id '.$product['id_product'].', group id '.$key;
							echo "<br>";
							echo $str7;
							fwrite($logFile, sprintf("%s",$str7."\n"));
						}
						else
						{
							$str8 = 'Specific Price created for product with id '.$product['id_product'].', group id '.$key;
							echo "<br>";
							echo $str8;
							fwrite($logFile, sprintf("\n%s\n",$str8."\n"));
							$counter++;
						}
					}
				}
			}
			$stre = 'End-----------------';
			fwrite($logFile, sprintf("%s",$stre."\n"));
		}

		$str6 = 'The number of products for which specific price will be created '.$counter;
		fwrite($logFile, sprintf("\n%s\n",$str6."\n"));
	}
	
	fwrite($logFile,'END'."\n");
	fclose($logFile);
}
else
{
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");

	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");

	header("Location: ../");
	exit;
}
function getExistingGroups($id_lang)
{
	$existing_grps = array();
	$groups = Group::getGroups($id_lang);

	foreach($groups AS $group)
	{
		$existing_grps[$group['name']]  = $group['id_group'];
		$existing_grps['names'][]  = $group['name'];
	}

	return $existing_grps;
}

function createGroups($group_names, $id_lang, $logFile)
{
	$create_groups = array();
	$grp_ids = array();
	$existing_grps = getExistingGroups($id_lang);

	//print_r($existing_grps);
	//print_r($group_names);

	foreach($group_names as $grp_name)
	{
		if(!in_array($grp_name, $existing_grps['names']))
		{
			$create_groups[] = $grp_name;
		}
		else
		{
			$price_variation = getPriceVariation($grp_name);
			$grp_ids[$existing_grps[$grp_name]] = $price_variation;
			$str1 = 'This group already exists (group name = '.$grp_name.')';
			echo $str1;
			fwrite($logFile, sprintf("\n%s\n",$str1."\n"));
			echo "<br>";
		}
	}

	//print_r($create_groups);exit;

	if($create_groups)
	{
		//echo 'test123';
		$languages = Language::getLanguages(false);

		foreach($create_groups as $create)
		{
			$group = new Group();
			$group->reduction = 0.00;
			$group->price_display_method = 0;
			foreach ($languages as $language)
				$group->name[$language['id_lang']] = strval($create);
			$group->add();

			if($group->id !=0 || $group->id = '')
			{
				$price_variation = getPriceVariation($create);
				$grp_ids[$group->id] = $price_variation;

				$str2 =  'Group with id '.$group->id.' and name '.$create.' and variation '.$price_variation.'% is added';
				fwrite($logFile, sprintf("\n%s\n",$str2."\n"));
				echo "<br>";
				echo $str2;
			}
			else
			{
				$str9 =  'Group '.$create.' could not be added';echo "<br>";
				echo $str9;
				fwrite($logFile, sprintf("\n%s\n",$str9."\n"));
			}

		}
		//echo "<br>";print_r($grp_ids);exit;
	}
	if(!empty($grp_ids))
		return $grp_ids;
	else
		return false;
}

function createSpecificPrice($id_product, $id_group , $price, $strike_out)
{

	$id_shop = 0;
	$id_currency = 0;
	$id_country = 0;
	$from_quantity = 1;
	$reduction = 0.000000;
	$reduction_type = 'amount';
	//$strike_out = 1;
	//$from = '2012-08-01 16:47:44';
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

function getPriceVariation($group_name)
{

	$sub_str = substr($group_name, 5);
	$symbol = substr($sub_str, 0, 1);
	$price_variation = substr($sub_str, 1);
	if($symbol == 'm')
		$price_variation = $price_variation * -1;
	return $price_variation;
}

?>
