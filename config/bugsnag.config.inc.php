<?php

require_once(_PS_TOOL_DIR_.'bugsnag/bugsnag.php');

// Configure Bugsnag with API key
Bugsnag::register("523f4c7b58c9f39e7d1fe2ea6d2f3838");

// Attach Bugsnag's error and exception handlers
set_error_handler("Bugsnag::errorHandler");
set_exception_handler("Bugsnag::exceptionHandler");

// To log only on production
Bugsnag::setReleaseStage(_BU_ENV_);
?>
