<?php

if (! defined('_CAN_LOAD_FILES_')) {
    exit;
}

class ButigoCache extends Module {
    public $name = 'butigocache';
    public $version = 1.0;
    public $author = 'Osman Yüksel';

    // dynamically enable/disable caching
    public static $isEnabled = true;

    public function __construct() {
        parent::__construct();

        $this->displayName = $this->l('ButigoCache');
        $this->description = $this->l('ButigoCache');

        $this->log = Logger::getLogger(get_class($this));
    }

    public function install() {
        if (! parent::install()
            OR ! $this->registerHook('addProduct') // invalidate categories
            OR ! $this->registerHook('newOrder') // invalite categories and product
            OR ! $this->registerHook('productLiked') // invalite categories and product
            OR ! $this->registerHook('productDisliked') // invalite categories and product
            OR ! $this->registerHook('updateQuantity') // invalite categories and product
            OR ! $this->registerHook('updateProduct') // invalite categories and product
            OR ! $this->registerHook('updateProductAttribute') // invalite categories and product
            OR ! $this->registerHook('updateProductPosition') // invalite category
            OR ! $this->registerHook('productRated')
            OR ! $this->registerHook('invalidateCDN')) {

            return false;
        }

        return true;
    }

    public function hookInvalidateCDN($params) {
        if (! isset($params['uris'])) {
            $this->log->info("URI's either not passed or passed with incorrect key, aborting..");

            return;
        }

        $session = curl_init();

        curl_setopt($session, CURLOPT_SSL_VERIFYHOST, true);
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);

        foreach ($params['uris'] as $uri) {
            $url = sprintf('https://purge.mncdn.com/?username=%s&pass=%s&file=%s',
                MEDIANOVA_USERNAME, MEDIANOVA_PASSWORD, $uri);

            $this->log->debug('Invalidating CDN cache for: ' . $url);

            curl_setopt($session, CURLOPT_URL, $url);

            $response = curl_exec($session);

            if (curl_errno($session)) {
                $this->log->error('Invalidating cache failed due to error: ' . curl_error($session));
            }
        }

        curl_close($session);
    }

    public function hookProductLiked($params) {
        $outputCaching = new OutputCaching();

        $this->log->debug("invalidating cache for Product #" . $params['id_product'] . " upon LIKE request");

        $outputCaching->invalidateProductById($params['id_product']);

        //invalidate unit cache
        Product::invalidateCacheById($params['id_product']);
    }

    public function hookProductDisliked($params) {
        $outputCaching = new OutputCaching();

        $this->log->debug("invalidating cache for Product #" . $params['id_product'] . " upon DISLIKE request");

        $outputCaching->invalidateProductById($params['id_product']);

        //invalidate unit cache
        Product::invalidateCacheById($params['id_product']);
    }

    public function hookNewOrder($params) {
        $cart = $params['cart'];
        $outputCaching = new OutputCaching();

        foreach ($cart->getProducts() AS $parr) {
            $product = new Product($parr['id_product']);

            $this->log->debug("invalidating cache for Product #" . $product->id . " upon NEW_ORDER request");

            $outputCaching->invalidateProductAndCategories($product);

            //invalidate unit cache
            Product::invalidateCacheById($parr['id_product']);
        }
    }

    public function hookUpdateQuantity($params) {
        if (! static::$isEnabled) {
            $this->log->info('Caching is temporarily disabled, skipping cache invalidation..');

            return true;
        }

        $outputCaching = new OutputCaching();

        $this->log->debug("invalidating cache for Product #" . $params['product']->id . " upon UPDATE_QUANTITY request");

        $outputCaching->invalidateProductAndCategories($params['product']);

        //invalidate unit cache
        Product::invalidateCacheById($params['product']->id);
    }

    public function hookAddProduct($params) {
        $outputCaching = new OutputCaching();

        $this->log->debug("invalidating cache for Product #" . $params['product']->id . " upon ADD request");

        $outputCaching->invalidateCategories($params['product']);

        //invalidate unit cache
        Product::invalidateCacheById($params['product']->id);
    }

    public function hookUpdateProduct($params) {
        if (! static::$isEnabled) {
            $this->log->info('Caching is temporarily disabled, skipping cache invalidation..');

            return true;
        }

        $outputCaching = new OutputCaching();

        $this->log->debug("invalidating cache for Product #" . $params['product']->id . " upon UPDATE request");

        $outputCaching->invalidateProductAndCategories($params['product']);

        //invalidate unit cache
        Product::invalidateCacheById($params['product']->id);
    }

    public function hookUpdateProductAttribute($params) {
        if (! static::$isEnabled) {
            $this->log->info('Caching is temporarily disabled, skipping cache invalidation..');

            return true;
        }

        $outputCaching = new OutputCaching();
        $productId = Product::getIdByAttributeId($params['id_product_attribute']);

        $this->log->debug("invalidating cache for Product #" . $productId . " upon ATTR_CHANGE request");

        $product = new Product($productId);
        $outputCaching->invalidateProductAndCategories($product);


        //invalidate unit cache
        Product::invalidateCacheById($productId);
    }

    public function hookUpdateProductPosition($params) {
        $outputCaching = new OutputCaching();

        $this->log->debug("invalidating cache for Product #" . $params['id_product'] . " upon REPOSITION request");

        $outputCaching->invalidateCategoryById($params['id_category']);

        //invalidate unit cache
        Product::invalidateCacheById($params['id_product']);
    }


    public function hookProductRated($params) {
        $outputCaching = new OutputCaching();

        $this->log->debug("invalidating cache for Product #" . $params['product']->id . " upon RATE request");

        $outputCaching->invalidateProductById($params['id_product']);

        //invalidate unit cache
        Product::invalidateCacheById($params['id_product']);
    }
}
