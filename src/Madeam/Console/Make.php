<?php
class Madeam_Console_Make extends Madeam_Console {

  public function app($options = array('db_name', 'db_user', 'db_pass', 'db_host'), $defaults = array('db_name' => 'madeam', 'db_user' => 'root', 'db_pass' => '', 'db_host' => 'localhost')) {
    if (Madeam_Console_CLI::getYN('Create a Madeam application in "' . getcwd() . '"?')) {
      Madeam_Console_CLI::outCreate('Application in ' . getcwd());
      
      if ($this->fullCopy(Madeam::$pathToRoot, getcwd())) {
        return true;
      } else {
        return false;
      }
    } else {
      return false;
    }
  }
  
  public function app2() {
    if (Madeam_Console_CLI::getYN('Create a Madeam application in "' . getcwd() . '"?')) {
      $path = dirname(__FILE__);
      $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);
      foreach($objects as $name => $object) {
        echo "$name\n";
      }
    }
  }

  private function fullCopy($source, $target) {
    if (is_dir($source)) {
      @mkdir($target);
      $d = dir($source);
      while(FALSE !== ($entry = $d->read())) {
        if ($entry == '.' || $entry == '..' || $entry == '.svn' || $entry == '.git') {
          continue;
        }
        $Entry = $source . '/' . $entry;
        if (is_dir($Entry)) {
          $this->fullCopy($Entry, $target . '/' . $entry);
          Madeam_Console_CLI::outCreate($target . '/' . $entry);
          continue;
        }
        Madeam_Console_CLI::outCreate($target . '/' . $entry);
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