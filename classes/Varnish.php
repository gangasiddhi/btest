<?php
/**
 * Varnish invalidation class
 */
class VarnishCore extends ObjectModel {

    public function __construct()
    {
        $this->log = Logger::getLogger(get_class($this));
        $this->dbName = _DB_PREFIX_ . "varnish_queue"; 
    }




    /**
     * invalidate cache for by sending a PURGE request to URL
     */
    public function invalidateURL($url)
    {
        //$url = static::getLatestUrlAfterRedirects($url);
        //$latestUrl = end($url);
        //

        $url = ltrim($url, "/");
        $baseUrl = _PS_BASE_URL_.__PS_BASE_URI__;


        $reqUrl = $baseUrl . $url;

        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, $reqUrl );
        curl_setopt( $ch, CURLOPT_ENCODING, "" );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, "PURGE");
        curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
        $content = curl_exec( $ch );
        $response = curl_getinfo( $ch );
        curl_close ( $ch );

        $this->log->debug("invalidating VARNISH URL cache for " . $reqUrl);
        return $this;
    }


    /**
     * This is written to get latest url after redirections
     * Note: this is not used now
     */
    public static function getLatestUrlAfterRedirects($url, $responses = array())
    {
        $url = str_replace( "&amp;", "&", urldecode(trim($url)) );

        $cookie = tempnam ("/tmp", "CURLCOOKIE");
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1" );
        curl_setopt( $ch, CURLOPT_URL, $url );
        curl_setopt( $ch, CURLOPT_COOKIEJAR, $cookie );
        curl_setopt( $ch, CURLOPT_ENCODING, "" );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 10 );
        curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );
        curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
        $content = curl_exec( $ch );
        $response = curl_getinfo( $ch );
        curl_close ( $ch );

        $d = new \StdClass();
        $d->time = date("Y-m-d H:i:s");
        $d->url = $url;
        $d->code =  $response['http_code'];
        $d->content = $content;
        $responses[] = $d;

        if ($response['http_code'] == 301 || $response['http_code'] == 302) {
            ini_set("user_agent", "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1");
            $headers = get_headers($response['url']);

            $location = "";
            foreach ($headers as $value) {
                if ( substr( strtolower($value), 0, 9 ) == "location:" )
                    return static::getLatestUrlAfterRedirects( trim( substr( $value, 9, strlen($value) ) ) , $responses);
            }
        }

        return $responses;
    }


    public function getQueue() {
		$sql = 'SELECT url FROM `'.$this->dbName.'`';
        $urls = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql);

        return $urls;
    }


    public function enqueue($url) {
		Db::getInstance()->Execute("insert into `".$this->dbName."` values('".$url."')");
    }


    public function dequeue($url) {
		Db::getInstance()->Execute("DELETE FROM `".$this->dbName."` WHERE `url` = '".$url."'");
    }

}
