<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php');
include_once(dirname(__FILE__).'/pgf.php');

if (!$cookie->isLogged())
    Tools::redirect('authentication.php?back=order.php');
$pgf = new PGF();
echo $pgf->hookPaymentError($cart);

include_once(dirname(__FILE__).'/../../footer.php');

?>