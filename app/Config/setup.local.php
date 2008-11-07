<?php
// set environment
$cfg['environment'] = 'testing';


// development
  $env['development']['data_servers'][]     = 'mysql://root:@localhost?name=madeam_development';
  $env['development']['cache_controllers']  = false;
  $env['development']['cache_models']       = false;
  $env['development']['cache_routes']       = false;
  $env['development']['cache_inline']       = false;
  $env['development']['clear_cache']        = true;
  $env['development']['enable_debug']       = true;
  $env['development']['enable_logger']      = false;
  $env['development']['inline_errors']      = true;

// testing
  $env['testing']['data_servers'][]      = 'mysql://root:@localhost?name=madeam_testing';
  $env['testing']['cache_controllers']   = false;
  $env['testing']['cache_models']        = false;
  $env['testing']['cache_routes']        = false;
  $env['testing']['cache_inline']        = false;
  $env['testing']['clear_cache']         = true;
  $env['testing']['enable_debug']        = true;
  $env['testing']['enable_logger']       = false;
  $env['testing']['inline_errors']       = true;


/**
 * The name of the public directory.
 */
$cfg['public_directory_name']   = 'public';

/**
 * This is the default controller called by the framework. This does NOT support
 * the format "sub/index". Its recommended that you leave this alone unless you really
 * need to change it. (highly unlikely).
 */
$cfg['default_controller']      = 'index';

/**
 * This is the controller's default action that will be called if no other
 * action is requested. This should be left untouched unless unlikely circumstances
 * require that it be something else.
 */
$cfg['default_action']          = 'index';

/**
 * This is the default file extension for Views and Layouts.
 * The "." is not necessary. Only the name.
 */
$cfg['default_format']          = 'html';

/**
 * This is the name of the log files. You can use the date formats from http://ca3.php.net/date
 * to custome the names and the accuracy of the logs. For example by default it's set to 'Y-m'
 * which is the year and month but if you want to log it every day you could do 'Y-m-d'.
 * Obviously if you want to be really crazy you can even identify the logs by seconds.
 */
$cfg['log_file_name']           = 'Y-m-d';


/**
 * By default madeam always returns a layout when called for the first time. When an ajax call is
 * made its the same as calling madeam for the first time so the layout is called which could break
 * things for you. To avoid including a layout with the content being called set the ajax_layout to "false".
 * By default this is already done for you. To include layouts when making ajax calls set it to "true".
 */
$cfg['enable_ajax_layout']      = false;


/**
 * When madeam encounters a system error it calls an error controller to display the error. Here you can
 * select which controller handles the errors by name.
 */
$cfg['error_controller']        = 'error';

