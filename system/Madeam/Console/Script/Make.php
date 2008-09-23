<?php
class Madeam_Console_Script_Make extends Madeam_Console_Script {

  public function app($options = array('db_name', 'db_user', 'db_pass', 'db_host'), $defaults = array('db_name' => 'madeam', 'db_user' => 'root', 'db_pass' => '', 'db_host' => 'localhost')) {
    if (Madeam_Console_CLI::getYN('Create a Madeam application in "' . CURRENT_DIR . '"?')) {
      Madeam_Console_CLI::outCreate('Application in ' . CURRENT_DIR);
      if ($this->fullCopy(PATH_TO_MADEAM, CURRENT_DIR)) {
        return true;
      } else {
        return false;
      }
    } else {
     return false;
    }
  }

  private function fullCopy($source, $target) {
    if (is_dir($source)) {
      @mkdir($target);
      $d = dir($source);
      while(FALSE !== ($entry = $d->read())) {
        if ($entry == '.' || $entry == '..' || $entry == '.svn') {
          continue;
        }
        $Entry = $source . '/' . $entry;
        if (is_dir($Entry)) {
          $this->fullCopy($Entry, $target . '/' . $entry);
          Madeam_Console_CLI::outCreate($Entry);
          continue;
        }
        Madeam_Console_CLI::outCreate($Entry);
        if (! copy($Entry, $target . '/' . $entry)) {
          return false;
        }
      }
      $d->close();
    } else {
      Madeam_Console_CLI::outCreate($source);
      if (! copy($source, $target)) {
        return false;
      }
    }
    return true;
  }
}