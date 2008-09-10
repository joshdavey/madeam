<?php

class Builder_Json extends Madeam_Builder {

  public function buildView() {
    $this->output = json_encode($this->data);
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