<?php
class Madeam_Console_Make extends Madeam_Console {

  public function app($name, $clone = false, $symlink = false) {
    
    $path = trim(`pwd`);
    
    // add app name to path
    if ($name !== true) { 
      $path .= '/' . $name;
    }
    
    // create full path to application
    if (!file_exists($path)) { `mkdir -p $path`; }
    
    $madeam = dirname(dirname(dirname(dirname(__FILE__))));
    echo `cp -rpv {$madeam}/www/ {$path}`;
    
    if ($clone === true) {
      // clone madeam from remote
      echo `git clone -v git://github.com/joshdavey/madeam {$path}/app/vendor/Madeam`;
    } elseif ($symlink === true) {
      echo `ln -s {$madeam}/ {$path}/app/vendor/Madeam`;
    } else {
      // copy local madeam copy
      echo `cp -rpv {$madeam} {$path}/app/vendor`;
    }
  }
  
}