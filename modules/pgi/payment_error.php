<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php');
include_once(dirname(__FILE__).'/pgi.php');

if (!$cookie->isLogged())
    Tools::redirect('authentication.php?back=order.php');
$pgi = new PGI();
echo $pgi->hookPaymentError($cart);

include_once(dirname(__FILE__).'/../../footer.php');

?>
