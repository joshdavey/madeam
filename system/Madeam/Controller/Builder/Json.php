<?php

class Madeam_Controller_Builder_Json extends Madeam_Controller_Builder {

  public function buildView($view = null, $data = array()) {
    $this->output = json_encode($data);
    return $this->output;
  }

  public function missingView($view, $data) {
    $this->buildView($view, $data);
    return;
  }

  public function buildLayout($layouts, $data, $content) {
    return $content;
  }

  public function missingLayout() {
    return;
  }

}