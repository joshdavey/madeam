<?php

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
class Madeam_Console {

  /**
   * undocumented 
   *
   * @author Joshua Davey
   */
  public function __construct($params = array()) {
    array_shift($params); // remove script path
    
    $action = explode('/', array_shift($params)); // example: create/controller => Madeam_Console_Script_Create::controller();
    
    $script = array_shift($action);
    $method = array_shift($action);
    
    // parse POSIX params
    // -name Posts => array('name' => 'Posts')
    // -scaffold -name Posts => array('scaffold' => true, 'name' => 'Posts')
    foreach ($params as $key => $param) {
      if (preg_match('/-.*/', $param)) {
        if (!isset($params[$key+1]) || preg_match('/-.*/', $params[$key+1])) {
          $params[substr($param, 1)] = true;
          unset($params[$key]);
        } else {
          $params[substr($param, 1)] = $params[$key+1];
          unset($params[$key]);
          unset($params[$key+1]);
        }
      }
    }
    
    // make sure we're pointed at the project's root
    if ($script != 'make') {
      if (!file_exists(realpath('app/vendor/Madeam/src/Madeam.php'))) {
        Madeam_Console_CLI::outError('Please point Madeam Console to the root directory of your application.');
        exit();
      }
    
      // get list of scripts
      $scripts = array();
      foreach (new DirectoryIterator(realpath('app/vendor/Madeam/src/Madeam/Script')) as $file) {
        if ($file->isFile()) {
          $scripts[] = strtolower(substr($file->getFilename(), 0, -4));
        }
      }
  
      // make sure script entered exists
      if (!in_array($script, $scripts)) {
        Madeam_Console_CLI::outError('Oops. The script ' . $script . ' does not exist.');
        exit();
      }
    }
    
    
    $class = 'Madeam_Script_' . ucfirst($script);
    $console = new $class;
    $console->$method($params);
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
        if (!file_exists(realpath('app/vendor/Madeam.php'))) {
          Madeam_Console_CLI::outError('Please point Madeam Console to the root directory of your application.');
          exit();
        }
      }

      try {
        $scriptClassName = 'Madeam_Console_Script_' . ucfirst($scriptName);
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

  /**
   * undocumented 
   *
   * @author Joshua Davey
   */
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
