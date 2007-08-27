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

class madeam_router {
  static $routes  = array(); // regex, names, params, http_request_method -- damn these really need to be cached! (Store them in a text file?)
  static $links   = array(); // a place to store the magic smart links

  /**
   * When intercepting "connect" method calls we are check to see if they are special in anyway.
   * A connect method with something on the end like "_article_add" means that it should be added to the self::$links array.
   * Helpers, like an html helper will be able to use these links to create anchors.
   *
   * @param string $method
   * @param array $args
   */
  public function __call($method, $args) {
    $match = array();
    if (preg_match("/connect_(.*)/", $method, $match)) {
      $link   = $match[1];
      $route  = $args[0];
      $params = $args[1];
      // get var name
      self::$links[$link] = $route;
      
      $this->connect($route, $params);
    }
  }
  
  /**
   * Early stages of defining a resource. This is a good start.
   * It adds all the necessary routes for a controller
   *
   * @param string $name
   */
  public function resource($name) {
    $plural = madeam_inflector::pluralize($name);
    $this->{'connect_' . $plural}($plural . '.:format', array('controller' => $name, 'action' => 'index')); 
    $this->{'connect_' . $name . '_add'}($name . '/add.:format', array('controller' => $name, 'action' => 'add'));
    $this->{'connect_' . $name . '_edit'}($name . '/edit/:id.:format', array('controller' => $name, 'action' => 'edit'));
    $this->{'connect_' . $name . '_show'}($name . '/:id.:format', array('controller' => $name, 'action' => 'show'));
    $this->{'connect_' . $name . '_delete'}($name . '/delete/:id.:format', array('controller' => $name, 'action' => 'delete'));
  }
  
  /**
   * This cool method adds paths by formatting the string the user has entered and turning it into a regular expression
   * which can be used to be compared against URIs.
   *
   * @param string $route
   * @param array $params
   * @param string $http_method (GET, POST, DELETE?, PUT?)
   */
  public function connect($route, $params = array(), $http_request_method = 'GET') {
    // root route - doesn't require parsing
    if ($route == '' || $route == '/') {
			self::$routes[] = array('/^[\/]*$/', array(), $params);
    // parse route
    } else {
      // break into pieces/bits
      $bits     = preg_split('/[\/\.]/', $route);
      $mini_exp   = $names = array();
      $bitkey   = 0; // key for named bits

      // find splitters
      preg_match_all('/[\/\.]/', $route, $splitters);
      $splitters = $splitters[0];

      // pad with / to offset number of splitters when using next()
      array_unshift($splitters, '/');
      array_unshift($splitters, '/');

      // parse each bit into it's regular expression form
      foreach ($bits as $bit) {
        if (preg_match('/^:(.+)$/', $bit, $match)) {
          // named parameter
          $bitkey++;
          $name = $match[1];

          if (isset($params[$name])) {
            $mini_exp[] = '(?:\\' . next($splitters) . '(' . $params[$name] . '){1})';
          } else {
            $mini_exp[] = '(?:\\' . next($splitters) . '([^\/\.]+))?';
          }

          $names[$bitkey]  = $name;
        } else {
          // a string
          $mini_exp[] = '\\' . next($splitters) . $bit;
        }
      }

      // build route's regexp
      $regexp = '/^' . implode('', $mini_exp) . '[\/\.]?([.]*)$/';
      
      // add to routes list
		  self::$routes[] = array($regexp, $names, $params, $http_request_method);
    }
  }

  /**
   * Parses the URI and returns the params within.
   *
   * @param string $uri
   * @return array $params
   */
  public static function parseURI($uri = false) {
    // set uri if not set by user
    // it's not set if the $uri is false or null
    if ($uri == null) { $uri = self::getURI(); }

    // matchs count
    $matchs = 0;

    // makes sure the first character is "/"
    if (substr($uri, 0, 1) != '/') { $uri = '/' . $uri; }

    // define params as array
    $params = array();

    // match uri to route map
    foreach(self::$routes as $route) {
      if (preg_match($route[0], $uri, $match) /*&& count($route[1]) >= (count($match) - 1) && $_SERVER['REQUEST_METHOD'] == $route[3]*/) {
        // set default params
        $params = $route[2]; // default values

        // set derived params
        foreach ($route[1] as $key => $name) { @$params[$name] = $match[$key]; }

        // flag as matched
        $matchs++;

        // we've found our match and now we're done here
        break;
      }
    }

    if ($matchs == 0) {
      // this is lame and needs to be done better
      //header("HTTP/1.0 404 Not Found");
      //ob_clean();
      //readfile(ERROR_DIR . '404.html');
			t($uri);
      exit();
    }
    
    return $params;
  }

  /**
   * returns the uri
   */
  public function getURI() {
    if (MOD_REWRITE === true) {
      return '/' . @$_GET['uri'];
    } else {
      $url = explode(SCRIPT_FILENAME, $_SERVER['REQUEST_URI']);
			// check if it split it into 2 peices. 
			// If it didn't then there is ending "index.php" so we assume there is no URI on the end either
			if (isset($url[1])) { 
				return $url[1]; 
			} else {
			 return null; 
			}
    }
  }
}
?>