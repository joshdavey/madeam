<?php
class Madeam_Parser {

  protected $_controller;
  protected $_output;

  public function __construct($controller) {
    $this->_controller = $controller;
  }

  public function getOutput() {
    return $this->_output;
  }

}
?>