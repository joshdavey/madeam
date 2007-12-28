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
class Madeam_Console extends Madeam_CLI {

  public function initialize() {
    array_shift($_SERVER['argv']);
    $args = $_SERVER['argv'];

    $script  = false;
    $command = false;


    // get list of available consoles
    $scripts = array('make', 'create', 'delete', 'cache', 'test');

    do {
      // by entering a console name at this point it means they've tried entering one that doesn't exist.
      // prompt them with an saying please try again.
      if ($script !== false) {
        $this->out_error("Sorry the script you entered does not exist.");
      }

      // reset console
      $script = false;

      // check to see if the console exists in the args
      if (isset($args[0])) { $script = array_shift($args); }

      if ($script == null) {
        // ask them for the script of the console they'd like to use
        $this->out_menu('Scripts', $scripts);

        $script = $this->get_command('script');
      }

    } while (!in_array($script, $scripts));


    // get list of available commands
    $commands = array('controller', 'scaffold', 'view', 'model', 'app');

    do {
      // by entering a console name at this point it means they've tried entering one that doesn't exist.
      // prompt them with an saying please try again.
      if ($command !== false) {
        $this->out_error("Sorry the command you entered does not exist.");
      }

      // reset command
      $command = false;

      // check to see if the command exists in the args
      if (isset($args[0])) { $command = array_shift($args); }

      if ($command == null) {
        $this->out_menu('Commands', $commands);

        $command = $this->get_command('command');
      }
    } while (!in_array($command, $commands));


    // execute commands
    if ($this->scriptCommand($script, $command, $args) === true) {
      out();
      out("Success!");
    } else {
      out();
      out("Aborted");
    }

    // unset arguments -- they are only for first time use
    $args = array();
  }

  protected function scriptCommand($script_name, $command_name, $args) {
    $script_name = 'script_' . $script_name;
    $script 	= new $script_name;
  	$requires = $script->{'require_' . $command_name};

    if (!empty($args)) {
      $params = $this->parseArguments($args);
    } else {
      $params = array();
    }

  	// if the command requires to be in the application's root path then check it.
  	// If we aren't in the applicatin's root path then tell the user and exit
  	if (!in_array($command_name, (array) $script->execute_outside_root)) {
  		if (!file_exists(PUBLIC_PATH . DS . 'bootstrap.php')) {
  			oute('Please point Madeam to the root directory of your application.');
  			exit();
  		}
  	}

  	return $script->$command_name($params);
  }

  protected function parseArguments($commands) {
  	$params = array();
  	foreach ($commands as $command) {
  		$nodes = explode('=', $command);
  		$name = $nodes[0];
  		$value = $nodes[1];
  		$params[$name] = $value;
  	}
  	return $params;
  }

}
?>