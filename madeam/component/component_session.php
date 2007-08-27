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
class component_session extends madeam_component {

  function start($sess_id = false) {	
		// load session by ID
    if ($sess_id) { session_id($sess_id); }
    
		// start session
    if (!isset($_SESSION)) {
			session_start();
		}
		
		// merge post data with flash post data
		if (isset($_SESSION[FLASH_DATA_NAME][FLASH_POST_NAME])) {
      $_POST = array_merge($_SESSION[FLASH_DATA_NAME][FLASH_POST_NAME], $_POST);
    }
  }
  
  function destroy() {
    session_destroy();
  }
  
  function flash($name, $data) {
    $_SESSION[FLASH_DATA_NAME][$name] = $data;
  }
	
	function flash_post() {
	  if (isset($_POST)) {
			$_SESSION[FLASH_DATA_NAME][FLASH_POST_NAME] = $_POST;
		}
	}
	
	function flash_life($pages_to_live = 1) {
	  $_SESSION[FLASH_LIFE_NAME] = $pages_to_live;
	}
  
  function error($name, $msg) {
    $_SESSION[USER_ERROR_NAME][$name][] = $msg;
  }
}
?>