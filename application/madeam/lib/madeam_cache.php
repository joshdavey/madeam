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
 * @version			0.0.6
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 * @author      Joshua Davey
 */
class madeam_cache {
	static $dir = 'cache';
	static $open_caches = array();
	
	public static function read($id, $life_time = 0) {
		// check to see if cache is disabled
		if (DISABLE_CACHE === true) { return false; }
		
		// set file name
		$file = TMP_PATH . self::$dir . DS . md5($id);
				
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
	
	public static function save($id, $value) {
		// check to see if cache is disabled
		if (DISABLE_CACHE === true) { return false; }
		
		// set file name
		$file = TMP_PATH . self::$dir . DS . md5($id);
		echo 'saving';
		// save serialization to file
		file_put_contents($file, serialize($value));
	}
	
	public static function start($name, $life_time = 0) {
		// check to see if cache is disabled
		if (DISABLE_CACHE === true) { return false; }
		
		if (!$cache = self::read($name, $life_time)) {
			ob_start();
			self::$open_caches[] = $name;
			return false;
		} else {
			echo $cache;
			return true;
		}
	}
	
	public static function stop() {
		// check to see if cache is disabled
		if (DISABLE_CACHE === true) { return false; }
		
		$name = array_shift(self::$open_caches);
		$cache = ob_get_contents();
		self::save($name, $cache);
		ob_clean();
		echo $cache;
	}
}
?>