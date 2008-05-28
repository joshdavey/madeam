<?php
class Controller_Error extends Madeam_Controller {

  protected $layout = 'error';

  public function beforeAction() {
    $this->pageTitle = 'Madeam PHP MVC Framework';
  }

}
