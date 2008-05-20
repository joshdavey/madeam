<?php
class Controller_Index extends Controller_App {

  public function index() {
    // welcome to the Index Controller's index action
    //$this->Post->where('fail')->findAll();
    $all = $this->Post->findAll();
    $all = $this->Post->where('fail')->findAll();
  }

}
