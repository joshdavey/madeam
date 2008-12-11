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

// directory splitter
if (! defined('DS')) {
  define('DS', DIRECTORY_SEPARATOR);
}

// idea... use this as a last resort when all autoloads fail.
// have this one throw an exception or make a last resort to check every path for the file.
spl_autoload_register('Madeam::autoload');

/**
 * Set exception handler
 */
set_exception_handler('Madeam_UncaughtException');

/**
 * Set error handler
 */
set_error_handler('Madeam_ErrorHandler');


// function library
// ========================================================
/**
 * This function exists as a quick tool for developers to test
 * if a function executes and how many times.
 */
function test($var = null) {
  static $tests;
  $tests ++;
  for ($i = 0; $i < (6 - strlen($tests)); $i ++) {
    $tests = '0' . $tests;
  }
  
  if (is_array($var) || is_object($var)) {
    echo '<br /><pre>[TEST::' . $tests . '] &nbsp;&nbsp;' . "\n";
    print_r($var);
    echo ' &nbsp;&nbsp;</pre>' . "\n";
  } elseif (is_bool($var)) {
    if ($var === true) {
      $var = 'TRUE';
    } else {
      $var = 'FALSE';
    }
    
    echo "<br /> [TEST::" . $tests . "] &nbsp;&nbsp;" . (string) $var . "&nbsp;&nbsp;  \n";
  } else {
    echo "<br /> [TEST::" . $tests . "] &nbsp;&nbsp;" . $var . "&nbsp;&nbsp;  \n";
  }
}

/**
 * Re-named strtoupper
 *
 * @param string $word
 * @return string
 */
function up($word) {
  return strtoupper($word);
}

/**
 * Re-named strtolower
 *
 * @param string $word
 * @return string
 */
function low($word) {
  return strtolower($word);
}

/**
 * Checks to see if a relative file exists by checking each include path.
 * Special thanks to Ahmad Nassri from PHP-Infinity for the proof of concept.
 *
 * @param string $file
 * @return boolean
 */
function fileLives($file) {
  $paths = explode(PATH_SEPARATOR, get_include_path());
  
  foreach ($paths as $path) {
    if (is_file($path . $file)) {
      return true;
    }
  }
  
  return false;
}

/**
 * Enter description here...
 *
 * @param unknown_type $e
 */
function Madeam_UncaughtException($e) {
  Madeam_Exception::catchException($e, array('message' => "Uncaught Exception: \n" . $e->getMessage()));
  return true;
}

/**
 * Enter description here...
 *
 * @param unknown_type $code
 * @param unknown_type $string
 * @param unknown_type $file
 * @param unknown_type $line
 */
function Madeam_ErrorHandler($code, $string, $file, $line) {
  // return regular PHP errors when they're non-fatal
  if ($code == 2 || $code == 4 || $code == 8) { return false; }

  $exception = new Madeam_Exception($string, $code);
  throw $exception;
  return true;
}


// include core files
$cd = dirname(__FILE__) . DS . 'Madeam' . DS;
require $cd . 'Controller.php';
require $cd . 'Inflector.php';
require $cd . 'Router.php';
require $cd . 'Config.php';
require $cd . 'Cache.php';
require $cd . 'Registry.php';
require $cd . 'Logger.php';


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
  const version = '0.2 Alpha';
  
  /**
	 * define user errors variable name for $_SESSION
	 * example: $_SESSION[Madeam::userErrorName];
	 */
  const userErrorName = 'muerrors';  
  
  /**
	 * this is used for passing misc data from one page to the other
	 */
  const flashDataName	= 'mflash';
  
  /**
	 * this sets how many pages the flash has to live (ptl: pages to live)
	 */
  const flashLifeName	= 'mflife';
  
	/**
	 * this is used for passing post data from one page to the next
	 * the post data is merged with the flash post data on the next page
	 */
  const flashPostName	= 'mfpost';
  
	/**
	 * Used for joining models and other associations
	 * example use: "user.name"
	 */
  const associationJoint	= '.';
  
  const defaultController = 'index';
  
  const defaultAction     = 'index';
  
  const defaultFormat     = 'html';
  
  const errorController   = 'error';
  
  /**
   *
   */
  public static $requestUri         = '/';
  public static $requestParams      = array();
  
  public static $environment        = 'development';
  
  public static $pathToProject      = false;
  public static $pathToPublic       = false;
  public static $pathToMadeam       = false;
  public static $pathToApp          = false;
  public static $pathToLib          = false;
  public static $pathToEtc          = false;
                                    
  public static $pathToUri          = false;
  public static $pathToRel          = false;
  
  
  
  public static function cleanUriPath($docRoot, $publicPath) {
    return '/' . substr(str_replace(DS, '/', substr($publicPath, strlen($docRoot), -strlen(basename($publicPath)))), 0, -1);
  }
  
  public static function dirtyUriPath($docRoot, $publicPath) {
    return '/' . str_replace(DS, '/', substr(substr($publicPath, strlen($docRoot)), 0, -strlen(DS . basename($publicPath)))) . 'index.php/';
  }
  
  public static function relPath($docRoot, $publicPath) {
    return '/' . str_replace(DS, '/', substr($publicPath, strlen($docRoot)));
  }
  
  /**
   * This method defines all the absolute file paths to all the important
   * Madeam directories.
   * It returns the file paths that should be added to the include_path.
   * 
   * @param string $publicDirectory -- absolute path to the public directory
   * @return array -- list of all paths to be added to the include_path
   */
  public static function paths($publicDirectory) {
    // set public directory path
    self::$pathToPublic   = $publicDirectory;
    
    // set path to entire project
    self::$pathToProject  = dirname(self::$pathToPublic) . DS;
    
    // set application path
    self::$pathToApp      = self::$pathToProject . 'app' . DS;
    
    // set library path
    self::$pathToLib      = self::$pathToProject . 'lib' . DS;
    
    // set etc path
    self::$pathToEtc      = self::$pathToProject . 'etc' . DS;
    
    // set path to madeam
    self::$pathToMadeam   = dirname(__FILE__) . DS . 'Madeam' . DS;
    
    // return paths for include path
    return array(self::$pathToApp, self::$pathToLib, self::$pathToMadeam . 'Helper' . DS);
  }
  
  
  /**
   * 
   */
  public static function setup($environment, $params, $server, $cfg = array()) {    
    // check for expected server parameters
  	$diff = array_diff(array('DOCUMENT_ROOT', 'REQUEST_URI', 'QUERY_STRING', 'REQUEST_METHOD'), array_keys($server));
  	if (!empty($diff)) {
  	  throw new Madeam_Exception_MissingExpectedParam('Missing expected server Parameter(s).');
  	}
    
    // add ending / to document root if it doesn't exist -- important because it differs from unix to windows (or I think that's what it is)
    if (substr($server['DOCUMENT_ROOT'], - 1) != '/') { $server['DOCUMENT_ROOT'] .= '/'; }
    
    // define environment
    self::$environment = $environment;
    
    // set request params
    self::$requestParams = $params;
    
      
    // set path to uri based on whether mod_rewrite is turned on or off.
    if (isset(self::$requestParams['_uri'])) {
      self::$pathToUri = self::cleanUriPath($server['DOCUMENT_ROOT'], self::$pathToPublic);
      self::$requestUri = self::$requestParams['_uri'] . '?' . $server['QUERY_STRING'];
    } else {
      self::$pathToUri = self::dirtyUriPath($server['DOCUMENT_ROOT'], self::$pathToPublic);
      $url = explode('index.php', $server['REQUEST_URI']);
      // check if it split into 2 peices.
      // If it didn't then there is an ending "index.php" so we assume there is no URI on the end either
      if (isset($url[1])) {
        self::$requestUri = $url[1];
      } else {
        self::$requestUri = '/';
      }
    }
    
    // determine the relative path to the public directory
    self::$pathToRel = self::relPath($server['DOCUMENT_ROOT'], self::$pathToPublic);    
    
    // set layout if it hasn't already been set
    if (!isset(self::$requestParams['_layout'])) { self::$requestParams['_layout'] = 1; }
    
    // set overriding request method -- note: we need to get rid of all the $_SERVER references for testing purposes
    if (isset($server['X_HTTP_METHOD_OVERRIDE'])) {
      self::$requestParams['_method'] = low($server['X_HTTP_METHOD_OVERRIDE']);
    } elseif (isset(self::$requestParams['_method']) && $server['REQUEST_METHOD'] == 'POST') {
      self::$requestParams['_method'] = low($params['_method']);
    } else {
      self::$requestParams['_method'] = low($server['REQUEST_METHOD']);
    }
    
    
    // configure core classes
    Madeam_Cache::$path   = self::$pathToEtc . 'cache' . DS;
    Madeam_Logger::$path  = self::$pathToEtc . 'log' . DS;
    
    // include base setup configuration
    if (empty($cfg)) {
      if (file_exists(self::$pathToApp . 'Config' . DS . 'setup.local.php')) {
        require self::$pathToApp . 'Config' . DS . 'setup.local.php';
      } else {
        require self::$pathToApp . 'Config' . DS . 'setup.php';
      }
    }

    // save configuration
    Madeam_Config::set($cfg);
    unset($cfg);
  }

  /**
   * dispatches all operations to controller specified by uri
   *
   * @return boolean
   */
  public static function dispatch() {
    
		// include routes
		// check cache for routes
		if (! Madeam_Router::$routes = Madeam_Cache::read('madeam.routes', - 1, Madeam_Config::get('ignore_routes_cache'))) {
		  // include routes configuration
		  require self::$pathToApp . 'Config' . DS . 'routes.php';
		
		  // save routes to cache
		  if (Madeam_Config::get('ignore_routes_cache') === false) {
		    Madeam_Cache::save('madeam.routes', Madeam_Router::$routes);
		  }
		}
    
    // make request
    $output = Madeam::request(self::$requestUri, self::$requestParams);

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
   * @param string $uri -- example: controller/action/32?foo=bar
   * @param array $params
   * @return string
   */
  public static function request($uri, $params = array()) {
    // get request parameters from uri and merge them with other params
    // example input: 'posts/show/32'
    $params = array_merge($params, Madeam_Router::parse($uri, self::$pathToUri, self::defaultController, self::defaultAction, self::defaultFormat));    
    
    // because we allow controllers to be grouped into sub folders we need to recognize this when
    // someone tries to access them. For example if someone wants to access the 'admin/index' controller
    // they should be able to just type in 'admin' because 'index' is the default controller in that
    // group of controllers. To make this possible we check to see if a directory exists that is named
    // the same as the controller being called and then append the default controller name to the end
    // so 'admin' becomes 'admin/index' if the admin directory exists.
    // note: there is a consequence for this feature which means if you have a directory named 'admin'
    // you can't have a controller named 'Controller_Admin'
    if (is_dir(self::$pathToApp . 'Controller' . DS . ucfirst($params['_controller']))) {
      $params['_controller'] .= '/' . self::defaultController;
    }
    
    // set controller's class
    $params['_controller'] = preg_replace("/[^A-Za-z0-9_\-\/]/", null, $params['_controller']); // strip off the dirt
    $controllerClassNodes = explode('/', $params['_controller']);
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
      if (is_dir(self::$pathToApp . 'View' . DS . $params['_controller'])) {
        $view = $params['_controller'] . '/' . $params['_action'];
        $params['_controller'] = 'app';
        $controller = new Controller_App($params);
        $controller->view($view);
      } elseif (is_file(self::$pathToApp . 'View' . DS . $params['_controller'] . '.' . $params['_format'])) {
        $view = $params['_controller'];
        $params['_action'] = $params['_controller'];
        $params['_controller'] = 'app';
        $controller = new Controller_App($params);
        $controller->view($view);
      } else {
        // no controller or view found = critical error.
        header("HTTP/1.1 404 Not Found");
        Madeam_Exception::catchException($e, array('message' => 'Missing Controller <strong>' . $controllerClass . "</strong> \n Create File: <strong>" . str_replace('_', DS, $controllerClass) . ".php</strong> \n <code>php \n class $controllerClass extends Controller_App {\n}</code>"));
      }
    }

    try {
      // process request
      $response = $controller->process();

      // delete controller
      unset($controller);

      // return response
      return $response;
    } catch (Madeam_Controller_Exception_MissingAction $e) {
      header("HTTP/1.1 404 Not Found");
      Madeam_Exception::catchException($e);
    } catch (Madeam_Controller_Exception_MissingView $e) {
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
      return self::$pathToUri;
    }

    if (substr($url, 0, 1) != "#") {
      if (substr($url, 0, 1) == '/') {
        $url = self::$pathToRel . substr($url, 1, strlen($url));
      } elseif (! preg_match('/^[a-z]+:/', $url, $matchs)) {
        $url = self::$pathToUri . $url;
      }
    }
    return $url;
  }
  
  public static function autoload($class) {
  	// set class file name
	  //$file = preg_replace('/[\_]/', DS, $class) . '.php'; // for PHP 5.3+
	  $file = str_replace('_', DS, $class) . '.php';
	  
	  // include class file
	  if (fileLives($file)) {
	    require $file;
	  }
	
	  if (! class_exists($class, false) && ! interface_exists($class, false)) {
	    $class = preg_replace("/[^A-Za-z0-9_]/", null, $class); // clean the dirt
	    eval("class $class {}");
	    throw new Madeam_Exception_AutoloadFail('Missing Class ' . $class);
	  }
  }
  
}