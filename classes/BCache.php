<?php
class BCacheCore
 {
    static $host = "127.0.0.1";
    static $port = 11211;

    private static $logger;
    public static $memcache;
    public static $force = false;


    public static function connect() {
         if (!static::$memcache) {
            static::$memcache = memcache_pconnect(static::$host, static::$port);
        }
    }


    public static function get($hash)
    {
        if (!Configuration::get('PS_BCACHE_MEMCACHED') && !static::$force) return false;

        static::connect();
        static::_log($hash, "GET");

        return memcache_get(static::$memcache, $hash);
    }

    public static function set($hash, $data, $duration = 3600) 
    {
        if (!Configuration::get('PS_BCACHE_MEMCACHED') && !static::$force) return false;

        static::connect();
        static::_log($hash, "SET");

        return memcache_set(static::$memcache, $hash, $data, 0, $duration);
    }


    public static function flushAll() 
    {
        if (!Configuration::get('PS_BCACHE_MEMCACHED') && !static::$force) return false;

        static::connect();
        static::_log("ALL", "FLUSH");

        return memcache_flush(static::$memcache);
    }


    public static function remove($hash)
    {
        if (!Configuration::get('PS_BCACHE_MEMCACHED') && !static::$force) return false;

        static::connect();
        static::_log($hash, "DELETE");
        memcache_set(static::$memcache, $hash, "", 0, 1);
    }


    public static function _log($hash, $event) 
    {
        $key = "PS_BCACHE_LOG_" . $event;
        if (!Configuration::get($key)) return false;

        if (!self::$logger) {
            self::$logger = Logger::getLogger(); 
        }

        self::$logger->debug("BCACHE EVENT ". $event . " -> HASH : ". $hash); 
    }
}
