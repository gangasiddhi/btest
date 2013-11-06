<?php

//
include_once(dirname(__FILE__).'/../../config/config.inc.php');
include_once(dirname(__FILE__).'/../../init.php');
include_once(_PS_MODULE_DIR_.'blockbestsellers/blockbestsellers.php');
include_once(dirname(__FILE__).'/../../images.inc.php');
$blockbestsellers = new blockbestsellers();
$details = 0;

if($_GET['action'] == 'add'){
//	$name_product_custom = $_GET['name_product_custom'];
	$name_product = $_GET['name_product'];
//	$id_product_custom = $_GET['id_product_custom'];
	$id_product = $_GET['id_product'];
	$id_lang = $_GET['id_lang'];
	
	//writing into xml file
		$doc = new DOMDocument();
		$doc->load(dirname(__FILE__).'/bestsale.xml' );

		$xmlfile = dirname(__FILE__).'/bestsale.xml';
		$file = fopen($xmlfile,"w");
		fwrite($file, "<PRODUCTID>");

		$employees = $doc->getElementsByTagName( "ID_PRODUCT" );
		foreach( $employees as $employee )
		{
		  $name = $employee->nodeValue;
		  fwrite($file, "<ID_PRODUCT>" .$name. "</ID_PRODUCT>");
		 /*if(isset($_POST['id_product_custom']) && ($name == $_POST['id_product_custom']) && ($_POST['best_sale'] == 'Delete'))
		  {}
		  elseif($name)
		  {
			  if(isset($_POST['id_product_custom']) && ($name != $_POST['id_product_custom'])){
				fwrite($file, "<ID_PRODUCT>" .$name. "</ID_PRODUCT>"); }
			  elseif(!isset($_POST['id_product_custom'])){
			    fwrite($file, "<ID_PRODUCT>" .$name. "</ID_PRODUCT>");}
		  }*/
		}
		
		if(isset($id_product) && ($id_product != $name) && ($_GET['action'] == 'add'))
		{
			$name = array();
			foreach( $employees as $employee )
			{
			  $name[] = $employee->nodeValue;
			}
			if(in_array($id_product, $name) != 0){}
			else{ fwrite($file, "<ID_PRODUCT>" .$id_product. "</ID_PRODUCT>"); }
			$details = 'show';
			$_GET['action'] = "";
			$id_product = "";
			unset ($_GET['action']);
			unset ($id_product);
		}


		fwrite($file, "</PRODUCTID>");
		fclose($file);
}
elseif($_GET['action'] == 'delete'){
		$id_product = $_GET['id_product'];
		$id_lang = $_GET['id_lang'];
		//writing into xml file
		$doc = new DOMDocument();
		$doc->load(dirname(__FILE__).'/bestsale.xml' );

		$xmlfile = dirname(__FILE__).'/bestsale.xml';
		$file = fopen($xmlfile,"w");
		fwrite($file, "<PRODUCTID>");

		$employees = $doc->getElementsByTagName( "ID_PRODUCT" );
		foreach( $employees as $employee )
		{

		  $name = $employee->nodeValue;
		  if(isset($id_product) && ($name == $id_product) && ($_GET['action'] == 'Delete'))
		  {}
		  elseif($name)
		  {
			  if(isset($id_product) && ($name != $id_product)){
				fwrite($file, "<ID_PRODUCT>" .$name. "</ID_PRODUCT>"); }
			  elseif(!$id_product){
			    fwrite($file, "<ID_PRODUCT>" .$name. "</ID_PRODUCT>");}
		  }
		}
		/*if(isset($id_product) && ($id_product != "") && ($_GET['action'] == 'add'))
		{
			fwrite($file, "<ID_PRODUCT>" .$id_product. "</ID_PRODUCT>");
			$details = 'show';
			$_GET['action'] = "";
			$id_product = "";
			unset ($_GET['action']);
			unset ($id_product);
		}*/
		fwrite($file, "</PRODUCTID>");
		fclose($file);
		$details = 'show';



//	$id_product_custom = $_GET['id_product_custom'];
//	$id_product = $_GET['id_product'];
//	$id_lang = $_GET['id_lang'];
//
//	Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'crosssellingmanager` WHERE `id_product_custom`='.intval($id_product_custom).' AND `id_product`='.intval($id_product));
//
//	// Construction du tableau
//	$result = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'crosssellingmanager` WHERE `id_product_custom`='.intval($id_product_custom));
//	$tab_refresh = '<table class="table" style="width:100%;">';
//	$tab_refresh .= '<tr><th>Produit</th><th style="width:50px;text-align:center">Supprimer</th></tr>';
//	foreach($result as $product){
//		$result = Db::getInstance()->getRow('SELECT `name` FROM `'._DB_PREFIX_.'product_lang` WHERE `id_product`=\''.$product['id_product'].'\' AND `id_lang`='.intval($id_lang));
//		$tab_refresh .= '<tr><td>'.$result['name'].'</td><td style="width:50px;text-align:center"><input style="cursor:pointer;background-image:url(\'../img/admin/delete.gif\');border:none;width:16px;height:16px;background-color:#FFFFF0;" onClick="deleteProduct('.$product['id_product'].');return false;" /></td></tr>';
//	}
//	$tab_refresh .= '</table>';
//
//	echo $tab_refresh;	
} elseif($_GET['action'] == 'sort' && $_GET['move_towards']){

	$id_product = $_GET['id_product'];
	$id_lang = $_GET['id_lang'];
	
	/*Get the products from the XML file*/
	$products_array = getXmlSale();
	
	/*Extract only product Ids */
	foreach($products_array as $product)
		$products[]= $product['id_product'];
	
	/*Down, swapping the current postion element with the below*/
	if($_GET['move_towards'] == 'down')
		for($i = 0; $i < count($products) ; $i++)
		{ 
			if($_GET['id_product'] == $products[$i])
			{
				$temp = $products[$i+1];
				$products[$i] = $products[$i+1];
				$products[$i+1] = $_GET['id_product'] ;
				break;
			}

		}
	
	/*UP , swapping the current postion element with the upper*/
	if($_GET['move_towards'] == 'up')
		for($i = 0; $i < count($products) ; $i++)
		{ 
			if($_GET['id_product'] == $products[$i])
			{
				$temp = $products[$i-1];
				$products[$i] = $products[$i-1];
				$products[$i-1] = $_GET['id_product'] ;
				break;
			}
		}
	/*Removing the keys, which doesn't have the elements*/
	$sorted_products = array();
	foreach($products as $product)
		if(isset($product))
			$sorted_products[]=$product;
	
	/*writing into xml file*/
	$doc = new DOMDocument();
	$doc->load(dirname(__FILE__).'/bestsale.xml' );
	$xmlfile = dirname(__FILE__).'/bestsale.xml';
	$file = fopen($xmlfile,"w");
	
	fwrite($file, "<PRODUCTID>");
	foreach($sorted_products as $product)
		fwrite($file, "<ID_PRODUCT>" .$product. "</ID_PRODUCT>");
	fwrite($file, "</PRODUCTID>");
	fclose($file);
	
}


if(($details == 'show') || (isset($_GET['action']) && ($_GET['action'] == 'show')))
{
	$product = getXmlSale();
	// Construction du tableau
	$tab_refresh = '<table border="0" class="table" style="width:100%;">';
	$tab_refresh .= '<tr><th>Produt Name</th><th>Produt Id</th><th>Cover Image</th><th style="width:100px;text-align:center">Action</th><th style="width:100px;text-align:center">Position</th></tr>';
	$iteration = 0;
	foreach($product as $prod)
	{
		$bestsellers = ProductSale::getBestSalesProductName($cookie->id_lang, $prod['id_product']);
		$cover_image = $link->getImageLink($bestsellers[0]['link_rewrite'], intval($bestsellers[0]['id_product']).'-'.intval($bestsellers[0]['id_image']), 'small');
		$imageObj = new Image($bestsellers[0]['id_image']);
		$tab_refresh .= '<tr'.($iteration % 2 ? ' class="alt_row"' : '').' ><td>'.$bestsellers[0]["name"].'</td><td>'.$bestsellers[0]["id_product"].'</td><td>'.(isset($bestsellers[0]['id_image']) ? cacheImage(_PS_IMG_DIR_.'p/'.$imageObj->getExistingImgPath().'.jpg','product_mini_'.(int)($bestsellers[0]['id_product']).(isset($bestsellers[0]['id_product_attribute']) ? '_'.(int)($bestsellers[0]['id_product_attribute']) : '').'.jpg', 45, 'jpg') : '--').'</td><td  style="width:50px;text-align:center"><input style="cursor:pointer;background-image:url(\'../img/admin/delete.gif\');border:none;width:16px;height:16px;background-color:#FFFFF0;" onClick="deleteProduct('.$prod['id_product'].');return false;" /></td>
						<td style="width:100px;text-align:center">';
		if($iteration == count($product)-1)
			$tab_refresh .= '<input title="up" type="image" src="../img/admin/up.gif" class="positionUp" value="up" onClick="sortProducts('.$bestsellers[0]["id_product"].',1);return false;"></td></tr>';
		else if($iteration == 0)			
			$tab_refresh .= '<input type="image" src="../img/admin/down.gif" class="positonDown" value="down" onClick="sortProducts('.$bestsellers[0]["id_product"].',0);return false;">
						</td></tr>';
		else
			$tab_refresh .= '<input type="image" src="../img/admin/up.gif" class="positionUp" value="up" onClick="sortProducts('.$bestsellers[0]["id_product"].',1);return false;">
							<input type="image" src="../img/admin/down.gif" class="positonDown" value="down" onClick="sortProducts('.$bestsellers[0]["id_product"].',0);return false;">
						</td></tr>';
		$iteration++;
	}
//	foreach($result as $product){
//		//$result = Db::getInstance()->getRow('SELECT `name` FROM `'._DB_PREFIX_.'product_lang` WHERE `id_product`=\''.$product['id_product'].'\' AND `id_lang`='.intval($id_lang));
//		$tab_refresh .= '<tr><td>'.$result['name'].'</td><td  style="width:50px;text-align:center"><input style="cursor:pointer;background-image:url(\'../img/admin/delete.gif\');border:none;width:16px;height:16px;background-color:#FFFFF0;" onClick="deleteProduct('.$product['id_product'].');return false;" /></td></tr>';
//	}
	$tab_refresh .= '</table>';
	echo '<sep>'.$tab_refresh;
}


 function getXmlSale()
	{
		if (file_exists(dirname(__FILE__).'/bestsale.xml'))
		{
			$xmlfile = dirname(__FILE__).'/bestsale.xml';
			$xmlparser = xml_parser_create();
			$fp = fopen($xmlfile, 'r');
			$xmldata = fread($fp, filesize($xmlfile));
			xml_parse_into_struct($xmlparser,$xmldata,$values);
			xml_parser_free($xmlparser);
		}
			//print_r($values);
		$data = array();
		$i = 0;
		foreach ($values as $value)
		{
			if($value['type'] == 'complete')
			{
				$data[$i][strtolower($value['tag'])] = $value['value'];
				$i++;
			}
			if($value['tag'] == 'ID_PRODUCT' AND $value['type'] == 'close')
				$i++;

		}
	//echo "<pre>";print_r($data);echo "</pre>";
		return $data;
	}


?>