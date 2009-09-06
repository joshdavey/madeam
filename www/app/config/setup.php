<?php
namespace madeam;
/**
 * Welcome to Madeam PHP Framework!
 * 
 * This is the configuration file for your application. You can customize the configuration for
 * each of your environments. Below we've already setup some environment configurations for you.
 * 
 * The "development" environment is for building your application on your local machine. In this
 * environment all the developer friendly debugging options are enabled and caches are ignored.
 * 
 * The "production" environment is for a deployed version of your script on a live web server.
 * Errors are turned off and caches are enabled for maximum performance.
 * 
 * = connections =
 * This is a connection string for your database. The first part is the type of database followed
 * by a username, password, host and database name. For example:
 * driver://username:password@host?name=database_name
 * 
 * = enable_debug =
 * To display Madeam's debug messages when an exception occurs set this value to "true". Otherwise
 * set it to false to disable.
 * 
 * = enable_logger = 
 * To enagle the logger set this value to "true". Set to "false" to disable
 * 
 * = cache_controllers =
 * By setting this value to "true" your controllers will be cached. This is recommended for a production
 * environment. To disable set to "false" (recommended for development).
 * 
 * = cache_models =
 * By setting this value to "true" your models will be cached. This is recommended for a production
 * environment. To disable set to "false" (recommended for development).
 * 
 * = cache_routes =
 * By setting this value to "true" your routes will be cached. This is recommended for a production
 * environment. To disable set to "false" (recommended for development).
 * 
 * = cache_inline = 
 * By setting this value to "true" your inline caches in views will be cached. This is 
 * recommended for a production environment. To disable set to "false" (recommended for development).
 * 
 * = inline_errors =
 * To enable inline errors set this to "true". To disable set to "false".
 */

$cfg = array();
switch (MADEAM_ENV) {
  case 'development' :
  
    error_reporting(E_ALL);
    Config::set('connections', array(
      'mysql://username:password@localhost?name=madeam_development'
    ));
    Config::set('inline_errors', true);
    Config::set('debug_mode', true);
    
    break;  
  case 'production' :
    
    error_reporting(0);
    Config::set('connections', array(
      'mysql://username:password@localhost?name=madeam_production'
    ));
    Config::set('inline_errors', false);
    Config::set('debug_mode', false);
    
    break;
}

Framework::$middleware = array(
  'SessionsMiddleware'
);

/**
 * Set session path
 */
//session_save_path(Framework::$pathToEtc . 'sessions' . DS);