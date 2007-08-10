<?php
$this->view('form');

if ($_POST[$this->represent]) {
  if ($this->{$this->represent}->save($_POST[$this->represent])) {
    $this->flash('Saved', $this->scaffold_controller . '/index', 1);
  } else {
    // failed to find anything
  }
}    
?>