<?php
namespace madeam;
/**
 * Madeam PHP Framework <http://madeam.com>
 * Copyright (c)  2009, Joshua Davey
 *                202-212 Adeliade St. W, Toronto, Ontario, Canada
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright    Copyright (c) 2009, Joshua Davey
 * @link        http://www.madeam.com
 * @package      madeam
 * @license      http://www.opensource.org/licenses/mit-license.php The MIT License
 */
class Framework {
  
  static public $environment = 'development';
  static public $uriPathRoot = '/';
  static public $uriPathPublic = '/public/';
  static public $pathToProject = '/';
  static public $middleware = array();
  
  
  /**
   * 
   * @author Joshua Davey
   * @param string $environment
   * @param array $request
   * @param array $server
   */
  static public function setup($request, $application_document_root, $server_document_root, $server_request_uri, $server_query_string, $server_request_method) {
    
    // add ending / to document root if it doesn't exist -- important because it differs from unix to windows (or I think that's what it is)
    if (substr($server_document_root, - 1) != '/') { $server_document_root .= '/'; }
    
    // set views directory -- does this belong here?...
    View::$path = self::$pathToProject . 'app/views/';
    
    // set query string
    $request['_query'] = $server_query_string;
    
    // set path to uri based on whether mod_rewrite is turned on or off.
    if (isset($request['_uri'])) {
      self::$uriPathRoot = self::cleanUriPath($server_document_root, $application_document_root);
    } else {
      self::$uriPathRoot = self::dirtyUriPath($server_document_root, $application_document_root);
      $url = explode('index.php', $server_request_uri);
      // check if it split into 2 peices.
      // If it didn't then there is an ending "index.php" so we assume there is no URI on the end either
      if (isset($url[1])) {
        $request['_uri'] = $url[1];
      } else {
        $request['_uri'] = '/';
      }
    }
    
    // determine the relative path to the public directory
    self::$uriPathPublic = self::pubPath($server_document_root, $application_document_root);
    
    // if the absolute path to the public directory can't be established based on the uriPathPublic
    // we've derived then it's likely the developer is using symlinks to point to their project.
    // In this case we can't determine the paths.
    // Most likely the user has advanced priveledges and is able to set the DocumentRoot in the apache
    // config to point to "path/to/project/public/" and therefore all of our relative paths can be
    // set to "/".
    // 
    // Therefore if the developer is using symlinks they must point their DocumentRoot to Madeam's public
    // directory or everything will explode.
    if (!file_exists($server_document_root . self::$uriPathPublic)) {
      self::$uriPathPublic = '/';
      self::$uriPathRoot = '/';
    }
    
    // set layout if it hasn't already been set
    isset($request['_layout']) ?: $request['_layout'] = 1;
    
    // set overriding request method -- note: we need to get rid of all the $_SERVER references for testing purposes
    if (isset($server['X_HTTP_METHOD_OVERRIDE'])) {
      $request['_method'] = strtolower($server['X_HTTP_METHOD_OVERRIDE']);
    } elseif (isset($request['_method']) && $server_request_method == 'POST') {
      $request['_method'] = strtolower($request['_method']);
    } else {
      $request['_method'] = strtolower($server_request_method);
    }
    
    // check if this is an ajax call
    if (isset($server['HTTP_X_REQUESTED_WITH']) && $server['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
      $request['_ajax'] = 1;
    } else {
      $request['_ajax'] = 0;
    }
    
    return $request;
  }

  /**
   * dispatches all operations to controller specified by uri
   *
   * @return boolean
   * @author Joshua Davey
   */
  static public function dispatch($request) {
    
    // parse request with router
    $request = Router::parse($request['_uri'] . '?' . $request['_query'], self::$uriPathRoot, $request + array(
      '_controller' => 'index',
      '_action'     => 'index',
      '_format'     => 'html'
    ));
    
    // execute beforeRequest middleware
    foreach (self::$middleware as $class) {
      $request = $class::beforeRequest($request);
    }
    
    // make request
    $response = self::control($request);
    
    // execute beforeResponse middleware
    foreach (self::$middleware as $class) {
      $response = $class::beforeResponse($request, $response);
    }
    
    // return output
    return $response;
  }

  
  /**
   * undocumented 
   *
   * @author Joshua Davey
   * @param array $request
   */
  static public function control($request) {    
    // because we allow controllers to be grouped into sub folders we need to recognize this when
    // someone tries to access them. For example if someone wants to access the 'admin/index' controller
    // they should be able to just type in 'admin' because 'index' is the default controller in that
    // group of controllers. To make this possible we check to see if a directory exists that is named
    // the same as the controller being called and then append the default controller name to the end
    // so 'admin' becomes 'admin/index' if the admin directory exists.
    // note: there is a consequence for this feature which means if you have a directory named 'admin'
    // you can't have a controller named 'Controller_Admin'
    if (is_dir('app/src/Controller/' . ucfirst($request['_controller']))) {
      $request['_controller'] .= '/' . 'index';
    }
    
    // set controller's class
    $controllerClassNodes = explode('/', $request['_controller']);
    foreach ($controllerClassNodes as &$node) {
      $node = Inflector::camelize($node);
      $node = ucfirst($node);
    }
    
    // set controller class
    $controllerClass = implode('\\', $controllerClassNodes) . 'Controller';
    
    try {
      $controller = new $controllerClass();
    } catch (Exception\AutoloadFail $e) {
      if (is_dir(self::$pathToProject . 'app/views/' . $request['_controller'])) {
        $view = $request['_controller'] . '/' . $request['_action'];
        $request['_controller'] = 'app';
        $controller = new \AppController();
        $controller->view($view);
      } elseif (is_file(self::$pathToProject . 'app/views/' . $request['_controller'] . '/' . $request['_action'] . '.' . $request['_format'])) {
        $view = $request['_controller'];
        $request['_action'] = $request['_controller'];
        $request['_controller'] = 'app';
        $controller = new \AppController();
        $controller->view($view);
      } else {
        // no controller or view found = critical error.
        //header("HTTP/1.1 404 Not Found");
        Exception::handle($e, array('message' => 'Missing Controller <strong>' . $controllerClass . "</strong> \n Create File: <strong>app/Controller/" . str_replace('_', DS, $controllerClass) . ".php</strong> \n <code>&lt;?php \n class $controllerClass extends AppController {\n\n  &nbsp; public function " . Inflector::camelize(lcfirst($request['_action'])) . "Action() {\n &nbsp;&nbsp;&nbsp; \n &nbsp; }\n\n   }</code>"));
        return;
      }
    }

    try {
      // process request
      $response = $controller->process($request);
      
      // delete controller
      unset($controller);

      // return response
      return $response;
    } catch (controller\exception\MissingAction $e) {
      //header("HTTP/1.1 404 Not Found");
      Exception::handle($e);
      return;
    } catch (controller\exception\MissingView $e) {
      header("HTTP/1.1 404 Not Found");
      Exception::handle($e);
      return;
    }
  }
  
  
  /**
   * This method returns a clean base uri path.
   * 
   * /apache/document_root/website/  => /website/
   * /apache/document_root/          => /
   * 
   * @param $docRoot 
   * @param $pathToPublic
   * @author Joshua Davey
   */
  static public function cleanUriPath($docRoot, $pathToPublic) {
    return '/' . substr(str_replace(DIRECTORY_SEPARATOR, '/', substr($pathToPublic, strlen($docRoot), -strlen(basename($pathToPublic)))), 0, -1) . '/';
  }
  
  /**
   * This method returns a base uri path but includes the "index.php" at the end, hence the dirty part. This is used
   * for sites that don't have mod_rewrite enabled and required the "index.php" at the end.
   * 
   * /apache/document_root/website/  => /website/index.php/
   * /apache/document_root/          => /index.php/
   * 
   * @param $docRoot 
   * @param $pathToPublic
   * @author Joshua Davey
   */
  static public function dirtyUriPath($docRoot, $pathToPublic) {
    return '/' . str_replace(DIRECTORY_SEPARATOR, '/', substr(substr($pathToPublic, strlen($docRoot)), 0, -strlen(DIRECTORY_SEPARATOR . basename($pathToPublic)))) . 'index.php/';
  }
  
  /**
   * This method returns the relative path to the public directory
   * 
   * /apache/document_root/website/  => /website/public/
   * /apache/document_root/          => /public/
   * 
   * @param $docRoot
   * @param $pathToPublic
   * @author Joshua Davey
   */
  static public function pubPath($docRoot, $pathToPublic) {
    return '/' . str_replace(DIRECTORY_SEPARATOR, '/', substr($pathToPublic, strlen($docRoot)));
  }

  /**
   * Enter description here...
   *
   * @param string $url
   * @param boolean $exit
   * @author Joshua Davey
   */
  static public function redirect($url, $exit = true) {
    if (! headers_sent()) {
      header('Location:  ' . self::url($url));
      if ($exit) {
        exit();
      }
    } else {
      throw new Exception\HeadersSent('Tried redirecting when headers already sent. (Check for echos before redirects)');
    }
  }
  
  /**
   * This method is used for creating application urls and external urls.
   * For the examples below assume the website is located at "apache/htdocs/website/"
   * 
   * URL:
   * posts/show         => /website/posts/show/
   * 
   * Relative URL: (beings with /)
   * /imgs/header.png   => /website/public/imgs/header.png
   * 
   * External URL: (beings with a protocol)
   * http://example.com => http://example.com
   *
   * @param string $url
   * @return string
   * @author Joshua Davey
   */
  static public function url($url) {
    if ($url == null || $url == '/') {
      return self::$uriPathRoot;
    }

    if (substr($url, 0, 1) != "#") {
      if (substr($url, 0, 1) == '/') {
        $url = self::$uriPathPublic . substr($url, 1, strlen($url));
      } elseif (! preg_match('/^[a-z]+:/', $url, $matchs)) {
        $url = self::$uriPathRoot . $url;
      }
    }
    return $url;
  }

  
}