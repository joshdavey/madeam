<?php
// Welcome to your application's bootstrap
// Note: Check the Controller_App class and make sure the include for this file is uncommented.

// use unicode
if (function_exists('mb_internal_encoding')) { mb_internal_encoding("UTF-8"); }

// uncomment to set content type to utf8
header('Content-type: text/html; charset=utf-8');

// set default timezone
date_default_timezone_set('America/Toronto');

// development environment settings
if (MADEAM_ENVIRONMENT == 'development') { error_reporting(E_ALL); }

// production environment settings
elseif (MADEAM_ENVIRONMENT == 'production') { error_reporting(0); }