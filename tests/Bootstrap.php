<?php

// set path to public directory
$publicDirectory = realpath('../public/') . DIRECTORY_SEPARATOR;


require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Framework/IncompleteTestError.php';
require_once 'PHPUnit/Framework/TestCase.php';
require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'PHPUnit/Runner/Version.php';
require_once 'PHPUnit/TextUI/TestRunner.php';
require_once 'PHPUnit/Util/Filter.php';

require_once '../lib/Madeam.php';

error_reporting(E_ALL);

set_include_path(implode(PATH_SEPARATOR, Madeam::paths(getcwd() . DIRECTORY_SEPARATOR)) . PATH_SEPARATOR . get_include_path());


Madeam::setup(require '../env.php', true);