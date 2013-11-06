<?php
include_once(dirname(__FILE__).'/../../config/config.inc.php');
include_once(dirname(__FILE__).'/../../init.php');
include_once(_PS_MODULE_DIR_.'crosssellingmanager/crosssellingmanager.php');
$crosssellingmanager = new crosssellingmanager();

if($_GET['action'] == 'add'){
	$name_product_custom = $_GET['name_product_custom'];
	$name_product = $_GET['name_product'];
	$id_product_custom = $_GET['id_product_custom'];
	$id_product = $_GET['id_product'];
	$id_lang = $_GET['id_lang'];
	
	// checkCorrespondance
	$result = Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.'crosssellingmanager` WHERE `id_product_custom`=\''.$id_product_custom.'\' AND `id_product`=\''.$id_product.'\'');
	
	if(!$result){
		Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'crosssellingmanager` SET `id_product_custom`='.intval($id_product_custom).',`id_product`='.intval($id_product));

		// Construction du tableau	
		$result = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'crosssellingmanager` WHERE `id_product_custom`='.intval($id_product_custom));
				
		$tab_refresh = '<table class="table" style="width:100%;">';
		$tab_refresh .= '<tr><th>Produit</th style="width:50px;text-align:center"><th>Supprimer</th></tr>';
		foreach($result as $product){
			$result = Db::getInstance()->getRow('SELECT `name` FROM `'._DB_PREFIX_.'product_lang` WHERE `id_product`=\''.$product['id_product'].'\' AND `id_lang`='.intval($id_lang));
			$tab_refresh .= '<tr><td>'.$result['name'].'</td><td  style="width:50px;text-align:center"><input style="cursor:pointer;background-image:url(\'../img/admin/delete.gif\');border:none;width:16px;height:16px;background-color:#FFFFF0;" onClick="deleteProduct('.$product['id_product'].');return false;" /></td></tr>';
		}
		$tab_refresh .= '</table>';
		
		echo $tab_refresh;
	} else {
		// Construction du tableau	
		$result = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'crosssellingmanager` WHERE `id_product_custom`='.intval($id_product_custom));
		$tab_refresh = '<table class="table" style="width:100%;">';
		$tab_refresh .= '<tr><th>Produit</th><th style="width:50px;text-align:center">Supprimer</th></tr>';
		foreach($result as $product){
			$result = Db::getInstance()->getRow('SELECT `name` FROM `'._DB_PREFIX_.'product_lang` WHERE `id_product`=\''.$product['id_product'].'\' AND `id_lang`='.intval($id_lang));
			$tab_refresh .= '<tr><td>'.$result['name'].'</td><td  style="width:50px;text-align:center"><input style="cursor:pointer;background-image:url(\'../img/admin/delete.gif\');border:none;width:16px;height:16px;background-color:#FFFFF0;" onClick="deleteProduct('.$product['id_product'].');return false;" /></td></tr>';
		}
		$tab_refresh .= '</table>';
		
		echo strtoupper($name_product).$crosssellingmanager->getLError().'<sep>'.$tab_refresh;
	}		
}

if($_GET['action'] == 'show') {
	$name_product_custom = $_GET['name_product_custom'];
	$id_product_custom = $_GET['id_product_custom'];
	$id_lang = $_GET['id_lang'];
	
	// Construction du tableau	
	$result = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'crosssellingmanager` WHERE `id_product_custom`='.intval($id_product_custom));
	$tab_refresh = '<table class="table" style="width:100%;">';
	$tab_refresh .= '<tr><th>Produit</th><th style="width:50px;text-align:center">Supprimer</th></tr>';
	foreach($result as $product){
		$result = Db::getInstance()->getRow('SELECT `name` FROM `'._DB_PREFIX_.'product_lang` WHERE `id_product`=\''.$product['id_product'].'\' AND `id_lang`='.intval($id_lang));
		$tab_refresh .= '<tr><td>'.$result['name'].'</td><td  style="width:50px;text-align:center"><input style="cursor:pointer;background-image:url(\'../img/admin/delete.gif\');border:none;width:16px;height:16px;background-color:#FFFFF0;" onClick="deleteProduct('.$product['id_product'].');return false;" /></td></tr>';
	}
	$tab_refresh .= '</table>';
	
	echo $name_product_custom.'<sep>'.$tab_refresh;
}

if($_GET['action'] == 'delete') {
	$id_product_custom = $_GET['id_product_custom'];
	$id_product = $_GET['id_product'];
	$id_lang = $_GET['id_lang'];
	
	Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'crosssellingmanager` WHERE `id_product_custom`='.intval($id_product_custom).' AND `id_product`='.intval($id_product));

	// Construction du tableau	
	$result = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'crosssellingmanager` WHERE `id_product_custom`='.intval($id_product_custom));
	$tab_refresh = '<table class="table" style="width:100%;">';
	$tab_refresh .= '<tr><th>Produit</th><th style="width:50px;text-align:center">Supprimer</th></tr>';
	foreach($result as $product) {
		$result = Db::getInstance()->getRow('SELECT `name` FROM `'._DB_PREFIX_.'product_lang` WHERE `id_product`=\''.$product['id_product'].'\' AND `id_lang`='.intval($id_lang));
		$tab_refresh .= '<tr><td>'.$result['name'].'</td><td style="width:50px;text-align:center"><input style="cursor:pointer;background-image:url(\'../img/admin/delete.gif\');border:none;width:16px;height:16px;background-color:#FFFFF0;" onClick="deleteProduct('.$product['id_product'].');return false;" /></td></tr>';
	}
	$tab_refresh .= '</table>';
	
	echo $tab_refresh;	
} 

?>