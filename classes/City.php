<?php
class CityCore extends ObjectModel {
    public static function getTownsStatic($id_city) {
        $id_city = pSQL($id_city);
        $sql = 'SELECT t.id_town, t.name FROM `'._DB_PREFIX_.'city` c, `'._DB_PREFIX_.'town` t';
        $sql .= " where c.id_city=t.id_city and c.id_city='$id_city'";
        return Db::getInstance()->ExecuteS($sql);
    }

    public static function getTurkeyCities() {
        $sql = 'SELECT id_city, name FROM `'._DB_PREFIX_.'city`';
        return Db::getInstance()->ExecuteS($sql);
    }

    public static function getNameStatic($id_city) {
        $sql = 'SELECT name FROM `'._DB_PREFIX_.'city` where id_city='.pSQL($id_city);
        return Db::getInstance()->getValue($sql);
    }
}
?>