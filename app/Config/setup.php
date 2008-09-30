<?php

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
 * [cache_controllers]
 *   true: controller setup data is cached
 *   false controller setup data isn't cached
 *
 * [cache_models]
 *   true: model setup data is cached
 *   false:  model setup data isn't cached
 *
 * [cache_routes]
 *   true: routes are cached
 *   false: routes aren't cached
 *
 * [cache_inline]
 * This is normally used for cacheing parts of a view.
 *   true: inline text is cached
 *   false: inline text isn't cached
 *
 * [clear_cache]
 * This is a feature for developers that clears the cache whenever changes are made to any of the
 * application files.
 *   true: clear cache when application files are changed
 *   false: do not clear cache
 *
 * [enable_debug]
 * When debugging is enabled debug messages will be displayed. For a development setup this is
 * perfect but you'll want to turn this off for production.
 *  true:   debug messages are displayed
 *  false:  no debug messages are shown. Recommended for production
 *
 * [enable_logger]
 * Sometimes you may want to disable this to improve speed.
 *  true:   logging is turned on
 *  false:  logging is turned off
 * 
 * [enable_toolbar]
 * The Madeam debug toolbar allows you to see useful information about the page
 *  true:  toolbar is displayed
 *  false: tool bar is hidden
 *
 * [inline_errors]
 *   true: all errors are displayed inline
 *   false: errors will be displayed by Madeam's error handling controller
 */
$cfg['environment'] = 'development';


// development
  $env['development']['data_servers'][]     = 'mysql://username:password@localhost?name=madeam';
  $env['development']['cache_controllers']  = false;
  $env['development']['cache_models']       = false;
  $env['development']['cache_routes']       = false;
  $env['development']['cache_inline']       = false;
  $env['development']['clear_cache']        = true;
  $env['development']['enable_debug']       = true;
  $env['development']['enable_logger']      = true;
  $env['development']['enable_toolbar']     = true;
  $env['development']['inline_errors']      = false;

// production
  $env['production']['data_servers'][]      = 'mysql://username:password@localhost?name=madeam';
  $env['production']['cache_controllers']   = true;
  $env['production']['cache_models']        = true;
  $env['production']['cache_routes']        = true;
  $env['production']['cache_inline']        = true;
  $env['production']['clear_cache']         = false;
  $env['production']['enable_debug']        = false;
  $env['production']['enable_logger']       = true;
  $env['production']['enable_toolbar']      = false;
  $env['production']['inline_errors']       = false;


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



