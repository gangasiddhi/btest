<?php
$params = $_GET;
if (!$params['fn']){
  exit;
}

$fn = $params['fn'];

require_once('ArasKargoShipment.php');

$aras = new ArasKargoShipment();

if (!method_exists($aras, $params['fn'])) {
	exit;
}

unset($params['fn']);

try{
    if ($params['hash'] == Tools::encrypt(Configuration::get('ARAS_CARGO_HASH'))){
    	$params['password'] = Configuration::get('ARAS_CARGO_PASSWORD');
    } else {
    	echo "Authentication failed.";
    	exit;
    }

    echo '<pre>';
	    var_dump($aras->$fn($params));
    echo '</pre>';
}
catch(Exception $e) {
    echo "Error:\n".$e->getMessage();
}
?>