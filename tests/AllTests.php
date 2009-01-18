<?php

require_once 'Bootstrap.php';

class Madeam_AllTests {
  
  public static function main() {
    PHPUnit_TextUI_TestRunner::run(self::suite());
  }
  
  public static function suite() {
    $suite = new PHPUnit_Framework_TestSuite('Madeam Framework');

    $suite->addTestSuite('MadeamTest');
    $suite->addTestSuite('Madeam_EnvironmentTest');
    $suite->addTestSuite('Madeam_ControllerTest');

    return $suite;
  }
  
}