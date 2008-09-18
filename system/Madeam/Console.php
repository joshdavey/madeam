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
class Madeam_Console extends Madeam_Console_CLI {

  public function initialize() {
    array_shift($_SERVER['argv']);
    $args         = $_SERVER['argv'];
    $scriptName   = false;
    $commandName  = false;

    // get list of available consoles
    $scriptNames = array('create', 'delete', 'test', 'migration');

    while(true) {
      // reset console
      $scriptName = false;

      // check to see if the console exists in the args
      if (isset($args[0])) {
        $scriptName = array_shift($args);
      }

      // if the command requires to be in the application's root path then check it.
      // If we aren't in the applicatin's root path then tell the user and exit
      if (!$scriptName != 'make') {
        if (! file_exists(PATH_TO_SYSTEM . 'bootstrap.php')) {
          Madeam_Console_CLI::outError('Please point Madeam Console to the root directory of your application.');
          exit();
        }
      }

      try {
        $scriptClassName = 'Script_' . $scriptName;
        $script = new $scriptClassName;
        break;
      } catch (Madeam_Exception_AutoloadFail $e) {
        if ($scriptName === false) {
          // ask them for the script of the console they'd like to use
          Madeam_Console_CLI::outMenu('Scripts', $scriptNames);
          $scriptName = Madeam_Console_CLI::getCommand('script');
        }

        // by entering a console name at this point it means they've tried entering one that doesn't exist.
        // prompt them with an saying please try again.
        if ($scriptName !== false) {
          Madeam_Console_CLI::outError("Sorry the script you entered does not exist.");
        }
      }
    }

    // get list of commands for this script
    $commandNames = array();
    $classReflection = new ReflectionClass($script);
    foreach ($classReflection->getMethods(ReflectionMethod::IS_PUBLIC) as $mehodReflection) {
      $commandNames[] = $mehodReflection->getName();
    }

    do {
      // by entering a console name at this point it means they've tried entering one that doesn't exist.
      // prompt them with an saying please try again.
      if ($commandName !== false) {
        Madeam_Console_CLI::outError("Sorry the command you entered does not exist.");
      }

      // reset command
      $commandName = false;

      // check to see if the command exists in the args
      if (isset($args[0])) {
        $commandName = array_shift($args);
      }

      if ($commandName == null) {
        Madeam_Console_CLI::outMenu('Commands', $commandNames);
        $commandName = Madeam_Console_CLI::getCommand('command');
      }

    } while(! in_array($commandName, $commandNames));

    try {
      return $script->$commandName($this->parseArguments($args));
    } catch (Madeam_Exception $e) {
      Madeam_Exception::catchException($e);
    }

    // unset arguments -- they are only for first time use
    $args = array();
  }

  protected function parseArguments($commandNames) {
    $params = array();
    foreach ($commandNames as $commandName) {
      $nodes = split('=', $commandName);
      $name = $nodes[0];
      $value = $nodes[1];
      $params[$name] = $value;
    }
    return $params;
  }

  protected function createFile($file_name, $file_path, $file_content) {
    if (substr($file_path, - 1) !== DS) {
      $file_path = $file_path . DS;
    }
    $file = $file_path . $file_name;
    if (file_exists($file)) {
      if (Madeam_Console_CLI::getYN('The file ' . $file_name . ' already exists. Overwrite?') === false) {
        return false;
      }
    }
    if (file_put_contents($file, $file_content)) {
      Madeam_Console_CLI::outCreate('file ' . $file);
      return true;
    }
  }
}
