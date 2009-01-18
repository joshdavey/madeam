<?php
class Linkr extends Htmlr {
	
	public static function to($label, $uri) {
		return parent::link($label, $uri);
	}
	
}