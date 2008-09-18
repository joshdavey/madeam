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
	 * version of madeam
	 */
  const version = '0.1';  
  
  /**
	 * define user errors variable name for $_SESSION
	 * example: $_SESSION[MADEAM_USER_ERROR_NAME];
	 */
  const user_error_name = 'muerrors';  
  
  /**
	 * this is used for passing misc data from one page to the other
	 */
  const flash_data_name	= 'mflash';
  
  /**
	 * this sets how many pages the flash has to live (ptl: pages to live)
	 */
  const flash_life_name	= 'mflife';
  
	/**
	 * this is used for passing post data from one page to the next
	 * the post data is merged with the flash post data on the next page
	 */
  const flash_post_name	= 'mfpost';
  
	/**
	 * Used for joining models and other associations
	 * example use: "user.name"
	 */
  const association_joint	= '.';
  

  /**
   * dispatches all operations to controller specified by uri
   *
   * @return boolean
   */
  public static function dispatch() {    
		// clear routes cache if it cache is disabled for routes
		if (!Madeam_Config::get('cache_routes')) { 
			Madeam_Cache::clear('madeam.routes');
		}
		
		// include routes
		// check cache for routes
		if (! Madeam_Router::$routes = Madeam_Cache::read('madeam.routes', - 1)) {
		  // include routes configuration
		  require PATH_TO_APP . 'Config' . DS . 'routes.php';
		
		  // save routes to cache
		  if (Madeam_Config::get('cache_routes')) {
		    Madeam_Cache::save('madeam.routes', Madeam_Router::$routes);
		  }
		}

    // set layout if it hasn't already been set
    if (!isset($_GET['layout'])) { $_GET['layout'] = 1; }
    
    // get current url
    $url = null;
    if (MADEAM_REWRITE_URI !== false) {
      $url = MADEAM_REWRITE_URI . '?' . $_SERVER['QUERY_STRING'];
    } else {
      $url = split(SCRIPT_FILENAME, $_SERVER['REQUEST_URI']);
      // check if it split it into 2 peices.
      // If it didn't then there is ending "index.php" so we assume there is no URI on the end either
      if (isset($url[1])) {
        $url = $url[1];
      }
    }
    
    // call controller action
    $output = Madeam::request($url, array_merge($_GET, $_POST, $_COOKIE));

    // destroy user error notices
    if (isset($_SESSION[self::user_error_name])) {
      unset($_SESSION[self::user_error_name]);
    }

    // destroy flash data when it's life runs out
    if (isset($_SESSION[self::flash_life_name])) {
      if (-- $_SESSION[self::flash_life_name] < 1) {
        unset($_SESSION[self::flash_life_name]);
        if (isset($_SESSION[self::flash_data_name])) {
          unset($_SESSION[self::flash_data_name]);
        }
      } else {
        if (isset($_SESSION[self::flash_data_name][self::flash_data_name])) {
          $_POST = array_merge($_SESSION[self::flash_data_name][self::flash_post_name], $_POST);
          $_SERVER['REQUEST_METHOD'] = 'POST';
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
  public static function request($uri, $params = array()) {
    // get request parameters from uri and merge them with other params
    // example input: 'posts/show/32'
    $params = array_merge($params, Madeam_Router::parseURI($uri));
        
    // because we allow controllers to be grouped into sub folders we need to recognize this when
    // someone tries to access them. For example if someone wants to access the 'admin/index' controller
    // they should be able to just type in 'admin' because 'index' is the default controller in that
    // group of controllers. To make this possible we check to see if a directory exists that is named
    // the same as the controller being called and then append the default controller name to the end
    // so 'admin' becomes 'admin/index' if the admin directory exists.
    // note: there is a consequence for this feature which means if you have a directory named 'admin'
    // you can't have a controller named 'Controller_Admin'
    if (is_dir(PATH_TO_CONTROLLER . ucfirst($params['controller']))) {
      $params['controller'] .= '/' . Madeam_Config::get('default_controller');
    }

    // set controller's class
    $params['controller'] = preg_replace("/[^A-Za-z0-9_\-\/]/", null, $params['controller']); // strip off the dirt
    $controllerClassNodes = split('/', $params['controller']);
    foreach ($controllerClassNodes as &$node) {
      $node = Madeam_Inflector::camelize($node);
      $node = ucfirst($node);
    }

    // set controller class
    $controllerClass = 'Controller_' . implode('_', $controllerClassNodes);

    try {
      // create controller instance
      $controller = new $controllerClass($params);
    } catch(Madeam_Exception_AutoloadFail $e) {
      if (is_dir(PATH_TO_VIEW . $params['controller'])) {
        $view = $params['controller'] . '/' . $params['action'];
        $params['controller'] = 'app';
        $controller = new Controller_App($params);
        $controller->view($view);
      } elseif (is_file(PATH_TO_VIEW . $params['controller'] . '.' . $params['format'])) {
        $view = $params['controller'];
        $params['action'] = $params['controller'];
        $params['controller'] = 'app';
        $controller = new Controller_App($params);
        $controller->view($view);
      } else {
        // no controller found = critical error.
        header("HTTP/1.1 404 Not Found");
        Madeam_Exception::catchException($e, array('message' => 'Missing Controller <strong>' . $controllerClass . '</strong>'));
      }
    }

    try {
      // process request
      $response = $controller->process();

      // delete controller
      unset($controller);

      // return response
      return $response;
    } catch (Madeam_Exception_MissingAction $e) {
      header("HTTP/1.1 404 Not Found");
      Madeam_Exception::catchException($e);
    } catch (Madeam_Exception_MissingView $e) {
      header("HTTP/1.1 404 Not Found");
      Madeam_Exception::catchException($e);
    }
  }

  /**
   * Enter description here...
   *
   * @param unknown_type $url
   * @param unknown_type $exit
   */
  public static function redirect($url, $exit = true) {
    if (! headers_sent()) {
      header('Location:  ' . self::url($url));
      if ($exit) {
        exit();
      }
    } else {
      throw new Madeam_Exception_HeadersSent('Tried redirecting when headers already sent. (Check for echos before redirects)');
    }
  }

  /**
   * Enter description here...
   *
   * @param unknown_type $url
   * @return unknown
   */
  public static function url($url) {
    if ($url == null || $url == '/') {
      return PATH_TO_URI;
    }

    if (substr($url, 0, 1) != "#") {
      if (substr($url, 0, 1) == '/') {
        $url = PATH_TO_REL . substr($url, 1, strlen($url));
      } elseif (! preg_match('/^[a-z]+:/', $url, $matchs)) {
        $url = PATH_TO_URI . $url;
      }
    }
    return $url;
  }
  
  public static function autoload($class) {
  	// set class file name
	  $file = str_replace('_', DS, $class) . '.php';
	  // include class file
	  if (file_lives($file)) {
	    require $file;
	  }
	
	  if (! class_exists($class, false) && ! interface_exists($class, false)) {
	    $class = preg_replace("/[^A-Za-z0-9_]/", null, $class); // clean the dirt
	    eval("class $class {}");
	    throw new Madeam_Exception_AutoloadFail('Missing Class ' . $class);
	  }
  }

  /**
   * When any file is modified in the application directory this method clears
   * the cache.
   *
   */
  public static function clearCacheOnUpdate() {}
}