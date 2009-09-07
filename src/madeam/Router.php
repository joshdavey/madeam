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
class Router {

  public static $routes = array(); // regex, values, rules

  /**
   * This cool method adds paths by formatting the string the user has entered and turning it into a regular expression
   * which can be used to be compared against URIs.
   *
   * @param string $route
   * @param array $params
   * @author Joshua Davey
   */
  public static function connect($route, $values = array(), $rules = array()) {
    // break into pieces/bits
    $bits = explode('/', $route);
    $regex = null;

    // parse each bit into it's regular expression form
    foreach ($bits as $bit) {
      if (preg_match('/^:([a-zA-Z_]+)$/', $bit, $match)) {
        // named parameter
        $name = $match[1];
        if (isset($rules[$name])) {
          // named param with a rule
          $regex .= '(?:\\/(?P<' . $name . '>' . $rules[$name] . '))'; 
          // we don't need the rule anymore if its in the regex
          unset($rules[$name]); 
        } else {
          // named param with no rules
          $regex .= '(?:\\/(?P<' . $name . '>' . '[^\/]+))?';
        }
      } else {
        // a string
        $regex .= '\\/' . $bit;
      }
    }
    
    // build route's regexp
    $regex = '/^' . $regex . '\/?(?P<_extra>.*)$/';
    
    // add to routes list
    self::$routes[] = array($regex, $values, $rules);
  }
  
  /**
   * Default routes for a RESTful resource.
   * 
   * To use a slug instead of an id or to simply change the rule for the id the $id param
   * has a value for the id's name and the id's rule. For example if I wanted to call "id"
   * "slug" and change the rule to one that matches slugs I could do the following:
   * 
   * madeam\Router::resource::('posts', array('slug', '[a-z\-]+'));
   *
   * @param string $name
   * @param array $id
   * @author Joshua Davey
   */
  public static function resource($name, $id = array('id', '\d+')) {
    self::connect("$name",            array('_action' => 'index',   '_controller' => $name),  array('_method' => 'get'));
    self::connect("$name/:$id[0]",    array('_action' => 'show',    '_controller' => $name),  array('_method' => 'get', $id[0] => $id[1]));
    self::connect("$name",            array('_action' => 'delete',  '_controller' => $name),  array('_method' => 'delete'));
    self::connect("$name",            array('_action' => 'update',  '_controller' => $name),  array('_method' => 'put'));
    self::connect("$name",            array('_action' => 'create',  '_controller' => $name),  array('_method' => 'post'));
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
   *  index/test?blah=nah
   *
   * @param string $uri
   * @return array
   * @author Joshua Davey
   */
  public static function parse($uri = false, $baseUri = '/', $defaults = array()) {    
    // makes sure the first character is "/"
    // if (substr($uri, 0, 1) != '/') { $uri = '/' . $uri; }
    substr($uri, 0, 1) == '/' ?: $uri = '/' . $uri;
    
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

    // set get
    $get = array();
    if (isset($parsedURI['query'])) {
      $query = $parsedURI['query'];
      // retrieve $_GET vars manually from uri -- so we can enter the uri as index/index?foo=bar when calling a component from the view
      parse_str($query, $get); // assigns $get array of query params
    }
    
    
    // add query to params
    !isset($query) ?: $defaults['_query'] = $query;
    
    // set default format
    $format === false ?: $defaults['_format'] = $format;

    // matchs count
    $matchs = 0;

    // match uri to route map
    foreach (self::$routes as $route) {
      if (preg_match($route[0], $uri, $match)) {
        // set default params
        $params = $route[1]; // default values
        $rules  = $route[2]; // param rules
        
        // set _uri param for websites that don't have mod_rewrite
        // sites with mod_rewrite have _uri assigned automatically in the .htaccess file
        $params['_uri'] = $match[0];
        
        // clean param matchs by removing nulls and preg_match's numbered results
        // every other match is a numbered preg_match result
        $index = 0;
        foreach ($match as $key => $val) {
          if ($val == null) {
            unset($match[$key]); // remove null values in order for the default values to work
          } elseif (++$index % 2) {
            unset($match[$key]); // remove preg_match's numbered results
          }
        }
        
        // merge default param values with matched params
        $params = array_merge($defaults, $params, $match);
        
        // check each rule against its associated param.
        // if it fails then we break out of this loop and continue to the next route
        $continue = false;
        foreach ($rules as $rule => $val) {
          if (!isset($params[$rule]) || $params[$rule] !== $val) {
            $continue = true;
            break;
          }
        }        
        if ($continue === true) { continue; }
        
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
      throw new madeam\Exception('Unable to find page');
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
    
    return $params;
  }
}
