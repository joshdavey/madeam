<?php
require_once 'Bootstrap.php';

class Madeam_ControllerTest extends PHPUnit_Framework_TestCase {
  
  /**
   * 
   */
  protected $params;
  
  /**
   * 
   */
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
    
    // create controller instance
    $this->controller = new Controller_Tests($this->params);
  }
  
  /**
   * 
   */
  public function tearDown() {
    unset($this->controller);
  }
  
  /**
   * 
   * <File tests/_partial.html>
   * Partial
   * </File>
   */
  public function testRenderPartial() {
    $return = $this->controller->render(array('partial' => 'tests/_partial'));
    $this->assertEquals('Partial', $return);
  }
  
  /**
   * 
   * <File tests/_partial-data.html>
   * Partial Data is <?php echo $data; ?>
   * </File>
   */
  public function testRenderPartialWithData() {
    $return = $this->controller->render(array('partial' => 'tests/_partial-data', 'data' => array('data' => 'True')));
    $this->assertEquals('Partial Data is True', $return);
  }
  
  /**
   * 
   * <File tests/data.html>
   * Data is <?php echo $data; ?>
   * </File>
   */
  public function testRenderData() {
    $return = $this->controller->render(array('view' => 'tests/data', 'data' => array('data' => 'True')));
    $this->assertEquals('Data is True', $return);
  }
  
  /**
   * 
   * <File tests/view.html>
   * View
   * </File>
   */
  public function testRenderView() {
    $return = $this->controller->render(array('view' => 'tests/view'));
    $this->assertEquals('View', $return);
  }
  
  /**
   * 
   * <File tests/data.html>
   * Data is <?php echo $data; ?>
   * </File>
   */
  public function testRenderViewData() {
    $return = $this->controller->render(array('view' => 'tests/data', 'data' => array('data' => 'True')));
    $this->assertEquals('Data is True', $return);
  }
  
  /**
   * 
   * <File tests/view.html>
   * View
   * </File>
   * 
   * <File layout.layout.html>
   * Layout <?php echo $_content; ?>
   * </File>
   */
  public function testRenderLayout() {
    $return = $this->controller->render(array('view' => 'tests/view', 'layout' => 'layout'));
    $this->assertEquals('Layout View', $return);
  }
  
  /**
   * 
   * <File tests/view.html>
   * View
   * </File>
   */
  public function testRenderNoLayout() {
    $return = $this->controller->render(array('view' => 'tests/view', 'layout' => false));
    $this->assertEquals('View', $return);
  }
  
  /**
   * 
   * <File tests/view.html>
   * View
   * </File>
   */
  public function testRenderAction() {
    $return = $this->controller->render(array('action' => 'view'));
    $this->assertEquals('View', $return);
  }
  
  /**
   * 
   * <File tests/data.html>
   * Data is <?php echo $data; ?>
   * </File>
   */
  public function testRenderActionData() {
    $return = $this->controller->render(array('action' => 'data'));
    $this->assertEquals('Data is True', $return);
  }
  
  /**
   * 
   * <File tests/view.html>
   * View
   * </File>
   */
  public function testRenderControllerAction() {
    $return = $this->controller->render(array('action' => 'view', 'controller' => 'tests'));
    $this->assertEquals('View', $return);
  }
  
}