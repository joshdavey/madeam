<?php
$this->{$this->represent}->depth(0)->order($this->{$this->represent}->primary_key . " DESC");
$this->set(madeam_inflector::pluralize($this->represent), $this->{$this->represent}->find_all());
?>