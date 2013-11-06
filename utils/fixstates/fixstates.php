<?php
require(dirname(__FILE__).'/../../config/config.inc.php');

require 'functions.php';

// Clear, because not used and created below for 156.
$deleteSql = "DELETE from bu_province where id_state=34";
execSql($deleteSql);

createTemporaryTablesAndFields();

$deleteSql = "DELETE from bu_province where id_state=1 and name in ('Girne','Gazimagosa', 'Güzelyurt') ";
execSql($deleteSql);

// Wrong typed province
execSql("Update bu_province set name='Çağlayancerit' where name='Çağlıyancerit'");


upperCaseStateAndProvinces();

$sql = "Select * from tr_city";
$cities = DB::getInstance()->ExecuteS($sql);
$wrongStates = array(); // States these have wrong city_id

foreach ($cities as $city) {
	$idStateWrong = false;

	$id_city = $city[id];
	//echo "id_city=>" .$id_city.'<br>';

	// TODO remove
	/*
	if (!in_array($id_city, array(34,42,43,44,45,46))) {
		continue;
	}
	*/

	$sql = "select id_state from bu_state where name='{$city[name]}'";
	$id_state = DB::getInstance()->getValue($sql);
	//var_dump($id_state,$sql);

	if ($id_state != $id_city) {
		echo sprintf("%s : => id_state: %s id_city: %s",$city[name], $id_state,$id_city).'<br>';
		array_push($wrongStates, array(
			"id_state" => $id_state,
			"id_city" => $id_city
		));

		$idStateWrong = true;
	}

	$city_townsSql = "Select * from tr_province where city_id='{$id_city}'";
	$city_towns = DB::getInstance()->ExecuteS($city_townsSql);

	// Create missing provinces with id_state
	foreach ($city_towns as &$town) {
		$townName = $town[name];
		if (!provinceExistByName($id_state, $townName)) {
			echo "Creating new province". $id_state. "--". $town[name].'<br>';
			createProvince($id_state, $town[name]);
		}
	}

	$provincesSql = "Select * from bu_province where id_state='{$id_state}' and old_id_state=0 ";
	$provinces = DB::getInstance()->ExecuteS($provincesSql);

	foreach ($provinces as &$province) {
		//$province[name] =  toUpper($province[name]);

		//S**** Eski provinceleri yenilerle update et
		$newProvinceId = getNewProvinceIdByName($id_city, $province[name]);
		if ($newProvinceId) {
			//var_dump($newProvinceId, $id_city, $province[name]);
			//changeOldProvinceWithNew($province['id_province'], $id_city, $newProvinceId, $province[name]);
		} else {
			echo "Olmayan Province-->", $province['id_province'],"--",$id_city,"--", $province[name]."__".$newProvinceId."<br>";
		}
		//F**** Eski provinceleri yeni dilerle falan update et
	}

	if ($idStateWrong) {
		increaseStateId($id_state, $id_city, $city['name']);
	}
}

echo "Wrong States:";
var_dump($wrongStates);

$wrongIdStates = array();
foreach ($wrongStates as $item) {
	$id_state = $item["id_state"];
	$id_city = $item["id_city"];
	echo "descresing". $id_city."--". $id_state.'<br>';
	decreaseStateId($id_city);
	$wrongIdStates[] = $id_state;
}

//S**** bu_address state duzeltme

if ($wrongIdStates) {
	$wrongIdStatesStr = implode(',', $wrongIdStates);
	$sql = "UPDATE bu_address ba ".
	"INNER JOIN bu_state bs on ba.id_state=bs.old_id_state ".
	"set ba.id_state=bs.id_state where ba.id_state in ($wrongIdStatesStr)";
	echo $sql.'<br>';
	execSql($sql);
}

//F**** bu_address state duzeltme

clearUnncessaries();
echo "Process completed";


?>