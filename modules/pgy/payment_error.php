<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php');
include_once(dirname(__FILE__).'/pgy.php');

if (!$cookie->isLogged())
    Tools::redirect('authentication.php?back=order.php');
$pgy = new PGY();
echo $pgy->hookPaymentError($cart);

include_once(dirname(__FILE__).'/../../footer.php');

?>