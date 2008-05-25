<?php
class Controller_App extends Madeam_Controller {

  protected $layout = 'master';

  protected function beforeAction() {
    $this->pageTitle = 'Powered By Madeam PHP MVC Framework';
  }

}
