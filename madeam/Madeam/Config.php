<?php
class Madeam_Config {
  
  static $heap;
  
  public static function set($name, $value) {
    self::$heap[$name] = $value;
  }
  
  public static function get($name) {
    return self::$heap[$name];
  }
  
}
?>