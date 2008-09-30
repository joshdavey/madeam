<?php
class Controller_Error extends Madeam_Controller {

  public function beforeFilter() {
    $this->layout('error');
  }
  
  public function debugAction() {    
    $this->title = 'Debugging';
  }

  public function http404Action() {
    $this->layout(false);
  }

}
