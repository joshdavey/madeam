<?php
require_once 'tests' . DIRECTORY_SEPARATOR . 'Bootstrap.php';

/**
 * undocumented 
 * @author Joshua Davey
 */
class Madeam_FrameworkTest extends PHPUnit_Framework_TestCase {
  
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
    Madeam_Framework::$uriAppPath = false;
    Madeam_Framework::$uriPubPath = false;
  }
  
  /**
   * undocumented 
   * @author Joshua Davey
   */
  public function testPublicFolderShouldExist() {
    Madeam_Framework::paths(TESTS_MADEAM_PUB_DIRECTORY);
    $this->assertTrue(file_exists(Madeam_Framework::$pathToPublic), 'The public directory should exist');
  }
  
  /**
   * undocumented 
   * @author Joshua Davey
   */
  public function testAppFolderShouldExist() {
    Madeam_Framework::paths(TESTS_MADEAM_PUB_DIRECTORY);
    $this->assertTrue(file_exists(Madeam_Framework::$pathToApp), 'The app directory should exist');
  }
  
  /**
   * undocumented 
   * @author Joshua Davey
   */
  public function testEtcFolderShouldExist() {
    Madeam_Framework::paths(TESTS_MADEAM_PUB_DIRECTORY);
    $this->assertTrue(file_exists(Madeam_Framework::$pathToEtc), 'The etc directory should exist');
  }
  
  /**
   * undocumented 
   * @author Joshua Davey
   */
  public function testLibFolderShouldExist() {
    Madeam_Framework::paths(TESTS_MADEAM_PUB_DIRECTORY);
    $this->assertTrue(file_exists(Madeam_Framework::$pathToLib), 'The lib directory should exist');
  }
    
  /**
   * Relative paths point
   * @author Joshua Davey
   */
  public function testPublicPath() {
    $docRoot  = '/Apache/htdocs/';
    $pubDir   = '/Apache/htdocs/madeam/public/';
    
    $this->assertEquals('/madeam/public/', Madeam_Framework::pubPath($docRoot, $pubDir));
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
    $this->assertEquals('/madeam/', Madeam_Framework::cleanUriPath($docRoot, $pubDir));
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
    $this->assertEquals('/madeam/index.php/', Madeam_Framework::dirtyUriPath($docRoot, $pubDir));
  }
  
  /**
   * undocumented
   * @author Joshua Davey
   */
  public function testUrl() {
    Madeam_Framework::$uriAppPath = '/madeam/';
    Madeam_Framework::$uriPubPath = '/madeam/index.php/';
    
    $this->assertEquals('/madeam/test', Madeam_Framework::url('test'));
    $this->assertEquals('/madeam/index.php/test', Madeam_Framework::url('/test'));
  }
  
}