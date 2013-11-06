<?php
/*
Page:           _config-rating.php
Created:        Aug 2006
Last Mod:       Mar 18 2007
Holds info for connecting to the db, and some other vars
--------------------------------------------------------- 
ryan masuga, masugadesign.com
ryan@masugadesign.com 
Licensed under a Creative Commons Attribution 3.0 License.
http://creativecommons.org/licenses/by/3.0/
--------------------------------------------------------- */

//Connect to  your rating database
$rating_dbhost        = _DB_SERVER_;
$rating_dbuser        = _DB_USER_;
$rating_dbpass        = _DB_PASSWD_;
$rating_dbname        = _DB_NAME_;
$rating_tableName     = _DB_PREFIX_.'ratings';
$rating_path_db       = '..'.__PS_BASE_URI__.'modules/productrating/rating/db.php'; // the path to your db.php file
$rating_path_rpc      = '..'.__PS_BASE_URI__.'modules/productrating/rating/rpc.php'; // the path to your rpc.php file
$rating_unitwidth     = 15; // the width (in pixels) of each rating unit (star, etc.)

//$rating_conn 		  = 
//mysql_connect($rating_dbhost, $rating_dbuser, $rating_dbpass) or die  ('Error connecting to mysql');
//mysql_select_db($rating_dbname);

?>