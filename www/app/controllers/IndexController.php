<?php 
class IndexController extends AppController {
  
  protected $beforeAction_authenticate = array('except' => 'sessions');
  protected $beforeAction_setup;
  
  protected $afterRender_tidyhtml;
  protected $afterRender_compress;
  
  protected function authenticate() {
    
  }
  
  protected function compress() {
    
  }
  
  protected function setup() {
    $this->title = 'Awesome';
  }
  
  public function indexAction($request) {
    // welcome to the Index Controller's index action
  }
  
}