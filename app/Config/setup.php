<?php
/**
 * Change this to select the environment settings you'd like to run with.
 */
$cfg['environment'] = 'development';

  
/**
 * Setup Environments
 * 
 * The following variables are specific to the environment setup. For example you wouldn't 
 * want debug messages to appear in your production environment. Consider this when adding
 * your own configuration variables
 * 
 * [data_servers]
 * You can add as many data servers as you'd like to distribute load.
 *  driver://username:password@host?name=application&port=0000
 * 
 * [enable_cache]
 * If the cache is disabled then nothing will be cached. This is useful for debugging purposes.
 *  true:   cache is enabled
 *  false:  cache is disabled
 * 
 * [enable_debug]
 * When debugging is enabled debug messages will be displayed. For a development setup this is
 * perfect but you'll want to turn this off for production.
 *  true:   debug messages are displayed
 *  false:  no debug messages are shown. Recommended for production
 */

// development
  $env['development']['data_servers'][]  = 'mysql://root:@localhost?name=madeam';
  $env['development']['enable_cache']    = true;
  $env['development']['enable_debug']    = true;

// production
  $env['production']['data_servers'][]   = 'mysql://root:password@localhost?name=madeam';
  $env['production']['enable_cache']     = true;
  $env['production']['enable_debug']     = false;

  
/**
 * This is the default controller called by the framework. This does NOT support
 * the format "sub/index". Its recommended that you leave this alone unless you really
 * need to change it. (highly unlikely).
 */
$cfg['default_controller'] = 'index';

/**
 * This is the controller's default action that will be called if no other
 * action is requested. This should be left untouched unless unlikely circumstances
 * require that it be something else.
 */
$cfg['default_action']     = 'index';

/**
 * This is the default file extension for Views and Layouts.
 * The "." is not necessary. Only the name.
 */
$cfg['default_format']     = 'html';

/**
 * This is the name of the log files. You can use the date formats from http://ca3.php.net/date 
 * to custome the names and the accuracy of the logs. For example by default it's set to 'Y-m' 
 * which is the year and month but if you want to log it every day you could do 'Y-m-d'. 
 * Obviously if you want to be really crazy you can even identify the logs by seconds.
 */
$cfg['log_file_name']      = 'Y-m';


/**
 * By default madeam always returns a layout when called for the first time. When an ajax call is
 * made its the same as calling madeam for the first time so the layout is called which could break
 * things for you. To avoid including a layout with the content being called set the ajax_layout to "false".
 * By default this is already done for you. To include layouts when making ajax calls set it to "true".
 */
$cfg['ajax_layout']        = false;
?>