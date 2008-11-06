<?php
require_once '../system/bootstrap.php';

class Controller_Tests extends Madeam_Controller {}


class DescribeNewController extends PHPSpec_Context {
  
  private $_controller = false;
  
  public function beforeAll() {
    $this->_params = array(
      '_controller' => 'tests',
      '_action'     => 'index',
      '_layout'     => 1,
      '_method'     => 'get'
    );
    
    $this->_controller = new Controller_Tests($this->_params);
  }
  
  public function itShouldStoreLayoutsAsArray() {
    $this->spec(is_array($this->_controller->_layout))->should->beTrue();
  }
  
  public function itShouldHaveControllerParam() {
    $this->spec($this->_controller->params)->should->beTrue();
  }
  
}