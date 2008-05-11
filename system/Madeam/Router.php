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
  public static $routes           = array(); // regex, names, params
  public static $links            = array(); // a place to store the magic smart links

  // do we really need this
  public static $actionRequestMethodMap  = array(
    array('action' => 'index',  'method' => 'get',    'id' => false),
    array('action' => 'show',   'method' => 'get',    'id' => true),
    array('action' => 'view',   'method' => 'get',    'id' => true), // same as show
    array('action' => 'delete', 'method' => 'delete', 'id' => true),
    array('action' => 'edit',   'method' => 'put',    'id' => true),
    array('action' => 'edit',   'method' => 'post',   'id' => false),
    array('action' => 'add',    'method' => 'post',   'id' => false)
  );

  public static $resourceMap      = array(

  );

  /**
   * This cool method adds paths by formatting the string the user has entered and turning it into a regular expression
   * which can be used to be compared against URIs.
   *
   * @param string $route
   * @param array $params
   */
  public static function connect($route, $params = array()) {
    if (!is_array(self::$routes)) { self::$routes = array(); }

    // root route - doesn't require parsing
    if ($route == '' || $route == '/') {
			self::$routes[] = array('/^\/*$/', array(), $params);
    // parse route
    } else {
      // break into pieces/bits
      //$bits     = preg_split('/[\/\.]/', $route);
      $bits = explode('/', $route);
      $mini_exp   = $names = array();
      $bitkey   = 0; // key for named bits

      // parse each bit into it's regular expression form
      foreach ($bits as $bit) {
        if (preg_match('/^:(.+)$/', $bit, $match)) {
          // named parameter
          $bitkey++;
          $name = $match[1];

          if (isset($params[$name])) {
            $mini_exp[] = '(?:\\/(' . $params[$name] . '){1})';
          } else {
            $mini_exp[] = '(?:\\/([^\/]+))?';
          }

          $names[$bitkey]  = $name;
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
        foreach ($route[1] as $key => $name) { $params[$name] = $match[$key]; }

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
			//test($uri);
      //exit();

      throw new Madeam_Exception('Unable to find page');

      // but what about returning the params if we throw an error?

      return $params;
    }

    // get params from uri
    $params = array_merge($params, $gets);

    // automagically disable the layout when making an AJAX call
    if (!MADEAM_ENABLE_AJAX_LAYOUT && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') { $params['useLayout'] = '0'; }

    $config = Madeam_Registry::get('config');

    // set default values for controller and action
    !isset($params['controller']) || $params['controller'] == null ? $params['controller'] = $config['default_controller'] : false ;
    !isset($params['action']) || $params['action'] == null ? $params['action'] = $config['default_action'] : false ;
    !isset($params['useLayout']) || $params['useLayout'] == null ? $params['useLayout'] = '0' : false ;
    !isset($format) || $format == null ? $params['format'] = $config['default_format'] : $params['format'] = $format;


    return $params;
  }

  /**
   * returns the current uri
   * @return string
   */
  public static function getCurrentURI() {
    if (MADEAM_REWRITE_URI !== false) {
      return MADEAM_REWRITE_URI;
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