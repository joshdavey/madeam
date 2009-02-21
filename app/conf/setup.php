<?php

/**
 * 
 */

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
    $cfg['inline_errors']             = true;
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
}

/**
 * Set session path
 */
session_save_path(Madeam::$pathToEtc . 'sessions' . DS);


/**
 * Register logger as shutdown function
 */
//register_shutdown_function('Madeam_Logger::write');

/**
 * Configure Cache
 */
Madeam_Cache::$path   = Madeam::$pathToEtc . 'cache' . DS;


/*
// config idea...
Madeam_Config::set('Madeam_Model', array(
  'connections' => 'mysql://username:password@localhost?name=madeam_development'
));
*/