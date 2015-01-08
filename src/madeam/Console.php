<?php
namespace madeam;
/**
 * Madeam PHP Framework <http://madeam.com>
 * Copyright (c)  2009, Joshua Davey
 *                202-212 Adeliade St. W, Toronto, Ontario, Canada
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright    Copyright (c) 2009, Joshua Davey
 * @link        http://www.madeam.com
 * @package      madeam
 * @license      http://www.opensource.org/licenses/mit-license.php The MIT License
 * @author      Joshua Davey
 */
class Console {

  /**
   * undocumented
   *
   * @author Joshua Davey
   */
  public function __construct($params = array()) {
    array_shift($params); // remove script path

    $action = array_shift($params);

    // parse POSIX params
    // -name Posts => array('name' => 'Posts')
    // -scaffold -name Posts => array('scaffold' => true, 'name' => 'Posts')
    foreach ($params as $key => $param) {
      if (preg_match('/--.*/', $param)) {
        if (!isset($params[$key+1]) || preg_match('/--.*/', $params[$key+1])) {
          $params[substr($param, 2)] = true;
          unset($params[$key]);
        } else {
          $params[substr($param, 2)] = $params[$key+1];
          unset($params[$key]);
          unset($params[$key+1]);
        }
      }
    }

    // reflection
    $reflection = new \ReflectionObject($this);

    // check methods for callbacks
    $method = $reflection->getMethod($action);

    //
    $defaults = array();
    $parameters = $method->getParameters();
    foreach ($parameters as $parameter) {
      // set parameters of callback (parameters in methods act as meta data for callbacks)
      if ($parameter->isDefaultValueAvailable()) {
        $defaults[$parameter->getName()] = $parameter->getDefaultValue();
      } else {
        $defaults[$parameter->getName()] = null;
      }
    }

    $args = array();
    foreach ($defaults as $param => $value) {
      if (isset($params[$param])) {
        $args[] = $params[$param];
      } else {
        $args[] = $value;
      }
    }

    call_user_func_array(array($this, $action), $args);
  }

  /**
   * undocumented
   *
   * @author Joshua Davey
   */
  public function initialize2() {
    array_shift($_SERVER['argv']);
    $args         = $_SERVER['argv'];
    $scriptName   = false;
    $commandName  = false;

    // get list of available consoles
    $scriptNames = array('create', 'delete', 'test', 'migrate');

    while(true) {
      // reset console
      $scriptName = false;

      // check to see if the console exists in the args
      if (isset($args[0])) {
        $scriptName = array_shift($args);
      }

      // if the command requires to be in the application's root path then check it.
      // If we aren't in the applicatin's root path then tell the user and exit
      if ($scriptName != 'make') {
        if (!file_exists(realpath('application/vendor/Madeam.php'))) {
          console\CLI::outError('Please point Madeam Console to the root directory of your application.');
          exit();
        }
      }

      try {
        $scriptClassName = 'Console_Script_' . ucfirst($scriptName);
        $script = new $scriptClassName;
        break;
      } catch (exception\AutoloadFail $e) {
        if ($scriptName === false) {
          // ask them for the script of the console they'd like to use
          console\CLI::outMenu('Scripts', $scriptNames);
          $scriptName = console\CLI::getCommand('script');
        }

        // by entering a console name at this point it means they've tried entering one that doesn't exist.
        // prompt them with an saying please try again.
        if ($scriptName !== false) {
          console\CLI::outError("Sorry the script you entered does not exist.");
        }
      }
    }

    // get list of commands for this script
    $commandNames = array();
    $classReflection = new \ReflectionClass($script);
    foreach ($classReflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $mehodReflection) {
      $commandNames[] = $mehodReflection->getName();
    }

    do {
      // by entering a console name at this point it means they've tried entering one that doesn't exist.
      // prompt them with an saying please try again.
      if ($commandName !== false) {
        console\CLI::outError("Sorry the command you entered does not exist.");
      }

      // reset command
      $commandName = false;

      // check to see if the command exists in the args
      if (isset($args[0])) {
        $commandName = array_shift($args);
      }

      if ($commandName == null) {
        console\CLI::outMenu('Commands', $commandNames);
        $commandName = console\CLI::getCommand('command');
      }

    } while(! in_array($commandName, $commandNames));

    try {
      return $script->$commandName($this->parseArguments($args));
    } catch (Exception $e) {
      Exception::handle($e);
    }

    // unset arguments -- they are only for first time use
    $args = array();
  }

  /**
   * undocumented
   *
   * @author Joshua Davey
   */
  protected function parseArguments($commandNames) {
    $params = array();
    foreach ($commandNames as $commandName) {
      $nodes = explode('=', $commandName);
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
      if (console\CLI::getYN('The file ' . $file_name . ' already exists. Overwrite?') === false) {
        return false;
      }
    }

    if (file_put_contents($file, $file_content)) {
      console\CLI::outCreate('file ' . $file);
      return true;
    }
  }

  protected function createDir($dir) {
    if (!file_exists($dir)) {
      mkdir($dir, 0777, true);
      console\CLI::outCreate('directory ' . $dir);
    }
  }

}
