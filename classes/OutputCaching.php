<?php

class OutputCachingCore {
    public $cookie;

    public function __construct()
    {
        $cookieLifetimeFromConfig = (int)Configuration::get('PS_COOKIE_LIFETIME_FO');

        $cookieLifetime = (time() + (($cookieLifetimeFromConfig > 0 ? $cookieLifetimeFromConfig : 1)* 3600));

        $this->cookie = new Cookie('ps', '', $cookieLifetime);

        //turkish lang hardcoded since we may not get proper lang on admin
        $this->_lang = 4;

    }

    public static function getCache($hash)
    {
        return BCache::get($hash);
    }

    public static function setCache($hash, $data)
    {
        BCache::set($hash, $data, 3600);
    }

    public static function removeCache($hash)
    {
        BCache::remove($hash);
    }

    /**
     * invalidate cache for by sending a PURGE request to URL
     */
    public function invalidateURL($url)
    {

        if (preg_match("/http/", $url)) {
            //get only URI from url

            $parsed = parse_url($url);
            $url = $parsed['path'];
            if ($parsed['query']) {
                $url .= "?" . $parsed['query'];
            }


            $url = preg_replace('/(\/+)/','/',$url); //remove double slashes in url
        }

        OutputCaching::removeCache(OutputCaching::getUriHash($url));
        OutputCaching::removeCache(OutputCaching::getUriHash($url, true));

        $varnish = new Varnish();
        $varnish->enqueue($url);

        return $this;
    }

    /**
     * Invalidate filte urls
     */
    public function invalidateFiltersByCategoryId($categoryId)
    {
        $baseUrl = _PS_BASE_URL_.__PS_BASE_URI__ . "/modules/taggingandfiltering/sizeTagsFilter.php?categoryId=$categoryId";

        $this->invalidateURL($baseUrl);

        $paramsForShoeSizes = array();
        $paramForAllShoeSizes = "";

        $minShoeSize = 35;
        $maxShoeSize = 41;

        for($i = $minShoeSize; $i < $maxShoeSize; $i++) {
            $paramsForShoeSizes[] = "&shoeSizes[]=$i";
            $paramForAllShoeSizes .= "&shoeSizes[]=$i";
        }

        $paramsForShoeSizes[] = $paramForAllShoeSizes;
        foreach($paramsForShoeSizes as $param) {
            $this->invalidateURL($baseUrl . $param);
        }
    }

    /**
     * Invalidate product from $productOrj data
     */
    private function _invalidateProduct($productOrj)
    {
        $product = clone $productOrj; //prevent other processes use modified object
        if (is_array($product->link_rewrite)) {
            $product->link_rewrite = $product->link_rewrite[$this->_lang];
        }

        $link = new Link();
        $productLink = $link->getProductLink($product);
        $this->invalidateURL($productLink);

        //remove products link for all categories
        $productCategories = Product::getProductCategories($product->id);
        foreach($productCategories as $productCategory) {
            $category = new Category($productCategory);

            $link = new Link();
            if ($category->link_rewrite[$this->_lang]) {
                $product->category = $category->link_rewrite[$this->_lang];
                $productLink = $link->getProductLink($product);
                $this->invalidateURL($productLink);
            }

        }
    }

    /**
     * invalidate category data by id
     */
    public function invalidateCategoryById($categoryId)
    {
        $this->invalidateFiltersByCategoryId($categoryId);
        $category = new Category($categoryId);

        $link = new Link();
        if ($category->link_rewrite[$this->_lang]) {
            $categoryLink = $link->getCategoryLink($category->link_rewrite[$this->_lang]);
            $this->invalidateUrl($categoryLink);
        }
    }

    /**
     * invalidate product data by product id
     */
    public function invalidateProductById($productId)
    {
        $product = new Product($productId);
        $this->_invalidateProduct($product);
    }

    /**
     * invalidate product and products categories
     */
    public function invalidateProductAndCategories(Object $product)
    {
        $this->_invalidateProduct($product);
        $productCategories = Product::getProductCategories($product->id);

        foreach($productCategories as $productCategory) {
            $this->invalidateCategoryById($productCategory);
        }
    }

    /**
     * Invalidate each category the product is in.
     */
    public function invalidateCategories(Product $product) {
        $productCategories = Product::getProductCategories($product->id);

        foreach($productCategories as $productCategory) {
            $this->invalidateCategoryById($productCategory);
        }
    }

    public static function getUri()
    {
        $uri = $_SERVER['REQUEST_URI'];
        $uri = preg_replace("/&_(.*)/", "", $uri); //remove jQuery timestamp param
        return $uri;
    }

    public static function getUriHash($uri = null, $https = false)
    {
        $uri = $uri ? $uri : static::getUri();

        if ($https) {
            $uri = "https-" . $uri;
        }

        return md5($uri) . ".cache";
    }

    public function shouldUriBeCached()
    {
        $isUserLogged = $this->cookie->isLogged();
        $uri = $this->getUri();

        if (!$isUserLogged && (
                (preg_match('/ayakkabi-modelleri/', $uri)) ||
                (preg_match('/bayan-ayakkabi-modelleri/', $uri)) ||
                (preg_match('/yeni-sezon-bayan-ayakkabilar/', $uri)) ||
                (preg_match('/cok-satan-bayan-ayakkabilar/', $uri)) ||
                (preg_match('/bot-cizme-modelleri/', $uri)) ||
                (preg_match('/spor-ayakkabi-modelleri/', $uri)) ||
                (preg_match('/babet-modelleri/', $uri)) ||
                (preg_match('/sandalet-modelleri/', $uri)) ||
                (preg_match('/topuklu-ayakkabi-modelleri/', $uri)) ||
                (preg_match('/kisa-topuklu-ayakkabi-modelleri/', $uri)) ||
                (preg_match('/dolgu-topuklu-ayakkabi-modelleri/', $uri)) ||
                (preg_match('/bayan-canta-modelleri/', $uri)) ||
                (preg_match('/ayakkabi-modelleri/', $uri)) ||
                (preg_match('/indirimli-ayakkabilar/', $uri)) ||
                (preg_match('/ayakkabi-tasarimcilari/', $uri)) ||
                (preg_match('/dugun-toren-ayakkabi-modelleri/', $uri)) ||
                (preg_match('/butigo-magazin/', $uri)) ||
                (preg_match('/gorusleriniz/', $uri)) ||
                (preg_match('/sss/', $uri)) ||
                (preg_match('/gorusleriniz/', $uri)) ||
                (preg_match('/content\//', $uri)) ||
                (preg_match('/loland/', $uri)) ||
                (preg_match('/landing/', $uri)) ||
                (preg_match('/ayakkabi-tasarimcilari/', $uri))
                // (preg_match('/html$/', $uri)) // caching of product details pages disabled
            )) {

            return true;
        } else if ((preg_match('/taggingandfiltering/', $uri) && !preg_match('/tags/', $uri))) {
            return true;
        }
    }

    public static function getUriData()
    {
        $https = false;
        if (isset($_SERVER['HTTPS']) && $_SERVER["HTTPS"] == "on") {
            $https = true;
        }

        $uriHash = static::getUriHash(null, $https);
        return OutputCaching::getCache($uriHash, $https);
    }

    public static function setUriData($data)
    {
        $https = false;
        if (isset($_SERVER['HTTPS']) && $_SERVER["HTTPS"] == "on") {
            $https = true;
        }
        $uriHash = static::getUriHash(null, $https);
        OutputCaching::setCache($uriHash, $data);
    }

    public function run()
    {
        if ($this->shouldUriBeCached()) {
            $cache = static::getUriData();

            if (!$cache) {
                ob_start();

                register_shutdown_function(function () {
                    $cache = ob_get_contents();
                    ob_end_clean();

                    OutputCaching::setUriData($cache);
                    echo $cache;
                });

            } else {
                echo $cache;
                exit;
            }
        }
    }
}
