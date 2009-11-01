<?php
// set environment
$environemnt = apache_getenv('MADEAM_ENV');
if ($environemnt === false) {
  madeam\Framework::$environment = require './env.php';
} else {
  madeam\Framework::$environment = $environemnt;
}

switch (madeam\Framework::$environment) {
  case 'development' :
    madeam\Exception::$inlineErrors  = true;
    madeam\Exception::$debugMode     = true;
  break;  
  case 'production' :
    madeam\Exception::$inlineErrors  = false;
    madeam\Exception::$debugMode     = false;
  break;
}

// add middleware
madeam\Framework::$middleware = array(
  // 'madeam\middleware\Common',
  // 'madeam\middleware\Sessions',
  // 'madeam\middleware\Tidy',
);

// set default timezone
date_default_timezone_set('America/Toronto');