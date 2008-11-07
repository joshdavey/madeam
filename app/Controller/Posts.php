<?php

class Controller_Posts extends Controller_App {
  
  public function indexAction() {
    $this->posts = $this->Post->findAll();
  }
  
}