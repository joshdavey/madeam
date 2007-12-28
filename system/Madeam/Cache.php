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
class Madeam_Cache {

	static $dir = 'cache';

	static $openCaches = array();

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $id
	 * @param unknown_type $life_time
	 * @return unknown
	 */
	public static function read($id, $life_time = 0) {
	  // check registry first
	  if (Madeam_Registry::exists($id)) {
	    return Madeam_Registry::get($id);
	  }

		// check to see if cache is disabled
		if (MADEAM_ENABLE_CACHE === false) { return false; }

		// set file name
		$file = PATH_TO_TMP . self::$dir . DS . md5($id);

		if (file_exists($file)) {
			if ((time() - filemtime($file)) <= $life_time || $life_time == -1) {
				// get cache from file and unserialize
				return unserialize(file_get_contents($file));
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $id
	 * @param unknown_type $value
	 * @param unknown_type $store_in_registry
	 * @return unknown
	 */
	public static function save($id, $value, $store_in_registry = false) {
	  // store in registry
		if ($store_in_registry === true) {
		  Madeam_Registry::set($id, $value);
		}

		// check to see if cache is disabled -- only disables the use of the file system cache.
		// can still use registry
		if (MADEAM_ENABLE_CACHE === false) { return false; }

		// set file name
		$file = PATH_TO_TMP . self::$dir . DS . md5($id);

		// save serialization to file
		file_put_contents($file, serialize($value));
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $id
	 * @param unknown_type $life_time
	 * @return unknown
	 */
	public static function start($id, $life_time = 0) {
		// check to see if cache is disabled
		if (MADEAM_ENABLE_CACHE === false) { return false; }

		if (!$cache = self::read($id, $life_time)) {
			ob_start();
			self::$openCaches[] = $id;
			return false;
		} else {
			echo $cache;
			return true;
		}
	}

	/**
	 * Enter description here...
	 *
	 * @return unknown
	 */
	public static function stop() {
		// check to see if cache is disabled
		if (MADEAM_ENABLE_CACHE === false) { return false; }

		$id = array_shift(self::$openCaches);
		$cache = ob_get_contents();
		self::save($id, $cache);
		ob_clean();
		echo $cache;
	}

  /**
   * Enter description here...
   *
   * @param unknown_type $id
   */
	public static function clear($id) {
	  // set file name
		$file = PATH_TO_TMP . self::$dir . DS . md5($id);

		// save serialization to file
		file_put_contents($file, null);
	}


	/**
	 * Check to see if a cache exists
	 *
	 * @param string $id
	 * @return boolean
	 */
	public static function check($id) {

	  // check registry first
	  if (Madeam_Registry::get($id)) { return true; }

	  // check file system cache
		$file = PATH_TO_TMP . self::$dir . DS . md5($id);
		if (file_exists($file)) { return true; }

		return false;
	}

}

?>