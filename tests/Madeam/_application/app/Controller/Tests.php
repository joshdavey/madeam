<?php

class Controller_Tests extends Madeam_Controller {
  
  public function returnAction() {
    return 'Action';
  }
  
  public function viewAction() {
    
  }
  
  public function dataAction() {
    $this->data = 'True';
  }
  
}