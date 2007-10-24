<?php
// The script file name is the name of the script that includes the bootstrap and runs the framework
if (!defined('SCRIPT_FILENAME')) { define('SCRIPT_FILENAME', basename(__FILE__)); }

// set forein path
if (!defined('FOREIGN_PATH')) { define('FOREIGN_PATH', dirname(__FILE__)); }

// get our bearings
$current_dir = getcwd();

define('CURRENT_DIR', $current_dir . DIRECTORY_SEPARATOR);

// define root application path
if (file_exists('madeam/console/cli.php')) {
  define('ROOT_APP_PATH', CURRENT_DIR);
  // www
} else {
  // location of cli.php (www/madeam)
}

// change the current working directory to our foreign path
// foreign path is anything that executes outside of the public directory
chdir(FOREIGN_PATH);    // www/madeam

// include boostrap and include all of the madeam core files and configurations
require '../../public/bootstrap.php';


// get args from original input
array_shift($_SERVER['argv']);
$args = $_SERVER['argv'];

out("Welcome to the Madeam Console");

while (1) {
  $console  = false;
  $command  = false;    
  
  outhr();
  
  // get list of available consoles
  $consoles = array('new', 'generate');
  
  do {
    // by entering a console name at this point it means they've tried entering one that doesn't exist. 
    // prompt them with an saying please try again.
    if ($console !== false) {
      oute("Sorry the console you've entered does not exist.");
    }
    
    // reset console
    $console = false;
    
    // check to see if the console exists in the args
    if (isset($args[0])) { $console = array_shift($args); }
    
    if ($console == null) {
      // ask them for the name of the console they'd like to use
      outr("Console");
      $console = getc();
    }
    
  } while (!in_array($console, $consoles));
  
  // get list of available commands
  $commands = array('controller', 'view', 'model', 'application');
  
  do {
    // by entering a console name at this point it means they've tried entering one that doesn't exist. 
    // prompt them with an saying please try again.
    if ($command !== false) {
      oute("Sorry the command you've entered does not exist.");
    }
    
    // reset command
    $command = false;
    
    // check to see if the command exists in the args
    if (isset($args[0])) { $command = array_shift($args); }
    
    if ($command == null) {
      // ask them for the name of the console they'd like to use
      outr("Command");
      $command  = getc();
    }    
  } while (!in_array($command, $commands));
  
  // execute commands
  if (execute_console_command($console, $command, $args) === true) {
    out("Success!");
  } else {
    out("Failure");
  }
  
  // unset arguments -- they are only for first time use
  $args = array();
}

function oute($string) {
  out();
  out('* ' . $string);
}

function outr($string) {
  out('  ' . $string . '> ', 0);
}

function outhr() {
  out();
  out('--------------------------');
}

function getc() {
  $command = get();
  if ($command != 'exit') {
    return $command;
  } else {
    exit();
  }
}

exit();

function execute_console_command($console_name, $command_name, $args) {
  $console_name = 'console_' . $console_name;
  $console 	= new $console_name;
	$requires = $console->{'require_' . $command_name};
	
  if (!empty($args)) {
    $params = cli_parse_arguments($args);
  } else {
    $params = array();
  }

	// if the command requires to be in the application's root path then check it.
	// If we aren't in the applicatin's root path then tell the user and exit
	if (in_array($command_name, $console->command_requires_root)) {
		if (!file_exists(PUB_PATH . DS . 'bootstrap.php')) {
			out('Please point Madeam to the root directory of your application.');
			exit();
		}
	}

	return $console->$command_name($params);
}

while ($command != 'exit') {
  $command = get();
  
  if (count($commands) > 1) {
  	$console_name = 'console_' . array_shift($commands);
  	$command = array_shift($commands);
  
  	$console 	= new $console_name;
  	$requires = $console->{'require_' . $command};
  	$params 	= cli_parse_arguments($commands);
  
  	// if the command requires to be in the application's root path then check it.
  	// If we aren't in the applicatin's root path then tell the user and exit
  	if (in_array($command, $console->command_requires_root)) {
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
  	$command = get();
  } else {
  	out("Sorry the command you entered did not register. Please try again");
  }
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