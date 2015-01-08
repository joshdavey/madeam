<?php
namespace madeam;

// Madeam version
const VERSION = '2.0.0 Alpha';

// idea... use this as a last resort when all autoloads fail.
// have this one throw an exception or make a last resort to check every path for the file.
spl_autoload_register('madeam\autoload');
spl_autoload_register('madeam\autoloadPackage');

// move the autoload fail logic into it's own function so that we don't need to do checks
// every time we attempt to load a file.
spl_autoload_register('madeam\autoloadFail');

/**
 * Include core files
 * These files will be included in 99% of requests so it is more effecient to include them now
 * than for them to be autoloaded.
 */
$madeamLibrary = dirname(__FILE__) . '/madeam/';
require $madeamLibrary . 'Framework.php';
require $madeamLibrary . 'Controller.php';
require $madeamLibrary . 'Inflector.php';
require $madeamLibrary . 'Exception.php';
require $madeamLibrary . 'Middleware.php';
require $madeamLibrary . 'Router.php';
require $madeamLibrary . 'View.php';


/**
 * Define include paths
 */
Framework::$pathToProject = getcwd() . '/';

set_include_path(
  Framework::$pathToProject . 'application/models' . PATH_SEPARATOR .
  Framework::$pathToProject . 'application/controllers' . PATH_SEPARATOR .
  Framework::$pathToProject . 'application/middleware' . PATH_SEPARATOR .
  Framework::$pathToProject . 'application/vendor' . PATH_SEPARATOR .
  Framework::$pathToProject . 'application' . PATH_SEPARATOR .
  get_include_path() . PATH_SEPARATOR .
  dirname(dirname(dirname(__FILE__))) . ''
);

/**
 * Set global exception handler when an exception isn't caught
 */
set_exception_handler('madeam\Exception::handle');


/**
 * Madeam's class Autoloader. This method should be used for autoloading by loading it with spl.
 * Example: spl_autoload_register('madeam\autoload');
 *
 * @param string $class
 * @author Joshua Davey
 */
function autoload($class) {
  // set class file name)
  $file = str_replace('_', '/', str_replace('\\', '/', $class)) . '.php'; // PHP 5.3

  // checks all the include paths to see if the file exist
  $paths = explode(PATH_SEPARATOR, get_include_path());
  foreach ($paths as $path) {
    if (file_exists($path . '/' . $file)) {
      require $path . '/' . $file;
      return true;
    }
  }
}

/**
 * This is the second autoloader (one day it will be primary).
 * This autoloader looks for packages. A package is the first part of a class name.
 *
 * The expected directory structure of a package is a modified version of PEAR2's directory structure
 * @see http://wiki.pear.php.net/index.php/PEAR2_Standards#Directory_structure
 * The difference is that PEAR assumes pacakges are always within the PEAR directory. This version
 * only expects the package to be in its on directory within an include path.
 *
 * packagename/
 *  doc/
 *  src/
 *    PackageName.php
 *    packagename/
 *  tests/
 *    PackageNameTest.php
 *    packagename/
 *
 * Here are some example classes and their location
 *  madeam                => madeam/src/Madeam.php
 *  madeam\Controller     => madeam/src/madeam/Controller.php
 *  madeam\serialize\Json => madeam/src/madeam/serialize/Json.php
 *
 * @param string $class
 * @author Joshua Davey
 */
function autoloadPackage($class) {
  // set class file name
  $file = str_replace('_', '/', str_replace('\\', '/', $class)) . '.php'; // PHP 5.3
  $packageNameLength = strlen(strstr($class, '\\'));
  if ($packageNameLength == 0) {
    $file = $class . '/src/' . str_replace('_', '/', $class) . '.php';
  } else {
    $file = substr($class, 0, -$packageNameLength) . '/src/' . str_replace('\\', '/', $class) . '.php';
  }

  // checks all the include paths to see if the file exist
  $paths = explode(PATH_SEPARATOR, get_include_path());
  foreach ($paths as $path) {
    if (file_exists($path . '/' . $file)) {
      require $path . '/' . $file;
      return true;
    }
  }
}

/**
 * Catch all failed attempts at finding a class. By putting this logic in it's own function
 * instead of in the other autoload functions we save time but not having to check to see
 * if the class or interface exists
 *
 * @author Joshua Davey
 */
function autoloadFail($class) {
  $fullClass = preg_replace("/[^A-Za-z0-9_\\\]/", null, $class); // clean the dirt

  $namespace = substr($fullClass, 0, strrpos($class, '\\'));
  $class = substr($fullClass, strrpos($class, '\\') + 1, strlen($class));

  if ($namespace != null) {
    $namespace = 'namespace ' . $namespace . ';';
  }

  eval("$namespace class $class {}");
  throw new exception\AutoloadFail('Missing Class ' . $fullClass);
}





function throwExceptionErrorHandler($errno, $errstr, $errfile, $errline) {
  return true;
}

set_error_handler("madeam\\throwExceptionErrorHandler");






/**
 * Debug tool for developers
 */
function debug($var = null) {
  static $tests;
  $tests ++;
  for ($i = 0; $i < (6 - strlen($tests)); $i ++) {
    $tests = '0' . $tests;
  }

  if (!isset($_SERVER['SHELL'])) {
    $header = '<br /><pre>[T::' . $tests . '] &nbsp;&nbsp;' . "\n";
    $footer = ' &nbsp;&nbsp;</pre>' . "\n";
  } else {
    $header = null;
    $footer = "\n";
  }

  if (is_array($var) || is_object($var)) {
    echo $header;
    print_r($var);
    echo $footer;
  } elseif (is_bool($var)) {
    if ($var === true) {
      $var = 'TRUE';
    } else {
      $var = 'FALSE';
    }

    echo $header . (string) $var . $footer;
  } else {
    echo $header . $var . $footer;
  }
}

/**
 * Wrapper for htmlentities
 * @param string $string
 */
function h($string) {
  return htmlentities(iconv('UTF-8', 'UTF-8//IGNORE', $string), ENT_QUOTES, 'UTF-8');
}
