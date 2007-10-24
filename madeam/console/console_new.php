<?php
class console_new extends madeam_console {

  public function application($params) {
    // set app name
    $app_name = $params['name'];

    $required = array('name');
    asort($required);

    $param_names = array_values($params);
    asort($param_names);

    do {
      if ($required != $param_names) { continue; }

      $still_required = array_diff_assoc($required, $param_names);

      foreach ($still_required as $param) {
        outp($param);
        $params[$param] = getc();

        $param_names = array_values($params);
        asort($param_names);
      }

    } while ($required != $param_names);


    if ($app_name != null) {

      // notify user of progress
      outc("Generating new $app_name application in " . CURRENT_DIR);

      // make controller directory if it does not already exist
      if (!file_exists(CURRENT_DIR . DS . $app_name)) {
        mkdir(CURRENT_DIR . $app_name);
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
        outc("Generating " . basename($Entry) . " directory");
        continue;
      }

      outc("Generating " . basename($Entry) . " file");
      if (!copy($Entry, $target . '/' . $entry)) {
        return false;
      }
    }

    $d->close();
  } else {
    outc("Generating " . basename($source) . " file");
    if (!copy($source, $target)) {
      return false;
    }
  }

  return true;
}
?>