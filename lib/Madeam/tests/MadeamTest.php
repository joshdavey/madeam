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
    Madeam::$uriAppPath = false;
    Madeam::$uriPubPath = false;
  }
  
  /**
   * undocumented 
   * @author Joshua Davey
   */
  public function testPublicFolderShouldExist() {
    Madeam::paths(TESTS_MADEAM_PUB_DIRECTORY);
    $this->assertTrue(file_exists(Madeam::$pathToPublic), 'The public directory should exist');
  }
  
  /**
   * undocumented 
   * @author Joshua Davey
   */
  public function testAppFolderShouldExist() {
    Madeam::paths(TESTS_MADEAM_PUB_DIRECTORY);
    $this->assertTrue(file_exists(Madeam::$pathToApp), 'The app directory should exist');
  }
  
  /**
   * undocumented 
   * @author Joshua Davey
   */
  public function testEtcFolderShouldExist() {
    Madeam::paths(TESTS_MADEAM_PUB_DIRECTORY);
    $this->assertTrue(file_exists(Madeam::$pathToEtc), 'The etc directory should exist');
  }
  
  /**
   * undocumented 
   * @author Joshua Davey
   */
  public function testLibFolderShouldExist() {
    Madeam::paths(TESTS_MADEAM_PUB_DIRECTORY);
    $this->assertTrue(file_exists(Madeam::$pathToLib), 'The lib directory should exist');
  }
    
  /**
   * Relative paths point
   * @author Joshua Davey
   */
  public function testPublicPath() {
    $docRoot  = '/Apache/htdocs/';
    $pubDir   = '/Apache/htdocs/madeam/public/';
    
    $this->assertEquals('/madeam/public/', Madeam::pubPath($docRoot, $pubDir));
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
    $this->assertEquals('/madeam/', Madeam::cleanUriPath($docRoot, $pubDir));
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
    $this->assertEquals('/madeam/index.php/', Madeam::dirtyUriPath($docRoot, $pubDir));
  }
  
  /**
   * undocumented
   * @author Joshua Davey
   */
  public function testUrl() {
    Madeam::$uriAppPath = '/madeam/';
    Madeam::$uriPubPath = '/madeam/index.php/';
    
    $this->assertEquals('/madeam/test', Madeam::url('test'));
    $this->assertEquals('/madeam/index.php/test', Madeam::url('/test'));
  }
  
}