<?php
// Application's front controller
class AppController extends madeam\Controller {
  
  public function beforeFilter() {
    $this->title = 'Powered By Madeam PHP Framework';
  }

}
