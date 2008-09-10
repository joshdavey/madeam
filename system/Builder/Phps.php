<?php

class Builder_Phps extends Madeam_Builder {

  public function buildView() {
    $this->output = serialize($this->data);
    return $this->output;
  }

  public function missingView() {
    $this->buildView();
    return;
  }

  public function buildLayout() {
    return $this->output;
  }

  public function missingLayout() {
    return;
  }

}