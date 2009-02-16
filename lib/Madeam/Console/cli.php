<?php

/**
 * Madeam PHP Framework <http://www.madeam.com/>
 * Copyright (c)	2009, Joshua Davey
 *								202-212 Adeliade St. W, Toronto, Ontario, Canada
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright		Copyright (c) 2009, Joshua Davey
 * @link				http://www.madeam.com
 * @package			madeam
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
class Madeam_Console_CLI {

  static public function outMenu($label, $options = array()) {
    self::out();
    asort($options);
    self::out($label . ': ' . implode(' | ', $options));
  }

  static public function outError($msg) {
    self::out('error  ' . $msg);
  }

  static public function outCreate($msg) {
    self::out('create ' . $msg);
  }

  static public function outDelete($msg) {
    self::out('delete ' . $msg);
  }

  static public function outGet($name, $msg = null) {
    self::out();
    if ($msg != null) {
      self::out($msg);
    }
    self::out($name . '>', 0);
  }

  static public function getYN($msg) {
    self::outGet('[y/n]', $msg);
    $command = self::get();
    if ($command == 'y') {
      return true;
    } else {
      return false;
    }
  }

  static public function getCommand($msg) {
    self::outGet($msg);
    return self::get();
  }

  static public function get() {
    $command = trim(fgets(STDIN));
    if ($command == 'exit') {
      exit();
    } else {
      return $command;
    }
  }

  static public function out($string = null, $newline = 1) {
    if ($newline == 1) {
      fwrite(STDOUT, ' ' . $string . "\n");
    } else {
      fwrite(STDOUT, ' ' . $string);
    }
  }
}