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
    if (defined(PATH_TO_LOG)) {
      define('PATH_TO_LOG', '../log');
    }
    //test($message);
    if (MADEAM_ENABLE_LOGGER === true) {
      file_put_contents(PATH_TO_LOG . date($config['log_file_name']) . '.txt', date("d-m-o H:i:s") . ' | ' . $message . "\n", FILE_APPEND | LOCK_EX);
    }
    if ($lvl < 25 && MADEAM_ENABLE_DEBUG === true) {
      self::show();
    }
  }

  public static function show() {
    $helpful_reminders = array("Don't worry, be happy. It could be a lot worse.", "Oops. Did someone make a boo boo?", "You should really fix this.", "Just blame Josh Davey.", "It wasn't me.", "This is the last time I trust open source software.");
    $reminder = rand(0, count($helpful_reminders) - 1);
    echo '<style>
    h1 { margin: 0; padding: 0; font-size: 12pt; }
    .info { background: #f8f8f8; padding: 6px; border: 1px solid #fff; margin: 2px; }
    .error { font-family: verdana, "Lucida Grande", arial, helvetica, sans-serif; font-size: 10pt; padding: 8px; background: #a32108; }
    hr { height: 2px; background: #eee; margin: 2px; border: none; }
    </style>';
    echo '<div class="error">';
    echo '<h1 style="color: #fff;">' . $helpful_reminders[$reminder] . '</h1>';
    echo '<div style=" text-align: left;">';
    echo '<div id="errors" class="info">';
    echo '<h1>Errors/Logs - ' . /*apache_get_version() . */ '</h1>';
    echo '<hr />';
    echo '<ul>';
    foreach (self::$logs as $log) {
      echo "<li>$log</li>";
    }
    echo '</ul>';
    echo '</div>';
    echo '<div class="info">';
    echo '<h1>Backtrace</h1>';
    echo '<hr />';
    echo '<pre id="backtrace">';
    debug_print_backtrace();
    echo '</pre>';
    echo '</div>';
    echo '<div class="info">';
    echo '<h1>Params</h1>';
    echo '<hr />';
    echo '<table>';
    foreach (Madeam_Router::parseURI(Madeam_Router::getCurrentURI()) as $name => $value) {
      echo "<tr>";
      echo "<td>$name</td>";
      echo "<td> = $value </td>";
      echo "</tr>";
    }
    echo '</table>';
    echo '</div>';
    //var_dump(debug_backtrace());
    echo PATH_TO_REL;
    echo '</div>';
    echo '</div>';
    echo '<div class="error">';
    echo '<div class="info">';
    echo '<a href="http://madeam.com">Madeam.com</a>';
    echo '<div>';
    echo '<div>';
    exit();
  }
}