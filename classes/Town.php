<?php
class TownCore extends ObjectModel {
    public static function getNameStatic($id_town) {
        $sql = 'SELECT name FROM `'._DB_PREFIX_.'town` where id_town='.pSQL($id_town);
        return Db::getInstance()->getValue($sql);
    }
}
?>