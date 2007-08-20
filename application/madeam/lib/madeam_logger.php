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
 * @version			0.0.4
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 * @author      Joshua Davey
 */

class madeam_logger {
  public static $logs = array();

  public static function log($message, $lvl = 25) {
    self::$logs[] = $message;

    // append log to end of file
		if (defined(LOG_PATH)) { define('LOG_PATH', '../log'); }
    file_put_contents(LOG_PATH . date(LOG_FORMAT) . '.txt', $message . "\n", FILE_APPEND | LOCK_EX);

    if ($lvl < 25) {
      self::show();
    }
  }  

  public static function query($query) {
    

  }

  
  public static function system_error($msg) {
    

  }

  
  public static function fatal_error($msg) {
    

  }

  

  public static function user_error($msg) {
    

  }



  public static function show() {
    echo
    '<style>
    h1 { margin: 0; padding: 0; }
    .info { background: #eee; padding: 6px; border: 1px solid #ccc;}
    </style>';
    echo '<div style="border: solid 1px #f00; padding: 10px; background: #ddd; text-align: left;">';
      echo '<div id="errors" class="info">';
        //echo '<h1>Errors/Logs - ' . apache_get_version() . '</h1>';
        echo '<hr />';
        echo '<ul>';
          foreach(self::$logs as $log) {
            echo "<li>$log</li>";
          }
        echo '</ul>';
      echo '</div>';
      echo '<pre id="backtrace" class="info">';
        echo '<h1>Backtrace</h1>';
        echo '<hr />';
        debug_print_backtrace();
      echo '</pre>';
      echo '<div id="errors" class="info">';
        echo '<h1>Params</h1>';
        echo '<hr />';
        echo '<table>';
          foreach(madeam::params() as $name => $value) {
            echo "<tr>";
            echo "<td>$name</td>";
            echo "<td> = $value </td>";
            echo "</tr>";
          }
        echo '</table>';
      echo '</div>';
        //var_dump(debug_backtrace());
      echo REL_PATH;
    echo '</div>';

    exit();
  }

}