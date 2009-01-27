<?php

class Controller_Tests extends Madeam_Controller {
  
  public $include = 'False';
  public $exclude = 'False';
  
  public function beforeFilter_Include($include = array('tests/include')) {
    $this->include = 'True';
  }
  
  public function beforeFilter_Exclude($exclude = array('tests/exclude')) {
    $this->exclude = 'True';
  }
  
  /**
   * This action is to test the exclusion feature of the callbacks
   * $this->exclude should not be set by the beforeFilter_Exclude
   * $this->include should not be set by the beforeFilter_Include
   */
  public function excludeAction() {
    if ($this->exclude == 'False' && $this->include == 'False') {
      return 'True';
    } else {
      return 'False';
    }
  }
  
  /**
   * This action is to test the inclusion feature of the callbacks
   * $this->exclude should be set by the beforeFilter_Exclude
   * $this->include should be set by the beforeFilter_Include
   */
  public function includeAction() {
    if ($this->exclude == 'True' && $this->include == 'True') {
      return 'True';
    } else {
      return 'False';
    }
  }
  
  public function returnAction() {
    return 'Action';
  }
  
  public function viewAction() {
    
  }
  
  public function dataAction() {
    $this->data = 'True';
  }
  
  public function serializeAction() {
    $this->data = 'True';
  }
  
  public function paramAction($data) {
    return $data;
  }
  
  public function modelAction() {
    $this->Test->findAll();
  }
  
}