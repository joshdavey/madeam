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

class madeam_router {
  public static $routes  = array(); // regex, names, params, http_request_method -- damn these really need to be cached! (Store them in a text file?)
  public static $links   = array(); // a place to store the magic smart links

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
    $parsed_uri = parse_url($uri);

    // set uri
    if (isset($parsed_uri['path'])) {
      $extracted_path = explode(URI_PATH, $parsed_uri['path'], 2);
      $uri = array_pop($extracted_path);
    }

    // set format
    $uri_anatomy = explode('.', $uri, 2);
    $uri = $uri_anatomy[0];
    $format = $uri_anatomy[1];

    // set get
    $get = array();
    if (isset($parsed_uri['query'])) {
      $query = $parsed_uri['query'];

      // retrieve $_GET vars manually from uri -- so we can enter the uri as index/index?foo=bar when calling a component from the view
      parse_str($query, $get); // assigns $get array of query params
    }

    // merge manual $_GETs with http $_GETs
    $gets = array_merge($get, $_GET); // http $_GETs overide manual $_GETs

    // makes sure the first character is "/"
    if (substr($uri, 0, 1) != '/') { $uri = '/' . $uri; }

    // define params as array
    $params = array();

    // matchs count
    $matchs = 0;

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
			test($uri);
      exit();
    }

    // get params from uri
    $params = array_merge($params, $gets);

    // automagically disable the layout when making an AJAX call
    if (!AJAX_LAYOUT && @$_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') { $params['layout'] = '0'; }

    // set default values for controller and action
    @$params['controller'] == null ? $params['controller'] = DEFAULT_CONTROLLER : false ;
    @$params['action']     == null ? $params['action']     = DEFAULT_ACTION : false;
  	@$format               == null ? $params['format']     = DEFAULT_FORMAT : $params['format'] = $format  ;
    @$params['layout']     == null ? $params['layout']     = '0' : false ;

    return $params;
  }

  /**
   * returns the current uri
   */
  public static function getCurrentURI() {
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