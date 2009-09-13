<?php
namespace madeam\console;
class Make extends \madeam\Console {

  public function app($name, $clone = false, $symlink = false) {
    
    $path = trim(`pwd`);
    
    // add app name to path
    if ($name !== true) { 
      $path .= '/' . $name;
    }
    
    // create full path to application
    if (!file_exists($path)) { `mkdir -p $path`; }    
    
    $madeam = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/';
    echo `cp -rpv {$madeam}madeam/www/ {$path}`;
    
    if ($clone === true) {
      // clone madeam from remote
      echo `git clone -v git://github.com/joshdavey/madeam {$path}/app/vendor/madeam`;
    } elseif ($symlink === true) {
      echo `ln -s {$madeam}madeam/ {$path}/app/vendor/madeam`;
    } else {
      // copy local madeam copy
      echo `cp -rpv {$madeam}madeam {$path}/app/vendor`;
    }
  }
  
}