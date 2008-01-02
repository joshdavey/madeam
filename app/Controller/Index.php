<?php
class Controller_Index extends Controller_App {

  public function index() {
    // welcome to the Index Controller's index action
    
    $this->post = new Model_Post(32);
    $this->post = new Model_Post($_POST['Post']);
    $this->post = new Model_Post(array('post_title' => 'So cool'));
    
    $this->post->post_title = 'I love this';
    
    $this->post->save();
    
    $this->post = new Model_Post;
    $this->post->findOne(32);
  }

}
?>