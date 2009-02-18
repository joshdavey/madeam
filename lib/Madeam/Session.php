<?php
/**
 * Madeam PHP Framework <http://madeam.com>
 * Copyright (c)  2009, Joshua Davey
 *                202-212 Adeliade St. W, Toronto, Ontario, Canada
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright    Copyright (c) 2009, Joshua Davey
 * @link        http://www.self.com
 * @package      self
 * @version      0.0.6
 * @license      http://www.opensource.org/licenses/mit-license.php The MIT License
 * @author      Joshua Davey
 */
class Madeam_Session {

  public static $driver;
  
  public static $flashDataName = '_flash';
  
  public static $flashLifeName = '_flashData';

  public static function start($sess_id = false) {
    // load session by ID
    if ($sess_id) {
      session_id($sess_id);
    }
    // start session
    if (!isset($_SESSION)) {
      session_start();
      
      // handle flash stuff here...
      if (isset($_SESSION[self::$flashLifeName])) {
        if (--$_SESSION[self::$flashLifeName] == 0) {
          unset($_SESSION[self::$flashLifeName]);
          if (isset($_SESSION[self::$flashDataName])) {
            unset($_SESSION[self::$flashDataName]);
          }
        }
      }
      
    }
  }

  public static function destroy() {
    session_destroy();
  }

  public static function flash($name, $data) {
    self::flashSet($name, $data);
  }

  public static function flashSet($name, $data) {
    if (!isset($_SESSION[self::$flashLifeName])) {
      $_SESSION[self::$flashLifeName] = 1;
    }
    $_SESSION[self::$flashDataName][$name] = $data;
  }

  public static function flashGet($name) {
    if (isset($_SESSION[self::$flashDataName][$name])) {
      return $_SESSION[self::$flashDataName][$name];
    } else {
      return false;
    }
  }

  public static function flashDestroy($name = false) {
    if ($name === false) {
      unset($_SESSION[self::$flashDataName]);
    } else {
      unset($_SESSION[self::$flashDataName][$name]);
    }
  }

  public static function flashLife($pagesToLive = 1) {
    $_SESSION[self::$flashLifeName] = $pagesToLive;
  }

  public static function set($name, $value) {
    $_SESSION[$name] = $value;
  }

  public static function get($name) {
    if (isset($_SESSION[$name])) {
      return $_SESSION[$name];
    } else {
      return false;
    }
  }
}
