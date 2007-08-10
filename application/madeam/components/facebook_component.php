<?php
vendor('facebook/facebook.php');
class facebookComponent extends component {
  var $api_key = 'af224c91bd7e0d268b0ac623b5f2fc62';
  var $secret  = 'b26602ba0b84e8faeebc8af5dd014132';
  
  function __construct(&$controller) {
    parent::__construct($controller);
    $controller->facebook = new facebook($this->api_key, $this->secret);
  }
}
?>