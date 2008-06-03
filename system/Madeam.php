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
   * dispatches all operations to controller specified by uri
   *
   * @return boolean
   */
  public static function dispatch() {
    // call user front controller?
    // include app/app.php // -- includes stuff that executes before dispatching -- config stuff?

    if (!isset($_GET['useLayout'])) {
      $useLayout = 'useLayout=1';
    } else {
      $useLayout = null;
    }

    // call controller action
    $output = Madeam::makeRequest(Madeam_Router::getCurrentURI() . $useLayout, $_GET, $_POST, $_COOKIE);

    // destroy user error notices
    if (isset($_SESSION[MADEAM_USER_ERROR_NAME])) {
      unset($_SESSION[MADEAM_USER_ERROR_NAME]);
    }

    // destroy flash data when it's life runs out
    if (isset($_SESSION[MADEAM_FLASH_LIFE_NAME])) {
      if (-- $_SESSION[MADEAM_FLASH_LIFE_NAME] < 1) {
        unset($_SESSION[MADEAM_FLASH_LIFE_NAME]);
        if (isset($_SESSION[MADEAM_FLASH_DATA_NAME])) {
          unset($_SESSION[MADEAM_FLASH_DATA_NAME]);
        }
      } else {
        if (isset($_SESSION[MADEAM_FLASH_DATA_NAME][MADEAM_FLASH_POST_NAME])) {
          $_POST = array_merge($_SESSION[MADEAM_FLASH_DATA_NAME][MADEAM_FLASH_POST_NAME], $_POST);
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
  public static function makeRequest($uri, $requestGet = array(), $requestPost = array(), $requestCookie = array()) {
    // get request parameters from uri
    // example input: 'posts/show/32'
    $params = Madeam_Router::parseURI($uri);

    // combine GETs
    $params = array_merge($requestGet, $params);

    // pass uri as a _GET variable
    $params['uri'] = $uri;

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
    $controllerClassNodes = explode('/', $params['controller']);
    foreach ($controllerClassNodes as &$node) {
      $node = Madeam_Inflector::camelize($node);
      $node = ucfirst($node);
    }

    // set controller class
    $controllerClass = 'Controller_' . implode('_', $controllerClassNodes);

    try {
      // create controller instance
      $controller = new $controllerClass($params, $requestPost, $requestCookie, $_SERVER['REQUEST_METHOD']);
    } catch(Madeam_Exception_AutoloadFail $e) {
      if (is_dir(PATH_TO_VIEW . $params['controller'])) {
        $view = $params['controller'] . '/' . $params['action'];
        $params['controller'] = 'app';
        $controller = new Controller_App($params);
        $this->view($view);
      } elseif (is_file(PATH_TO_VIEW . $params['controller'] . '.' . $params['format'])) {
        $view = $params['controller'];
        $params['action'] = $params['controller'];
        $params['controller'] = 'app';
        $controller = new Controller_App($params);
        $controller->view($view);
      } else {
        // no controller found = critical error.
        Madeam_Exception::catchException($e, array('message' => 'Missing Controller <b>' . $controllerClass . '</b>'));
      }
    }

    try {
      // process request
      $output = $controller->process();

      // delete controller
      unset($controller);

      // return output
      return $output;
    } catch (Madeam_Exception_MissingAction $e) {
      Madeam_Exception::catchException($e);
    } catch (Madeam_Exception_MissingView $e) {
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

  /**
   * When any file is modified in the application directory this method clears
   * the cache.
   *
   */
  public static function clearCacheOnUpdate() {}
}