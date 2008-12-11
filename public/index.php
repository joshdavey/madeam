<?php
/**
 * Madeam :  Rapid Development MVC Framework <http://www.madeam.com/>
 * Copyright (c)	2006, Joshua Davey
 *								24 Ridley Gardens, Toronto, Ontario, Canada
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright		Copyright (c) 2006, Joshua Davey
 * @link				http://www.madeam.com
 * @package			madeam
 * @version			1.0.0
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 * @author      Joshua Davey
 */


// use unicode
  if (function_exists('mb_internal_encoding')) { mb_internal_encoding("UTF-8"); }

// uncomment to set content type to utf8
  header('Content-type: text/html; charset=utf-8');

// set default timezone
  date_default_timezone_set('America/Toronto');


// set the public directory as our current working directory
  chdir(dirname(__FILE__));
  
// if Madeam is in our local lib, include it. Otherwise use the one in the PHP include path
  $lib = realpath('..') . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR;
  require file_exists($lib . DIRECTORY_SEPARATOR . 'Madeam') ? $lib . DIRECTORY_SEPARATOR . 'Madeam.php' : 'Madeam.php';
  
// determine if mod_rewrite is enabled or not
  $rewrite = (isset($_REQUEST['_uri'])) ? true : false;
  
// set include paths  
  set_include_path(implode(PATH_SEPARATOR, Madeam::paths(getcwd() . DIRECTORY_SEPARATOR)) . PATH_SEPARATOR . get_include_path());
  
// setup Madeam
  Madeam::setup(
    require '../env.php',   // environment
    $_REQUEST,              // params
    $_SERVER                // server
  );
  
  
// remove _uri from request
  // _uri is defined in the public/.htaccess file. Many developers may not notice it because of
  // it's transparency during development. We unset it here incase developers are using the query string
  // for any reason. An example of where it might be an unexpected problem is when taking the hash of the query
  // string to identify the page. This problem was first noticed in some OpenID libraries
  unset($_GET['_uri']);
  unset($_REQUEST['_uri']);

  // remove it from the query string as well
  if (isset($_SERVER['QUERY_STRING'])) {
    $_SERVER['QUERY_STRING'] = preg_replace('/&?_uri=[^&]*&?/', null, $_SERVER['QUERY_STRING']);
  }
  
// dispatch handles the request and returns the output  
  echo Madeam::dispatch();

// interesting stuff
  // foreach (get_included_files() as $file) { isset($x) ? ++$x : $x = 1; echo $x . ' ' . $file . '<br />'; }