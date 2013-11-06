<?php
// Get the current working directory
define('PS_ADMIN_DIR', getcwd());
include(PS_ADMIN_DIR.'/../config/config.inc.php');

$cookie = new Cookie('psAdmin');
if (!$cookie->id_employee)
	Tools::redirect('login.php');

if (isset($_GET['create_xls']))
{
	$result = Order::getInvoiceDetailsByDate(Tools::getValue('date_from'), Tools::getValue('date_to'));
	if (!is_array($result))
		die (Tools::displayError('No records found within the given date'));

	$sep_tab = "\t";
	$sep_newline = "\n";
	//$filepath = _PS_DOWNLOAD_DIR_;
	$filename = "Invoice_Details_".Tools::getValue('date_from')."_".Tools::getValue('date_to').".xls";
	//$fp = fopen($filepath.$filename, "w");
	$product_tax_percentage = 8;
	$shipping_tax_percentage = 18;
	
/*	$schema_insert = "";
	$schema_insert_rows = "";
	$content = "";

	//$schema_insert_rows = 'Invoice Number'.$sep.'invoice_date'.$sep.'Percentage of  Tax of Product'.$sep.'Product Price without Tax'.$sep.'Percentage of  Tax of Others'.$sep.'Shipping without Tax'.$sepn;
	$schema_insert_rows = mb_convert_encoding('Fatura No', 'UTF-16LE', 'UTF-8').$sep.mb_convert_encoding('Fatura Tarih', 'UTF-16LE', 'UTF-8').$sep.mb_convert_encoding('Ürünün KDV Yüzdesi', 'UTF-16LE', 'UTF-8').$sep.mb_convert_encoding('KDVsiz ürün Fiyatı', 'UTF-16LE', 'UTF-8').$sep.mb_convert_encoding('Geri Kalan KDV Yüzdesi', 'UTF-16LE', 'UTF-8').$sep.mb_convert_encoding('KDVsiz Kargo Fiyatı', 'UTF-16LE', 'UTF-8').$sepn;

	//start while loop to get data
	foreach($result as $row)
	{
		$shipping_cost = round($row['total_shipping']  / (100 + $row['carrier_tax_rate']) * 100, 2);
		$schema_insert = strip_tags($row['invoice_number']).$sep.strip_tags($row['invoice_date']).$sep.$product_tax_percentage.$sep.strip_tags($row['total_products']).$sep.$shipping_tax_percentage.$sep.$shipping_cost.$sepn;
		$content .= $schema_insert;
	}
	$xls_output = $schema_insert_rows.$content;
	//fwrite($fp, $xls_output);
	//fclose($fp);

	//header("Content-Type: application/vnd.ms-excel; charset=UTF-16LE");
	header("Content-Type: application/octet-stream; charset=UTF-16LE");
	header("Content-Disposition: attachment;filename=".$filename);
	header("Content-Transfer-Encoding: binary ");
	echo $xls_output;
	exit;
*/

	$xls_output = '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<style type="text/css">
body, table { font-family:Arial,Helvetica,sans-serif; font-size:12px; color:#000 }
</style>';
	$xls_output .= '<table border="1">
	<tr height="30" style="background-color:#EDEDEE">
		<th>Fatura No</th>
		<th>Fatura Tarih</th>
		<th>Ürünün KDV Yüzdesi</th>
		<th>KDVsiz ürün Fiyatı</th>
		<th>Ürünün KDV</th>
		<th>Geri Kalan KDV Yüzdesi</th>
		<th>KDVsiz Kargo Fiyatı</th>
		<th>Kargo KDV</th>
	</tr>';
	foreach($result as $row)
	{
		$shipping_cost = round($row['total_shipping'] / (100 + $row['carrier_tax_rate']) * 100, 2);
		$xls_output .= '<tr height="20">
			<td>'.$row['invoice_number'].'</td>
			<td>'.$row['invoice_date'].'</td>
			<td>'.$product_tax_percentage.'</td>
			<td>'.$row['total_products'].'</td>
			<td>'.round($product_tax_percentage / 100 * $row['total_products'], 2).'</td>
			<td>'.$shipping_tax_percentage.'</td>
			<td>'.$shipping_cost.'</td>
			<td>'.round($shipping_tax_percentage / 100 * $shipping_cost, 2).'</td>
		</tr>';
	}
	$xls_output .= '</table>';

	//fwrite($fp, $xls_output);
	//fclose($fp);
	header('Content-Type: text/html; charset=utf-8');
	header("Content-type: application/vnd.ms-excel; charset=utf-8");
	header("Content-Disposition: attachment; filename=\"$filename\"");
	echo $xls_output;
	exit;
}
?>
