<?php
require_once 'tests' . DIRECTORY_SEPARATOR . 'Bootstrap.php';

class Madeam_EnvironmentTest extends PHPUnit_Framework_TestCase {
 
  /**
   * 
   */
  public function testPHPVersionIs520OrGreater() {
    $version = explode('.', phpversion());
    
    if ((int) $version[0] == 5 && (int) $version[1] >= 2) {
      $this->assertTrue(true);
    } else {
      $this->fail('Invalid version of PHP');
    }
  }
  
  /**
   * 
   */
  public function testRequestOrderIsGPC() {
    //$this->assertEquals('GPC', ini_get('request_order')); // PHP 5.3
    //test(ini_get('gpc_order'));
    //$this->assertEquals('GPC', ini_get('gpc_order'), 'The get, post, cookie order should equal "GPC"');
  }
  
  /**
   * 
   */
  public function testMagicQuotesAreDisabled() {
    $this->assertEquals(0, get_magic_quotes_runtime(), 'Shit');
  }
  
}