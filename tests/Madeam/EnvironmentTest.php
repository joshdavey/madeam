<?php

require_once 'TestHelper.php';

class Madeam_EnvironmentTest extends PHPUnit_Framework_TestCase {
  
  public function setUp() {
    $this->env = $_SERVER;
    Madeam::setup();
  }
  
  public function testPHPVersionIs520OrGreater() {
    $version = explode('.', phpversion());
    
    if ((int) $version[0] == 5 && (int) $version[1] >= 2) {
      $this->assertTrue(true);
    } else {
      $this->fail('Invalid version of PHP');
    }
  }
  
  public function testPublicFolderExists() {
    $this->assertTrue(file_exists(PATH_TO_PUBLIC), 'The public directory should exist');
  }
  
  public function testAppFolderExists() {
    $this->assertTrue(file_exists(Madeam::$pathToPublic), 'The app directory should exist');
  }
  
  public function testEtcFolderExists() {
    $this->assertTrue(file_exists(PATH_TO_ETC), 'The etc directory should exist');
  }
  
  public function testLibFolderExists() {
    $this->assertTrue(file_exists(PATH_TO_LIB), 'The lib directory should exist');
  }
  
  public function testDocumentRootEndsInForwardSlash() {
    $this->assertEquals('/', substr($this->env['DOCUMENT_ROOT'], -1));
  }
  
  public function testRequestOrder() {
    //$this->assertEquals('GPC', ini_get('request_order')); // PHP 5.3
    $this->assertEquals('GPC', ini_get('gpc_order'), 'The get, post, cookie order should equal "GPC"');
  }
  
}