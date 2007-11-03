<?php
/**
 * Madeam :  Rapid Development MVC Framework <http://www.madeam.com/>
 * Copyright (c)	2006, Joshua Davey
 *								24 Ridley Gardens, Toronto, Ontario, Canada
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright		Copyright (c) 2006, Joshua Davey
 * @link				http://www.madeam.com
 * @package			madeam
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
class madeam_script extends madeam_cli {

  public $command_requires_root = array();

  protected function create_file($file_name, $file_path, $file_content) {
    if (substr($file_path, -1) !== DS) { $file_path = $file_path . DS; }
    $file = $file_path . $file_name;
    if (file_exists($file)) {
      $continue = $this->get_yesno('The file ' . $file_name . ' already exists. Overwrite?');
      if ($continue === false) {
        return false;
      }
    }

    if (file_put_contents($file, $file_content)) {
      $this->out_create('file ' . $file);
      return true;
    }
  }
}
?>