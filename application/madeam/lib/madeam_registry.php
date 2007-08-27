<?php
class madeam_registry {

	static $registry;
	
	public static function init() {
		
	}
	
	public static function set($index, $value) {
		self::$registry[$index] = $value;
	}
	
	public static function get($index) {
		return self::$registry[$index];
	}
}
?>