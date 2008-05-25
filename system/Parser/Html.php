<?php

class Parser_Html extends Madeam_Parser {

  public function renderView() {
    ob_start();
    extract($this->_controller->getData());
    $view = $this->_controller;
    include ($this->_controller->getView());
    $this->_output = ob_get_contents();
    ob_clean();
    return $this->_output;
  }

  public function missingView() {
    throw new Madeam_Exception_MissingView('Missing View <strong>' . substr($this->_controller->getView(), strlen(PATH_TO_VIEW)) . '</strong>');
  }

  public function renderLayout($layoutFile) {
    ob_start();
    extract($this->_controller->getData());
    $view = $this->_controller;
    $content_for_layout = $this->_output;
    include ($layoutFile);
    $this->_output = ob_get_contents();
    ob_clean();
    return $this->_output;
  }

  public function missingLayout($layoutFile) {
    throw new Madeam_Exception_MissingView('Missing View <strong>' . substr($layoutFile, strlen(PATH_TO_VIEW)) . '</strong>');
  }

}
?>