<?php
require_once 'Bootstrap.php';

class Madeam_EnvironmentTest extends PHPUnit_Framework_TestCase {
  
  protected $server;
  
  public function setUp() {
    
    $_SERVER['REQUEST_URI'] = '/';
    Madeam::setup(require Madeam::$pathToProject . 'env.php', false);
    
    $this->server = $_SERVER;
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
    $this->assertTrue(file_exists(Madeam::$pathToPublic), 'The public directory should exist');
  }
  
  public function testAppFolderExists() {
    $this->assertTrue(file_exists(Madeam::$pathToApp), 'The app directory should exist');
  }
  
  public function testEtcFolderExists() {
    $this->assertTrue(file_exists(Madeam::$pathToEtc), 'The etc directory should exist');
  }
  
  public function testLibFolderExists() {
    $this->assertTrue(file_exists(Madeam::$pathToLib), 'The lib directory should exist');
  }
  
  public function testDocumentRootEndsInForwardSlash() {
    $this->assertEquals('/', substr($this->server['DOCUMENT_ROOT'], -1));
  }
  
  public function testRequestOrder() {
    //$this->assertEquals('GPC', ini_get('request_order')); // PHP 5.3
    //$this->assertEquals('GPC', ini_get('gpc_order'), 'The get, post, cookie order should equal "GPC"');
  }
  
}