<?php
class Madeam_Parser {

  protected $controller;
  protected $output;

  public function __construct($controller) {
    $this->controller = $controller;
  }

  public function getOutput() {
    return $this->output;
  }

}
?>