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

  public static $logs = array();

  public static function log($message, $lvl = 25) {
    $config = Madeam_Registry::get('config');

    $date = date("d-m-o H:i:s");

    self::$logs[] = array('message' => $message, 'datetime' => $date);

    if (MADEAM_ENABLE_LOGGER == true) {
      file_put_contents(PATH_TO_LOG . date($config['log_file_name']) . '.txt', $date . ' | ' . $message . "\n", FILE_APPEND | LOCK_EX);
    }
  }
}