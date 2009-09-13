<?php
namespace madeam;
require_once 'Bootstrap.php';
class ViewTest extends \PHPUnit_Framework_TestCase {
  
  /**
   * 
   */
  public function setUp() {
    View::$path = TESTS_MADEAM_PROJECT_DIRECTORY . 'app/views' . DIRECTORY_SEPARATOR;
  }
  
  /**
   * 
   */
  public function tearDown() {
    // reset view path
    View::$path = false;
  }
  
  /**
   * 
   * <File tests/_partial.html>
   * Partial
   * </File>
   */
  public function testRenderPartial() {
    $return = View::render(array('template' => 'tests/_partial', 'format' => 'html'));
    $this->assertEquals('Partial', $return);
  }
  
  /**
   * 
   * <File tests/_partial-data.html>
   * Partial Data is <?php echo $data; ?>
   * </File>
   */
  public function testRenderPartialWithData() {
    $return = View::render(array('template' => 'tests/_partial-data', 'data' => array('data' => 'True'), 'format' => 'html'));
    $this->assertEquals('Partial Data is True', $return);
  }
  
  /**
   * 
   * <File tests/data.html>
   * Data is <?php echo $data; ?>
   * </File>
   */
  public function testRenderData() {
    $return = View::render(array('template' => 'tests/data', 'data' => array('data' => 'True'), 'format' => 'html'));
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
    $return = View::render(array('template' => 'tests/action', 'format' => 'html'));
    $this->assertEquals('Action View', $return);
  }
  
  /**
   * 
   * <File tests/view.html>
   * View
   * </File>
   */
  public function testRenderViewWhenActionMethodMissing() {
    $return = View::render(array('template' => 'tests/view', 'format' => 'html'));
    $this->assertEquals('View', $return);
  }
  
  /**
   * 
   * <File tests/data.html>
   * Data is <?php echo $data; ?>
   * </File>
   */
  public function testRenderViewData() {
    $return = View::render(array('template' => 'tests/data', 'data' => array('data' => 'True'), 'format' => 'html'));
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
    $return = View::render(array('template' => 'tests/data', 'data' => array(), 'format' => 'html'));
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
    $return = View::render(array('template' => 'tests/data', 'format' => 'html'));
    $this->assertEquals('Data is Member', $return);
    
    $this->controller->data = 'Member';
    $return = View::render(array('template' => 'tests/data', 'data' => array('data' => 'Implicit'), 'format' => 'html'));
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
    $return = View::render(array('template' => 'tests/view', 'layout' => array('layout'), 'format' => 'html'));
    $this->assertEquals('Layout View', $return);
  }
  
  /**
   * 
   * <File tests/view.html>
   * View
   * </File>
   */
  public function testRenderNoLayout() {
    $return = View::render(array('template' => 'tests/view', 'format' => 'html'));
    $this->assertEquals('View', $return);
  }
  
  /**
   * 
   * <File tests/view.html>
   * View
   * </File>
   */
  public function testRenderAction() {
    $return = View::render(array('action' => 'view'));
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
    $return = View::render(array('action' => 'data'));
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
    $return = View::render(array('action' => 'view', 'controller' => 'tests'));
    $this->assertEquals('View', $return);
  }
  
}