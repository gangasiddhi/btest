<?php

include(dirname(__FILE__) . '/../../config/config.inc.php');
include(dirname(__FILE__) . '/../../init.php');
include_once(dirname(__FILE__) . '/mediator.php');

/**
 * id_carrier in cart is updated when the user chooses one of the payment options.
 * Shipping cost varies depending on payment option chosen by customer
 */
/*if ($id_carrier = (int)(Tools::getValue('pgf_id_carrier'))) {
	$cart->id_carrier = $id_carrier;
	$cart->update();
}*/

// called via ajax.
$med = new mediator();
echo $med->displayPayment(false, Tools::getValue('bank_id'));

?>
