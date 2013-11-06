<?php

include(dirname(__FILE__) . '/../../config/config.inc.php');
include(dirname(__FILE__) . '/../../header.php');
include_once(dirname(__FILE__) . '/pgtw.php');

if (! $cookie->isLogged()) {
    Tools::redirect('authentication.php?back=order.php');
}

$pgtw = new PGTW();
echo $pgtw->hookPaymentError($cart);

include_once(dirname(__FILE__) . '/../../footer.php');

?>
