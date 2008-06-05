<?php
class Controller_Error extends Madeam_Controller {

  public $layout = 'error';

  public function debugAction() {
     $this->pageTitle = 'Debugging';
  }

  public function http404Action() {
    $this->pageTitle = 'Debugging';
    $this->layout(false);
  }

}
