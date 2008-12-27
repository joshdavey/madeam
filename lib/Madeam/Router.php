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

  // do we really need this
  public static $actionRequestMethodMap = array(
  array('_action' => 'index', '_method' => 'get', 'id' => false), 
  array('_action' => 'read', '_method' => 'get', 'id' => true), 
  array('_action' => 'delete', '_method' => 'delete', 'id' => true), 
  array('_action' => 'edit', '_method' => 'put', 'id' => true), 
  array('_action' => 'edit', '_method' => 'post', 'id' => false), 
  array('_action' => 'add', '_method' => 'post', 'id' => false));

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
      //$bits     = preg_explode('/[\/\.]/', $route);
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
  public static function parse($uri = false, $baseUri, $defaults) {
    // makes sure the first character is "/"
    if (substr($uri, 0, 1) != '/') {
      $uri = '/' . $uri;
    }
    
    // parse uri
    $parsedURI = parse_url($uri);
            
    // set uri
    if (isset($parsedURI['path']) && $baseUri == '/') {
      $uri = $parsedURI['path'];
    } elseif (isset($parsedURI['path'])) {
      // we do an explode because we can't always expect the uri path to be included
      // it's normally only inlcuded during the original request and all sub calls
      // consist of just the uri without the path to the uri
      // for example on request: /madeam/posts/view/32
      // and during the request in a controller: /posts/view/32
      $extractedPath = explode($baseUri, $parsedURI['path'], 2);
      $uri = array_pop($extractedPath);
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
      unset($query);
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
      throw new Madeam_Exception('Router: Unable to find page');
      // but what about returning the params if we throw an error?
      return $params;
    }

    // get params from uri
    $params = array_merge($defaults, $params, $get);        
    
    // check if this is an ajax call
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
      $params['_ajax'] = 1;
    } else {
    	$params['_ajax']	= 0;
    }    
  	
  	// automagically disable the layout when making an AJAX call
    if (!isset($params['_layout']) && $params['_ajax'] == 1) {
      $params['_layout'] = 0;
    } elseif (!isset($params['_layout'])) {
    	$params['_layout'] = 1;
    }
    
    // add query to params
    if (isset($query)) {
    	$params['_query'] = $query;
  	}
  	
    // set format
    if ($format !== false) {
    	$params['_format'] = $format;
    }
    
    return $params;
  }
}
