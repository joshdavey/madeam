<?php
class Script_Make extends Madeam_Script {

  public $command_requirements = array(
    'app' => array('name')
  );

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
        $params[$param] = $this->get_command($param);

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
        $this->out_create("application $app_name in " . CURRENT_DIR);
        $this->out_create($app_name);

        mkdir(CURRENT_DIR . $app_name);
      } else {
        if (!$this->get_yesno("Overwrite $app_name application?")) {
          return false;
        }

        // notify user of progress
        out();
        $this->out_create("application $app_name in " . CURRENT_DIR);
      }

      if ($this->full_copy(dirname(MADEAM_PATH), CURRENT_DIR . $app_name)) {
        return true;
      } else {
        return false;
      }

    } else {
      $this->out_error("Requires name");
    }
  }

  private function full_copy($source, $target) {
  if (is_dir($source)) {
    @mkdir($target);

    $d = dir($source);

    while (FALSE !== ($entry = $d->read())) {
      if ($entry == '.' || $entry == '..' || $entry == '.svn') { continue; }

      $Entry = $source . '/' . $entry;
      if (is_dir($Entry)) {
        $this->full_copy($Entry, $target . '/' . $entry);
        $this->out_create(basename($Entry));
        continue;
      }

      $this->out_create(basename($Entry));
      if (!copy($Entry, $target . '/' . $entry)) {
        return false;
      }
    }

    $d->close();
  } else {
    $this->out_create(basename($source));
    if (!copy($source, $target)) {
      return false;
    }
  }

  return true;
}

}
?>