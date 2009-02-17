<?php
require_once 'tests' . DIRECTORY_SEPARATOR . 'Bootstrap.php';

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
    
    Madeam_Controller::$viewPath = TESTS_MADEAM_APP_DIRECTORY . 'View' . DIRECTORY_SEPARATOR;
    
    // create controller instance
    $this->controller = new Controller_Tests($this->params);
  }
  
  /**
   * 
   */
  public function tearDown() {
    // reset view path
    Madeam_Controller::$viewPath = false;
    
    // reset controller
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
   *  public function actionAction() {
   *    
   *  }
   * 
   * <File tests/view.html>
   * Action View
   * </File>
   */
  public function testRenderViewWithActionMethod() {
    $return = $this->controller->render(array('view' => 'tests/action'));
    $this->assertEquals('Action View', $return);
  }
  
  /**
   * 
   * <File tests/view.html>
   * View
   * </File>
   */
  public function testRenderViewWhenActionMethodMissing() {
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
   * When implicitly defining data for a view the controller class's member variables
   * should be passed a long as well.
   * 
   * <File tests/data.html>
   * Data is <?php echo $data; ?>
   * </File>
   */
  public function testRenderMemberDataEvenWhenDataImplicitlyDefined() {
    $this->controller->data = 'Member';
    $return = $this->controller->render(array('view' => 'tests/data', 'data' => array()));
    $this->assertEquals('Data is Member', $return);
  }
  
  /**
   * This tests to make sure that if you implicitly define the data it should over overide
   * whatever you defined in the controller class.
   * 
   * <File tests/data.html>
   * Data is <?php echo $data; ?>
   * </File>
   */
  public function testRenderDataOrder() {
    $this->controller->data = 'Member';
    $return = $this->controller->render(array('view' => 'tests/data'));
    $this->assertEquals('Data is Member', $return);
    
    $this->controller->data = 'Member';
    $return = $this->controller->render(array('view' => 'tests/data', 'data' => array('data' => 'Implicit')));
    $this->assertEquals('Data is Implicit', $return);
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
   *  public function dataAction() {
   *    $this->data = 'True';
   *  }
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
   *  public function viewAction() {
   *    
   *  }
   * 
   * <File tests/view.html>
   * View
   * </File>
   */
  public function testRenderControllerAction() {
    $return = $this->controller->render(array('action' => 'view', 'controller' => 'tests'));
    $this->assertEquals('View', $return);
  }
  
  /**
   * 
   *  public function excludeAction() {
   *    if ($this->exclude == 'False' && $this->include == 'False') {
   *      return 'True';
   *    } else {
   *      return 'False';
   *    }
   *  }
   */
  public function testCallbackDataWhenExcludingActions() {
    $params = array(
      '_controller' => 'tests',
      '_action'     => 'exclude',
      '_format'     => 'html',
      '_method'     => 'get',
      '_ajax'       => 0,
      '_layout'     => 1
    );
    
    $controller = new Controller_Tests($params);
    $return = $controller->process();
    $this->assertEquals('True', $return);
  }
  
  /**
   *  
   * 
   *  public function includeAction() {
   *    if ($this->exclude == 'True' && $this->include == 'True') {
   *      return 'True';
   *    } else {
   *      return 'False';
   *    }
   *  }
   */
  public function testCallbackDataWhenIncludingActions() {
    $params = array(
      '_controller' => 'tests',
      '_action'     => 'include',
      '_format'     => 'html',
      '_method'     => 'get',
      '_ajax'       => 0,
      '_layout'     => 1
    );
    
    $controller = new Controller_Tests($params);
    $return = $controller->process();
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
    
    $controller = new Controller_Tests($this->params);
    $return = $controller->process();
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
    
    $controller = new Controller_Tests($this->params);
    $return = $controller->process();
    $this->assertEquals('{"data":"True"}', $return);
  }
  
  /**
   * 
   *  public function modelAction() {
   *    $this->Test->findAll();
   *  }
   */
  public function testModelsAreNotSerialized() {
    $this->params['_action'] = 'model';
    $this->params['_format'] = 'json';
    
    $controller = new Controller_Tests($this->params);
    $return = $controller->process();
    $this->assertEquals('[]', $return);
  }
  
  /**
   * 
   *  public function modelAction() {
   *    $this->Test->findAll();
   *  }
   */
  public function testModelsAreBeingStoredWhenInitialized() {
    $this->params['_action'] = 'model';
    
    $controller = new Controller_Tests($this->params);
    $controller->process();
    
    if (isset($controller->_models['Test'])) {
      $this->assertTrue(true);
    } else {
      $this->fail('The model should be stored in the _models property when initialized');
    }
  }
  
}