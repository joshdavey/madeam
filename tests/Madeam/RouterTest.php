<?php
namespace madeam;
require_once 'Bootstrap.php';
class RouterTest extends \PHPUnit_Framework_TestCase {

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
    Router::$routes = array();
  }

  public function testConnectionOrder() {
    Router::connect(':one');
    Router::connect(':two');

    $params = Router::parse('url');

    if (isset($params['one'])) {
      $this->assertTrue(true);
    } else {
      $this->fail('The first matching route should connect and return params.');
    }
  }

  public function testDefaultParamValue() {
    Router::connect(':param', array('param' => 'def'));
    $params = Router::parse('');
    $this->assertEquals('def', $params['param']);
  }

  public function testRuleFails() {
    Router::connect(':rule', array(), array('rule' => '\d'));
    Router::connect(':fail');
    $params = Router::parse('string');
    $this->assertEquals('string', $params['fail']);
  }

  public function testRulePasses() {
    Router::connect(':rule', array(), array('rule' => '\d'));
    Router::connect(':fail');
    $params = Router::parse('9');
    $this->assertEquals('9', $params['rule']);
  }

  public function testBlankURIGetsReturnedAsAForwardSlash() {
    Router::connect('');
    $params = Router::parse('');
    $this->assertEquals('/', $params['_uri']);
  }

  public function testURIGetsReturnedWithAForwardSlash() {
    Router::connect('');
    $params = Router::parse('someuri');
    $this->assertEquals('/someuri', $params['_uri']);
  }

  public function testBaseURI() {
    Router::connect('');
    $params = Router::parse('base/test', 'base');
    $this->assertEquals('/test', $params['_uri']);
  }

  public function testFormatIsParsed() {
    Router::connect('');
    $params = Router::parse('uri.json');
    $this->assertEquals('json', $params['_format']);
  }

  public function testQueryIsAParam() {
    Router::connect('');
    $params = Router::parse('uri?test=cool');
    $this->assertEquals('test=cool', $params['_query']);
  }

  public function testQueryIsParsed() {
    Router::connect('');
    $params = Router::parse('uri?test=cool');
    $this->assertEquals('cool', $params['test']);
  }

  public function testExtraIsReturned() {
    Router::connect('');
    $params = Router::parse('extra/stuff');
    $this->assertEquals('extra/stuff', $params['_extra']);
  }

  public function testLayoutIsDefinedAs0Or1() {

  }

}