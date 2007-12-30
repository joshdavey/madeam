<?php
class Madeam_Validate {
  
  public static function isEmail($string) {
    return false;
  }
  
  public static function isLength($string, $min, $max) {
    return false;
  }
  
  public static function isPattern($string, $regex) {
    return false;
  }
  
  public static function isNotEmpty($string) {
    if ($string != null) { return true; }
    return false;
  }
  
  public static function isDate($string) {
    return false;
  }
  
  public static function isISBN($string) {
    return false;
  }
  
}
?>