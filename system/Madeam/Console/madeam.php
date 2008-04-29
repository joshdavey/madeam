<?php
// really weird bug when I installed zend core made SERVER_NAME inaccessible and threw a notice error
// this is a simple hack to fix that
  if (!isset($_SERVER['SERVER_NAME'])) { $_SERVER['SERVER_NAME'] = 'localhost'; }

// get our bearings
  $currentDir = getcwd();

// the path to the original copy of madeam - this is so we can point to an untouched copy for copying
  define('PATH_TO_MADEAM', dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR);

// set full path of current directory
  define('CURRENT_DIR', $currentDir . DS);

if (file_exists('system/Madeam/Console/' . SCRIPT_FILENAME)) {
  define('PATH_TO_PROJECT', CURRENT_DIR);

  // the console is currently relative to a project
  // project specific scripts can be used
  define('IS_RELATIVE_TO_PROJECT', 1);
} else {
  // the console is pointed outside of any projects
  // project specific scripts cannot be used
  define('IS_RELATIVE_TO_PROJECT', 0);
}

// include boostrap and include all of the madeam core files and configurations
  require PATH_TO_MADEAM . 'system/bootstrap.php';

// initiated console
  $console = new Madeam_Console;
  $console->initialize();


?>