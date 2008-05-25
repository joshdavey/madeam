<?php

class Parser_Json extends Madeam_Parser {

  public function renderView() {
    $data = $this->_controller->getData();
    $this->_output = json_encode($data);
    return $this->_output;
  }

  public function missingView() {
    $this->renderView();
    return;
  }

  public function renderLayout() {
    return $this->_output;
  }

  public function missingLayout() {
    return;
  }

}
?>