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
class madeam_component {
  static $controller;
  
  public static function init($controller_id) {
    self::$controller = madeam_registry::get($controller_id);
    
    // what about using a class registry
    //$this->controller =& madeam_registry::get('controller_name');
  }
  
  // idea...
  public function __call($name, $args) {
  	$this->controller->$name($args);
	}
}
?>