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
if (!defined('DS')) { define('DS', DIRECTORY_SEPARATOR); }

// important paths
if (!defined('PATH_TO_PUBLIC'))  { define('PATH_TO_PUBLIC', dirname(dirname(__FILE__)) . DS . 'public' . DS); }
if (!defined('PATH_TO_PROJECT')) { define('PATH_TO_PROJECT', dirname(PATH_TO_PUBLIC) . DS); }

// add ending / to document root if it doesn't exist -- important because it differs from unix to windows (or I think that's what it is)
if (substr($_SERVER['DOCUMENT_ROOT'], -1) != '/') { $_SERVER['DOCUMENT_ROOT'] .= '/'; }

// set key paths
define('PATH_TO_APP',         PATH_TO_PROJECT . 'app' . DS);
define('PATH_TO_ANTHOLOGY',   PATH_TO_APP . 'Anthology' . DS);

// include base setup configuration
require PATH_TO_APP . 'Config' . DS . 'setup.php';

// set configuration
$config = array_merge($env[$cfg['environment']], $cfg);
unset($cfg, $env);

// turn configs into constants for speed?
define('MADAEM_ENVIRONMENT',        $config['environment']);
define('MADEAM_ENABLE_DEBUG',       $config['enable_debug']);
define('MADEAM_ENABLE_CACHE',       $config['enable_cache']);
define('MADEAM_ENABLE_LOGGER',      $config['enable_logger']);
define('MADAEM_ENABLE_AJAX_LAYOUT', $config['enable_ajax_layout']);

// set PATH_TO_URI based on whether mod_rewrite is turned on or off.
// If it's off then we need to add the SCRIPT_FILENAME at the end.
if (isset($_GET['uri'])) {
  $public_dir = basename(PATH_TO_PUBLIC);
	define('PATH_TO_URI', '/' . substr(str_replace(DS, '/', substr(PATH_TO_SCRIPT, strlen($_SERVER['DOCUMENT_ROOT']), -strlen($public_dir))), 0, -1));
} else {
	define('PATH_TO_URI', '/' . str_replace(DS, '/', substr(PATH_TO_SCRIPT, strlen($_SERVER['DOCUMENT_ROOT']))) . SCRIPT_FILENAME . '/');
}

// determine the relative path to the public directory
define('PATH_TO_REL', '/' . str_replace(DS, '/', substr(PATH_TO_PUBLIC, strlen($_SERVER['DOCUMENT_ROOT']))));


// major madeam directories
if (!defined('PATH_TO_SYSTEM')) { define('PATH_TO_SYSTEM', PATH_TO_PROJECT . 'system' . DS); }

// application files
define('PATH_TO_VIEW',        PATH_TO_APP . 'View' . DS);
define('PATH_TO_CONTROLLER',  PATH_TO_APP . 'Controller' . DS);
define('PATH_TO_MODEL',       PATH_TO_APP . 'Model' . DS);

define('PATH_TO_LAYOUT',      PATH_TO_VIEW);

define('PATH_TO_LOG', 		    PATH_TO_PROJECT . 'etc' . DS . 'log' . DS);
define('PATH_TO_TMP', 		    PATH_TO_PROJECT . 'etc' . DS . 'tmp' . DS);

// set include paths
$include_paths = array(PATH_TO_SYSTEM, PATH_TO_APP, PATH_TO_ANTHOLOGY, ini_get('include_path'));
ini_set('include_path', implode(PATH_SEPARATOR, $include_paths));

// define user errors variable name for $_SESSION
// example: $_SESSION[USER_ERROR_NAME];
define('USER_ERROR_NAME', 'muerrors');

// this is used for passing misc data from one page to the other
define('FLASH_DATA_NAME', 'mflash');

// this sets how many pages the flash has to live (ptl: pages to live)
define('FLASH_LIFE_NAME', 'mflife');

// this is used for passing post data from one page to the next
// the post data is merged with the flash post data on the next page
define('FLASH_POST_NAME', 'mfpost');

// Model joiner
// example use: "user.name"
define('MODEL_JOINT', '.');

// autoload function
function Madeam_Autoload($class) {

  // set class file name
  $file = str_replace('_', DS, $class) . '.php';

  // include class file
  if (file_lives($file)) { require $file; }

  if (!class_exists($class, false) && !interface_exists($class, false)) {
    $class = preg_replace("/[^A-Za-z0-9_]/", null, $class); // clean the dirt
    eval("class $class {}");
	  throw new Madeam_Exception('Missing Class ' . $class, Madeam_Exception::ERR_CLASS_MISSING);
  }
}

// include application bootstrap
require PATH_TO_APP . 'Config' . DS . 'bootstrap.php';

// idea... use this as a last resort when all autoloads fail.
// have this one throw an exception or make a last resort to check every path for the file.
spl_autoload_register('Madeam_Autoload');


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


// save config to registry
// ========================================================

// save configuration to registry
Madeam_Registry::set('config', $config);
unset($config);


// include routes
// ========================================================

// check cache for routes
if (!Madeam_Router::$routes = Madeam_Cache::read('madeam.routes', -1)) {
  // include routes configuration
  require PATH_TO_APP . 'Config' . DS . 'routes.php';

  // save routes to cache
  Madeam_Cache::save('madeam.routes', Madeam_Router::$routes);
}


/**
 * Enter description here...
 *
 * @param unknown_type $e
 */
function Madeam_uncaughtException($e) {
  echo 'Uncaught Exception: ' . $e->getMessage() . ' on line ' . $e->getLine() . ' in ' . $e->getFile();

  return true;
}

/**
 * Set exception handler
 */
set_exception_handler('Madeam_uncaughtException');

/**
 * Enter description here...
 *
 * @param unknown_type $code
 * @param unknown_type $string
 * @param unknown_type $file
 * @param unknown_type $line
 */
function Madeam_errorHandler($code, $string, $file, $line) {
  $exception = new Madeam_Exception($string, $code);
  $exception->setLine($line);
  $exception->setFile($file);
  throw $exception;

  return true;
}

/**
 * Set error handler
 */
set_error_handler('Madeam_errorHandler');


// function library
// ========================================================

/**
 * This function exists as a quick tool for developers to test
 * if a funciton executes and how many times.
 */
function test($var = null) {
	static $tests;
	$tests++;

	for($i = 0; $i < (6 - strlen($tests)); $i++) { $tests = '0' . $tests; }

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
  $entries_checked  = 0;
  $keys_string      = null;

  // lists must be represented as arrays
  if (is_array($data)) {
    foreach($data as $entry) {
      // each entry must be an array
      if (is_array($entry)) {
        $entries_checked++; // record that an entry has been checked

        // get keys
        $keys = array_keys($entry);

        // sort the keys so that they are always in alphabetical order and therefore always comparable
        asort($keys);

        // compare keys string -- do not need to compare the first one.
        if ($entries_checked != 1) {
          if ($keys_string == implode($keys)) {
            $matching_entries++;
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

  $group_name = implode($values);

  if (!$groups[$group_name]) {
    array_unshift($values, 'offset');
    $groups[$group_name] = $values;
  }

  $returned = next($groups[$group_name]);

  if ($returned == $groups[$group_name][count($groups[$group_name])-1]) {
    reset($groups[$group_name]);
  }

  return $returned;
}
?>