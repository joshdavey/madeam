<?php
require_once 'Bootstrap.php';

class MadeamTest extends PHPUnit_Framework_TestCase {
  
  protected $server;
  protected $params;
  
  public function setUp() {
    $this->server = array('DOCUMENT_ROOT' => TESTS_MADEAM_DOCUMENT_ROOT, 'REQUEST_METHOD' => 'GET', 'QUERY_STRING' => null, 'REQUEST_URI' => 'madeam/index.php');
    $this->params = array();
  }
    
  public function testPublicFolderShouldExist() {
    Madeam::paths(TESTS_MADEAM_PUBLIC_DIRECTORY);
    $this->assertTrue(file_exists(Madeam::$pathToPublic), 'The public directory should exist');
  }
  
  public function testAppFolderShouldExist() {
    Madeam::paths(TESTS_MADEAM_PUBLIC_DIRECTORY);
    $this->assertTrue(file_exists(Madeam::$pathToApp), 'The app directory should exist');
  }
  
  public function testEtcFolderShouldExist() {
    Madeam::paths(TESTS_MADEAM_PUBLIC_DIRECTORY);
    $this->assertTrue(file_exists(Madeam::$pathToEtc), 'The etc directory should exist');
  }
  
  public function testLibFolderShouldExist() {
    Madeam::paths(TESTS_MADEAM_PUBLIC_DIRECTORY);
    $this->assertTrue(file_exists(Madeam::$pathToLib), 'The lib directory should exist');
  }

  public function testMagicQuotesAreOff() {
    $this->assertEquals(get_magic_quotes_gpc(), 0, 'Magic quotes are the evil'));
  }
  
  public function testRelativePath() {
    $docRoot  = '/Apache/htdocs/';
    $pubDir   = '/Apache/htdocs/madeam/public/';
    
    $this->assertEquals('/madeam/public/', Madeam::relPath($docRoot, $pubDir));
  }
  
  public function testUriPath() {
    $docRoot  = '/Apache/htdocs/';
    $pubDir   = '/Apache/htdocs/madeam/public/';
    
    // clean uris
    $this->assertEquals('/madeam/', Madeam::cleanUriPath($docRoot, $pubDir));
    
    // dirty uris
    $this->assertEquals('/madeam/index.php/', Madeam::dirtyUriPath($docRoot, $pubDir));
  }
  
  public function testUrl() {
    Madeam::$pathToUri = '/madeam/';
    Madeam::$pathToRel = '/madeam/index.php/';
    
    $this->assertEquals('/madeam/test', Madeam::url('test'));
    $this->assertEquals('/madeam/index.php/test', Madeam::url('/test'));
  }
  
}