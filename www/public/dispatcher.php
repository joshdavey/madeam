<?php
/**
 * Madeam PHP Framework <http://madeam.com>
 * Copyright (c)  2009, Joshua Davey
 *                202-212 Adeliade St. W, Toronto, Ontario, Canada
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright    Copyright (c) 2009, Joshua Davey
 * @link        http://www.madeam.com
 * @package      madeam
 * @version      2.0.0
 * @license      http://www.opensource.org/licenses/mit-license.php The MIT License
 * @author      Joshua Davey
 */

// use unicode
  mb_internal_encoding("UTF-8");

// content type to utf8
  header('Content-type: text/html; charset=utf-8');

// set default timezone
  date_default_timezone_set('America/Toronto');

// set current working directory
  $cwd = dirname(dirname($_SERVER['SCRIPT_FILENAME'])); // this is prefered over getcwd() when using symlinks

// set the public directory as our current working directory
  chdir($cwd);

// if Madeam is in our local lib, include it. Otherwise use the one in the PHP include path
// note: the library in the PHP include path should only be used for Madeam development and never
// the development of a project based on madeam. Madeam should always be in the vendor directory
  require 'app/vendor/Madeam/src/Madeam.php';
  
// set environment
  $environemnt = apache_getenv('MADEAM_ENV');
  if ($environemnt === false) {
    define('MADEAM_ENV', require 'env.php');
  } else {
    define('MADEAM_ENV', $environemnt);
  }
  
  require 'app/conf/setup.php';
  require 'app/conf/routes.php';
  
// setup Madeam
  madeam\Framework::setup(
    $_REQUEST, 
    $_SERVER['DOCUMENT_ROOT'],    // example: /Users/batman/Sites
    $_SERVER['REQUEST_URI'],      // example: /myblog/ (sub-directory of document root)
    $_SERVER['QUERY_STRING'],     // example: _uri=&blah=testing
    $_SERVER['REQUEST_METHOD']    // example: GET
  );
  
  madeam\debug($_SERVER);
  
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
  echo madeam\Framework::dispatch();