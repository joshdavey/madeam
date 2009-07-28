<?php
namespace madeam;
/**
 * Madeam PHP Framework <http://madeam.com>
 * Copyright (c)  2009, Joshua Davey
 *                202-212 Adeliade St. W, Toronto, Ontario, Canada
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright    Copyright (c) 2009, Joshua Davey
 * @link        http://www.madeam.com
 * @package      madeam
 * @license      http://www.opensource.org/licenses/mit-license.php The MIT License
 * @author      Joshua Davey
 */
class Config {

  /**
   * undocumented 
   *
   * @author Joshua Davey
   */  
  public static $heap;

  /**
   * undocumented function
   *
   * @return void
   * @author Joshua Davey
   */  
  public static function set() {
    if (func_num_args() == 2) {
      self::$heap[func_get_arg(0)] = func_get_arg(1);
    } else {
      self::$heap = func_get_arg(0);
    }
  }

  /**
   * undocumented function
   *
   * @param string $name 
   * @return void
   * @author Joshua Davey
   */  
  public static function get($name) {
    return self::$heap[$name];
  }
  
  /**
   * undocumented function
   *
   * @param string $name 
   * @return void
   * @author Joshua Davey
   */  
  public static function exists($name) {
    if (isset(self::$heap[$name])) {
      return true;
    } else {
      return false;
    }
  }
  
}