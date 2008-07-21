<?php
class Madeam_Parser {

  protected $controller;
  public $output;

  public function __construct($controller) {
    $this->controller = $controller;
  }

  public function getData() {
    $data = array();
    
    foreach ($this->controller->data as $name) {
      $data[$name] = $this->controller->$name;
    }
    
    return $data;
  }

}