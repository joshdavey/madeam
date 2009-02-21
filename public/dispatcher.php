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
 * @version      0.1.0
 * @license      http://www.opensource.org/licenses/mit-license.php The MIT License
 * @author      Joshua Davey
 */


// use unicode
  if (function_exists('mb_internal_encoding')) { mb_internal_encoding("UTF-8"); }

// content type to utf8
  header('Content-type: text/html; charset=utf-8');

// set default timezone
  date_default_timezone_set('America/Toronto');

// set the public directory as our current working directory
  chdir(dirname(__FILE__));

  /*
  $path = getcwd() . '/posts/show/32';
  //mkdir(getcwd() . '/posts/show/32/', 0777, true);
  file_put_contents($path . '.html', '<?php unlink($_SERVER["SCRIPT_FILENAME"]); ?> tests ' . time());
  file_put_contents($path . '/index.html', '<?php unlink($_SERVER["SCRIPT_FILENAME"]); ?> tests ' . time());
  //*/
  
// if Madeam is in our local lib, include it. Otherwise use the one in the PHP include path
  $lib = realpath('..') . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR;
  require file_exists($lib . DIRECTORY_SEPARATOR . 'Madeam') ? $lib . DIRECTORY_SEPARATOR . 'Madeam' . DIRECTORY_SEPARATOR . 'src/Madeam.php' : 'Madeam' . DIRECTORY_SEPARATOR . 'Framework.php';
  
// set include paths  
  set_include_path(implode(PATH_SEPARATOR, Madeam::paths(getcwd() . DIRECTORY_SEPARATOR)) . PATH_SEPARATOR . get_include_path());
  
// setup Madeam
  Madeam::webSetup(
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
  
  //foreach (get_included_files() as $file) { isset($x) ? ++$x : $x = 1; echo $x . ' ' . $file . '<br />'; }