<?php
$this->{$this->represent}->delete($this->params[$this->scaffold_key]);
$this->flash('The ' . madeamInflector::singalize($this->represent) . ' has been removed.', $this->scaffold_controller, 3);
?>