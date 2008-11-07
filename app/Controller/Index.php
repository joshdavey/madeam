<?php
class Controller_Index extends Controller_App {

  public function indexAction() {
    $this->posts = $this->Post->findAll();
  }
  
}
