<?php
require_once 'Bootstrap.php';

/**
 * undocumented 
 *
 * @author Joshua Davey
 */
class madeam\EnvironmentTest extends PHPUnit_Framework_TestCase {
 
  /**
   * Madeam Requires that version 5.2.3 or greater of PHP be running. 
   * Features such as call_user_func with dynamic class static method calls are only possibly as of 5.2.3
   * @author Joshua Davey
   */
  public function testPHPVersionIs523OrGreater() {
    $version = explode('.', phpversion());
    
    if (((int) $version[0] == 5 && (int) $version[1] >= 2 && (int) $version[2] >= 3) || ((int) $version[0] == 5 && (int) $version[1] >= 3)) {
      $this->assertTrue(true);
    } else {
      $this->fail('Invalid version of PHP');
    }
  }
  
  /**
   * To make sure the expected values of request variables are consistent PHP's variable order
   * should always be Environemnt (E), Get (G), Post (P), Cookie (C) and System (S)
   * @author Joshua Davey
   */
  public function testRequestOrderIsEGPCS() {
    //$this->assertEquals('EGPCS', ini_get('request_order')); // PHP 5.3
    $this->assertEquals('EGPCS', ini_get('variables_order'));
  }
  
  
  /**
   * This is not a required feature but it is important to identify as on or off
   * @author Joshua Davey
   */
  public function testShortTagsAreEnabled() {
    $this->assertEquals('1', ini_get('short_open_tag'));
  }
  
  /**
   * Magic quotes are evil! They should never be enabled.
   * @author Joshua Davey
   */
  public function testMagicQuotesAreDisabled() {
    $this->assertEquals('0', get_magic_quotes_runtime());
    $this->assertEquals('0', get_magic_quotes_gpc());
  }
  
}