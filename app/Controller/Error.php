<?php
class Controller_Error extends Madeam_Controller {

  public $layout = 'error';
  
  public $beforeFilter_prepare;
  
  public function prepare() {
    $this->pageTitle = 'Oops.';
  }

}
