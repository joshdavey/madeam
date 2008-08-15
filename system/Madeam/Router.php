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
class Madeam_Router {

  public static $routes = array(); // regex, names, params

  public static $links = array(); // a place to store the magic smart links

  // do we really need this
  public static $actionRequestMethodMap = array(array('action' => 'index', 'method' => 'get', 'id' => false), array('action' => 'show', 'method' => 'get', 'id' => true), array('action' => 'view', 'method' => 'get', 'id' => true), // same as show
array('action' => 'delete', 'method' => 'delete', 'id' => true), array('action' => 'edit', 'method' => 'put', 'id' => true), array('action' => 'edit', 'method' => 'post', 'id' => false), array('action' => 'add', 'method' => 'post', 'id' => false));

  public static $resourceMap = array();

  /**
   * This cool method adds paths by formatting the string the user has entered and turning it into a regular expression
   * which can be used to be compared against URIs.
   *
   * @param string $route
   * @param array $params
   */
  public static function connect($route, $params = array()) {
    if (! is_array(self::$routes)) {
      self::$routes = array();
    }

    // root route - doesn't require parsing
    if ($route == '' || $route == '/') {
      self::$routes[] = array('/^\/*$/', array(), $params);
      // parse route
    } else {
      // break into pieces/bits
      //$bits     = preg_split('/[\/\.]/', $route);
      $bits = explode('/', $route);
      $mini_exp = $names = array();
      $bitkey = 0; // key for named bits

      // parse each bit into it's regular expression form
      foreach ($bits as $bit) {
        if (preg_match('/^:([a-zA-Z_]+)$/', $bit, $match)) {
          // named parameter
          $bitkey ++;
          $name = $match[1];
          if (isset($params[$name])) {
            //$mini_exp[] = '(?:\\/(?P<' . $name . '>' . $params[$name] . '){1})';
            //$mini_exp[] = '(?:\\/(' . $params[$name] . '){1})';
            $mini_exp[] = '(?:\\/(' . $params[$name] . '))';
          } else {
            //$mini_exp[] = '(?:\\/(?P<' . $name . '>' . '[^\/]+))?';
            $mini_exp[] = '(?:\\/([^\/]+))?';
          }
          $names[$bitkey] = $name;
        } else {
          // a string
          $mini_exp[] = '\\/' . $bit;
        }
      }
      
      // build route's regexp
      $regexp = '/^' . implode('', $mini_exp) . '\/?(.*)$/';
      
      // add to routes list
      self::$routes[] = array($regexp, $names, $params);
    }
  }

  /**
   * This method takes a URL and parses it for parameters
   *
   * Parameters (params) can be passed to the framework by adding a get query to the end of a url like so: ?foo=bar
   * Or by defining params in the routes configuration file @see config/routes.php
   *
   * If no values have been assigned to madeam's special params then default values are assigned
   * which can be defined in the configuration @see config/setup.php
   *
   * This method excepts URIs in anyformat.
   * Examples:
   *  http://localhost/website/index?foo=bar
   *  index/test?blah=nah
   *
   * @param string $uri
   * @return array
   */
  public static function parseURI($uri = false) {
    // parse uri
    $parsedURI = parse_url($uri);
        
    // set uri
    if (isset($parsedURI['path'])) {
      $extracted_path = explode(PATH_TO_URI, $parsedURI['path'], 2);
      $uri = array_pop($extracted_path);
    } else {
      $uri = null;
    }

    // set format
    $format = false;
    $URIAnatomy = explode('.', $uri, 2);
    if (count($URIAnatomy) > 1) {
      $format = array_pop($URIAnatomy);
      $uri = implode($URIAnatomy);
    } else {
      $uri = $URIAnatomy[0];
    }

    // set get
    $get = array();
    if (isset($parsedURI['query'])) {
      $query = $parsedURI['query'];
      // retrieve $_GET vars manually from uri -- so we can enter the uri as index/index?foo=bar when calling a component from the view
      parse_str($query, $get); // assigns $get array of query params
    }
    
    // makes sure the first character is "/"
    if (substr($uri, 0, 1) != '/') {
      $uri = '/' . $uri;
    }

    // define params as array
    $params = array();

    // matchs count
    $matchs = 0;

    // match uri to route map
    foreach (self::$routes as $route) {
      if (preg_match($route[0], $uri, $match)) {
        // set default params
        $params = $route[2]; // default values
        
        // set derived params
        foreach ($route[1] as $key => $name) {
          if ($match[$key] != null) {
            $params[$name] = $match[$key];
          }
        }
        
        // flag as matched
        $matchs++;
        
        // we've found our match and now we're done here
        break;
      }
    }

    if ($matchs == 0) {
      // this is lame and needs to be done better
      header("HTTP/1.1 404 Not Found");
      //ob_clean();
      throw new Madeam_Exception('Unable to find page');
      // but what about returning the params if we throw an error?
      return $params;
    }
    
    // get params from uri
    $params = array_merge($params, $get);
    
    // set request method in case it hasn't been set (command line environment)
    if (!isset($_SERVER['REQUEST_METHOD'])) { $_SERVER['REQUEST_METHOD'] = 'GET'; }
    
    // set overriding request method
    if (!isset($params['method']) && isset($_SERVER['X_HTTP_METHOD_OVERRIDE'])) {
    	$params['method'] = low($_SERVER['X_HTTP_METHOD_OVERRIDE']);
    } elseif (isset($params['method']) && $_SERVER['REQUEST_METHOD'] != 'POST') {
    	$params['method'] = 'get';
    } else {
    	$params['method'] = low($_SERVER['REQUEST_METHOD']);
    }
        
    // check if this is an ajax call
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
      $params['ajax'] = 1;
    } else {
    	$params['ajax']	= 0;
    }    
  	
  	// automagically disable the layout when making an AJAX call
    if (!isset($params['layout']) && !Madeam_Config::get('enable_ajax_layout') && $params['ajax'] == 1) {
      $params['layout'] = 0;
    } elseif (!isset($params['layout'])) {
    	$params['layout'] = 1;
    }
    
    // add uri to params
    $params['uri'] = $uri;
    
    // add query to params
    if (isset($query)) {
    	$params['query'] = $query;
  	}
        
    // set default controller
    if (!isset($params['controller']) || $params['controller'] == null) {
    	$params['controller'] = Madeam_Config::get('default_controller');
    }
    
    // set default action
    if (!isset($params['action']) || $params['action'] == null) {
    	$params['action'] = Madeam_Config::get('default_action');
    }
    
    // set default format
    if (!isset($format) || $format == null) {
    	$params['format'] = Madeam_Config::get('default_format');
    } else {
    	$params['format'] = $format;
    }
    
    return $params;
  }
}
