<?php
class madeam_cli {

  protected function out_menu($name, $options = array()) {
    foreach ($options as $opt) {
      out('|' . $opt);
    }
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

  protected function out_get($msg) {
    out();
    out($msg);
    out('>', 0);
  }

  protected function get_yesno($msg) {
    $this->out_get($msg . ' [y/n]');
    return get();
  }

  protected function get_command($msg) {
    $this->out_get($msg);
    $command = get();
    if ($command == 'exit') {
      exit();
    } else {
      return $command;
    }
  }

}