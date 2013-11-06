<?php

require_once(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../init.php');
include_once(dirname(__FILE__) .'/productrecommend.php');
$productId = Tools::getValue('productId');

$productrecommend = new productrecommend();

$productrecommend->getProductSizes($productId);

?>
