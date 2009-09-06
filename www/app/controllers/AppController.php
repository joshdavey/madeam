<?php
// Application's front controller
class AppController extends madeam\Controller {
  
  protected $beforeAction_setup;
  
  protected $afterRender_tidyhtml;
  
  protected function setup() {
    $this->title = 'Powered By Madeam PHP Framework';
  }
  
  protected function tidyhtml() {
    // Specify configuration
    $config = array(
      'indent'         => true,
      'output-xhtml'   => true,
      'wrap'           => 200
    );

    // Tidy
    $tidy = new tidy;
    $tidy->parseString($this->_output, $config, 'utf8');
    $this->_output = $tidy->cleanRepair();
  }

}
