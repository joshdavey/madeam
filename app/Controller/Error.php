<?php
class Controller_Error extends Madeam_Controller {

  public $layout = 'error';

  public function debugAction() {
     $this->page_title = 'Debugging';
  }

  public function http404Action() {
    $this->layout(false);
  }

}
