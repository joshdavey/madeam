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

  public static function resource($name) {
    self::connect("$name",      array('_action' => 'index',   '_controller' => $name),  array('_method' => 'get'));
    self::connect("$name/:id",  array('_action' => 'show',    '_controller' => $name),  array('_method' => 'get'));
    self::connect("$name",      array('_action' => 'delete',  '_controller' => $name),  array('_method' => 'delete'));
    self::connect("$name",      array('_action' => 'update',  '_controller' => $name),  array('_method' => 'put'));
    self::connect("$name",      array('_action' => 'create',  '_controller' => $name),  array('_method' => 'post'));
  }

  /**
   * This cool method adds paths by formatting the string the user has entered and turning it into a regular expression
   * which can be used to be compared against URIs.
   *
   * @param string $route
   * @param array $params
   * @author Joshua Davey
   */
  public static function connect($route, $params = array(), $rules = array()) {
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
      self::$routes[] = array($regexp, $names, $params, $rules);
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
   * @author Joshua Davey
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
    
    
    $format = false;
    
    // grab format if it exists in the uri and strip it from the uri
    // index.html => format = 'html' && uri = 'index'
    $dotPosition = (int) strrpos($uri, '.');
    if ($dotPosition !== 0) {
      $format = substr($uri, $dotPosition + 1);
      $uri    = substr($uri, 0, $dotPosition);
    }
    
    
    // TODO: Benchmark this format and URI code against the code above
    //$URIAnatomy = explode('.', $uri, 2);
    //if (count($URIAnatomy) > 1) {
    //  $format = array_pop($URIAnatomy);
    //  $uri = implode($URIAnatomy);
    //} else {
    //  $uri = $URIAnatomy[0];
    //}
   

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
        //test($match);
        
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
      //header("HTTP/1.1 404 Not Found");
      //ob_clean();
      throw new Madeam_Exception('Unable to find page');
      // but what about returning the params if we throw an error?
      return $params;
    }

    // get params from uri
    $params = array_merge($defaults, $params, $get);
  	
  	// automagically disable the layout when making an AJAX call
    if (!isset($params['_layout']) && isset($params['_ajax']) && $params['_ajax'] == 1) {
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
