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

echo 'test';
$cli = new madeam_cli;
$cli->initialize();

exit();

// get args from original input
array_shift($_SERVER['argv']);
$args = $_SERVER['argv'];


//while (1) {
  $console  = false;
  $command  = false;

  // get list of available consoles
  $consoles = array('make', 'create');

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

      out('  cache');
      out('  create');
      out('  delete');
      out('  make');
      out();
      outp("console");

      $console = getc();
    }

  } while (!in_array($console, $consoles));


  // get list of available commands
  $commands = array('controller', 'view', 'model', 'app');

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

      out();
      out('  application');
      out('  controller');
      out('  model');
      out('  view');
      out();
      outp("command");

      $command  = getc();
    }
  } while (!in_array($command, $commands));


  // execute commands
  if (execute_console_command($console, $command, $args) === true) {
    out();
    out("Success!");
  } else {
    out();
    out("Aborted");
  }

  // unset arguments -- they are only for first time use
  $args = array();
//}

// error
function oute($string) {
  out();
  out('* ' . $string);
}

// create
function outc($string, $display = true) {
  if ($display === true) {
    out('create ' . $string);
  }
}

// delete
function outd($string, $display = true) {
  if ($display === true) {
    out('delete ' . $string);
  }
}

// info
function outi($string, $display = true) {
  if ($display === true) {
    out('= ' . $string);
  }
}

// param
function outp($string) {
  out('  ' . $string . '> ', 0);
}

// horizontal rule
function outhr() {
  out();
  out('--------------------------');
}

// get command
function getc() {
  $command = get();
  if ($command == 'exit') {
    exit();
  } else {
    return $command;
  }
}

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
			oute('Please point Madeam to the root directory of your application.');
			exit();
		}
	}
  // 360R58
	return $console->$command_name($params);
}

function cli_parse_arguments($commands) {
	$params = array();
	foreach ($commands as $command) {
		$nodes = explode('=', $command);
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