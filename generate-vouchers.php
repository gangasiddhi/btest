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

/*Only for one timer use*/
function generateVouchers($voucher_qty, $discount_value, $desc, $expire_years)
{
	$xls_output = '';
	//$sep = "\t";
	$sepn = "\n";
	//echo $voucher_qty;exit;
	for($i=0;$i<$voucher_qty;$i++)
	{
		$password = Tools::passwdGen();
		$name = $password.$i;
		$xls_output .= $name.$sepn;
		$languages = Language::getLanguages(false);
		// create discount
		$discount = new Discount();
		$discount->id_discount_type = 2;
		$discount->behavior_not_exhausted = 0;
		foreach ($languages as $language)
		$discount->description[$language['id_lang']] = strval($desc);
		$discount->value = floatval($discount_value);
		$discount->name = $name;
		$discount->id_customer = 0;
		$discount->id_currency = 4;
		$discount->quantity = 1;
		$discount->quantity_per_user = 1;
		$discount->cumulable = 0;
		$discount->cumulable_reduction = 1;
		$discount->minimal = 0.00;
		$discount->active = 1;
		$now = time();
		$discount->date_from = date('Y-m-d H:i:s', $now);
		$discount->date_to = date('Y-m-d H:i:s', $now + ($expire_years * 365 * 24 * 60 * 60));
		if (!$discount->validateFieldsLang(false) OR !$discount->add())
		{
			echo 'Generation Failed';echo "<br/>";
			return false;
		}
	}

	$filename ="vouchers_$desc.xls";
	$fp = fopen(_PS_ROOT_DIR_.'/download/'.$filename, "w");
	fwrite($fp, $xls_output);
	fclose($fp);
	return true;
}

if($cron_user == _CRON_USER_ && $cron_pass == _CRON_PASSWD_)
{
	$voucher_qty =  Tools::getValue('qty');
	$discount_value = Tools::getValue('val');
	$desc =  Tools::getValue('desc');
	$expire_years = Tools::getValue('exp');
	//echo $voucher_qty.'----'.$discount_value.'----'.$desc;exit;
	if($voucher_qty && $discount_value && $desc)
	{
		if(!generateVouchers($voucher_qty, $discount_value, $desc, $expire_years))
		{
			echo 'Voucher could not be generated';
		}
		else
		{
			if($voucher_qty == 1)
				echo $voucher_qty.' voucher has been successfully generated with value '.$discount_value;
			else
				echo $voucher_qty.' vouchers have been successfully generated with value '.$discount_value;
		}
	}
	else
	{
		echo "Please enter the url parameters 'qty - quantity','val - value of the voucher','desc - description for the vouchers' as shown : qty=10&val=10.00&desc=fivehundred";
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
