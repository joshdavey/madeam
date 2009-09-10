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
 * @copyright   Copyright (c) 2009, Joshua Davey
 * @link        http://www.self.com
 * @version     2.0.0
 * @license     http://www.opensource.org/licenses/mit-license.php The MIT License
 * @author      Joshua Davey
 */
class Session {

  static public $driver   = 'madeam\session\PHP';  
  static public $flashKey = '_flash';

  static public function start($sessionId = false) {
    $driver = self::$driver;
    $driver::start($sessionId);
    
    // flashy
    $flashes = self::get(self::$flashKey);
    if (!empty($flashes)) {
      foreach ($flashes as $key => $pages) {
        --$flashes[$key];
        if ($pages == 0) {
          unset($flashes[$key]);
          self::delete($key);
        }
      }
      self::set(self::$flashKey, $flashes);
    }
  }

  static public function destroy() {
    $driver = self::$driver;
    $driver::destroy();
  }
  
  static public function key() {
    return sha1($_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR'] . time());
  }

  static public function set($key, $value, $flash = 0) {
    $driver = self::$driver;
    
    if ($flash != 0) {
      $flashes = $driver::get(self::$flashKey);
      $flashes[$key] = $flash + 1;
      $driver::set(self::$flashKey, $flashes);
    }
    
    $driver::set($key, $value);
  }
  
  static public function exists($key) {
    $driver = self::$driver;
    return $driver::exists($key);
  }
  
  static public function delete($key) {
    $driver = self::$driver;
    $driver::delete($key);
  }

  static public function get($key) {
    $driver = self::$driver;
    if ($driver::exists($key)) {
      return $driver::get($key);
    } else {
      return null;
    }
  }

}
