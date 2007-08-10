<?php
$this->view('form');

if ($_POST[$this->represent] && $this->{$this->represent}->save($_POST[$this->represent])) {
  $this->flash('Saved', $this->scaffold_controller . '/index', 1);
} else {
  if (!isset($_POST[$this->represent])) {
    $this->set($this->{$this->represent}->depth(0)->find_one($this->params[$this->scaffold_key]));
  } else {
    $this->set_errors($this->{$this->represent}->get_errors());
  }
}
?>