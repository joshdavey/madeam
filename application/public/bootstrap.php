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
 * @version			0.0.4
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
define('APP_PATH', dirname(PUB_PATH) . DS);

// set current directory to application directory
chdir(APP_PATH);

// set application name
define('APP_NAME', basename(APP_PATH));

// Config directory
define('CFG_PATH', APP_PATH . 'config' . DS);

// define config variable
$cfg = array();

// include base setup configuration
require_once CFG_PATH . 'environment.php';

// turn configs into constants for speed?
define('DB_USER', $cfg[ENVIRONMENT]['db_user']);
define('DB_PASS', $cfg[ENVIRONMENT]['db_pass']);
define('DB_NAME', $cfg[ENVIRONMENT]['db_name']);
define('DB_HOST', $cfg[ENVIRONMENT]['db_host']);
define('MOD_REWRITE', $cfg[ENVIRONMENT]['mod_rewrite']);

// set base url
define('BASE_URL', $_SERVER['SERVER_NAME']);

// set URI_PATH based on whether MOD_REWRITE is turned on or off. If it's off then we need to add the SCRIPT_FILENAME at the end
if (MOD_REWRITE) {
	define('URI_PATH', '/' . substr(str_replace(DS, '/', substr(FOREIGN_PATH . '/', strlen(DOC_PATH), -strlen(PUB_DIR))), 1, -1));
} else {
	define('URI_PATH', str_replace(DS, '/', substr(FOREIGN_PATH . '/', strlen(DOC_PATH))) . SCRIPT_FILENAME . '/');
}

// determine the relative path to the public directory
define('REL_PATH', str_replace(DS, '/', substr(PUB_PATH, strlen(DOC_PATH))));

// major madeam directories
define('MADEAM_PATH',       realpath($cfg[ENVIRONMENT]['madeam_dir']) . DS);

// application files
define('VIEW_PATH',         APP_PATH . 'views' . DS);
define('LAYOUT_PATH',       VIEW_PATH . '_layouts' . DS);
define('ERROR_PATH',        VIEW_PATH . '_errors' . DS);
define('CONTROLLER_PATH',   APP_PATH . 'controllers' . DS);
define('MODEL_PATH',        APP_PATH . 'models' . DS);
define('VENDOR_PATH',       APP_PATH . 'vendor' . DS);
define('LOG_PATH', 					APP_PATH . 'log' . DS);

// madeam paths
define('MADEAM_HELPER_PATH',      MADEAM_PATH . 'helpers' . DS);
define('MADEAM_COMPONENTS_PATH',  MADEAM_PATH . 'components' . DS);
define('MADEAM_BEHAVIORS_PATH',   MADEAM_PATH . 'behaviors' . DS);
define('MADEAM_SCAFFOLDS_PATH',   MADEAM_PATH . 'scaffolds' . DS);
define('MADEAM_LIB_PATH',					MADEAM_PATH . 'lib' . DS);

// vendor paths
define('VENDOR_HELPER_PATH',      VENDOR_PATH . 'helpers' . DS);
define('VENDOR_COMPONENTS_PATH',  VENDOR_PATH . 'components' . DS);
define('VENDOR_BEHAVIORS_PATH',   VENDOR_PATH . 'behaviors' . DS);
define('VENDOR_PLUGINS_PATH',   	VENDOR_PATH . 'plugins' . DS);
define('VENDOR_LIB_PATH',   			VENDOR_PATH . 'lib' . DS);

// set include paths although I'm not really sure this is necessary...
$include_paths = array(MADEAM_PATH, VENDOR_PATH, ini_get('include_path'));
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

/**
 * Autoload function automatically loads classes when they're instantiated
 *
 * @param string $class
 */
function __autoload($class) {
  if ($class == 'inflector') {
    // include inflector class and object class
    //require_once MADAEM_DIR . 'inflector.php';
		require_once MADEAM_LIB_PATH . 'inflector.php';
  } elseif (preg_match('/(Helper|Component|Behavior)$/', $class, $matchs)) {
    // include extentions
    require_once MADEAM_PATH . inflector::pluralize(low($matchs[1])) . DS . inflector::underscorize($class) . '.php';
	} elseif (preg_match('/Help$/', $class, $matchs)) {
    // include extentions
    require_once 'helpers' . DS . inflector::underscorize($class) . '.php';
  } elseif (preg_match('/^app([A-Z]{1}[a-z_A-Z]+)/', $class, $matchs)) {
    // include application classes

    // format path
    // example: AdminController
    // returns: admin/controller
    $path = inflector::fslashize($matchs[1]);

    // format path again
    // example: admin/controller
    // returns: controllers/admin
    $path = implode(DS, array_reverse(explode('/', substr(inflector::pluralize($path), 1, strlen($path)))));
		
    require_once APP_PATH . $path . DS . '_' . inflector::underscorize($class) . '.php';
  } elseif (preg_match('/(Model)$/', $class, $matchs)) {
    // include models
    require_once APP_PATH . inflector::pluralize(low($matchs[1])) . DS. inflector::underscorize($class) . '.php' ;
  } else {
    // include core classes
    require_once MADEAM_LIB_PATH . inflector::underscorize($class) . '.php';
  }
	
	// check that class was loaded
	if (!class_exists($class, false)) {
		trigger_error("Unable to load class: $class", E_USER_WARNING);
	}
}

// include routes configuration
require_once CFG_PATH . 'routes.php';


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
 * re-named echo/print
 */
function p($string) {
  echo $string;
}

/**
 * re-named test
 */
function t($var = null) {
  test($var);
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

function vendor($file) {
  require_once VENDOR_LIB_PATH . $file;
}

function rnd_string($length = 7) {
  $string = null;
  $chars = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 0, 1, 2, 3, 4, 5, 6, 7, 8, 9);
    
  for ($i = 0; $i <= $length; $i++) {
    $string .= $chars[rand(0, 35)];
  }
  
  return $string;
}
?>