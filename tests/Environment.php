<?php

require '../system/bootstrap.php';

class EnvironmentTest extends PHPUnit_Framework_TestCase {
  
  public function setUp() {
    $this->env = $_SERVER;
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
    $this->assertTrue(file_exists(PATH_TO_APP), 'The app directory should exist');
  }
  
  public function testEtcFolderExists() {
    $this->assertTrue(file_exists(PATH_TO_ETC), 'The etc directory should exist');
  }
  
  public function testDocumentRootEndsInForwardSlash() {
    $this->assertEquals('/', substr($this->env['DOCUMENT_ROOT'], -1));
  }
  
}