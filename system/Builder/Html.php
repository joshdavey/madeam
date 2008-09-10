<?php

class Builder_Html extends Madeam_Builder {

  public function buildView() {
    ob_start();
    extract($this->data);
    $controller =& $this->controller;
    include($this->view);
    $this->output = ob_get_contents();
    //ob_clean();
    ob_end_clean();
    return $this->output;
  }

  public function missingView() {
    throw new Madeam_Exception_MissingView('Missing View <strong>' . substr($this->view, strlen(PATH_TO_VIEW)) . '</strong>');
  }

  public function buildLayout($layoutFile) {
    ob_start();
    extract($this->data);
    $controller =& $this->controller;
    $controller->content = $this->output;
    include ($layoutFile);
    $this->output = ob_get_contents();
    ob_clean();
    return $this->output;
  }

  public function missingLayout($layoutFile) {
    throw new Madeam_Exception_MissingView('Missing View <strong>' . substr($layoutFile, strlen(PATH_TO_VIEW)) . '</strong>');
  }

}