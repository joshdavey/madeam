<?php

class Madeam_Controller_Builder_Phps extends Madeam_Controller_Builder {

  public function missingView($view, $data) {
    return serialize($data);
  }

  public function buildLayouts($layouts, $data, $content) {
    return $content;
  }

}