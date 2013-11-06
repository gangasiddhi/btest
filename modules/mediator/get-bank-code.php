<?php

/**
 * This file is used to fetch the bankcode based on the credit card entered.
 */
include(dirname(__FILE__) . '/../../config/config.inc.php');
include(dirname(__FILE__) . '/../../init.php');

$creditCard = Tools::getBankViaCCNo(Tools::getValue('ccno'));

die(Tools::jsonEncode($creditCard));

?>
