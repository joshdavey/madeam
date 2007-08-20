<?php
class controller_app extends madeam_controller {
  
  function before_action() {
  	// sessions are used by form error handler
    session_start();
    
    // also consider using the session component
    // $this->session->start();
  }
  
}
?>