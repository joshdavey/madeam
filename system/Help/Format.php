<?php
class help_format {

  function date($format, $date) {

    $timestamp = null;
    
    // check current format of date
    
    if        // timestamp
    (preg_match('/[0-9]{9}/', $date)) {
      
    } elseif  // datetime
    (preg_match('//')) {
      
    } elseif  // date
    (preg_match('//')) {
      
    }

    return date($format, time());
  }

  function money($amount, $commas = true) {
    return $amount;
  }

}
?>