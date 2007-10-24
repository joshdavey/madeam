<?php
class console_make extends madeam_console {

  public function app($params) {

    //--------------------
    // this code is key and needs to be extracted for use in all commands
    $required_params = array('name');
    asort($required_params);

    $param_names = array_keys($params);
    asort($param_names);

    do {
      $still_required = array_diff_assoc($required_params, $param_names);

      foreach ($still_required as $param) {
        outp($param);
        $params[$param] = getc();

        $param_names = array_keys($params);
        asort($param_names);
      }
    } while ($required_params != $param_names);
    //--------------------



    // set app name
    $app_name = $params['name'];

    if ($app_name != null) {
      // make controller directory if it does not already exist
      if (!file_exists(CURRENT_DIR . DS . $app_name)) {
        // notify user of progress
        outc("application $app_name in " . CURRENT_DIR);
        outc('directory ' . $app_name);
        
        mkdir(CURRENT_DIR . $app_name);
      } else {
        outp("Overwrite $app_name application? [y/n]");
        $continue = getc();
        if ($continue == 'n') {
          return false;
        } 
        
        // notify user of progress
        out();
        outc("application $app_name in " . CURRENT_DIR);
      }

      if (full_copy(dirname(MADEAM_PATH), CURRENT_DIR . $app_name)) {
        return true;
      } else {
        return false;
      }

    } else {
      oute("Requires name");
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
        outc('directory ' . basename($Entry));
        continue;
      }

      outc('file ' . basename($Entry));
      if (!copy($Entry, $target . '/' . $entry)) {
        return false;
      }
    }

    $d->close();
  } else {
    outc('file ' . basename($source));
    if (!copy($source, $target)) {
      return false;
    }
  }

  return true;
}
?>