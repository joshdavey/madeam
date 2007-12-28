<?php
// The script file name is the name of the script that includes the bootstrap and runs the framework
if (!defined('SCRIPT_FILENAME')) { define('SCRIPT_FILENAME', basename(__FILE__)); }

// set forein path
if (!defined('FOREIGN_PATH')) { define('FOREIGN_PATH', dirname(__FILE__)); }

// really weird bug when I installed zend core made SERVER_NAME inaccessible and threw a notice error
// this is a simple hack to fix that
if (!isset($_SERVER['SERVER_NAME'])) { $_SERVER['SERVER_NAME'] = 'localhost'; }

// get our bearings
$current_dir = getcwd();

define('CURRENT_DIR', $current_dir . DIRECTORY_SEPARATOR);

// define root application path
if (file_exists('madeam/console/cli.php')) {
  define('PROJECT_PATH', CURRENT_DIR);
  // www
} else {
  // location of cli.php (www/madeam)
}

// change the current working directory to our foreign path
// foreign path is anything that executes outside of the public directory
chdir(FOREIGN_PATH);    // www/madeam

// include boostrap and include all of the madeam core files and configurations
require '../bootstrap.php';

$console = new madeam_console;
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