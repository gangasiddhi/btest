<?php
include(dirname(__FILE__).'/../config/config.inc.php');
include(dirname(__FILE__).'/../init.php');

$varnish = new Varnish();

$queue = $varnish->getQueue();

foreach($queue as $v) {
    $url = $v['url'];
    $varnish->invalidateUrl($url);
    $varnish->dequeue($url); 
}
