<?php
// Application's front controller
class Controller_App extends Madeam_Controller {
  
  public function beforeFilter() {
    $this->title = 'Powered By Madeam PHP Framework';
  }

}
