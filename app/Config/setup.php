<?php

error_reporting(E_ALL);

$cfg = array();

switch (Madeam::$environment) {
  case 'development' :
    $cfg['connections'][]             = 'mysql://username:password@localhost?name=madeam_development';
    $cfg['ignore_controllers_cache']  = true;
    $cfg['ignore_models_cache']       = true;
    $cfg['ignore_routes_cache']       = true;
    $cfg['ignore_inline_cache']       = true;
    $cfg['enable_debug']              = true;
    $cfg['enable_logger']             = true;
    $cfg['inline_errors']             = false;
    break;
  case 'testing' :
    $cfg['connections'][]             = 'mysql://username:password@localhost?name=madeam_testing';
    $cfg['ignore_controllers_cache']  = true;
    $cfg['ignore_models_cache']       = true;
    $cfg['ignore_routes_cache']       = true;
    $cfg['ignore_inline_cache']       = true;
    $cfg['enable_debug']              = false;
    $cfg['enable_logger']             = true;
    $cfg['inline_errors']             = false;
    break;
  case 'production' :
    
    error_reporting(0);
  
    $cfg['connections'][]             = 'mysql://username:password@localhost?name=madeam_production';
    $cfg['ignore_controllers_cache']  = false;
    $cfg['ignore_models_cache']       = false;
    $cfg['ignore_routes_cache']       = false;
    $cfg['ignore_inline_cache']       = false;
    $cfg['enable_debug']              = false;
    $cfg['enable_logger']             = true;
    $cfg['inline_errors']             = false;
    break;
}


/**
 * This is the name of the log files. You can use the date formats from http://ca3.php.net/date
 * to custome the names and the accuracy of the logs. For example by default it's set to 'Y-m'
 * which is the year and month but if you want to log it every day you could do 'Y-m-d'.
 * Obviously if you want to be really crazy you can even identify the logs by seconds.
 */
$cfg['log_file_name']           = 'Y-m-d';

