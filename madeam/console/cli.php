<?php
$current_dir = getcwd();
// The script file name is the name of the script that includes the bootstrap and runs the framework
if (!defined('SCRIPT_FILENAME')) { define('SCRIPT_FILENAME', basename(__FILE__)); }

// set forein path
if (!defined('FOREIGN_PATH')) { define('FOREIGN_PATH', $current_dir); }

// get our bearings
$current_dir = getcwd();

// change the current working directory to our foreign path
// foreign path is anything that executes outside of the public directory
chdir(FOREIGN_PATH);


// include boostrap and include all of the madeam core files and configurations
require_once $current_dir . '/public/bootstrap.php';


define('CURRENT_DIR', $current_dir . DS);

array_shift($_SERVER['argv']);
$commands = $_SERVER['argv'];

if (count($commands) > 1) {
	$console_name = 'console_' . array_shift($commands);
	$command = array_shift($commands);

	$console 	= new $console_name;
	$requires = $console->{'require_' . $command};
	$params 	= cli_parse_arguments($commands);

	// if the command requires to be in the application's root path then check it.
	// If we aren't in the applicatin's root path then tell the user and exit
	if (in_array($command, $console->root_app_path)) {
		if (!file_exists(CURRENT_DIR . DS . 'public' . DS . 'bootstrap.php')) {
			out('Please point Madeam to the root directory of your application.');
			exit();
		}
	}

	if ($console->$command($params) === true) {
    out('Success');
	} else {
	  out('Failed');
	}
} elseif (count($commands) > 0) {
	$console_name = 'console_' . array_shift($commands);
	$console 	= new $console_name;
	out($console->description);
	out();
	out('Commands: ..list..of..commands..');
} else {
	out("Sorry the command you entered did not register. Please try again");
}


function cli_parse_arguments($commands) {
	$params = array();
	foreach ($commands as $command) {
		$nodes = explode(':', $command);
		$name = $nodes[0];
		$value = $nodes[1];
		$params[$name] = $value;
	}
	return $params;
}

// standard output function
function out($string = null, $newline = 1) {
	if ($newline == 1) {
		fwrite(STDOUT, $string . "\n");
	} else {
		fwrite(STDOUT, $string);
	}
}

// standard input function
function get() {
	return trim(fgets(STDIN));
}

?>