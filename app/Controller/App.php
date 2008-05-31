<?php
class Controller_App extends Madeam_Controller {

  public $layout = 'master';
  
  public $beforeFilter_authenticate_except = array('user/login');
  public $beforeFilter_prepare = array();

  public function prepare() {
    $this->pageTitle = 'Powered By Madeam PHP MVC Framework';
  }
  
  public function authenticate() {
    
  }

}
