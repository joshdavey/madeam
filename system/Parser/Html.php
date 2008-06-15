<?php

class Parser_Html extends Madeam_Parser {

  public function renderView() {
    ob_start();
    extract($this->controller->data);
    $controller = $this->controller;
    include ($this->controller->view);
    $this->output = ob_get_contents();
    ob_clean();
    return $this->output;
  }

  public function missingView() {
    throw new Madeam_Exception_MissingView('Missing View <strong>' . substr($this->controller->view, strlen(PATH_TO_VIEW)) . '</strong>');
  }

  public function renderLayout($layoutFile) {
    ob_start();
    extract($this->controller->data);
    $controller = $this->controller;
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