<?php
$this->view('form');

if ($_POST[$this->represent] && $this->{$this->represent}->save($_POST[$this->represent])) {
  $this->redirect($this->scaffold_controller . '/show/' . $this->params[$this->scaffold_key]);
} else {
  if (!isset($_POST[$this->represent])) {
    $this->set($this->{$this->represent}->depth(0)->find_one($this->params[$this->scaffold_key]));
  }
}
?>