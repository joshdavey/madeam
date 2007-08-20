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
 * @version			0.0.5
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 * @author      Joshua Davey
 */
class madeam {
  /**
   * dispatches all operations to controller specified by uri
   *
   * @return boolean
   */
  public static function dispatch() {
    $output = madeam::component('?layout=1');
    
    // tidy up html output? -- this should be for html pages only
    //$options = array('output-xhtml' => true, 'clean' => true, 'hide-comments' => true, 'indent' => true, 'indent-spaces' => 2, 'wrap' => 100);
    //echo tidy_parse_string($output, $options);
    
    // destroy user error notices
    if (isset($_SESSION[USER_ERROR_NAME])) {
      unset($_SESSION[USER_ERROR_NAME]);
    }
    
    // destroy flash data when it's life runs out
		if (isset($_SESSION[FLASH_LIFE_NAME])) {
			if (--$_SESSION[FLASH_LIFE_NAME] < 1) {
				unset($_SESSION[FLASH_LIFE_NAME]);
				if (isset($_SESSION[FLASH_DATA_NAME])) {
					unset($_SESSION[FLASH_DATA_NAME]);
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
  public static function component($url = null, $cfg = array()) {
    // get params from uri
    $params = self::params($url);
   
    // if the controller is a directory then we need to append the default controller class name to the end (index)
    if (is_dir(CONTROLLER_PATH . $params['controller'])) { $params['controller'] .= '/index'; }
    
     // set clean
    $controller = $params['controller'];
    $action     = str_replace('_', null, $params['action']); // protect callback methods by removing underscores

    // set controller class name
    $controllerClass = 'controller_' . madeam_inflector::underscorize($controller);
    
    // check if controller's class exists
    if (class_exists($controllerClass)) {
      // create controller instance
      $inst = new $controllerClass($params);

      // HACK make params available on view
      $inst->set('params', $params);

      // when scaffold is enabled use scaffolding functions if the functions haven't been defined by the programmer in the controller
      if ($inst->scaffold == true && method_exists($controllerClass, $action) === false) {
        $inst->before_action();
        $inst->{'_scaffold_' . $action}();
        $inst->after_action();
        $inst->before_render();
        if ($params['layout'] == '0') { $inst->layout(false); } // render without layout
        $inst->render();
        $inst->after_render();
        return $inst->output;
      }

      if (method_exists($controllerClass, $action)) {
        // priotity 1 - execute action if it exists
        $inst->before_action();
        $inst->$action();
        $inst->after_action();
        // return just data if requested
        if (@$cfg['request'] == true) { return $inst->data; }
        // otherwise render the template
        $inst->before_render();
        if ($params['layout'] == '0') { $inst->layout(false); } // render without layout
        $inst->render();
        $inst->after_render();
        return $inst->output;
      } elseif (file_exists($inst->view)) {
        // priority 2 - show action's template if the action doesn't exist but it's template does
        $inst->before_action();
        $inst->after_action();
        $inst->before_render();
        if ($params['layout'] == '0') { $inst->layout(false); } // render without layout
        $inst->render();
        $inst->after_render();
        return $inst->output;
      } else {
        // or... there is an error and everything fails.
        madeam_logger::log("the action <b>$action</b> does not exist within the <b>$controller</b> controller", 10);
        return false;
      }
    } elseif (file_exists(VIEW_PATH . $controller . '/'. $action . '.' . $params['format'])) {
    	
    	$nodes = explode('/', $controller);
    	array_pop($nodes);
    	$controllerGroup = implode('_', $nodes);
    	
    	$controllerClass = 'controller_' . madeam_inflector::underscorize($controller);
    		
      // set controller's class name
      if ($controllerGroup == null) {
      	$appControllerClass = 'controller_app';
    	} else {
    		$appControllerClass = 'controller_' . $controllerGroup . '_app';
  		}
      
      if (class_exists($appControllerClass)) {
	      $view = new $appControllerClass($params);
	      if ($params['layout'] == '0') { $view->layout(false); } // render without layout
	      $view->set('params', $params); // HACK make params available on view
	      $view->before_action();
	      $view->after_action();
	      $view->before_render();
	      $view->render();
	      $view->after_render();
	      return $view->output;
      } else {
      	include VIEW_PATH . $controller . '/'. $action . '.' . $params['format'];     
  	  }
    } else {
      // darn! the controller you've selected is non-existent. Bah humbug.
      madeam_logger::log("the controller <b>$controller</b> does not exist", 10);
      return false;
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
   * @param string $url
   * @return array
   */
  public static function params($url = false) {
    // split url into uri ($uri[0]) and GET query ($uri[1])
    $url = explode('?', $url, 2);

    // set uri
    $uri = array_shift($url);

    // set query
    $query = array_shift($url);

    // retrieve $_GET vars manually from uri -- so we can enter the uri as index/index?foo=bar when calling a component from the view
    parse_str($query, $get); // assigns $get array of query params

    // merge manual $_GETs with http $_GETs
    $gets = array_merge($get, $_GET); // http $_GETs overide manual $_GETs

    // get params from uri
    $params = array_merge(madeam_router::parseURI($uri), $gets);

    // automagically disable the layout when making an AJAX call
    if (!AJAX_LAYOUT && @$_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') { $params['layout'] = '0'; }

    // set default values for controller and action
    @$params['controller'] == null ? $params['controller'] = DEFAULT_CONTROLLER : false ;
    @$params['action']     == null ? $params['action']     = DEFAULT_ACTION : false;
    @$params['format']     == null ? $params['format']     = DEFAULT_FORMAT : false ;
    @$params['layout']     == null ? $params['layout']     = '0' : false ;

    return $params;
  }
}
?>