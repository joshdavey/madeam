<?php

// directory splitter
  if (!defined('DS')) { define('DS', DIRECTORY_SEPARATOR); }

// The script file name is the name of the script that includes the bootstrap and runs the framework
  if (!defined('SCRIPT_FILENAME')) { define('SCRIPT_FILENAME', basename(__FILE__)); }

// set forein path
  if (!defined('PATH_TO_SCRIPT')) { define('PATH_TO_SCRIPT', dirname(__FILE__) . DS); }

// really weird bug when I installed zend core made SERVER_NAME inaccessible and threw a notice error
// this is a simple hack to fix that
  if (!isset($_SERVER['SERVER_NAME'])) { $_SERVER['SERVER_NAME'] = 'localhost'; }

// get our bearings
  $currentDir = getcwd();

// the path to the original copy of madeam - this is so we can point to an untouched copy for copying
  define('PATH_TO_MADEAM', dirname(dirname(dirname(PATH_TO_SCRIPT))) . DS);

// set full path of current directory
  define('CURRENT_DIR', $currentDir . DS);


if (file_exists('system/Madeam/Console/madeam.php')) {
  define('PATH_TO_PROJECT', CURRENT_DIR);
  define('PATH_TO_PUBLIC', PATH_TO_PROJECT . 'public' . DS);

  // the console is currently relative to a project
  // project specific scripts can be used
  define('IS_RELATIVE_TO_PROJECT', 1);
} else {
  // the console is pointed outside of any projects
  // project specific scripts cannot be used
  define('IS_RELATIVE_TO_PROJECT', 0);
}


// change the current working directory to our foreign path
// foreign path is anything that executes outside of the public directory
  chdir(PATH_TO_SCRIPT);    // www/madeam

// include boostrap and include all of the madeam core files and configurations
  require '../../bootstrap.php';

// initiated console
  $console = new Madeam_Console;
  $console->initialize();


// standard output function
function out($string = null, $newline = 1) {
	if ($newline == 1) {
		fwrite(STDOUT, ' ' . $string . "\n");
	} else {
		fwrite(STDOUT, ' ' . $string);
	}
}

// standard input function
function get() {
	return trim(fgets(STDIN));
}

?>