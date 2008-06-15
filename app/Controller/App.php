<?php
class Controller_App extends Madeam_Controller {

  public $layout = 'master';

  public function beforeFilter() {
    $this->page_title = 'Powered By Madeam PHP MVC Framework';
  }

}
