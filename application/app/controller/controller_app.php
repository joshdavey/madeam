<?php
class controller_app extends madeam_controller {

  function before_action() {
    // sessions are necessary for user errors
    $this->session->start();
  }

}
?>