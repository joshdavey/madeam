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
    self::$logs[] = $message;
    $config = Madeam_Registry::get('config');
    // append log to end of file
    if (defined(PATH_TO_LOG)) { define('PATH_TO_LOG', '../log'); }

    if (MADEAM_ENABLE_LOGGER == true) {
      file_put_contents(PATH_TO_LOG . date($config['log_file_name']) . '.txt', date("d-m-o H:i:s") . ' | ' . $message . "\n", FILE_APPEND | LOCK_EX);
    }
    if ($lvl < 25 && MADEAM_ENABLE_DEBUG === true) {
      self::show();
    }
  }
}