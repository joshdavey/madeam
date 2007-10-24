<?php
$this->view('form');

if ($_POST[$this->represent]) {
  if ($this->{$this->represent}->save($_POST[$this->represent])) {
    $this->redirect($this->scaffold_controller . '/show/' . $this->{$this->represent}->insert_id());
  }
}
?>