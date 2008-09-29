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
  
  public function buildView($view = null, $data = array()) {
    ob_start();
    extract($data);
    $controller =& $this->controller;
    include($view);
    $output = ob_get_contents();
    //ob_clean();
    ob_end_clean();
    return $output;
  }

  public function missingView($view) {
    throw new Madeam_Exception_MissingView('Missing View <strong>' . substr($view, strlen(PATH_TO_VIEW)) . '</strong>');
  }

  public function buildLayout($layoutFile, $data, $content) {
    ob_start();
    extract($data);
    $controller =& $this->controller;
    $controller->content = $content;
    include ($layoutFile);
    $output = ob_get_contents();
    ob_clean();
    return $output;
  }

  public function missingLayout($layoutFile) {
    throw new Madeam_Exception_MissingView('Missing Layout <strong>' . substr($layoutFile, strlen(PATH_TO_VIEW)) . '</strong>');
  }

}