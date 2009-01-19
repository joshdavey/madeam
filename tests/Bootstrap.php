<?php

// set path to public directory
  $publicDirectory = realpath('public/') . DIRECTORY_SEPARATOR;

// our favorite constant. short and sweet.
  if (! defined('DS')) { define('DS', DIRECTORY_SEPARATOR); }

// include phpunit framework
  require_once 'PHPUnit/Framework.php';
  require_once 'PHPUnit/Framework/IncompleteTestError.php';
  require_once 'PHPUnit/Framework/TestCase.php';
  require_once 'PHPUnit/Framework/TestSuite.php';
  require_once 'PHPUnit/Runner/Version.php';
  require_once 'PHPUnit/TextUI/TestRunner.php';
  require_once 'PHPUnit/Util/Filter.php';

// set error reporting level
  error_reporting(E_ALL);

// include madeam
  require_once 'lib/Madeam/Framework.php';
  
  restore_error_handler();
  restore_exception_handler();

// define test constants
  define('TESTS_MADEAM_PUBLIC_DIRECTORY', $publicDirectory);
  define('TESTS_MADEAM_DOCUMENT_ROOT', dirname(dirname(TESTS_MADEAM_PUBLIC_DIRECTORY)));

// configure Madeam paths
  Madeam_Framework::paths(TESTS_MADEAM_PUBLIC_DIRECTORY);
  
  Madeam_Framework::$pathToApp = realpath('tests/Madeam/_application/app') . DS;
  
  Madeam_Controller::$viewPath = Madeam_Framework::$pathToApp . 'View' . DS;
  
  $paths = array(
    Madeam_Framework::$pathToApp,
    Madeam_Framework::$pathToLib,
    Madeam_Framework::$pathToTests
  );

// set include path
  set_include_path(implode(PATH_SEPARATOR, $paths) . PATH_SEPARATOR . get_include_path());
