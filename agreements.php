<?php
include(dirname(__FILE__).'/config/config.inc.php');
ControllerFactory::getController('PaymentAgreementsController')->run();

?>
