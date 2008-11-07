<?php
// Include application's bootstrap
 require PATH_TO_APP . 'Config' . DS . 'bootstrap.php';
 require PATH_TO_SYSTEM . 'Madeam/Model.php';
 require PATH_TO_SYSTEM . 'Madeam/Model/ActiveRecord.php';

// Application's front controller
class Controller_App extends Madeam_Controller {
  
  public function beforeFilter() {
    $this->title = 'Powered By Madeam PHP MVC Framework';
  }

}
