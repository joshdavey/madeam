<?php
require_once 'Bootstrap.php';

class Controller_Tests extends Madeam_Controller {
  
  public function actionAction() {
    
  }
  
}

class Madeam_ControllerTest extends PHPUnit_Framework_TestCase {
  
  protected $params;
  protected $requiredParams;
  protected $controller;
  
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
    
    
    // define required params
    $this->requiredParams = array(
      '_controller' => true,
      '_action'     => true,
      '_layout'     => true,
      '_method'     => true,
      '_format'     => true,
      '_ajax'       => true
    );
    
    // set view directory
    Madeam_Controller::$viewDirectory = Madeam_Framework::$pathToTests . 'Madeam' . DS . 'View' . DS;
    
    // create controller instance
    $this->controller = new Controller_Tests($this->params);
  }
  
  /**
   * 
   */
  public function testRequiredParams() {    
    try {
      // all required params present
      $controller = new Controller_Tests($this->requiredParams);
    } catch (Madeam_Controller_Exception_MissingRequiredParams $e) {
      $this->assertTrue(false);
    }
    
    $this->assertTrue(true);
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
   * <File>
   */
  public function testRenderView() {
    $return = $this->controller->render(array('view' => 'tests/view'));
    $this->assertEquals('View', $return);
  }
  
  /**
   * 
   * <File tests/view-data.html>
   * View Data is <?php echo $data; ?>
   * <File>
   */
  public function testRenderViewData() {
    $return = $this->controller->render(array('view' => 'tests/view-data', 'data' => array('data' => 'True')));
    $this->assertEquals('View Data is True', $return);
  }
  
  /**
   * 
   * <File tests/action.html>
   * Action
   * <File>
   */
  public function testRenderAction() {
    //$return = $this->controller->render(array('action' => 'action'));
    $return = null;
    $this->assertEquals('Action', $return);
  }
  
  /**
   * 
   * <File tests/action.html>
   * Action Data is <?php echo $data; ?>
   * <File>
   */
  public function testRenderActionData() {
    //$return = $this->controller->render(array('action' => 'action'));
    $return = null;
    $this->assertEquals('Action Data is True', $return, 'Message');
  }
  
  /**
   * 
   * <File tests/controller-action.html>
   * Controller Action
   * <File>
   */
  public function testRenderControllerAction() {
    //$return = $this->controller->render(array('controller' => 'tests', 'action' => 'view'));
    $return = null;
    $this->assertEquals('Controller Action', $return);
  }
  
}