<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
//include(dirname(__FILE__).'/../../header.php');
include(dirname(__FILE__).'/showroomcollection.php');

$count = intval(Tools::getValue('count'));
$countvalue = $count;
$showroom = new showroomCollection();
echo $showroom->hookShowroomPopup($countvalue);

//include_once(dirname(__FILE__).'/../../footer.php');

?>