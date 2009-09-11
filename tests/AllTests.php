<?php
require_once 'Bootstrap.php';
class AllTests {
  
  public static function main() {
    PHPUnit_TextUI_TestRunner::run(self::suite());
  }
  
  public static function suite() {
    $suite = new PHPUnit_Framework_TestSuite('Madeam Framework');

    $suite->addTestSuite('madeam\FrameworkTest');
    $suite->addTestSuite('madeam\EnvironmentTest');
    $suite->addTestSuite('madeam\ControllerTest');
    $suite->addTestSuite('madeam\RouterTest');
    $suite->addTestSuite('madeam\InflectorTest');
    $suite->addTestSuite('madeam\ViewTest');

    return $suite;
  }
  
}
