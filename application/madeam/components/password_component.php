<?php
class passwordComponent extends component {
  var $chars = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 0, 1, 2, 3, 4, 5, 6, 7, 8, 9);
  
  function generate($length = 7) {
    $password = null;
    
    for ($i = 0; $i <= $length; $i++) {
      $password .= $this->chars[rand(0, 35)];
    }
    
    return $password;
  }
  
}
?>