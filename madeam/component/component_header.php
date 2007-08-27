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
class component_header extends madeam_component {  
  function javascript($path, $params = array()) {
    $this->controller->set('header_for_layout', $this->controller->data['header_for_layout'] . '<script src="' . REL_PATH . $path . '" type="text/javascript"></script>' . "\n");
  }
  
  function css($path, $params = array()) {
    $this->controller->set('header_for_layout', $this->controller->data['header_for_layout'] . '<link type="text/css" media="screen" href="' . REL_PATH . $path . '" />' . "\n");
  }
}
?>