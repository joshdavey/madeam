<?php
class appController extends controller {
  
  function before_action() {
    $this->session->start();
  }
	
}
?>