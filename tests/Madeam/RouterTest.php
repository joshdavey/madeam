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
  
  
  /**
   * undocumented 
   *
   * @author Joshua Davey
   */
  public function testConnectionOrder() {
    Madeam_Router::connect(':one');
    Madeam_Router::connect(':two');
    
    $params = Madeam_Router::parse('url', $this->baseUri, array());
    
    if (isset($params['one'])) {
      $this->assertTrue(true);
    } else {
      $this->fail('The first matching route should connect and return params.');
    }
  }
  
  public function testDefaultValue() {
    Madeam_Router::connect(':param', array('param' => 'def'));
    $params = Madeam_Router::parse('', $this->baseUri, array());
    $this->assertEquals('def', $params['param']);
  }
  
  /**
   * 
   */
  public function testBlankRoute() {
    Madeam_Router::connect('');
    $this->markTestIncomplete();
  }
  
  /**
   * 
   */
  public function testParser() {    
    Madeam_Router::connect(':_controller/:_action/:id');
        
    $params = Madeam_Router::parse('controller/action/id', $this->baseUri, $this->defaults);
    asort($params);
    
    $expected = array(
      '_controller' => 'controller',
      '_action'     => 'action',
      '_format'     => 'html',
      '_layout'     => 1,
      '_method'     => 'get',
      'id'          => 'id'
    );    
    asort($expected);
    
    $this->assertEquals($expected, $params);
  }

}