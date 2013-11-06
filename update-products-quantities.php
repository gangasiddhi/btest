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
*  @version  Release: $Revision: 6599 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

require_once(dirname(__FILE__).'/config/config.inc.php');
require_once(dirname(__FILE__).'/init.php');

$fileLog = fopen(_PS_LOG_DIR_.'upadate-product-quanties-log.txt','a');
fwrite($fileLog , "START=".date('Y-m-d H:i:s')."\n");

$cron_user = Tools::getValue('crnu');
$cron_pass = Tools::getValue('crnp');
if(!$cron_user)
	$cron_user = $argv[1];
if(!$cron_pass)
	$cron_pass = $argv[2];
$cron_pass = Tools::encrypt($cron_pass);

   $str = '';
  $send_email = false;
  $mysql_errors = array();
   $message  = '';

 if($cron_user == _CRON_USER_ && $cron_pass == _CRON_PASSWD_)
{
	$xmlfile = dirname(__FILE__).'/upload/product_quantities.xml';
	$xmlparser = xml_parser_create();

	// open  file and read data
	$fp = fopen($xmlfile, 'r');
	$xmldata = fread($fp, filesize($xmlfile));

	xml_parse_into_struct($xmlparser,$xmldata,$values);

	xml_parser_free($xmlparser);
	$data = array();
	$i = 0;
	foreach ($values as $value)
	{
		if($value[type] == 'complete')
		{
			$data[$i][strtolower($value[tag])] = $value[value];
		}
		if($value[tag] == 'PRODUCT_ITEM' AND $value[type] == 'close')
			$i++;
	}
	$products = array();
	foreach($data as $dat)
	{
		//get the id_product, id_product_attribute for the given reference number
		if($product = Product::getProductByReference($dat['reference']))
		{	/*merge the two arrays.$product with id_product, id_product_attribute and $dat with reference number and quantity which is in the xml of each product*/
			
			/*Check whether the product is backorderd or not. 
			 * 1. If the product is backordered and the quantity from the XML is 0 , then the product is not updated
			 * 2. If the product is backorderd and the quantity from the XML is greater than 0, then the product is 
			 *	removed from the backorder and the quantity is updated.
			 */
			$backOrder = Product::isProductCanBackOrder($product['id_product']);
			if($backOrder['out_of_stock'] == 1)
			{
				if($dat['quantity'] == 0){
					echo "<br/>". "Product with".' '.$dat['reference'].' '."is set backorder"."\r\n";
					continue;
				}else{
					if(Product::removeProductFromBackOrder($product['id_product']))
						echo "<br>"."Backorder removed from".$product['id_product'];
				}
				
			}
			$products[] = array_merge($product, $dat);
		}
		else
		{
			echo "<br/>"."Product with".' '.$dat['reference'].' '."does not exist"."\r\n";
		}
	}
	
	foreach($products as $product)
	{
		//update the quantity in product and product_attribute table
		if($result = Product::UpdateXmlProductQuantity($product))
		{
			/*echo "Product with ID:".$product['id_product'].", Product with ATTRID:".$product['id_product_attribute']." ,Prev Qty".$product['prev_qty'].", Reference Number:".$product[reference].", xml qty:".$result['product'][0].", order qty:".$result['product'][1].", update qty:".$result['product'][2];
			echo "<br/>";
			echo"\r\n"; */
			if(isset($result['state']))
			{
				echo "order_id with state 3:"."\r\n";
				foreach($result['state']  as $res)
				{
					print_r($res);
					echo "<br/>";
					echo"\r\n";
				}
			}
			if(isset($result['otherstate']))
			{
				echo "order_id without state 3:"."\r\n";
				foreach($result['otherstate']  as $resos)
				{
					print_r($resos);
					echo "<br/>";
					echo"\r\n";
				}
			}
		}
                else
                {
                    $mysql_errors[] = $product['id_product'].'_'.$product['id_product_attribute'];
                }
			echo "<br/>";
			echo"\r\n";
	}
	echo "\r\n";

        if(isset($mysql_errors) && !empty($mysql_errors))
        {
            $str .= "For the following product_id's quantities could not be updated.";
            foreach($mysql_errors As $errors)
            {
                $str .= $errors.'<br>';
            }
            $send_email = true;
        }
	//date_default_timezone_set('Europe/Istanbul');
	$backup_xmlfile = dirname(__FILE__).'/upload/backup/product_quantities'."_".date("Y-m-d").".xml";
	if (!copy($xmlfile, $backup_xmlfile))
		echo "Failed to copy $xmlfile";
	else
		echo "Successfully Backed up";
        
      
        if(!file_exists($xmlfile))
        {
            $str .= "product_quantities.xml file does not exist.";
            $send_email = true;

        }
        
        if(filemtime($xmlfile) < strtotime("today"))
        {
           $send_email = true;
            $str .= "product_quantities.xml is not the latest file.Last modified time ".date("Y-m-d H:i:s",filemtime($xmlfile))."";
        }
        if(filesize($xmlfile)== 0)
        {
            $send_email = true;
            $str .= "product_quantities.xml is  zero byte file";
        }
        if($send_email == true)
        {
            $message .= $str.'<br>';
       
            $message .= "</p>
			<p>Regards,<br>Butigo Admin</p>
			</body>
			</html>";
		if (_BU_ENV_ == 'production')
			$to = "operations@butigo.com";
		else
			$to = "rekha.gr@avishinc.com";
		$subject = "Making Logistics XML Script Failsafe";
		
		// Always set content-type when sending HTML email
		$headers = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type:text/html;charset=iso-8859-1" . "\r\n";

		// More headers
		$headers .= 'From: <admin@butigo.com>' . "\r\n";
		if (_BU_ENV_ == 'production')
		{
			$headers .= 'Cc: alper@butigo.com, shiva@avishinc.com, vinodkumar.a@avishinc.com' . "\r\n";
		}

		mail($to,$subject,$message,$headers);
        }
        fwrite($fileLog , "End=".date('Y-m-d H:i:s')."\n");                
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

?>
