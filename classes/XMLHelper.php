<?php
class XMLHelperCore {
    public static function loadFromString($xmlString) {
        try {
            return simplexml_load_string($xmlString);
        } catch (Exception $e) {
            return false;
        }
    }

    public static function loadFromFile($path) {
        try {
            return simplexml_load_file($path);
        } catch (Exception $e) {
            return false;
        }
    }

    public static function isValid($xmlString) {
        return self::loadFromString($xmlString) ? true : false;
    }
}

?>