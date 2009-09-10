<?php
namespace madeam;
require_once 'Bootstrap.php';
class FrameworkTest extends \PHPUnit_Framework_TestCase {
  
  /**
   * Mimics values of $_SERVER variable
   */
  protected $server;
  
  /**
   * Mimics values of $_GET + $_POST + $_COOKIE variable
   */
  protected $params;
  
  /**
   * undocumented
   * @author Joshua Davey
   */
  public function setUp() {
    $this->server = array('DOCUMENT_ROOT' => '/madeam/', 'REQUEST_METHOD' => 'GET', 'QUERY_STRING' => null, 'REQUEST_URI' => 'madeam');
    $this->params = array();
  }
  
  /**
   * undocumented
   * @author Joshua Davey
   */
  public function tearDown() {
    Framework::$uriPathDynamic = false;
    Framework::$uriPathStatic  = false;
  }
  
  /**
   * Relative paths point
   * @author Joshua Davey
   */
  public function testStaticPath() {
    $docRoot  = '/Apache/htdocs/';
    $pubDir   = '/Apache/htdocs/madeam/public/';
    
    $this->assertEquals('/madeam/public/', Framework::parseStaticUri($docRoot, $pubDir));
  }
  
  /**
   * Clean URI paths are paths relative to the document root WITHOUT "index.php" at the end. 
   * A clean URI is normally used when mod_rewrite is enabled.
   * @author Joshua Davey
   */
  public function testDynamicPath() {
    $docRoot  = '/Apache/htdocs/';
    $pubDir   = '/Apache/htdocs/madeam/public/';
    
    // clean uris
    $this->assertEquals('/madeam/', Framework::parseDynamicUri($docRoot, $pubDir));
  }
  
  /**
   * undocumented
   * @author Joshua Davey
   */
  public function testUrl() {
    Framework::$uriPathDynamic = '/madeam/';
    Framework::$uriPathStatic = '/madeam/public/';
    
    $this->assertEquals('/madeam/test', Framework::url('test'));
    $this->assertEquals('/madeam/public/test', Framework::url('/test'));
  }
  
}