<?php
require_once(realpath(dirname(__FILE__).'/../../').'/config/config.inc.php');
$class_name = basename(dirname(__FILE__));
require_once(dirname(__FILE__).'/'.$class_name.'.php');

$id = intval(Tools::getValue('id'));
$count = intval(Tools::getValue('count'));
$module = new $class_name();
//echo $count."--".$id;exit;
echo $module->_getFormItem($id, $count, true);
