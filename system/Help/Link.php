<?php
class Help_Link extends Help_Html {
	
	public static function to($label, $uri) {
		return parent::link($label, $uri);
	}
	
}