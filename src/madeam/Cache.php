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
class Cache {

  public static $path = false;

  public static $openCaches = array();
  
  public static $memory;
  
  public static function __init__() {
    
  }

  /**
   * Enter description here...
   *
   * @param string $id
   * @param int $lifeTime
   * @return unknown
   * @author Joshua Davey
   */
  public static function read($id, $lifeTime = 0, $checkCache = true) {
    
    // check registry first
    if (isset(self::$memory[$id])) {
      return self::$memory[$id];
    }
    
    if ($checkCache === false) { return false; }

    // set file name
    $file = self::$path . $id;
    if (file_exists($file)) {
      if ((time() - filemtime($file)) <= $lifeTime || $lifeTime == - 1) {
        // get cache from file and unserialize
        return unserialize(file_get_contents($file));
      } else {
        return false;
      }
    } else {
      return false;
    }
  }

  /**
   * Enter description here...
   *
   * @param string $id
   * @param string $value
   * @param boolean $storeInRegistry
   * @return unknown
   * @author Joshua Davey
   */
  public static function save($id, $value, $storeInMemory = false) {
    // store in registry
    if ($storeInMemory === true) {
      self::$memory[$id] = $value;
    }

    // set file name
    $file = self::$path . $id;

    // save serialization to file
    file_put_contents($file, serialize($value));
  }

  /**
   * Enter description here...
   *
   * @param string $id
   * @param int $lifeTime
   * @return unknown
   * @author Joshua Davey
   */
  public static function start($id, $lifeTime = 0) {
    // check if inline cache is enabled
    if (madeam\Config::get('cache_inline') === false) {
      return false;
    }

    if (! $cache = self::read($id, $lifeTime)) {
      ob_start();
      self::$openCaches[] = $id;
      return false;
    } else {
      echo $cache;
      return true;
    }
  }

  /**
   * Enter description here...
   *
   * @return unknown
   * @author Joshua Davey
   */
  public static function stop() {
    // check if inline cache is enabled
    if (madeam\Config::get('cache_inline') === false) {
      return false;
    }

    $id = array_shift(self::$openCaches);
    $cache = ob_get_contents();
    self::save($id, $cache);
    //ob_clean();
    ob_end_clean();
    echo $cache;
  }

  /**
   * Enter description here...
   *
   * @param string $id
   * @author Joshua Davey
   */
  public static function clear($id) {
    // set file name
    $file = self::$path . $id;
    
    // remove from memory
    unset($memory[$id]);

    // save serialization to file
    file_put_contents($file, null);
  }

  /**
   * Check to see if a cache exists
   *
   * @param string $id
   * @return boolean
   * @author Joshua Davey
   */
  public static function check($id) {
    // check registry first
    if (isset(self::$memory[$id])) {
      return true;
    }

    // check file system cache
    $file = self::$path . $id;
    if (file_exists($file)) {
      return true;
    }
    return false;
  }
}
