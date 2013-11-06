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
//$file_path=_PS_ROOT_DIR_.'/cron/date.txt';
//Group->getGroups();
//if($cron_user == _CRON_USER_ && $cron_pass == _CRON_PASSWD_)
//{

 $sql="SELECT * INTO OUTFILE '/tmp/cusomer_order_report.csv' FIELDS ENCLOSED BY '\"' TERMINATED BY ';' FROM ( ( SELECT 'OrderID', 'OrderTotal','OrderDate', 'MemberEmail', 'MemberID', 'RegisterDate') UNION( 
         SELECT o.`id_order` as OrderID, SUM(o.`total_paid_real`) as OrderTotal, o.`date_add` as OrderDate, c.`email` as MemberEmail, o.`id_customer` as MemberID, c.`date_add` as RegisterDate 
         FROM `bu_orders` o LEFT JOIN `bu_customer` c ON c.id_customer = o.id_customer
         WHERE o.`valid` = 1 AND o.`date_add`>= CONCAT(DATE_SUB(CURRENT_DATE ,INTERVAL 7 DAY),' ','00:00:01') AND o.`date_add`<=CONCAT(CURRENT_DATE, ' ','23:59:59')
         GROUP BY MemberID ORDER BY o.`date_add` ASC)
) AS CUSTOMERDETAILS";

$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql);

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
