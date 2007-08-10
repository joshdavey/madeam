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
 * @version			0.0.4
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 * @author      Joshua Davey
 */

class activeFile extends appModel {
  public $root_dir;
  public $path;

  public function __construct($depth = false) {
    // set depth
    if ($depth !== false) { $this->depth = $depth; }

    // call parent __construct which loads all the models and helpers
    parent::__construct();

    // set table_name
   
    // set root dir?
    $this->root_dir = ACTIVEFILE_STORE;
    
    // set path
    if ($this->path == null) {
      $this->path = $this->root_dir . inflector::underscorize(inflector::pluralize($this->name)) . '/';
    }
  }

  /**
   * Retrieves the path of a single file
   *
   * @param string $id
   * @return string
   */
  public function find_one($file_path = -1) {
    $file = array();
    $file_query = $this->path . $file_path;

    $file['name'] = basename($file_path);
    $file['path'] = $file_query;
    $file['size'] = filesize($file_query);
    $file['modified'] = filemtime($file_query);

    $class_name = $this->name;
    $return[$class_name] = $file;

    return $return;
  }

  public function find_all($dir_path = null) {
    $files      = array();
    $return     = array();
    $insert_id  = -1;
    $file_query = $this->path . $dir_path;

    $dir = dir($file_query);
    while (false !== ($file = $dir->read())) {
      if ($file != '.' && $file != '..') {
        $insert_id++;
        $files[$insert_id]['name'] = $file;
        $files[$insert_id]['path'] = $file_query . $file;
        $files[$insert_id]['size'] = filesize($file_query . $file);
        $files[$insert_id]['modified'] = filemtime($file_query . $file);
      }
    }

    $class_name = pluralize(get_class($this));
    $return[$class_name] = $files;

    return $return;
  }
  
  public function save($file, $dir = false) {
    $saved_files = array();

    // upload files from HTML form
    if (!empty($_FILES)) {
      $num_files  = 1;

      // count the numger of files that need to be uploaded
      if (is_array($file['name'])) { $num_files = count($file['name']); }

      if ($num_files > 1) {
        // upload multiple files
        for ($i = 0; $i <= $num_files; $i++) {
          $target_path = $this->path . $dir . $file['name'][$i];
          if(move_uploaded_file($file['tmp_name'][$i], $target_path)) {
            $saved_files[] = $target_path;
          }
        }
      } else {
        // upload single file
        $target_path = $this->path . $dir . $file['name'];
        if(move_uploaded_file($file['tmp_name'], $target_path)) {
          $saved_files[] = $target_path;
        }
      }
    }

    // return array or false
    if (!empty($saved_files)) { return $saved_files; } else { return false; }
  }

  public function delete($file_path = -1) {

  }
  
  final public function reset() {
    
  }
}
?>