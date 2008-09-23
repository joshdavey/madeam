<?php

class Madeam_Controller_Builder_Html extends Madeam_Controller_Builder {

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

  public function missingView() {
    throw new Madeam_Exception_MissingView('Missing View <strong>' . substr($this->view, strlen(PATH_TO_VIEW)) . '</strong>');
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
    throw new Madeam_Exception_MissingView('Missing View <strong>' . substr($layoutFile, strlen(PATH_TO_VIEW)) . '</strong>');
  }

}