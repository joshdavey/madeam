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
define('PATH_TO_LIB', PATH_TO_PROJECT . 'library' . DS);
define('PATH_TO_SYSTEM', PATH_TO_PROJECT . 'system' . DS);

// include base setup configuration
require PATH_TO_APP . 'Config' . DS . 'setup.php';

// set configuration
$config = array_merge($env[$cfg['environment']], $cfg);
unset($cfg, $env);

// define path to public directory
if (! defined('PATH_TO_PUBLIC')) {
  define('PATH_TO_PUBLIC', dirname(dirname(__FILE__)) . DS . $config['public_directory_name'] . DS);
}

// this is the name of the script that executes in the public directory
define('SCRIPT_FILENAME', 'index.php');

// turn configs into constants for speed?
define('MADEAM_ENVIRONMENT',    $config['environment']);

// set PATH_TO_URI based on whether mod_rewrite is turned on or off.
// mod_rewrite is on when $_GET['madeamURI'] exists. You can see it defined in the public .htaccess file
if (isset($_GET['madeamURI'])) {
  $publicDir = basename(PATH_TO_PUBLIC);
  define('PATH_TO_URI', '/' . substr(str_replace(DS, '/', substr(PATH_TO_PUBLIC, strlen($_SERVER['DOCUMENT_ROOT']), - strlen($publicDir))), 0, - 1));
  define('MADEAM_REWRITE_URI', '/' . $_GET['madeamURI']);
} else {
  define('PATH_TO_URI', '/' . str_replace(DS, '/', substr(PATH_TO_PUBLIC, strlen($_SERVER['DOCUMENT_ROOT']))) . SCRIPT_FILENAME . '/');
  define('MADEAM_REWRITE_URI', false);
}

// determine the relative path to the public directory
define('PATH_TO_REL', '/' . str_replace(DS, '/', substr(PATH_TO_PUBLIC, strlen($_SERVER['DOCUMENT_ROOT']))));

// madeamURI is defined in the public .htaccess file. Many developers may not notice it because of
// it's transparency during development. We unset it here incase developers are using the $_GET query string
// for any reason. An example of where it might be an unexpected problem is when taking the has of the query
// string to identify the page. This problem was first noticed in some OpenID libraries
unset($_GET['madeamURI']);

// remove it fromt he query string as well
if (isset($_SERVER['QUERY_STRING'])) {
  $_SERVER['QUERY_STRING'] = preg_replace('/&?madeamURI=[^&]*&?/', null, $_SERVER['QUERY_STRING']);
}

// application files
define('PATH_TO_VIEW', PATH_TO_APP . 'View' . DS);
define('PATH_TO_CONTROLLER', PATH_TO_APP . 'Controller' . DS);
define('PATH_TO_MODEL', PATH_TO_APP . 'Model' . DS);
define('PATH_TO_LAYOUT', PATH_TO_VIEW);
define('PATH_TO_LOG', PATH_TO_PROJECT . 'etc' . DS . 'log' . DS);
define('PATH_TO_TMP', PATH_TO_PROJECT . 'etc' . DS . 'tmp' . DS);

// set include paths
$includePaths = array(PATH_TO_SYSTEM, PATH_TO_APP, PATH_TO_LIB, ini_get('include_path'));
ini_set('include_path', implode(PATH_SEPARATOR, $includePaths));


// include core files
require PATH_TO_SYSTEM . 'Madeam.php';
require PATH_TO_SYSTEM . 'Madeam/Controller.php';
require PATH_TO_SYSTEM . 'Madeam/Inflector.php';
require PATH_TO_SYSTEM . 'Madeam/Router.php';
require PATH_TO_SYSTEM . 'Madeam/Config.php';
require PATH_TO_SYSTEM . 'Madeam/Parser.php';
require PATH_TO_SYSTEM . 'Madeam/Cache.php';
require PATH_TO_SYSTEM . 'Madeam/Registry.php';

// define user errors variable name for $_SESSION
// example: $_SESSION[MADEAM_USER_ERROR_NAME];
define('MADEAM_USER_ERROR_NAME', 'muerrors');

// this is used for passing misc data from one page to the other
define('MADEAM_FLASH_DATA_NAME', 'mflash');

// this sets how many pages the flash has to live (ptl: pages to live)
define('MADEAM_FLASH_LIFE_NAME', 'mflife');

// this is used for passing post data from one page to the next
// the post data is merged with the flash post data on the next page
define('MADEAM_FLASH_POST_NAME', 'mfpost');

// Used for joining models and other associations
// example use: "user.name"
define('MADEAM_ASSOCIATION_JOINT', '.');


// autoload function
function Madeam_Autoload($class) {
  // set class file name
  $file = str_replace('_', DS, $class) . '.php';
  // include class file
  if (file_lives($file)) {
    require $file;
  }

  if (! class_exists($class, false) && ! interface_exists($class, false)) {
    $class = preg_replace("/[^A-Za-z0-9_]/", null, $class); // clean the dirt
    eval("class $class {}");
    throw new Madeam_Exception_AutoloadFail('Missing Class ' . $class);
  }
}


// idea... use this as a last resort when all autoloads fail.
// have this one throw an exception or make a last resort to check every path for the file.
spl_autoload_register('Madeam_Autoload');

// include routes
// check cache for routes
if (! Madeam_Router::$routes = Madeam_Cache::read('madeam.routes', - 1)) {
  // include routes configuration
  require PATH_TO_APP . 'Config' . DS . 'routes.php';

  // save routes to cache
  if ($config['cache_routes']) {
    Madeam_Cache::save('madeam.routes', Madeam_Router::$routes);
  }
}

/**
 * Checks to see if a relative file exists by checking each include path.
 * Special thanks to Ahmad Nassri from PHP-Infinity for the proof of concept.
 *
 * @param string $file
 * @return boolean
 */
function file_lives($file) {
  $paths = explode(PATH_SEPARATOR, get_include_path());
  foreach ($paths as $path) {
    if (is_file($path . $file)) {
      return true;
    }
  }
  return false;
}

// save configuration
Madeam_Config::set($config);
unset($config);

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
 * Set exception handler
 */
set_exception_handler('Madeam_UncaughtException');

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

/**
 * Set error handler
 */
set_error_handler('Madeam_ErrorHandler');

// function library
// ========================================================
/**
 * This function exists as a quick tool for developers to test
 * if a funciton executes and how many times.
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
  } else {
    echo "<br /> [TEST::" . $tests . "] &nbsp;&nbsp;" . $var . "&nbsp;&nbsp;  \n";
  }
}

/**
 * A list is an array where there are multiple entries and the structure of the entries are identical.
 * We measure whether the entries are identical by taking the name of each of the entry's keys and turning them into a string.
 * If the strings match for every entry then it is a list
 *
 * @param array $data
 * @return boolean
 */
function is_list($data) {
  $matching_entries = 1;
  $entries_checked = 0;
  $keys_string = null;
  // lists must be represented as arrays
  if (is_array($data)) {
    foreach ($data as $entry) {
      // each entry must be an array
      if (is_array($entry)) {
        $entries_checked ++; // record that an entry has been checked
        // get keys
        $keys = array_keys($entry);
        // sort the keys so that they are always in alphabetical order and therefore always comparable
        asort($keys);
        // compare keys string -- do not need to compare the first one.
        if ($entries_checked != 1) {
          if ($keys_string == implode($keys)) {
            $matching_entries ++;
          } else {
            break;
          }
        }
        // set keys_string for entry so that it can be compared to the next one
        $keys_string = implode($keys);
      } else {
        break;
      }
    }
  }
  // the data is in list format if all the entries match
  if ($entries_checked == $matching_entries) {
    return true;
  } else {
    return false;
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
 * Rotates through an array of values. The array is saved to the static $group variable
 * and is identified by the values stored as a string
 *
 * @example
 * rotate('foo', 'bar', 'test');
 * call# / value
 * 1 / foo
 * 2 / bar
 * 3 / test
 *
 * @return string
 */
function rotate() {
  static $groups;
  $values = func_get_args();
  $groupName = implode($values);
  if (! $groups[$groupName]) {
    array_unshift($values, 'offset');
    $groups[$groupName] = $values;
  }
  $returned = next($groups[$groupName]);
  if ($returned == $groups[$groupName][count($groups[$groupName]) - 1]) {
    reset($groups[$groupName]);
  }
  return $returned;
}

/**
 * This function converts arrays to objects.
 *
 * Note: I found it here: http://blog.primalskill.com/?p=13 but not sure who the original author is.
 * It is super awesome and whoever made it has mad skillz.
 *
 * @param array $data
 * @return object
 */
function array2obj($data) {
  return is_array($data) ? (object) array_map(__FUNCTION__, $data) : $data;
}

function modifedFilesExist() {
  $path = PATH_TO_APP;
  foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::KEY_AS_PATHNAME), RecursiveIteratorIterator::CHILD_FIRST) as $file => $info) {
    echo $file . $info->getMTime() . "<br />";
  }
}
//modifedFilesExist();


// include application bootstrap
require PATH_TO_APP . 'Config' . DS . 'bootstrap.php';