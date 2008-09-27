<?php
class Madeam_Controller_Builder {

  public $controller;

  public function __construct(&$controller) {
    $this->controller = $controller;
  }
  
  public function buildPartial($view, $collection) {
    $output = null;
    
    foreach ($collection as $data) {
      $output .= $this->buildView($view, $data);
    }
    
    return $output;
  }
  
  public function buildLayouts($layouts, $data, $content) {
    foreach ($layouts as $layoutFile) {
      if ($layoutFile) {
        if (file_exists($layoutFile)) {
          // include layout if it exists
          $content = $this->buildLayout($layoutFile, $data, $content);
        } else {
          $content = $this->missingLayout($layoutFile);
        }
      }
    }
    
    return $content;
  }
  
  public function buildView($view, $data) {
    return false;
  }

}