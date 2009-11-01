<?php
namespace madeam;
require_once 'Bootstrap.php';
class ControllerTest extends \PHPUnit_Framework_TestCase {
  
  protected $params;
  protected $controller;
  
  /**
   * 
   */
  public function setUp() {
    
    // define request params
    $this->params = array(
      '_controller' => 'tests',
      '_action'     => 'index',
      '_layout'     => 1,
      '_method'     => 'get',
      '_format'     => 'html',
      '_ajax'       => 0
    );
    
    View::$path = TESTS_MADEAM_PROJECT_DIRECTORY . 'application/views' . DIRECTORY_SEPARATOR;
    
    // create controller instance
    $this->controller = new \TestsController();
  }
  
  /**
   * 
   */
  public function tearDown() {
    // reset view path
    View::$path = false;
    
    // reset controller
    unset($this->controller);
  }
  
  /**
   *  public function paramAction($data) {
   *    return $data;
   *  }
   */
  public function testActionWithNamedParam() {
    $params = $this->params;
    $params['_action'] = 'param';
    $params['data'] = 'True';
    $controller = new \TestsController();
    $return = $controller->process($params);
    $this->assertEquals('True', $return);
  }
  
  /**
   * 
   *  public function serializeAction() {
   *    $this->data = 'True';
   *  }
   */
  public function testSerializationWhenMissingView() {
    $this->params['_action'] = 'serialize';
    $this->params['_format'] = 'json';
    
    $controller = new \TestsController();
    $controller->returns('json');
    $return = $controller->process($this->params);
    $this->assertEquals('{"data":"True"}', $return);
  }
  
  /**
   * 
   *  public function serializeAction() {
   *    $this->data = 'True';
   *  }
   */
  public function testPredefinedClassParametersAreNotSerialized() {
    $this->params['_action'] = 'serialize';
    $this->params['_format'] = 'json';
    
    $controller = new \TestsController();
    $controller->returns('json');
    $return = $controller->process($this->params);
    $this->assertEquals('{"data":"True"}', $return);
  }
  
}