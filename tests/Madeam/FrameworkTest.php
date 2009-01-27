<?php
require_once 'tests' . DIRECTORY_SEPARATOR . 'Bootstrap.php';

class Madeam_FrameworkTest extends PHPUnit_Framework_TestCase {
  
  /**
   * 
   */
  protected $server;
  
  /**
   * 
   */
  protected $params;
  
  /**
   * 
   */
  public function setUp() {
    $this->server = array('DOCUMENT_ROOT' => '/madeam/', 'REQUEST_METHOD' => 'GET', 'QUERY_STRING' => null, 'REQUEST_URI' => 'madeam/index.php');
    $this->params = array();
  }
  
  /**
   * 
   */
  public function testPublicFolderShouldExist() {
    Madeam_Framework::paths(TESTS_MADEAM_PUB_DIRECTORY);
    $this->assertTrue(file_exists(Madeam_Framework::$pathToPublic), 'The public directory should exist');
  }
  
  /**
   * 
   */
  public function testAppFolderShouldExist() {
    Madeam_Framework::paths(TESTS_MADEAM_PUB_DIRECTORY);
    $this->assertTrue(file_exists(Madeam_Framework::$pathToApp), 'The app directory should exist');
  }
  
  /**
   * 
   */
  public function testEtcFolderShouldExist() {
    Madeam_Framework::paths(TESTS_MADEAM_PUB_DIRECTORY);
    $this->assertTrue(file_exists(Madeam_Framework::$pathToEtc), 'The etc directory should exist');
  }
  
  /**
   * 
   */
  public function testLibFolderShouldExist() {
    Madeam_Framework::paths(TESTS_MADEAM_PUB_DIRECTORY);
    $this->assertTrue(file_exists(Madeam_Framework::$pathToLib), 'The lib directory should exist');
  }
  
  /**
   * 
   */
  public function testMagicQuotesAreOff() {
    $this->assertEquals(get_magic_quotes_gpc(), 0, 'Magic quotes are the evil');
  }
  
  /**
   * 
   */
  public function testRelativePath() {
    $docRoot  = '/Apache/htdocs/';
    $pubDir   = '/Apache/htdocs/madeam/public/';
    
    $this->assertEquals('/madeam/public/', Madeam_Framework::relPath($docRoot, $pubDir));
  }
  
  public function testUriPath() {
    $docRoot  = '/Apache/htdocs/';
    $pubDir   = '/Apache/htdocs/madeam/public/';
    
    // clean uris
    $this->assertEquals('/madeam/', Madeam_Framework::cleanUriPath($docRoot, $pubDir));
    
    // dirty uris
    $this->assertEquals('/madeam/index.php/', Madeam_Framework::dirtyUriPath($docRoot, $pubDir));
  }
  
  /**
   * 
   */
  public function testUrl() {
    Madeam_Framework::$pathToUri = '/madeam/';
    Madeam_Framework::$pathToRel = '/madeam/index.php/';
    
    $this->assertEquals('/madeam/test', Madeam_Framework::url('test'));
    $this->assertEquals('/madeam/index.php/test', Madeam_Framework::url('/test'));
  }
  
}