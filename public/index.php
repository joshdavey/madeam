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

// set the public directory as our current working directory
  chdir(dirname(__FILE__));
  
// if Madeam is in our local lib, include it. Otherwise use the one in the PHP include path
  $lib = realpath('..') . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR;
  require file_exists($lib . DIRECTORY_SEPARATOR . 'Madeam') ? $lib . DIRECTORY_SEPARATOR . 'Madeam.php' : 'Madeam.php';
  
// determine if mod_rewrite is enabled or not
  $rewrite = (isset($_REQUEST['_uri'])) ? true : false;
  
// setup Madeam
  Madeam::setup(getcwd() . DIRECTORY_SEPARATOR, $_REQUEST, $rewrite, $_SERVER['DOCUMENT_ROOT'], $_SERVER['REQUEST_URI'], $_SERVER['QUERY_STRING']);
  
// dispatch handles the request and returns the output  
  echo Madeam::dispatch();

// interesting stuff
  // foreach (get_included_files() as $file) { isset($x) ? ++$x : $x = 1; echo $x . ' ' . $file . '<br />'; }