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
class Madeam_Controller {

  /**
   * Enter description here...
   *
   * @var unknown_type
   */
  private $output = false;

  /**
   * Enter description here...
   *
   * @var unknown_type
   */
  public $scaffold = false;

  /**
   * Enter description here...
   *
   * @var string/array
   */
  public $layout = 'master';

  /**
   * Enter description here...
   *
   * @var unknown_type
   */
  public $view = null;

  /**
   * Enter description here...
   *
   * @var unknown_type
   */
  public $data = array();

  /**
   * Enter description here...
   *
   * @var unknown_type
   */
  public $represent = false;

  /**
   * Enter description here...
   *
   * @var unknown_type
   */
  private $parser;

  /**
   * Enter description here...
   *
   * @var unknown_type
   */
  private $scaffoldController;

  /**
   * Enter description here...
   *
   * @var unknown_type
   */
  public $requestMethod;

  /**
   * Enter description here...
   *
   * @var unknown_type
   */
  public $requestGet;

  /**
   * Enter description here...
   *
   * @var unknown_type
   */
  public $requestPost;

  /**
   * Enter description here...
   *
   * @var unknown_type
   */
  public $requestCookie;

  /**
   * Enter description here...
   *
   * @var unknown_type
   */
  public $requestFiles;

  /**
   * Enter description here...
   *
   * @var unknown_type
   */
  public $params;

  /**
   * Enter description here...
   *
   * @var unknown_type
   */
  private $setup = array();

  /**
   * Enter description here...
   *
   * @var unknown_type
   */
  private $reflection;

  /**
   * Enter description here...
   *
   * @var unknown_type
   */
  private $cacheName = 'madeam.controller.';

  /**
   * Enter description here...
   *
   * @param unknown_type $params
   * @param unknown_type $requestPost
   * @param unknown_type $requestCookie
   * @param unknown_type $requestMethod
   */
  public function __construct($params) {
  	
  	// set params
  	$this->params = $params;
  	
    // set resource the controller represents
    if (is_string($this->represent)) {
      $this->represent = Madeam_Inflector::modelNameize($this->represent);
    } else {
    	$represent = explode('/', $this->params['controller']);
    	$this->represent = Madeam_Inflector::modelNameize(array_pop($represent));
    }

    // scaffold config
    $this->scaffoldController = $this->params['controller'];

    // set view
    $this->view($this->params['controller'] . '/' . $this->params['action']);

    // set layout
    // check to see if the layout param is set to true or false. If it's false then don't render the layout
    if (isset($this->params['layout']) && ($this->params['layout'] == 0)) {
      $this->layout(false);
    } else {
      $this->layout($this->layout);
    }

    try {
      // create parser instance
      $parserClassName = 'Parser_' . ucfirst($this->params['format']);
      $this->parser = new $parserClassName($this);
    } catch (Madeam_Exception_AutoloadFail $e) {
      Madeam_Exception::catchException($e, array('message' => 'Unknown format "' . $this->params['format'] . '". Missing class <strong>' . $parserClassName . '</strong>'));
    }

    // set cache name
    $this->cacheName .= low(get_class($this)) . '.setup';

		// clear controller cache if it cache is disabled for routes
		if (!Madeam_Config::get('cache_controllers')) { 
			Madeam_Cache::clear($this->cacheName);
		}

    // check cache for setup. if cache doesn't exist define it and then save it
    if (! $this->setup = Madeam_Cache::read($this->cacheName, - 1)) {

      // define callbacks
      $this->setup['beforeFilter'] = $this->setup['beforeRender'] = $this->setup['afterRender'] = array();

      // reflection
      $this->reflection = new ReflectionClass($this);

      // check methods for callbacks
      $methods = $this->reflection->getMethods(ReflectionMethod::IS_PUBLIC | !ReflectionMethod::IS_FINAL);
      foreach ($methods as $method) {
        $matches = array();
        if (preg_match('/^(beforeFilter|beforeRender|afterRender)(?:_[a-zA-Z0-9]*)?/', $method->getName(), $matches)) {
					// callback properties (name, include, exclude)
	        $callback = array();
	
	        // set callback method name
	        $callback['name'] = $method->getName();

          $parameters = $method->getParameters();
          foreach ($parameters as $parameter) {
            // set parameters of callback (parameters in methods act as meta data for callbacks)
            $callback[$parameter->getName()] = $parameter->getDefaultValue();
          }

          $this->setup[$matches[1]][] = $callback;
          
        } elseif (preg_match('/^[a-zA-Z0-9]*Action?/', $method->getName(), $matches)) {
        	// for each action we save it's arguments and map them to http params
        	
        	$action = array();
        	
          $parameters = $method->getParameters();
          foreach ($parameters as $parameter) {
            // set parameters of callback (parameters in methods act as meta data for callbacks)
            if ($parameter->isDefaultValueAvailable()) {
            	$action[$parameter->getName()] = $parameter->getDefaultValue();
          	} else {
          		$action[$parameter->getName()] = null;
          	}
          }
          
          $this->setup[$matches[0]] = $action;
        }
      }
      
      // save cache
      if (Madeam_Config::get('cache_controllers') === true) {
        Madeam_Cache::save($this->cacheName, $this->setup, true);
      }
      
      // we should be done with the reflection at this point so let's kill it to save memory
      unset($this->reflection);
    }
  }

  /**
  public function __destruct() {
    if (!headers_sent()) {

      // we need to destroy cookies that are unset
      // compare $_COOKIE and $this->requestCookie
      // delete unset cookies

      foreach ($this->requestCookie as $cookieName => $cookieValue) {
        if (isset($_COOKIE[$cookieName])) { continue; }

        $cExpire    = (60 * 60 * 24);
        $cPath      = dirname(PATH_TO_REL);
        $cDomain    = $_SERVER['SERVER_NAME'];
        $cSecure    = false;
        $cHttpOnly  = false;
        $cValue     = null;

        if (is_array($cookieValue)) {
          if (isset($cookieValue['expire']))    { $cExpire    = $cookieValue['expire']; }
          if (isset($cookieValue['path']))      { $cPath      = $cookieValue['path']; }
          if (isset($cookieValue['domain']))    { $cDomain    = $cookieValue['domain']; }
          if (isset($cookieValue['secure']))    { $cSecure    = $cookieValue['secure']; }
          if (isset($cookieValue['httponly']))  { $cHttpOnly  = $cookieValue['httponly']; }
          if (isset($cookieValue['value']))     { $cValue     = $cookieValue['value']; }
        } else {
          $cValue = $cookieValue;
        }

        setcookie($cookieName, $cValue, $cExpire, $cPath, $cDomain, $cSecure, $cHttpOnly);
      }
    }
  }
  */

  public function __get($name) {
    $match = array();
    if (preg_match("/^[A-Z]{1}/", $name, $match)) {
      // set model class name
      $modelClassName = 'Model_' . $name;

      // testing idea of not needing to create models in protoptype stage of site
      // this should still check to see if a table exists...
      // or maybe we can just let it throw a SQL error which works just as well
      /*
      // this isn't working because the class is eval()ed in the autoload function
      if (!class_exists($modelClassName)) {
        eval("class $modelClassName extends Madeam_ActiveRecord2 {}");
      }
      */

      // create model instance
      $model = new $modelClassName();
      $this->$name = $model;
      return $model;
    } elseif (preg_match('/^_[A-Z]{1}/', $name, $match)) {
      // set component class name
      $componentClassName = 'Component_' . $name;

      // create component instance
      $component = new $componentClassName($this);
      $component->$name = $component;
      return $component;
    }

		return false;
  }

  public function __call($name, $args) {
    if (! file_exists($this->view)) {
      throw new Madeam_Exception_MissingAction('Missing Action <strong>' . substr($name, 0, -6) . '</strong> in <strong>' . get_class($this) . '</strong> controller.' 
      . "\n Create a view called <strong>" . substr($name, 0, -6) . ".html</strong> OR Create a method called <strong>$name</strong>");
    } else {
    	// =============================================
    	// testing idea!
    	// set param values as variables when accessing views that don't have actions
    	// means you can do $name instead of $params['name']
    	// =============================================
    	foreach ($this->params as $param => $value) {
    		$this->$param = $value;
    	}
    }
  }

  public function __set($name, $value) {
    if (!preg_match('/^(?:_[A-Z]|[A-Z]){1}/', $name)) {
      $this->data[] = $name;      
    } 
    
    $this->$name = $value;
  }
  

  public function __unset($name) {
    unset($this->data[$name]);
  }

  final public function process() {

    // beforeFilter callbacks
    $this->_callback('beforeFilter');

    // action
    $action = Madeam_Inflector::camelize($this->params['action']) . 'Action';
    
    $params = array();
    if (isset($this->setup[$action])) {      
      foreach ($this->setup[$action] as $param => $value) {
      	if (isset($this->params[$param])) {
      		$params[] = "\$this->params['$param']";
      	} else {
      		$params[] = "\$this->setup['$action']['$param']";
      	}
      }
    }
    
    if (preg_match('/[a-zA-Z_]*/', $action)) {
    	eval('$this->' . $action . "(" . implode(',', $params) . ");");
  	}

    // beforeRender callbacks
    $this->_callback('beforeRender');

    // render
    // $this->output = $this->render(array('view' => $this->view, 'layout' => $this->layout, 'data' => $this->data));
    $this->render();

    // afterRender callbacks
    $this->_callback('afterRender');

    // return response
    return $this->output;
  }

  /**
   * Enter description here...
   *
   * @param string $name
   */
  final private function _callback($name) {
    foreach ($this->setup[$name] as $callback) {
    	// there has to be a better algorithm for this....
    	if (empty($callback['include']) || (in_array($this->params['controller'] . '/' . $this->params['action'], $callback['include']) || in_array($this->params['controller'], $callback['include']))) {
    		if (empty($callback['exclude']) || (!in_array($this->params['controller'] . '/' . $this->params['action'], $callback['exclude']) && !in_array($this->params['controller'], $callback['exclude']))) {
      		$this->{$callback['name']}();
    		}
    	}
    }
  }

  /**
   * Enter description here...
   *
   * @param string $uri
   * @param array $params
   * @return string
   */
  final public function request($uri, $params) {
    return Madeam::request($uri, $params);
  }

  /**
   * Enter description here...
   *
   * @param string $path
   * @param array $data
   * @param integer $start
   * @param integer $limit
   * @return string
   */
  final public function partial($path, $params) {
    // internal counter can be accessed in the view
    $_num = $start;

    /**
     * IMPORTANT! We should have our render logic in here!
     * I want to be able to use partials as templates!!!!!!!!!
     */

    // get partial name
    $partial = explode('/', $path);
    $partialName = array_pop($partial);
    $partialFile = PATH_TO_VIEW . implode(DS, $partial) . DS . '_' . $partialName . '.' . $this->params['format'];

    // splice array so that it is within the range defined by $start and $limit
    if ($limit !== false) {
      $data = array_splice($data, $start, $limit);
    } else {
      $data = array_splice($data, $start);
    }

    // set variables
    if (is_list($data)) {
    	test($data);
      foreach ($data as $key => $$partialName) {
        $_num ++;
        include ($partialFile);
      }
    } else {
      $$partialName = $data;
      $_num ++;
      test($data);
      extract($data);
      include ($partialFile);
    }
    return false;
  }

  /**
   * Enter description here...
   *
   * @param string $view
   */
  final public function view($view) {
    $this->view = PATH_TO_VIEW . str_replace('/', DS, low($view)) . '.' . $this->params['format'];
  }

  /**
   * Enter description here...
   *
   * @param string/array $layouts
   */
  final public function layout($layouts) {
    $this->layout = array();
    if (func_num_args() < 2) {
      if (is_string($layouts)) {
        $this->layout[] = PATH_TO_LAYOUT . $layouts . '.layout.' . $this->params['format'];
      } elseif (is_array($layouts)) {
        foreach ($layouts as $layout) {
          $this->layout[] = PATH_TO_LAYOUT . $layout . '.layout.' . $this->params['format'];
        }
      } else {
        $this->layout = array(false);
      }
    } else {
      foreach (funcget_args() as $layout) {
        $this->layout[] = PATH_TO_LAYOUT . $layout . '.layout.' . $this->params['format'];
      }
    }
  }

  /**
   * Enter description here...
   *
   * @param text/boolean $data
   * @return unknown
   */
  final public function render($data = true) {
  	if ($this->output === false) {
	    if ($data !== false) {
	    	
	      if (!is_string($data)) {
	      	
	        if (file_exists($this->view)) {
	          // pass params to view -- but only if the view exists
	          $this->data[] = 'params';
	          
	          // render the view
	          $this->parser->renderView();
	        } else {	        	
	        	if (in_array(Madeam_Inflector::pluralize(low($this->represent)), $this->data)) {
	        		$this->data = array(Madeam_Inflector::pluralize(low($this->represent)));
	        	} elseif (in_array(Madeam_Inflector::singalize(low($this->represent)), $this->data)) {
	        		$this->data = array(Madeam_Inflector::singalize(low($this->represent)));
	        	}
	        	
	          $this->parser->missingView();
	        }
	
	      } else {
	        // render a regular text string
	        $this->parser->output = $data;
	      }
	
	      foreach ($this->layout as $layoutFile) {
	        if ($layoutFile) {
	          if (file_exists($layoutFile)) {
	            // include layout if it exists
	            $this->parser->renderLayout($layoutFile);
	          } else {
	            $this->parser->missingLayout($layoutFile);
	          }
	        }
	      }
	
	      // set final output
	      $this->output = $this->parser->output;
	    } else {
	      // set final output as null
	      $this->output = null;
	    }
    }

    return true;
  }

  /**
   * Enter description here...
   *
   * @param string $action
   */
  final public function scaffold($action) {
    require (SCAFFOLD_PATH . $this->scaffold . '/action/' . $action . '.php');
  }

}
