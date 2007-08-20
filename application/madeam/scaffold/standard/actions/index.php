<?php
$this->{$this->represent}->depth(0)->order($this->{$this->represent}->primary_key . " DESC");
$this->set($this->{$this->represent}->find_all());
?>