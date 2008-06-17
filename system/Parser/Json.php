<?php

class Parser_Json extends Madeam_Parser {

  public function renderView() {
    $data = $this->getData();
    $this->output = json_encode($data);
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