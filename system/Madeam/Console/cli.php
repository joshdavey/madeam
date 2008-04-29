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
class Madeam_Console_CLI {

  protected function out_menu($label, $options = array()) {
    out();
    out($label);
    out('---------------');
    foreach ($options as $opt) {
      out('| ' . $opt);
    }
    out('---------------');
  }

  protected function out_error($msg) {
    out('error  ' . $msg);
  }

  protected function out_create($msg) {
    out('create ' . $msg);
  }

  protected function out_delete($msg) {
    out('delete ' . $msg);
  }

  protected function out_get($name, $msg = null) {
    out();
    if ($msg != null) { out($msg); }
    out($name . '>', 0);
  }

  protected function get_yesno($msg) {
    $this->out_get('[y/n]', $msg);
    $command = $this->get();

    if ($command == 'y') {
      return true;
    } else {
      return false;
    }
  }

  protected function get_command($msg) {
    $this->out_get($msg);
    return $this->get();
  }

  protected function get() {
    $command = get();
    if ($command == 'exit') {
      exit();
    } else {
      return $command;
    }
  }

}