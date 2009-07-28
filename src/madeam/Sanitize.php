<?php
namespace madeam;
class Sanitize {
  
  public static function html($string) {
    
  }
  
  public static function escape($string) {
    if (is_integer($string)) {
      return $string;
    } else {
      return "'" . addslashes($string) . "'";
    }
  }
  
}