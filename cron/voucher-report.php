<?php
require_once(dirname(__FILE__) . "/../config/config.inc.php");
require_once(dirname(__FILE__) . "/../init.php");

$cron_user = Tools::getValue('crnu');
$cron_pass = Tools::getValue('crnp');
if(!$cron_user)
	$cron_user = $argv[1];
if(!$cron_pass)
	$cron_pass = $argv[2];
$cron_pass = Tools::encrypt($cron_pass);

if($cron_user == _CRON_USER_ && $cron_pass == _CRON_PASSWD_){
	
	$start = date("Y-m-d 00:00:00", strtotime("-1 day"));
	$end = date("Y-m-d 23:59:59", strtotime("-1 day"));
    $defaultLanguage = Configuration::get('PS_LANG_DEFAULT');
	
	$monthStartDate = date('Y-m-d 00:00:00', strtotime('first day of this month'));
	$monthEndDate = date('Y-m-d 23:59:59', strtotime('last day of this month'));
	$log = true;
	/*$thisMonthFolder = _PS_DOWNLOAD_DIR_."voucher-reports/".date("F-Y",strtotime('this month'));
	
	if(!file_exists($thisMonthFolder)){
		mkdir($thisMonthFolder);
	}*/
	
	//$dailyVoucherFileName = $thisMonthFolder."/".date('Y-m-d', strtotime('today')).".csv";
	$dailyVoucherFileName = "/tmp/voucher_report_".date('Y-m-d', time()).".csv";
	//$monthlyVoucherFileName = _PS_DOWNLOAD_DIR_."voucher-reports/month-vise/".date("F-Y",strtotime('this month')).".csv";
	$monthlyVoucherFileName = "/tmp/month_voucher_report_".date("F-Y-m-d",strtotime('this month')).".csv";
	
	$query = "SELECT * INTO OUTFILE '".$dailyVoucherFileName."' FIELDS TERMINATED BY ',' ENCLOSED BY '\"'
		      FROM (
				    (SELECT 'date', 'voucherCode', 'discountType', 'numberOfVouchersUsed', 'totalDiscountAmount',
					'totalRevenueWithDiscount', 'totalRevenueWithoutDiscount')
			 UNION(
			  SELECT DATE_FORMAT(o.date_add,'%Y-%m-%d') As OrderDate, d.name AS voucher_code, dtl.name AS discountType,
					 COUNT(od.id_order_discount) AS numberOfVouchersUsed, SUM(d.value) AS DiscountAmount,
					 SUM(o.total_paid_real) AS totalRevenueWithDiscount,
					 SUM(o.total_paid_real+o.total_discounts) AS totalRevenueWithoutDiscount
				FROM `" . _DB_PREFIX_ . "orders` o
				LEFT JOIN `" . _DB_PREFIX_ . "order_discount` od ON (o.id_order = od.id_order)
				LEFT JOIN `" . _DB_PREFIX_ . "discount` d ON (od.id_discount = d.id_discount)
				LEFT JOIN `" . _DB_PREFIX_ . "discount_type_lang` dtl ON (dtl.id_discount_type = d.id_discount_type AND dtl.id_lang = ".$defaultLanguage.")
				WHERE o.date_add BETWEEN '".$start."' AND '".$end."' AND d.name != ''
				GROUP BY voucher_code
				ORDER BY od.id_order DESC)
				) AS VoucherRevenueReport";
		
	$dailyVoucherReport = Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute($query);
	
	if($dailyVoucherReport){
		$subject = 'Daily Voucher Report';
		//sendMailWithFileAttachment($dailyVoucherFileName,$subject);
	}else{
		//sendMail('Error While generating the daily voucher report');
		if ($log)
		{
			$myFile = _PS_LOG_DIR_."/voucher_report_errors.txt";
			if(!file_exists($myFile))
				$fh = fopen($myFile, 'w') or die("can't open file");
			else
				$fh = fopen($myFile, 'a') or die("can't open file");
			fwrite($fh, 'Daily Voucher Report Error: ----' . date("D M j G:i:s T Y") . "\n");
			fwrite($fh, "\n Error While generating the daily voucher report.");
			fwrite($fh, print_r($query,true)."\n");
			fclose($fh);
		}
	}
	
	$query = "SELECT * INTO OUTFILE '".$monthlyVoucherFileName."' FIELDS TERMINATED BY ',' ENCLOSED BY '\"'
		      FROM (
				    (SELECT 'date', 'voucherCode', 'discountType', 'numberOfVouchersUsed', 'totalDiscountAmount',
					'totalRevenueWithDiscount', 'totalRevenueWithoutDiscount')
			 UNION(
			  SELECT DATE_FORMAT(o.date_add,'%Y-%m-%d') As OrderDate, d.name AS voucher_code, dtl.name AS discountType,
					 COUNT(od.id_order_discount) AS numberOfVouchersUsed, SUM(d.value) AS DiscountAmount,
					 SUM(o.total_paid_real) AS totalRevenueWithDiscount,
					 SUM(o.total_paid_real+o.total_discounts) AS totalRevenueWithoutDiscount
				FROM `" . _DB_PREFIX_ . "orders` o
				LEFT JOIN `" . _DB_PREFIX_ . "order_discount` od ON (o.id_order = od.id_order)
				LEFT JOIN `" . _DB_PREFIX_ . "discount` d ON (od.id_discount = d.id_discount)
				LEFT JOIN `" . _DB_PREFIX_ . "discount_type_lang` dtl ON (dtl.id_discount_type = d.id_discount_type AND dtl.id_lang = ".$defaultLanguage.")
				WHERE o.date_add BETWEEN '".$monthStartDate."' AND '".$monthEndDate."' AND d.name != ''
				GROUP BY voucher_code
				ORDER BY od.id_order DESC)
				) AS VoucherRevenueReport";
	
	$monthlyVoucherReport = Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute($query);
	
	if($monthlyVoucherReport){
		$subject = 'Upto to today this month Voucher Report';
		//sendMailWithFileAttachment($monthlyVoucherFileName,$subject);
	}else{
		//sendMail('Error While generating the monthly voucher report');
		if ($log)
		{
			$myFile = _PS_LOG_DIR_."/voucher_report_errors.txt";
			if(!file_exists($myFile))
				$fh = fopen($myFile, 'w') or die("can't open file");
			else
				$fh = fopen($myFile, 'a') or die("can't open file");
			fwrite($fh, 'Monthly Voucher Report: START----' . date("D M j G:i:s T Y") . "\n");
			fwrite($fh, "\n Error While generating the monthly voucher report.");
			fwrite($fh, print_r($query,true)."\n");
			fclose($fh);
		}
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
	
function sendMailWithFileAttachment($file,$subject){
	$mailto = "marketing@butigo.com, ramesh.ks@avishinc.com, gangadhar.km@avishinc.com";
	$file_size = filesize($file);
	$handle = fopen($file, "r");
	$content = fread($handle, $file_size);
	fclose($handle);
	$content = chunk_split(base64_encode($content));
	$uid = md5(uniqid(time()));
	$name = basename($file);
	$header =  "From: Butigo <root@butigo.com>\r\n";
	$header .=  "Reply-To: root@butigo.com";
	$header .= "MIME-Version: 1.0\r\n";
	$header .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n";
	$header .= "This is a multi-part message in MIME format.\r\n";
	$header .= "--".$uid."\r\n";
	$header .= "Content-type:text/plain; charset=iso-8859-1\r\n";
	$header .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
	$header .= $message."\r\n\r\n";
	$header .= "--".$uid."\r\n";
	$header .= "Content-Type: application/octet-stream; name=\"".$name."\"\r\n"; // use different content types here
	$header .= "Content-Transfer-Encoding: base64\r\n";
	$header .= "Content-Disposition: attachment; filename=\"".$name."\"\r\n\r\n";
	$header .= $content."\r\n\r\n";
	$header .= "--".$uid."--";
	if (mail($mailto, $subject, "Find the attached Report", $header)) {
			echo "mail send ... OK"; // or use booleans here
	} else {
			echo "mail send ... ERROR!";
	}
}

function sendMail($message){
	$mailto = "ramesh.ks@avishinc.com,gangadhar.km@avishinc.com";
	$header =  "From: Butigo <root@butigo.com>\r\n";
	$header .=  "Reply-To: root@butigo.com";
	$header .= "MIME-Version: 1.0\r\n";
	mail($mailto, 'Error While Generating the Voucher Revenue Report.', $message, $header);
}

?>
