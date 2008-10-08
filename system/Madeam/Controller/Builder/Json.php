<?php

class Madeam_Controller_Builder_Json extends Madeam_Controller_Builder {

  public function missingView($view, $data) {
    return json_encode($data);
  }

  public function buildLayouts($layouts, $data, $content) {
    return $content;
  }

}