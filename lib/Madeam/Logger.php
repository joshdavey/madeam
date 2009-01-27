<?php

if (Madeam_Logger::$path === false) {
  Madeam_Logger::$path  = Madeam_Framework::$pathToEtc . 'log' . DS;
}

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
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
class Madeam_Logger {

  public $logs = array();

  public static $path = false;
  
  public static $fileName = 'Y-m';

  /**
   * Stores this registry's instance
   *
   * @var boolean/madeam_registry object
   */
  private static $_instance = array();

  public static function setInstance($instance) {
    if (! self::$_instance) {
      self::$_instance = $instance;
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
    if (! self::$_instance) {
      self::$_instance = new Madeam_Logger(array());
    }
    return self::$_instance;
  }


  public function log($message, $lvl = 25) {
    $registry = self::getInstance();

    $date = date("d-m-o H:i:s");

    $registry->logs[] = array('message' => $message, 'datetime' => $date);
  }

  public function __destruct() {
    $registry = self::getInstance();

    $requestLog = null;
    foreach ($registry->logs as $log) {
      $requestLog .= $log['datetime'] . ' | ' . $log['message'] . "\n";
    }

    if (Madeam_Config::get('enable_logger') == true && $requestLog != null) {
      $requestLog .= '------------------' . "\n";
      file_put_contents(self::$path . date(self::$fileName) . '.txt',  $requestLog, FILE_APPEND | LOCK_EX);
    }
  }

}