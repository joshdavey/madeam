<?php
class controller_index extends controller_app {

  function index() {
    $this->Posts->find_all();
  }
}
?>