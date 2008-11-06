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
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
class Madeam_Logger {

  private static $logs = array();

  public static $path = false;


  /**
   * Stores this registry's instance
   *
   * @var boolean/madeam_registry object
   */
  private static $_instance = array();

  public static function setInstance($instance) {
    if (! Madeam_Logger::$_instance) {
      Madeam_Logger::$_instance = $instance;
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
    if (! Madeam_Logger::$_instance) {
      Madeam_Logger::$_instance = new Madeam_Logger(array());
    }
    return Madeam_Logger::$_instance;
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
      file_put_contents(self::$path . date(Madeam_Config::get('log_file_name')) . '.txt',  $requestLog, FILE_APPEND | LOCK_EX);
    }
  }

}