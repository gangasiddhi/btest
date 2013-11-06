<?php

function createTemporaryTablesAndFields(){
	execSql("ALTER TABLE bu_state add column old_id_state int(10) DEFAULT 0");
}

function clearUnncessaries() {
	//execSql("ALTER table bu_state drop column old_id_state");
	//execSql("DROP table tr_city");
	//execSql("DROP table tr_province");

}

function provinceExistByName($id_state, $name) {
	$sql = "Select id_province from bu_province where name='$name' and id_state=$id_state";
	//echo $sql.'<br>';
	return DB::getInstance()->getValue($sql)? true:false;
}

function getProvinceIdByName($name) {
	$sql = "Select id_province from bu_province where name='$name'";
	//echo "$sql<br>";
	return DB::getInstance()->getValue($sql);
}

function getNewProvinceIdByName($city_id, $name) {
	$sql = "Select id from tr_province where city_id=$city_id and name='$name'";
	//echo "$sql<br>";
	return DB::getInstance()->getValue($sql);
}

function createProvince($id,$name) {
	$sql = "INSERT INTO bu_province (id_state, name) VALUES('$id','$name')";
	execSql($sql);
	//TODO: aktiflestir
}

function changeOldCityWithNew($oldId,$newId, $name) {
	$insertSql = "INSERT INTO bu_state (id_country,id_zone,iso_code,tax_behavior,active,old_id_state,id_state,name) VALUES (211,1,1,0,1,$oldId,$newId,'$name')";
	execSql($insertSql);
	// echo $insertSql.'<br>';
}

function increaseStateId($oldId, $id_city, $name) {
	// Duplicate hatası yememek için once yukselticez. Sonra dusurucez
	$newId = (int) $id_city * 1000;
	$insertSql = "INSERT INTO bu_state (id_country,id_zone,iso_code,tax_behavior,active,old_id_state,id_state,name) VALUES (211,1,1,0,1,$oldId,$newId,'$name')";
	// echo $insertSql.'<br>';
	execSql($insertSql);

	// Eski hatalı kaydı sil
	$sql = "DELETE from bu_state where id_state=$oldId";
	execSql($sql);
	// echo $sql.'<br><br>';

	// S**** Provincelerin statelerini yenisiyle değiştir
	$sql = "Select id_province from bu_province where id_state=$oldId";
	// echo "$sql<br>";
	$provinceIds = execSql($sql);
	foreach ($provinceIds as &$item) {
		$item = $item['id_province'];
	}

	$provincesStr = implode(",",$provinceIds);

	// echo $oldId;
	// var_dump($provinceIds);
	$sql = "UPDATE bu_province set id_state=$newId where id_province in ($provincesStr)";
	execSql($sql);
	// echo $sql.'<br>';

	// F**** Provincelerin statelerini yenisiyle değiştir
}

function decreaseStateId($id_city) {
	// Duplicate hatası yememek için yukselttigimiz idleri dusurucyoruz
	$id_state = (int) $id_city * 1000;
	$sql = "UPDATE bu_state set id_state=$id_city where id_state=$id_state";
	// echo $sql.'<br>';
	execSql($sql);

	//echo $sql;
	execSql("UPDATE bu_province set id_state=$id_city where id_state=$id_state");
}

function changeProvinceName($id_province, $name) {
	$sql = "UPDATE bu_province set name='$name' where id_province=$id_province";
	//echo $sql;
	execSql($sql);
}

function toUpper($str) {
	$replaceChars =  explode(',',"i,ğ,ü,ş,ç,ö,ı");
	$replaceWith = explode(',', "İ,Ğ,Ü,Ş,Ç,Ö,I");
	return strtoupper(str_replace($replaceChars, $replaceWith, $str));
}

function execSql($sql) {
	return DB::getInstance()->ExecuteS($sql);
}

/*
// Mersin(İçel) gibi olanlar farklılık gostermesin diye. Sadece Mersin alınıyor
preg_match('(^[\wİıĞğÜüŞşÇçÖö]+)', $province[name], $matches);
if ($matches[0]) {
	$province[name] = $matches[0];
}
*/

function upperCaseStateAndProvinces() {
	/*
	// tr_city
	$sql = "Select * from tr_city";
	$cities = DB::getInstance()->ExecuteS($sql);
	foreach ($cities as $city) {
		$upperName = toUpper($city[name]);
		DB::getInstance()->ExecuteS("UPDATE tr_city set name='$upperName' where id={$city[id]}");
	}

	// tr_province
	$sql = "Select * from tr_province";
	$cities = DB::getInstance()->ExecuteS($sql);
	foreach ($cities as $city) {
		$upperName = toUpper($city[name]);
		DB::getInstance()->ExecuteS("UPDATE tr_province set name='$upperName' where id={$city[id]}");
	}
	*/
	// bu_state
	$sql = "Select id_state,name from bu_state";
	$cities = DB::getInstance()->ExecuteS($sql);
	foreach ($cities as $city) {
		$upperName = toUpper($city[name]);
		DB::getInstance()->ExecuteS("UPDATE bu_state set name='$upperName' where id_state={$city[id_state]}");
	}


	$sql = "Select id_province,name from bu_province";
	$cities = DB::getInstance()->ExecuteS($sql);
	foreach ($cities as $city) {
		$upperName = toUpper($city[name]);
		DB::getInstance()->ExecuteS("UPDATE bu_province set name='$upperName' where id_province={$city[id_province]}");
	}

}

?>