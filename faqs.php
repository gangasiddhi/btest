<?php

include(dirname(__FILE__).'/config/config.inc.php');
//if(intval(Configuration::get('PS_REWRITING_SETTINGS')) === 1)
//	$rewrited_url = null;
require_once(dirname(__FILE__).'/modules/faq/faq.php');
ControllerFactory::getController('FaqsFooterController')->run();

?>