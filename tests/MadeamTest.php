<?php
require_once 'Bootstrap.php';

class MadeamTest extends PHPUnit_Framework_TestCase {
  
  protected $server;
  protected $params;
  
  public function setUp() {
    $this->server = array('DOCUMENT_ROOT' => TESTS_MADEAM_DOCUMENT_ROOT, 'REQUEST_METHOD' => 'GET', 'QUERY_STRING' => null, 'REQUEST_URI' => 'madeam/index.php');
    $this->params = array();
  }
  
  public function testPublicFolderExists() {
    Madeam::paths(TESTS_MADEAM_PUBLIC_DIRECTORY);
    $this->assertTrue(file_exists(Madeam::$pathToPublic), 'The public directory should exist');
  }
  
  public function testAppFolderExists() {
    Madeam::paths(TESTS_MADEAM_PUBLIC_DIRECTORY);
    $this->assertTrue(file_exists(Madeam::$pathToApp), 'The app directory should exist');
  }
  
  public function testEtcFolderExists() {
    Madeam::paths(TESTS_MADEAM_PUBLIC_DIRECTORY);
    $this->assertTrue(file_exists(Madeam::$pathToEtc), 'The etc directory should exist');
  }
  
  public function testLibFolderExists() {
    Madeam::paths(TESTS_MADEAM_PUBLIC_DIRECTORY);
    $this->assertTrue(file_exists(Madeam::$pathToLib), 'The lib directory should exist');
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