<?php
class ErrorController extends madeam\Controller {

  public $_layout = 'error/error';
  
  public function debugAction($request) {
    $this->layout('error/debug');
    $this->title = 'Debugging';
  }

  public function http404Action() {
    header("HTTP/1.1 404 Not Found");
    $this->title = 'Page not found';
  }

}
