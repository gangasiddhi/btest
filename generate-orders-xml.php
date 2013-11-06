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
	$hours = Tools::getValue('hr')*60*60;
	$now = time();
	$date_to = date('Y-m-d H:i:s', $now);
	$date_from = date('Y-m-d H:i:s', $now - $hours);

	$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
	 SELECT `id_order`
	 FROM `'._DB_PREFIX_.'orders`
	 WHERE invoice_date >= \''.pSQL($date_from).'\' AND invoice_date <= \''.pSQL($date_to).'\'
	 ORDER BY id_order ASC');
	if($result)
	{
		/*$output = "<?xml version=\"1.0\" encoding=\"utf-8\"?> \n";
		$output .= "<ORDERS>\n";*/
		$xml_dir = _PS_DOWNLOAD_DIR_.'acc/'.$date_from.'_'.$date_to.'_xmls';
		mkdir($xml_dir);
		foreach ($result AS $ord)
		{
			$order = new Order(intval($ord['id_order']));
			$res = Tools::generateXml($order);
			$output = $res;

			$fh = fopen($xml_dir . "/" . $order->invoice_number . ".xml", 'w');
			fwrite($fh, $output);
			fclose($fh);
		}
		/*$output .= "</ORDERS>\n";

		$fh = fopen(_PS_DOWNLOAD_DIR_."acc/orders_".$date_from."_".$date_to.".xml", 'w');
		fwrite($fh, $output);
		fclose($fh);*/

		echo "Order XMLs from $date_from to $date_to generated successfully.";
	}
	else
	{
		echo "No orders from $date_from to $date_to";
	}
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
