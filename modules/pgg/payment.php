<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php');
include(dirname(__FILE__).'/pgg.php');

if (!$cookie->isLogged())
    Tools::redirect('authentication.php?back=order.php');

$pgg = new PGG();
echo $pgg->execPayment($cart);

include_once(dirname(__FILE__).'/../../footer.php');

?>