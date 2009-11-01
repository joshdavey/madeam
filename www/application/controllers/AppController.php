<?php
// Application's front controller
class AppController extends madeam\Controller {
  
  protected $beforeAction_setup;
  
  protected function setup() {
    $this->title = 'Powered By Madeam PHP Framework';
  }

}
