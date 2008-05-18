<?php
class Controller_Error extends Madeam_Controller {

  protected $layout = 'error';

  protected function beforeAction() {
    $this->_pageTitle = 'Oops. There was an error.';
  }

}
