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
class component_file extends madeam_component {
  public $path          = 'files/';
  public $allowed_mimes = array(); // leave empty to allow all mimes
  
  var $chars = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 0, 1, 2, 3, 4, 5, 6, 7, 8, 9);
  
  public function set_path($path) {
    $this->path = $path;
  }
  
  public function set_mimes() {
    if (func_num_args() > 1) {
      foreach (func_get_args() as $mime) {
        $this->allowed_mimes[] = $mime;
      }
    }
  }
  
  public function save($file) {
    $saved_files = array();
		
    // upload files from HTML form
    if (!empty($_FILES)) {
      $num_files  = 1;

      // count the numger of files that need to be uploaded
      if (is_array($file['name'])) { $num_files = count($file['name']); }

      if ($num_files > 1) {
        // upload multiple files
        for ($i = 0; $i <= $num_files; $i++) {
          if ($new_file = $this->save_file($file['name'][$i], $file['tmp_name'][$i])) {
          $saved_files[] = $new_file;
        } else {
          // should we delete all the files that were just uploaded?
          // make sure the error system can target individual file upload fields
          
          // return false if an error occurs
          return false;
        }
        }
      } else {
        if ($new_file = $this->save_file($file['name'], $file['tmp_name'])) {
          $saved_files[] = $new_file;
        } else {
          // should we delete all the files that were just uploaded?
          // make sure the error system can target individual file upload fields
          
          // return false if an error occurs
          return false;
        }
      }
    }

    // return array or false
    if (!empty($saved_files)) { return $saved_files; } else { return false; }
  }
  
  public function save_file($name, $tmp_path) {
    $file_info = array();
    
    if (file_exists($tmp_path)) {
      // get file info
      $file_info = getimagesize($tmp_path);    
    
	    // check mime types
	    if (in_array($file_info['mime'], $this->allowed_mimes) || empty($this->allowed_mimes)) {
	      // get file extension
	      $ext = substr($name, strrpos($name, '.') + 1);
	      
	      // set file name
	      $name = $this->rnd_name(10) . '.' . $ext;
	      
	      // set target path
	      $target_path = $this->path . $name;
	      
	      // copy uploaded file to target path
	      if(move_uploaded_file($tmp_path, PUB_PATH . $target_path)) {
	        return $target_path;
	      }
	    } else {
	      return false;
	    } 
	  } else {
    	return false;
    }
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
        $files[$insert_id]['name']      = $file;
        $files[$insert_id]['path']      = $file_query . $file;
        $files[$insert_id]['size']      = filesize($file_query . $file);
        $files[$insert_id]['modified']  = filemtime($file_query . $file);
      }
    }

    $class_name = pluralize(get_class($this));
    $return[$class_name] = $files;

    return $return;
  }
  
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
  
  
  function rnd_name($length = 32) {
    $password = null;
    
    for ($i = 0; $i <= $length; $i++) {
      $password .= $this->chars[rand(0, 35)];
    }
    
    return $password;
  }
}
?>