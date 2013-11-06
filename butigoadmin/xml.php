<?php
//Gets the current working directory
define('PS_ADMIN_DIR', getcwd());
include(PS_ADMIN_DIR.'/../config/config.inc.php');
include_once(PS_ADMIN_DIR.'/zipstream.php');

$cookie = new Cookie('psAdmin');

if (!$cookie->id_employee)
	Tools::redirect('login.php');

//to display error message
function Error($msg) {
	//Fatal error
	die('<b>XML error:</b> '.$msg);
}

//to display the orders placed by customers in XML format
if (isset($_GET['order_xml'])) {
	global $cookie;

	$order = new Order(intval($_GET['id_order']));

	$filename = _PS_DOWNLOAD_DIR_ . "/orders_xml/all/" . $order->invoice_number . ".xml";

	if (file_exists($filename)) {
		$fp = fopen($filename, 'r');
		$output = fread($fp, filesize($filename));
		fclose($fp);
	} else{
		$output = Tools::generateXml($order);
	}

	$file_name = $order->invoice_number . ".xml";

	outputfile($file_name , $output);
} elseif (isset($_GET['multiple_xmls'])) {
	global $cookie;

	$orders = Order::getOrdersIdByInvoiceNumbers($_GET['invoice_from'], $_GET['invoice_to']);

	if (! is_array($orders))
		die (Tools::displayError('No orders within the given invoice numbers found'));

	 generateMutipleXml($orders, $_GET['invoice_from'], $_GET['invoice_to']);
}
//to display all the products in XML format
elseif (isset($_GET['products_xml']))
{
	//arguments passed to getProducts function
	$orderByValues = array(0 => 'name', 1 => 'price', 2 => 'date_add', 3 => 'date_upd', 4 => 'position', 5 => 'manufacturer_name', 6 => 'quantity');
	$orderWayValues = array(0 => 'ASC', 1 => 'DESC');
	$orderBy = Tools::strtolower(Tools::getValue('orderby', $orderByValues[intval(Configuration::get('PS_PRODUCTS_ORDER_BY'))]));
	$orderWay = Tools::strtoupper(Tools::getValue('orderway', $orderWayValues[intval(Configuration::get('PS_PRODUCTS_ORDER_WAY'))]));
	if (!in_array($orderBy, $orderByValues))
		$orderBy = $orderByValues[0];
	if (!in_array($orderWay, $orderWayValues))
		$orderWay = $orderWayValues[0];

	//to get all the categories available
	$categorys =  Category::getCategories(intval($cookie->id_lang),true,false);

	//$output variable to buffer the output and get the length of the output to pass it in header
	$output = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n" ;
	$output.= "<SHOP>\n" ;

	//get the details of the product of all catigories
	foreach($categorys as $category )
	{
		$idcategory = $category['id_category'];
		if($idcategory == 1)
			continue;
		$products = Product::getProducts(intval($cookie->id_lang), 0, 0, $orderBy, $orderWay,  $idcategory, true);
		foreach($products as $product)
		{
			$image = Image::getImages(intval($cookie->id_lang), $product['id_product']);
			$output.= "<SHOPITEM>\n";
			$output.= "\t<PRODUCT>" .Tools::xmlentities($product['name']) . "</PRODUCT>\n";
			$output.= "\t<DESCRIPTION>" .Tools::xmlentities($product['description_short']) . "</DESCRIPTION>\n";
			if (is_array($image) AND sizeof($image))
				  $output.= "\t<IMGURL>" .Tools::xmlentities("http://" . $_SERVER['SERVER_NAME'] . __PS_BASE_URI__ . "img/p/" . $image[0]['id_product'] . "-" . $image[0]['id_image'] . "-small.jpg") . "</IMGURL>\n";
			$output.= "\t<PRICE_VAT>" .Tools::xmlentities($product['price']) . "</PRICE_VAT>\n";
			$output.= "\t<AVAILABILITY>" .Tools::xmlentities($product['quantity']) . "</AVAILABILITY>\n";
			if($product['reference'] != "") $output.= "\t<Reference>" .Tools::xmlentities($product['reference']) . "</Reference>\n";
			   $output.= "\t<MANUFACTURER>" .Tools::xmlentities($product['manufacturer_name']) . "</MANUFACTURER>\n";
			$output.= "</SHOPITEM>\n";
		}
	}

	//XML end
	$output.= "</SHOP>\n";
	$file_name = "products.xml";
	outputfile($file_name , $output);
}
//to display shipment in XML format
elseif (isset($_GET['shipment_xml']))
{
	include_once(PS_ADMIN_DIR.'/../classes/CSVReader.php');
	$output = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n" ;
	$output.= "<SHIPMENT>\n" ;

	$csv_array = CsvToArray::open(dirname(__FILE__).'/import/shipping.csv',',');
	//reading the .csv file row and column wise
	foreach ($csv_array as $c)
	{
		$nome                = $c[0];
		$empresa           = $c[1];
		$cidade            = $c[2];
		$estado            = $c[3];
		$endereco           = $c[4];
		$numero            = $c[5];
		$complemento    = $c[6];
		// $bairro            = $c[7];
		// $cep                = $c[8];
		// $pais                = $c[9];
		$output.= "<SHIPMENT_ITEM>\n";
		$output.= "\t<PRODUCT>" .Tools::xmlentities($nome) . "</PRODUCT>\n";
		$output.= "\t<PRODUCT>" .Tools::xmlentities($empresa) . "</PRODUCT>\n";
		$output.= "\t<PRODUCT>" .Tools::xmlentities($cidade) . "</PRODUCT>\n";
		$output.= "\t<PRODUCT>" .Tools::xmlentities($estado) . "</PRODUCT>\n";
		$output.= "\t<PRODUCT>" .Tools::xmlentities($endereco) . "</PRODUCT>\n";
		$output.= "\t<PRODUCT>" .Tools::xmlentities($numero) . "</PRODUCT>\n";
		$output.= "\t<PRODUCT>" .Tools::xmlentities( $complemento) . "</PRODUCT>\n";
		$output.= "</SHIPMENT_ITEM>\n";
	}

	$output.= "</SHIPMENT>\n";
	$file_name = "shipment.xml";
	outputfile($file_name , $output);
}

function generateMutipleXml($order_ids, $invoice_from, $invoice_to)
{
	global $cookie;
	$count = 1;
	$folder = _PS_DOWNLOAD_DIR_.'temp/'.$invoice_from.'_'.$invoice_to.'_xmls';
	foreach ($order_ids AS $id_order)
	{
		$order = new Order(intval($id_order));
		$file_name = _PS_DOWNLOAD_DIR_ . "orders_xml/all/" . $order->invoice_number . ".xml";
		$temp_file = $order->invoice_number . ".xml";

		if(!is_dir($folder))
			mkdir($folder);

		if(file_exists($file_name))
		{
			$fp = @fopen($file_name, 'rb');
			$output = fread($fp, filesize($file_name));
			fclose($fp);

			$fh = @fopen($folder.'/'.$temp_file, 'wb');
			fwrite($fh, $output);
			fclose($fh);
		}else{
			$output = Tools::generateXml($order);
			$fh = @fopen($folder.'/'.$temp_file, 'wb');
			fwrite($fh, $output);
			fclose($fh);
		}
	}
	$files = array_diff( scandir($folder,1) , array(".",".."));

	$file_name = $invoice_from.'_'.$invoice_to.'_xmls.zip';
	$filepath = _PS_DOWNLOAD_DIR_.'temp';

	$name =  $invoice_from.'_'.$invoice_to.'_xmls';
	Tools::createZip($files, $folder, $name);
}

// To download the XML file
function outputfile($file_name, $output)
{
	header('Content-Type: application/x-download');
	if(headers_sent())
		Error('Some data has already been output, can\'t send XML file');
	header('Content-Length: '.strlen($output));
	header('Content-Disposition: attachment;filename ="'.$file_name.'"');
	header('Cache-Control: private, max-age=0, must-revalidate');
	header('Pragma: public');
	ini_set('zlib.output_compression','0');
	echo $output;
	//return '';
}

?>
