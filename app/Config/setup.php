<?php

error_reporting(E_ALL);

$cfg = array();

switch (Madeam_Framework::$environment) {
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
 * Configure Cache
 */
Madeam_Cache::$path   = Madeam_Framework::$pathToEtc . 'cache' . DS;

/**
 * Configure Logger
 */
Madeam_Logger::$path  = Madeam_Framework::$pathToEtc . 'log' . DS;
Madeam_Logger::$fileName = 'Y-m-d';

