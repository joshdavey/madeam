<?php
/**
 * Madeam PHP Framework <http://madeam.com>
 * Copyright (c)  2009, Joshua Davey
 *                202-212 Adeliade St. W, Toronto, Ontario, Canada
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright   Copyright (c) 2009, Joshua Davey
 * @link        http://www.madeam.com
 * @package     madeam
 * @version     2.0.0
 * @license     http://www.opensource.org/licenses/mit-license.php The MIT License
 * @author      Joshua Davey
 */

// set current working directory
  $cwd = dirname(dirname($_SERVER['SCRIPT_FILENAME'])); // this is prefered over getcwd() when using symlinks

// set the public directory as our current working directory
  chdir($cwd);

// if Madeam is in our local lib, include it. Otherwise use the one in the PHP include path
// note: the library in the PHP include path should only be used for Madeam development and never
// the development of a project based on madeam. Madeam should always be in the vendor directory
  require 'application/vendor/madeam/src/Madeam.php';

// include config files
  require './application/config/setup.php';
  require './application/config/routes.php';
  
// setup Madeam
  madeam\Framework::setup(
    dirname($_SERVER['SCRIPT_FILENAME']) . '/', // example: /Users/batman/Sites/myblog/
    $_SERVER['DOCUMENT_ROOT']                   // example: /Users/batman/Sites/
  );
  
// dispatch handles the request and returns the output  
  echo madeam\Framework::dispatch(
    $_GET + $_POST + $_COOKIE,  // not always the same as $_REQUEST depending on the php.ini configuration
    $_SERVER['QUERY_STRING'],   // example: _uri=posts/view/32&blah=testing
    $_SERVER['REQUEST_METHOD']  // example: GET
  );