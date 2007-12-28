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
class Madeam {
  /**
   * dispatches all operations to controller specified by uri
   *
   * @return boolean
   */
  public static function dispatch() {
    // call user front controller?
  	// include app/app.php // -- includes stuff that executes before dispatching -- config stuff?

    // call controller action
    $output = Madeam::makeRequest(Madeam_Router::currentUri() . '?layout=1');

    // destroy user error notices
    if (isset($_SESSION[USER_ERROR_NAME])) {
      unset($_SESSION[USER_ERROR_NAME]);
    }

    // destroy flash data when it's life runs out
		if (isset($_SESSION[FLASH_LIFE_NAME])) {
			if (--$_SESSION[FLASH_LIFE_NAME] < 1) {
				unset($_SESSION[FLASH_LIFE_NAME]);
				if (isset($_SESSION[FLASH_DATA_NAME])) {
					unset($_SESSION[FLASH_DATA_NAME]);
				}
			}
		}

    // return output
    return $output;
  }

  /**
   * This is where all the magic starts.
   *
   * This method workds by accepting a URL which acts as a query and some configuration information in the form of an array.
   * The URL is processed by the madeamRouter which returns paramaters based on the routing @see config/routes.php
   * The action of the framework is based on 3 parameters that are normally defined in the routes but have default values
   * assigned to them if not set. The 3 parameters are $controller, $action and $format.
   *
   * $controller is the name of the controller's class that the framework calls.  example: article
   * $action is the name of the controller class's method that is called.         example: show
   * $format is a file extension that determines which view to call.              example: html
   *
   * If there is not class associated with the controller being called then it looks for a view instead.
   *
   * If there is not method associated with the action called then it renders a view without calling the action.
   *
   * @param string $url -- example: controller/action/32?foo=bar
   * @return string
   */
  public static function makeRequest($uri) {
    // get request parameters from uri
    // example input: 'posts/show/32'
    $params = Madeam_Router::parseUri($uri);
    
    // get instance of configuration from registry
    $config = Madeam_Registry::get('config');
    
    // because we allow controllers to be grouped into sub folders we need to recognize this when 
    // someone tries to access them. For example if someone wants to access the 'admin/index' controller
    // they should be able to just type in 'admin' because 'index' is the default controller in that
    // group of controllers. To make this possible we check to see if a directory exists that is named
    // the same as the controller being called and then append the default controller name to the end
    // so 'admin' becomes 'admin/index' if the admin directory exists.
    // note: there is a consequence for this feature which means if you have a directory named 'admin'
    // you can't have a controller named 'controller_admin'
    if (is_dir(PATH_TO_CONTROLLER . ucfirst($params['controller']))) { $params['controller'] .= '/' . $config['default_controller']; }
    
    // set controller's class
    $controllerClass = 'Controller_' . str_replace(' ', '_', ucwords(str_replace('/', ' ', $params['controller'])));
    
    try {
      // create controller instance
      $controller = new $controllerClass($params);
    } catch (Madeam_Exception $e) {
      if (is_dir(PATH_TO_VIEW . $params['controller'])) {
        $controller = new Controller_App($params);
      } else {
        $e->setMessage('Missing Controller ' . $controllerClass);
        Madeam_Error::catchException($e, Madeam_Error::ERR_NOT_FOUND);
      }
    }
     
    try {   
      // before action callback
      $controller->beforeAction();
      
      // call action
      $controller->{Madeam_Inflector::camelize($params['action'])}();
      
      // after action callback
      $controller->beforeRender();
      
      // render
      $controller->render();
      
      // after render callback
      $controller->afterRender();    
      
      // return output
      return $controller->output;
    } catch (Madeam_Exception $e) {
      Madeam_Error::catchException($e, Madeam_Error::ERR_NOT_FOUND);
    }
      
  }

  /**
   * Enter description here...
   *
   * @param unknown_type $url
   * @param unknown_type $exit
   */
  public static function redirect($url, $exit = true) {
    if (!headers_sent()) {
      header('Location:  ' . self::url($url));
      if ($exit) { exit; }
    } else {
      Madeam_Logger::log('Tried redirecting when headers already sent. (Check for echos before script redirects)');
    }
  }
  
  /**
   * Enter description here...
   *
   * @param unknown_type $var
   */
  public static function debug($var) {
    
  }
  
  /**
   * Enter description here...
   *
   * @param unknown_type $url
   * @return unknown
   */
  public static function url($url) {
    if (substr($url, 0, 1) != "#") { 
      if (substr($url, 0, 1) == '/') {
        $url = PATH_TO_REL . substr($url, 1, strlen($url));
      } elseif (!preg_match('/[a-z]+:/', $url, $matchs)) {
        $url == "#" ? $url = "#" : $url = PATH_TO_URI . $url;
      }
    }    
    return $url;
  }
}

?>