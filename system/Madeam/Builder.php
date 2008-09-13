<?php
class Madeam_Builder {

  public $controller;
  public $data        = array();
  public $collection  = array();
  public $output      = null;
  public $layout      = array();
  public $view        = null;

  public function __construct(&$controller) {
    $this->controller = $controller;
  }
  
  public function buildPartial() {
    $output = null;
    
    foreach ($this->collection as $data) {
      $this->data = $data;
      $output .= $this->buildView();
    }
    
    $this->output = $output;
    
    return $this->output;
  }
  
  public function buildLayouts() {
    foreach ($this->layout as $layoutFile) {
      if ($layoutFile) {
        if (file_exists($layoutFile)) {
          // include layout if it exists
          $this->buildLayout($layoutFile);
        } else {
          $this->missingLayout($layoutFile);
        }
      }
    }
  }

}