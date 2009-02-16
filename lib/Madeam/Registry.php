<?php

/**
 * Madeam PHP Framework <http://www.madeam.com/>
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
 */
class Madeam_Registry extends ArrayIterator {

  /**
   * The registry is where all the data is stored to be available globally.
   *
   * @var array
   */
  public $registry = false;

  /**
   * Stores this registry's instance
   *
   * @var boolean/madeam_registry object
   */
  private static $_instance = array();

  public function __construct($registry) {}

  public static function setInstance($instance) {
    if (! Madeam_Registry::$_instance) {
      Madeam_Registry::$_instance = $instance;
    } else {
      throw new Madeam_Exception('Registry instance already exists');
    }
  }

  /**
   * Creates a new instance of the registry if it does not exist.
   * If it does exist then it returns the already existing instance.
   *
   * @return madeam_registry object
   */
  public static function getInstance() {
    if (! Madeam_Registry::$_instance) {
      Madeam_Registry::$_instance = new Madeam_Registry(array());
    }
    return Madeam_Registry::$_instance;
  }

  /**
   * Adds or modifies a an entry in the registry
   *
   * @param string $id
   * @param mixed $value
   */
  public static function set($id, $value) {
    $registry = self::getInstance();
    $registry->registry[$id] = $value;
  }

  /**
   * Returns a registry entry by id
   *
   * @param string $id
   * @return mixed/boolean
   */
  public static function get($id) {
    $registry = self::getInstance();
    if (isset($registry->registry[$id])) {
      return $registry->registry[$id];
    } else {
      return false;
    }
  }

  /**
   * Checks to see if an entry exists by id
   *
   * @param string $id
   * @return boolean
   */
  public static function exists($id) {
    $registry = self::getInstance();
    if (isset($registry->registry[$id])) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * Removes an entry in the registry by id
   *
   * @param string $id
   */
  public static function delete($id) {
    $registry = self::getInstance();
    if (isset($registry->registry[$id])) {
      unset($registry->registry[$id]);
    }
  }
}
