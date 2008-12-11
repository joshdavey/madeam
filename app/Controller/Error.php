<?php
class Controller_Error extends Madeam_Controller {

  public $_layout = 'error';

  public function debugAction() {    
    $this->layout('debug');
    $this->title = 'Debugging';
  }

  public function http404Action() {
    header("HTTP/1.1 404 Not Found");
    $this->title = 'Page not found';
  }

}
