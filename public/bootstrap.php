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
 * @version			0.0.6
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 * @author      Joshua Davey
 */

// directory splitter
define('DS', DIRECTORY_SEPARATOR);

// define root document path
define('DOC_PATH', $_SERVER['DOCUMENT_ROOT']);

// instance directory
define('PUB_PATH', dirname(__FILE__) . DS);

define('PUB_DIR', basename(PUB_PATH));

// Application Directory
if (!defined('ROOT_APP_PATH')) { define('ROOT_APP_PATH', dirname(PUB_PATH) . DS); }

// set current directory to application directory
chdir(ROOT_APP_PATH);

// set app path (contains controllers, models, views and vendors)
define('APP_PATH', ROOT_APP_PATH . 'app' . DS);

// Config directory
define('CFG_PATH', ROOT_APP_PATH . 'config' . DS);

// define config variable
$cfg = array();

// include base setup configuration
require CFG_PATH . 'environment.php';

// turn configs into constants for speed?
define('MOD_REWRITE',   $cfg[ENVIRONMENT]['mod_rewrite']);
define('DEBUG_MODE',    $cfg[ENVIRONMENT]['debug_mode']);
define('DISABLE_CACHE', $cfg[ENVIRONMENT]['disable_cache']);

// set base url
define('BASE_URL', $_SERVER['SERVER_NAME']);

// set URI_PATH based on whether MOD_REWRITE is turned on or off. If it's off then we need to add the SCRIPT_FILENAME at the end
if (MOD_REWRITE === true) {
	define('URI_PATH', '/' . substr(str_replace(DS, '/', substr(FOREIGN_PATH . '/', strlen(DOC_PATH), -strlen(PUB_DIR))), 1, -1));
} else {
	define('URI_PATH', str_replace(DS, '/', substr(FOREIGN_PATH . '/', strlen(DOC_PATH))) . SCRIPT_FILENAME . '/');
}

// determine the relative path to the public directory
define('REL_PATH', str_replace(DS, '/', substr(PUB_PATH, strlen(DOC_PATH))));

// major madeam directories
if (!defined('MADEAM_PATH')) { 
  define('MADEAM_PATH',     realpath($cfg[ENVIRONMENT]['madeam_dir']) . DS); 
}
define('MADEAM_LIB_PATH',		MADEAM_PATH . 'lib' . DS);
define('VENDOR_PATH',       APP_PATH . 'vendor' . DS);
define('VENDOR_LIB_PATH',   VENDOR_PATH . 'lib' . DS);

// scaffold path
define('SCAFFOLD_PATH',     'scaffold' . DS);

// application files
define('VIEW_PATH',         APP_PATH . 'view' . DS);
define('CONTROLLER_PATH',		APP_PATH . 'controller' . DS); // necessary for routing in madeam dispatcher
define('LAYOUT_PATH',       VIEW_PATH . '_layouts' . DS);
define('ERROR_PATH',        VIEW_PATH . '_errors' . DS);
define('LOG_PATH', 					ROOT_APP_PATH . 'etc' . DS . 'log' . DS);
define('TMP_PATH', 					ROOT_APP_PATH . 'etc' . DS . 'tmp' . DS);

// set include paths although I'm not really sure this is necessary...
$include_paths = array(MADEAM_PATH, VENDOR_PATH, VENDOR_LIB_PATH, ini_get('include_path'));
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

// define core file include handlers
$core_loaders = array(
	'inflector' 		=> '/madeam_inflector/',
	'madeam' 				=> '/^madeam/',
	'extensions' 		=> '/^component|behavior|parser|help|script/',
	'model'					=> '/^model/',
	'controller'		=> '/^controller/'
	);

// merge core include handlers with handlers from configuration
global $loaders;
$loaders = array_merge($core_loaders, $cfg['loaders']);

// include routes configuration
require CFG_PATH . 'routes.php';

// save database server configurations to registry
$registry = madeam_registry::instance();
$registry->set('db_servers', $cfg[ENVIRONMENT]['db_servers']);


// loaders
// ========================================================

// autoload class
// automatically includes classes upon instantiation
function __autoload($class) {
	global $loaders;
	foreach ($loaders as $loader => $regexp) {
		if (preg_match($regexp, $class, $matchs)) {
			$function = 'loader_' . $loader;
			$file = $function($class, $matchs);
			if (is_string($file)) { require $file; }
			break; // always break out! no second chances.
		}
	}
}

// inflector include handler
function loader_inflector($class, $matchs) {
	return MADEAM_LIB_PATH . 'madeam_inflector.php';
}

// madeam include handler
function loader_madeam($class, $matchs) {
	return MADEAM_LIB_PATH . $class . '.php';
}

// extensions include handler
function loader_extensions($class, $matchs) {
	$nodes = explode('_', $class);
	array_pop($nodes);
	return implode(DS, $nodes) . DS . $class . '.php';
}

// model include handler
function loader_model($class, $matchs) {
	$nodes = explode('_', $class);
	array_pop($nodes);
	return APP_PATH . implode(DS, $nodes) . DS . $class . '.php';
}

// controller include handler
function loader_controller($class, $matchs) {
	$nodes = explode('_', $class);
	array_pop($nodes);
	$file = APP_PATH . implode(DS, $nodes) . DS . $class . '.php';
	if (file_exists($file)) {
		return $file;
	} else {
		return false;
	}
}


// function library
// ========================================================

/**
 * This function exists as a quick tool for developers to test
 * if a funciton executes and how many times.
 */
function test($var = null) {
	static $tests;

	$tests++;

	$length = 3;
	$digits = preg_split('//', $tests, -1, PREG_SPLIT_NO_EMPTY);
	$digits = count($digits);
	$differ = $length - $digits;
	$pad    = null;

	if ($differ > 0) {
		for($i = 0; $i <= $differ; $i++) {
			$pad .= '0';
		}
	}

	if (is_array($var)) {
		echo '<pre> <br /> TESTING 123 (' . $pad . $tests . ') &nbsp;&nbsp;' . "\n";
		print_r($var);
		echo ' &nbsp;&nbsp;</pre>' . "\n";
	} else {
		echo "<br /> TESTING 123 (" . $pad . $tests . ") &nbsp;&nbsp;" . $var . "&nbsp;&nbsp;  \n";
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
function isListFormat($data) {
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

/**
 * Include vendor file
 *
 * @param string $file
 */
function vendor($file) {
  require_once VENDOR_LIB_PATH . $file;
}

// generate a random string
function rnd_string($length = 7) {
  $string = null;
  $chars = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 0, 1, 2, 3, 4, 5, 6, 7, 8, 9);

  for ($i = 0; $i <= $length; $i++) {
    $string .= $chars[rand(0, 35)];
  }

  return $string;
}


function parse_db_connection($string) {
  $details = array();

  // parse connection string as url
  $parsed_string = parse_url($string);

  isset($parsed_string['scheme']) ? $details['driver']  = $parsed_string['scheme']  : $details['driver'] = null;
  isset($parsed_string['host'])   ? $details['host']    = $parsed_string['host']    : $details['host']   = null;
  isset($parsed_string['user'])   ? $details['user']    = $parsed_string['user']    : $details['user']   = null;
  isset($parsed_string['pass'])   ? $details['pass']    = $parsed_string['pass']    : $details['pass']   = null;

  parse_str($parsed_string['query'], $options);

  isset($options['name']) ? $details['name'] = $options['name'] : $details['name'] = null;
  isset($options['port']) ? $details['port'] = $options['port'] : $details['port'] = false;

  return $details;
}


?>