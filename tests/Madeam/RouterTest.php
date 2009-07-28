<?php
require_once 'Bootstrap.php';

class madeam\RouterTest extends PHPUnit_Framework_TestCase {
  
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
    madeam\Router::$routes = array();
  }
  
  public function testConnectionOrder() {
    madeam\Router::connect(':one');
    madeam\Router::connect(':two');
    
    $params = madeam\Router::parse('url');
    
    if (isset($params['one'])) {
      $this->assertTrue(true);
    } else {
      $this->fail('The first matching route should connect and return params.');
    }
  }
  
  public function testDefaultParamValue() {
    madeam\Router::connect(':param', array('param' => 'def'));
    $params = madeam\Router::parse('');
    $this->assertEquals('def', $params['param']);
  }
  
  public function testRuleFails() {
    madeam\Router::connect(':rule', array(), array('rule' => '\d'));
    madeam\Router::connect(':fail');
    $params = madeam\Router::parse('string');
    $this->assertEquals('string', $params['fail']);
  }
  
  public function testRulePasses() {
    madeam\Router::connect(':rule', array(), array('rule' => '\d'));
    madeam\Router::connect(':fail');
    $params = madeam\Router::parse('9');
    $this->assertEquals('9', $params['rule']);
  }
  
  public function testBlankURIGetsReturnedAsAForwardSlash() {
    madeam\Router::connect('');
    $params = madeam\Router::parse('');
    $this->assertEquals('/', $params['_uri']);
  }
  
  public function testURIGetsReturnedWithAForwardSlash() {
    madeam\Router::connect('');
    $params = madeam\Router::parse('someuri');
    $this->assertEquals('/someuri', $params['_uri']);
  }
  
  public function testBaseURI() {
    madeam\Router::connect('');
    $params = madeam\Router::parse('base/test', 'base');
    $this->assertEquals('/test', $params['_uri']);
  }
  
  public function testFormatIsParsed() {
    madeam\Router::connect('');
    $params = madeam\Router::parse('uri.json');
    $this->assertEquals('json', $params['_format']);
  }
  
  public function testQueryIsAParam() {
    madeam\Router::connect('');
    $params = madeam\Router::parse('uri?test=cool');
    $this->assertEquals('test=cool', $params['_query']);
  }
  
  public function testQueryIsParsed() {
    madeam\Router::connect('');
    $params = madeam\Router::parse('uri?test=cool');
    $this->assertEquals('cool', $params['test']);
  }
  
  public function testExtraIsReturned() {
    madeam\Router::connect('');
    $params = madeam\Router::parse('extra/stuff');
    $this->assertEquals('extra/stuff', $params['_extra']);
  }
  
  public function testLayoutIsDefinedAs0Or1() {
    
  }

}