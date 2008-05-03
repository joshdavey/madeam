<?php
class Controller_Error extends Madeam_Controller {

  protected $layout = 'error';

  protected function beforeAction() {
    $this->page_title = 'Oops. There was an error.';
  }

}
?>