<?php
// Include application's bootstrap
// require PATH_TO_APP . 'Config' . DS . 'bootstrap.php';

// Application's front controller
class Controller_App extends Madeam_Controller {
  
  public function beforeFilter() {
    $this->title = 'Powered By Madeam PHP MVC Framework';
  }

}
