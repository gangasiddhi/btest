<?php

include(dirname(__FILE__) . '/../../config/config.inc.php');
include(dirname(__FILE__) . '/../../header.php');
include(dirname(__FILE__) . '/pga.php');

if (! $cookie->isLogged()) {
    Tools::redirect('authentication.php?back=order.php');
}

$pga = new PGA();

echo $pga->execPayment($cart);

include_once(dirname(__FILE__) . '/../../footer.php');

?>
