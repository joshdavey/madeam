<?php
class Controller_Index extends Controller_App {

  public function index() {
    // welcome to the Index Controller's index action
    
    $post = new Model_Post;
    $this->posts = $post->findAll();
    
    test($this->posts);
    
    test($this->posts[0]->post_title);
    
    $post = new Model_Post(array('post_title', 'post_body', 'post_user'));
    
    $dbh = new PDO("mysql:host=localhost;dbname=madeam", 'root', null);
    
    $sql = "SELECT * FROM posts";
    $stmt = $dbh->query($sql);
    
    $obj = $stmt->fetchAll(PDO::FETCH_CLASS);
    
    foreach ($obj as $row) {
      test($row);
    }
  }

}
?>