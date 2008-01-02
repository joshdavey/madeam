<?php
// Welcome to your applications bootstrap

/**
 * If you're using any third party libraries that require their own autoload function now
 * is your chance to include them.
 */

// spl_autoload_register('PEAR2_Autoload');

// set error handling
error_reporting(E_ALL);

// uncomment to enable unicode
mb_internal_encoding("UTF-8");

// uncomment to set content type to utf8
header ('Content-type: text/html; charset=utf-8');
?>