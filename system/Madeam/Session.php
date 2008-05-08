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
class Madeam_Session {

  static public function start($sess_id = false) {
		// load session by ID
    if ($sess_id) { session_id($sess_id); }

		// start session
    if (!isset($_SESSION)) {
			session_start();
		}

		// merge post data with flash post data
		if (isset($_SESSION[MADEAM_FLASH_DATA_NAME][MADEAM_FLASH_POST_NAME])) {
      $_POST = array_merge($_SESSION[MADEAM_FLASH_DATA_NAME][MADEAM_FLASH_POST_NAME], $_POST);
    }
  }

  static public function destroy() {
    session_destroy();
  }

  static public function flashSet($name, $data) {
    $_SESSION[MADEAM_FLASH_DATA_NAME][$name] = $data;
  }

  static public function flashGet($name) {
    if (isset($_SESSION[MADEAM_FLASH_DATA_NAME][$name])) {
			return $_SESSION[MADEAM_FLASH_DATA_NAME][$name];
		} else {
		  return false;
		}
  }

	static public function flashPost($postData = false) {
	  if ($postData !== false) {
	    $_SESSION[MADEAM_FLASH_DATA_NAME][MADEAM_FLASH_POST_NAME] = $postData;
	  } else {
  	  if (isset($_POST)) {
  			$_SESSION[MADEAM_FLASH_DATA_NAME][MADEAM_FLASH_POST_NAME] = $_POST;
  		}
	  }
	}

	static public function flashDestroy($name = false) {
	  if ($name === false) {
	    unset($_SESSION[MADEAM_FLASH_DATA_NAME]);
	  } else {
	    unset($_SESSION[MADEAM_FLASH_DATA_NAME][$name]);
	  }
	}

	static public function flashLife($pagesToLive = 1) {
	  $_SESSION[MADEAM_FLASH_LIFE_NAME] = $pagesToLive;
	}

  static public function error($name, $msg) {
    $_SESSION[MADEAM_USER_ERROR_NAME][$name][] = $msg;
  }

  static public function set($name, $value) {
    $_SESSION[$name] = $value;
  }

  static public function get($name) {
    if (isset($_SESSION[$name])) {
        return $_SESSION[$name];
    } else {
      return false;
    }
  }
}
?>
