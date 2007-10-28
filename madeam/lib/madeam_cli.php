<?php
class madeam_cli {

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
    return $this->get();
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