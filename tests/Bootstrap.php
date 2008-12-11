<?php

// set path to public directory
$publicDirectory = realpath('../public/') . DIRECTORY_SEPARATOR;

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

require_once '../lib/Madeam.php';

// define test constants
define('TESTS_MADEAM_PUBLIC_DIRECTORY', $publicDirectory);
define('TESTS_MADEAM_DOCUMENT_ROOT', dirname(dirname(TESTS_MADEAM_PUBLIC_DIRECTORY)));

// set include path
set_include_path(implode(PATH_SEPARATOR, Madeam::paths(TESTS_MADEAM_PUBLIC_DIRECTORY)) . PATH_SEPARATOR . get_include_path());