<?php
require_once 'Bootstrap.php';

/**
 * undocumented 
 * @author Joshua Davey
 */
class MadeamTest extends PHPUnit_Framework_TestCase {
  
  /**
   * Mimics values of $_SERVER variable
   */
  protected $server;
  
  /**
   * Mimics values of $_REQUEST variable
   */
  protected $params;
  
  /**
   * undocumented
   * @author Joshua Davey
   */
  public function setUp() {
    $this->server = array('DOCUMENT_ROOT' => '/madeam/', 'REQUEST_METHOD' => 'GET', 'QUERY_STRING' => null, 'REQUEST_URI' => 'madeam/index.php');
    $this->params = array();
  }
  
  /**
   * undocumented
   * @author Joshua Davey
   */
  public function tearDown() {
    Framework::$uriAppPath = false;
    Framework::$uriPubPath = false;
  }
  
  /**
   * undocumented 
   * @author Joshua Davey
   */
  public function testPublicFolderShouldExist() {
    Framework::paths(TESTS_MADEAM_APP_DIRECTORY);
    $this->assertTrue(file_exists(Framework::$pathToPub), 'The public directory should exist');
  }
  
  /**
   * undocumented 
   * @author Joshua Davey
   */
  public function testAppFolderShouldExist() {
    Framework::paths(TESTS_MADEAM_APP_DIRECTORY, 'public');
    $this->assertTrue(file_exists(Framework::$pathToApp), 'The app directory should exist');
  }
  
  /**
   * undocumented 
   * @author Joshua Davey
   */
  public function testEtcFolderShouldExist() {
    Framework::paths(TESTS_MADEAM_APP_DIRECTORY);
    $this->assertTrue(file_exists(Framework::$pathToEtc), 'The etc directory should exist');
  }
    
  /**
   * Relative paths point
   * @author Joshua Davey
   */
  public function testPublicPath() {
    $docRoot  = '/Apache/htdocs/';
    $pubDir   = '/Apache/htdocs/madeam/public/';
    
    $this->assertEquals('/madeam/public/', Framework::pubPath($docRoot, $pubDir));
  }
  
  /**
   * Clean URI paths are paths relative to the document root WITHOUT "index.php" at the end. 
   * A clean URI is normally used when mod_rewrite is enabled.
   * @author Joshua Davey
   */
  public function testCleanUriPath() {
    $docRoot  = '/Apache/htdocs/';
    $pubDir   = '/Apache/htdocs/madeam/public/';
    
    // clean uris
    $this->assertEquals('/madeam/', Framework::cleanUriPath($docRoot, $pubDir));
  }
  
  /**
   * Dirty URI paths are paths relative to the document root WITH "index.php" at the end. 
   * A dirty URI is normally used when mod_rewrite is disabled.
   * @author Joshua Davey
   */
  public function testDirtyUriPath() {
    $docRoot  = '/Apache/htdocs/';
    $pubDir   = '/Apache/htdocs/madeam/public/';
    
    // dirty uris
    $this->assertEquals('/madeam/index.php/', Framework::dirtyUriPath($docRoot, $pubDir));
  }
  
  /**
   * undocumented
   * @author Joshua Davey
   */
  public function testUrl() {
    Framework::$uriAppPath = '/madeam/';
    Framework::$uriPubPath = '/madeam/index.php/';
    
    $this->assertEquals('/madeam/test', Framework::url('test'));
    $this->assertEquals('/madeam/index.php/test', Framework::url('/test'));
  }
  
}