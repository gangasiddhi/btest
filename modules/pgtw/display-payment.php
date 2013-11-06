<?php

include(dirname(__FILE__) . '/../../config/config.inc.php');
include(dirname(__FILE__) . '/../../init.php');

include_once(dirname(__FILE__) . '/pgtw.php');

/*if ($id_carrier = (int)(Tools::getValue('pgf_id_carrier'))) {
	$cart->id_carrier = $id_carrier;
	$cart->update();
}*/

/* called via ajax. */
$pgtw = new PGTW();
echo $pgtw->displayPayment();

?>
