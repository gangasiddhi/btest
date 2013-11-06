<?php

/* Debug only */
@ini_set('display_errors', 'off');


//varnish sends client ip in http_x_forwarded_for header
if (isset($_SERVER['REMOTE_ADDR']) && ($_SERVER['REMOTE_ADDR'] == "127.0.0.1") && isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
    $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_X_FORWARDED_FOR"];
}

define('_PS_DEBUG_SQL_', false);

/**
 * let's us to change environment without having to overwrite
 * config.inc.php.. Just define _BU_ENV_ variable in your
 * environment.inc.php and you're good to go!
 *
 * If you want to stay in production delete environment.inc.php!
 */
if (file_exists(dirname(__FILE__) . '/environment.inc.php')) {
    require_once(dirname(__FILE__) . '/environment.inc.php');
} else {
    // DO NOT CHANGE THIS!!! CREATE A FILE CALLED environment.php
    // IN YOUR config/ DIRECTORY AND DEFINE THIS THERE!!
    define('_BU_ENV_', 'production');
    define('_DB_SERVER2_', '192.168.100.57');
}

// logger settings
include(dirname(__FILE__) . '/logger.inc.php');

/* Autoload */
require_once(dirname(__FILE__) . '/autoload.php');



$start_time = microtime(true);

/* Compatibility warning */
define('_PS_DISPLAY_COMPATIBILITY_WARNING_', false);

/* SSL configuration */
define('_PS_SSL_PORT_', 443);

/* Improve PHP configuration to prevent issues */
ini_set('upload_max_filesize', '100M');
ini_set('default_charset', 'utf-8');
ini_set('magic_quotes_runtime', 0);

// correct Apache charset (except if it's too late)
if (! headers_sent()) {
    header('Content-Type: text/html; charset=utf-8');
}

/* No settings file? goto installer...*/
if (! file_exists(dirname(__FILE__) . '/settings.inc.php')) {
    $dir = (
        (is_dir($_SERVER['REQUEST_URI']) OR substr($_SERVER['REQUEST_URI'], -1) == '/') ?
            $_SERVER['REQUEST_URI'] : dirname($_SERVER['REQUEST_URI']) . '/'
    );

    if (! file_exists(dirname(__FILE__) . '/../install')) {
        die('Error: \'install\' directory is missing');
    }

    header('Location: install/');

    exit;
}

require_once(dirname(__FILE__) . '/settings.inc.php');
require_once(dirname(__FILE__) . '/cron_settings.inc.php');

/* Include all defines */
require_once(dirname(__FILE__) . '/defines.inc.php');

if (! defined('_PS_MAGIC_QUOTES_GPC_')) {
    define('_PS_MAGIC_QUOTES_GPC_', get_magic_quotes_gpc());
}

if (! defined('_PS_MODULE_DIR_')) {
    define('_PS_MODULE_DIR_', _PS_ROOT_DIR_ . '/modules/');
}

if (! defined('_PS_MYSQL_REAL_ESCAPE_STRING_')) {
    define('_PS_MYSQL_REAL_ESCAPE_STRING_', function_exists('mysql_real_escape_string'));
}

/* Bugsnag */
require_once(dirname(__FILE__) . '/bugsnag.config.inc.php');

/* Redefine REQUEST_URI if empty (on some webservers...) */
if (! isset($_SERVER['REQUEST_URI']) OR empty($_SERVER['REQUEST_URI'])) {
    if (substr($_SERVER['SCRIPT_NAME'], -9) == 'index.php' AND empty($_SERVER['QUERY_STRING'])) {
        $_SERVER['REQUEST_URI'] = dirname($_SERVER['SCRIPT_NAME']) . '/';
    } else {
        $_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'];

        if (isset($_SERVER['QUERY_STRING']) AND ! empty($_SERVER['QUERY_STRING'])) {
            $_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
        }
    }
}

/* aliases */
function p($var) {
    return Tools::p($var);
}

function d($var) {
    Tools::d($var);
}

function ppp($var) {
    return Tools::p($var);
}

function ddd($var) {
    Tools::d($var);
}

global $_MODULES;

$_MODULES = array();

/* Load all configuration keys */
Configuration::loadConfiguration();

/* Load all language definitions */
Language::loadLanguages();

/* Define order state */
// DEPRECATED : these defines are going to be deleted on 1.6 version of Prestashop
// USE : Configuration::get() method in order to getting the id of order state
define('_PS_OS_CHEQUE_', Configuration::get('PS_OS_CHEQUE'));
define('_PS_OS_PAYMENT_', Configuration::get('PS_OS_PAYMENT'));
define('_PS_OS_PREPARATION_', Configuration::get('PS_OS_PREPARATION'));
define('_PS_OS_SHIPPING_', Configuration::get('PS_OS_SHIPPING'));
define('_PS_OS_DELIVERED_', Configuration::get('PS_OS_DELIVERED'));
define('_PS_OS_UNDELIVERED_', Configuration::get('PS_OS_UNDELIVERED'));
define('_PS_OS_CANCELED_', Configuration::get('PS_OS_CANCELED'));
define('_PS_OS_REFUND_', Configuration::get('PS_OS_REFUND'));
define('_PS_OS_ERROR_', Configuration::get('PS_OS_ERROR'));
define('_PS_OS_OUTOFSTOCK_', Configuration::get('PS_OS_OUTOFSTOCK'));
define('_PS_OS_BANKWIRE_', Configuration::get('PS_OS_BANKWIRE'));
define('_PS_OS_PAYPAL_', Configuration::get('PS_OS_PAYPAL'));
define('_PS_OS_WS_PAYMENT_', Configuration::get('PS_OS_WS_PAYMENT'));
define('_PS_OS_EXCHANGE_', Configuration::get('PS_OS_EXCHANGE'));
define('_PS_OS_PARTIALREFUND_', Configuration::get('PS_OS_PARTIALREFUND'));
define('_PS_OS_MANUALREFUND_', Configuration::get('PS_OS_MANUALREFUND'));
define('_PS_OS_PARTIALEXCHANGE_',  Configuration::get('PS_OS_PARTIALEXCHANGE'));
define('_PS_OS_PARTIALCREDITED_',  Configuration::get('PS_OS_PARTIALCREDITED'));
define('_PS_OS_FULLCREDITED_', Configuration::get('PS_OS_FULLCREDITED'));
define('_PS_OS_CREDITSGIVEN_', Configuration::get('PS_OS_CREDITSGIVEN'));
define('_PS_OS_WAITING_MANAGER_APPROVAL_', Configuration::get('PS_OS_WAITING_MANAGER_APPROVAL'));
define('_PS_OS_BACK_ORDER_', Configuration::get('PS_OS_BACK_ORDER'));
define('_PS_OS_PROCESSING_', Configuration::get('PS_OS_PROCESSING'));
define('_PS_OS_PROCESSED_', Configuration::get('PS_OS_PROCESSED'));
define('_PS_OS_WAITING_FOR_CUSTOMER_APPROVAL_', Configuration::get('PS_OS_WAITING_FOR_CUSTOMER'));
define('_PS_OS_FIRST_ORDER_OF_CUSTOMER_', Configuration::get('PS_OS_FIRST_ORDER_OF_CUSTOMER'));
define('_PS_OS_EXCHANGE_CANCELLATION_', Configuration::get('PS_EXCHANGE_CANCELLATION'));

// VOUCHER TYPES
define('_EXCHANGE_VOUCHER_TYPE_ID_', Configuration::get('EXCHANGE_VOUCHER_TYPE_ID'));
define('_PS_OS_CREDIT_ID_TYPE_', Configuration::get('PS_CREDIT_DISCOUNT_ID_TYPE'));
define('_PS_BUY1_GET1_FREE_TYPE_', Configuration::get('PS_BUY1_GET1_FREE_TYPE'));
define('_PS_DISCOUNT_AMOUNT_TYPE_', Configuration::get('PS_DISCOUNT_AMOUNT_TYPE'));
define('_PS_DISCOUNT_RATIO_TYPE_', Configuration::get('PS_DISCOUNT_RATIO_TYPE'));

/**
 * It is not safe to rely on the system's timezone settings,
 * and this would generate a PHP Strict Standards notice.
 */
if (function_exists('date_default_timezone_set')) {
    @date_default_timezone_set(Configuration::get('PS_TIMEZONE'));
}

/* Smarty */
require_once(dirname(__FILE__) . '/smarty.config.inc.php');

/**
 * Possible value are true, false, 'URL'
 * (for 'URL' append SMARTY_DEBUG as a parameter to the url)
 * default is false for production environment
 */
define('SMARTY_DEBUG_CONSOLE', false);

if (_BU_ENV_ == "production") {
    $outputCaching = new OutputCaching();
    $outputCaching->run();
}
