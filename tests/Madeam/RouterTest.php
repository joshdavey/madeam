<?php
require_once 'tests' . DIRECTORY_SEPARATOR . 'Bootstrap.php';

class Madeam_RouterTest extends PHPUnit_Framework_TestCase {
  
  protected $defaults;
  protected $baseUri;
  
  /**
   * 
   */
  public function setup() {
    $this->defaults = array(
      '_controller' => 'index',
      '_action'     => 'index',
      '_format'     => 'html',
      '_method'     => 'get'
    );
    
    $this->baseUri = '/';
  }
  
  /**
   * 
   */
  public function tearDown() {
    Madeam_Router::$routes = array();
  }
  
  public function testConnectionOrder() {
    Madeam_Router::connect(':one');
    Madeam_Router::connect(':two');
    
    $params = Madeam_Router::parse('url');
    
    if (isset($params['one'])) {
      $this->assertTrue(true);
    } else {
      $this->fail('The first matching route should connect and return params.');
    }
  }
  
  public function testDefaultParamValue() {
    Madeam_Router::connect(':param', array('param' => 'def'));
    $params = Madeam_Router::parse('');
    $this->assertEquals('def', $params['param']);
  }
  
  public function testRuleFails() {
    Madeam_Router::connect(':rule', array(), array('rule' => '\d'));
    Madeam_Router::connect(':fail');
    $params = Madeam_Router::parse('string');
    $this->assertEquals('string', $params['fail']);
  }
  
  public function testRulePasses() {
    Madeam_Router::connect(':rule', array(), array('rule' => '\d'));
    Madeam_Router::connect(':fail');
    $params = Madeam_Router::parse('9');
    $this->assertEquals('9', $params['rule']);
  }
  
  public function testBlankURIGetsReturnedAsAForwardSlash() {
    Madeam_Router::connect('');
    $params = Madeam_Router::parse('');
    $this->assertEquals('/', $params['_uri']);
  }
  
  public function testURIGetsReturnedWithAForwardSlash() {
    Madeam_Router::connect('');
    $params = Madeam_Router::parse('someuri');
    $this->assertEquals('/someuri', $params['_uri']);
  }
  
  public function testBaseURI() {
    Madeam_Router::connect('');
    $params = Madeam_Router::parse('base/test', 'base');
    $this->assertEquals('/test', $params['_uri']);
  }
  
  public function testFormatIsParsed() {
    Madeam_Router::connect('');
    $params = Madeam_Router::parse('uri.json');
    $this->assertEquals('json', $params['_format']);
  }
  
  public function testQueryIsAParam() {
    Madeam_Router::connect('');
    $params = Madeam_Router::parse('uri?test=cool');
    $this->assertEquals('test=cool', $params['_query']);
  }
  
  public function testQueryIsParsed() {
    Madeam_Router::connect('');
    $params = Madeam_Router::parse('uri?test=cool');
    $this->assertEquals('cool', $params['test']);
  }
  
  public function testExtraIsReturned() {
    Madeam_Router::connect('');
    $params = Madeam_Router::parse('extra/stuff');
    $this->assertEquals('extra/stuff', $params['_extra']);
  }
  
  public function testLayoutIsDefinedAs0Or1() {
    
  }

}