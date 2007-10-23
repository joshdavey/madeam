<?php
class console_new extends madeam_console {

  public function application($params) {
    // set app name
    $app_name = $params['name'];
    
    // notify user of progress
    out("Generating new $app_name application");
    
    // make controller directory if it does not already exist
    if (!file_exists(CURRENT_DIR . DS . $app_name)) {
      mkdir(CURRENT_DIR . DS . $app_name);
    }

    if (full_copy(dirname(MADEAM_PATH), CURRENT_DIR  . DS . $app_name)) {
      return true;
    } else {
      return false;
    }
  }
  
}


function full_copy($source, $target) {
  if (is_dir($source)) {
    @mkdir($target);
    
    $d = dir($source);
    
    while (FALSE !== ($entry = $d->read())) {
      if ($entry == '.' || $entry == '..' || $entry == '.svn') { continue; }
      
      $Entry = $source . '/' . $entry;           
      if (is_dir($Entry)) {
        full_copy($Entry, $target . '/' . $entry);
        out("Generating " . basename($Entry) . " directory");
        continue;
      }
      
      out("Generating " . basename($Entry) . " file");
      if (!copy($Entry, $target . '/' . $entry)) {        
        return false;
      }
    }
    
    $d->close();
  } else {
    out("Generating " . basename($source) . " file");
    if (!copy($source, $target)) {      
      return false;
    }
  }
  
  return true;
}
?>