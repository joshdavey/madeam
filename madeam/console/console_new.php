<?php
class console_new extends madeam_console {

  public function application($params) {
    echo CURRENT_DIR;
    
    $app_name = $params['name'];
    
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
        continue;
      }
      
      if (!copy($Entry, $target . '/' . $entry)) {
        return false;
      }
    }
    
    $d->close();
  } else {
    if (!copy($source, $target)) {
      return false;
    }
  }
  
  return true;
}
?>