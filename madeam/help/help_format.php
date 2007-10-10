<?php
class help_format {
  
  function date($format, $date) {
    
    $timestamp = null;
    
    return date($format, '12-25-2006 05:33:27');
  }
  
  function money($amount) {
    return $amount;
  }
  
}
?>