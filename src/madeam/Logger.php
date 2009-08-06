<?php
namespace madeam;

/**
 * Register save as a shutdown function so that the logs are saved.
 */
register_shutdown_function(array('madeam\Logger', 'save'));

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
 */
class Logger {

  public static $logs = array();

  public static $path = false;
  
  public static $fileName = 'Y-m';

  
  public function write($message, $lvl = 25) {
    $date = date("d-m-o H:i:s");
    self::$logs[] = array('message' => $message, 'datetime' => $date);
  }

  public function save() {
    $requestLog = null;
    foreach (self::$logs as $log) {
      $requestLog .= $log['datetime'] . ' | ' . $log['message'] . "\n";
    }

    if (Config::get('enable_logger') == true && $requestLog != null) {
      $requestLog .= '------------------' . "\n";
      file_put_contents(self::$path . date(self::$fileName) . '.txt',  $requestLog, FILE_APPEND | LOCK_EX);
    }
  }

}