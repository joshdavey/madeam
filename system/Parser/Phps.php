<?php

class Parser_Phps extends Madeam_Parser {

  public function renderView() {
    $data = $this->getData();
    $this->output = serialize($data);
    return $this->output;
  }

  public function missingView() {
    $this->renderView();
    return;
  }

  public function renderLayout() {
    return $this->output;
  }

  public function missingLayout() {
    return;
  }

}