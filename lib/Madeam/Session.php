<?php

/**
 * Madeam :  Rapid Development MVC Framework <http://www.madeam.com/>
 * Copyright (c)	2006, Joshua Davey
 *								24 Ridley Gardens, Toronto, Ontario, Canada
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright		Copyright (c) 2006, Joshua Davey
 * @link				http://www.madeam.com
 * @package			madeam
 * @version			0.0.6
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 * @author      Joshua Davey
 */
class Madeam_Session {

	public static $driver;

  public static function start($sess_id = false) {
    // load session by ID
    if ($sess_id) {
      session_id($sess_id);
    }
    // start session
    if (! isset($_SESSION)) {
      session_start();
    }
  }

  public static function destroy() {
    session_destroy();
  }

  public static function flash($name, $data) {
    self::flashSet($name, $data);
  }

  public static function flashSet($name, $data) {
    if (!isset($_SESSION[Madeam::flashLifeName])) {
      $_SESSION[Madeam::flashLifeName] = 1;
    }
    $_SESSION[Madeam::flashDataName][$name] = $data;
  }

  public static function flashGet($name) {
    if (isset($_SESSION[Madeam::flashDataName][$name])) {
      return $_SESSION[Madeam::flashDataName][$name];
    } else {
      return false;
    }
  }

  public static function flashDestroy($name = false) {
    if ($name === false) {
      unset($_SESSION[Madeam::flashDataName]);
    } else {
      unset($_SESSION[Madeam::flashDataName][$name]);
    }
  }

  public static function flashLife($pagesToLive = 1) {
    $_SESSION[Madeam::flashLifeName] = $pagesToLive;
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
