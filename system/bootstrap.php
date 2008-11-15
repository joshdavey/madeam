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
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
// directory splitter
if (! defined('DS')) {
  define('DS', DIRECTORY_SEPARATOR);
}

// define project directory
if (! defined('PATH_TO_PROJECT')) {
  define('PATH_TO_PROJECT', dirname(dirname(__FILE__)) . DS);
}

// add ending / to document root if it doesn't exist -- important because it differs from unix to windows (or I think that's what it is)
if (substr($_SERVER['DOCUMENT_ROOT'], - 1) != '/') {
  $_SERVER['DOCUMENT_ROOT'] .= '/';
}

// set key paths
define('PATH_TO_APP', PATH_TO_PROJECT . 'app' . DS);
define('PATH_TO_LIB', PATH_TO_PROJECT . 'lib' . DS);
define('PATH_TO_SYSTEM', PATH_TO_PROJECT . 'system' . DS);

// include base setup configuration
if (file_exists(PATH_TO_APP . 'Config' . DS . 'setup.local.php')) {
  require PATH_TO_APP . 'Config' . DS . 'setup.local.php';
} else {
  require PATH_TO_APP . 'Config' . DS . 'setup.php';
}

// define path to public directory
define('PATH_TO_PUBLIC', dirname(dirname(__FILE__)) . DS . $cfg['public_directory_name'] . DS);

// this is the name of the script that executes in the public directory
define('SCRIPT_FILENAME', 'index.php');

// turn configs into constants for speed?
define('MADEAM_ENVIRONMENT', $cfg['environment']);

// set PATH_TO_URI based on whether mod_rewrite is turned on or off.
// mod_rewrite is on when $_GET['_uri'] exists. You can see it defined in the public .htaccess file
if (isset($_GET['_uri'])) {
  $publicDir = basename(PATH_TO_PUBLIC);
  define('PATH_TO_URI', '/' . substr(str_replace(DS, '/', substr(PATH_TO_PUBLIC, strlen($_SERVER['DOCUMENT_ROOT']), - strlen($publicDir))), 0, - 1));
  define('MADEAM_REWRITE_URI', '/' . $_GET['_uri']);
} else {
  define('PATH_TO_URI', '/' . str_replace(DS, '/', substr(PATH_TO_PUBLIC, strlen($_SERVER['DOCUMENT_ROOT']))) . SCRIPT_FILENAME . '/');
  define('MADEAM_REWRITE_URI', false);
}

// determine the relative path to the public directory
define('PATH_TO_REL', '/' . str_replace(DS, '/', substr(PATH_TO_PUBLIC, strlen($_SERVER['DOCUMENT_ROOT']))));

// _uri is defined in the public .htaccess file. Many developers may not notice it because of
// it's transparency during development. We unset it here incase developers are using the $_GET query string
// for any reason. An example of where it might be an unexpected problem is when taking the hash of the query
// string to identify the page. This problem was first noticed in some OpenID libraries
unset($_GET['_uri']);

// remove it from the query string as well
if (isset($_SERVER['QUERY_STRING'])) {
  $_SERVER['QUERY_STRING'] = preg_replace('/&?_uri=[^&]*&?/', null, $_SERVER['QUERY_STRING']);
}

// application files
define('PATH_TO_ETC', PATH_TO_PROJECT . 'etc' . DS);

// set include paths
$includePaths = array(PATH_TO_APP, PATH_TO_LIB, PATH_TO_SYSTEM, PATH_TO_SYSTEM . DS . 'Madeam' . DS . 'Helper' . DS, ini_get('include_path'));
ini_set('include_path', implode(PATH_SEPARATOR, $includePaths));


// include core files
require PATH_TO_SYSTEM . 'Madeam.php';
require PATH_TO_SYSTEM . 'Madeam/Controller.php';
require PATH_TO_SYSTEM . 'Madeam/Inflector.php';
require PATH_TO_SYSTEM . 'Madeam/Router.php';
require PATH_TO_SYSTEM . 'Madeam/Config.php';
require PATH_TO_SYSTEM . 'Madeam/Cache.php';
require PATH_TO_SYSTEM . 'Madeam/Registry.php';
require PATH_TO_SYSTEM . 'Madeam/Logger.php';


// configure core classes
Madeam_Cache::$path   = PATH_TO_ETC . 'cache' . DS;
Madeam_Logger::$path  = PATH_TO_ETC . 'log' . DS;


// save configuration
Madeam_Config::set($cfg);
unset($cfg);


// idea... use this as a last resort when all autoloads fail.
// have this one throw an exception or make a last resort to check every path for the file.
spl_autoload_register('Madeam::autoload');


/**
 * Set exception handler
 */
set_exception_handler('Madeam_UncaughtException');

/**
 * Set error handler
 */
set_error_handler('Madeam_ErrorHandler');


// function library
// ========================================================
/**
 * This function exists as a quick tool for developers to test
 * if a function executes and how many times.
 */
function test($var = null) {
  static $tests;
  $tests ++;
  for ($i = 0; $i < (6 - strlen($tests)); $i ++) {
    $tests = '0' . $tests;
  }
  
  if (is_array($var) || is_object($var)) {
    echo '<br /><pre>[TEST::' . $tests . '] &nbsp;&nbsp;' . "\n";
    print_r($var);
    echo ' &nbsp;&nbsp;</pre>' . "\n";
  } elseif (is_bool($var)) {
    if ($var === true) {
      $var = 'TRUE';
    } else {
      $var = 'FALSE';
    }
    
    echo "<br /> [TEST::" . $tests . "] &nbsp;&nbsp;" . (string) $var . "&nbsp;&nbsp;  \n";
  } else {
    echo "<br /> [TEST::" . $tests . "] &nbsp;&nbsp;" . $var . "&nbsp;&nbsp;  \n";
  }
}

/**
 * Re-named strtoupper
 *
 * @param string $word
 * @return string
 */
function up($word) {
  return strtoupper($word);
}

/**
 * Re-named strtolower
 *
 * @param string $word
 * @return string
 */
function low($word) {
  return strtolower($word);
}

/**
 * Checks to see if a relative file exists by checking each include path.
 * Special thanks to Ahmad Nassri from PHP-Infinity for the proof of concept.
 *
 * @param string $file
 * @return boolean
 */
function fileLives($file) {
  $paths = explode(PATH_SEPARATOR, get_include_path());
  
  foreach ($paths as $path) {
    if (is_file($path . $file)) {
      return true;
    }
  }
  
  return false;
}

/**
 * Enter description here...
 *
 * @param unknown_type $e
 */
function Madeam_UncaughtException($e) {
  Madeam_Exception::catchException($e, array('message' => "Uncaught Exception: \n" . $e->getMessage()));
  return true;
}

/**
 * Enter description here...
 *
 * @param unknown_type $code
 * @param unknown_type $string
 * @param unknown_type $file
 * @param unknown_type $line
 */
function Madeam_ErrorHandler($code, $string, $file, $line) {
  // return regular PHP errors when they're non-fatal
  if ($code == 2 || $code == 4 || $code == 8) { return false; }

  $exception = new Madeam_Exception($string, $code);
  throw $exception;
  return true;
}
