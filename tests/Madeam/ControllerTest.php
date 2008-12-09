<?php
require_once '../Bootstrap.php';
class TestController extends PHPUnit_Framework_TestCase {
  
  public function setUp() {
    $this->_params = array(
      '_controller' => 'tests',
      '_action'     => 'index',
      '_layout'     => 1,
      '_method'     => 'get'
    );
    
    $this->_controller = new Controller_Tests($this->_params);
  }
  
  
}