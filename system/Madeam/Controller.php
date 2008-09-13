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
  private $_output = false;

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
  private $_view = null;

  /**
   * Enter description here...
   *
   * @var unknown_type
   */
  private $_data = array();

  /**
   * Enter description here...
   *
   * @var unknown_type
   */
  private $_represent = false;

  /**
   * Enter description here...
   *
   * @var unknown_type
   */
  private $_setup = array();

  /**
   * Enter description here...
   *
   * @var unknown_type
   */
  private $_reflection;

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
    if (is_string($this->_represent)) {
      $this->_represent = Madeam_Inflector::modelNameize($this->_represent);
    } else {
    	$represent = explode('/', $this->params['controller']);
    	$this->_represent = Madeam_Inflector::modelNameize(array_pop($represent));
    }
    
    // set view
    $this->view($this->params['controller'] . '/' . $this->params['action']);

    // set layout
    // check to see if the layout param is set to true or false. If it's false then don't render the layout
    if (isset($this->params['layout']) && ($this->params['layout'] == 0)) {
      $this->layout(false);
    } else {
      $this->layout($this->layout);
    }

    // set cache name
    $cacheName = 'madeam.controller.' . low(get_class($this)) . '.setup';

		// clear controller cache if it cache is disabled for routes
		if (!Madeam_Config::get('cache_controllers')) { 
			Madeam_Cache::clear($cacheName);
		}

    // check cache for setup. if cache doesn't exist define it and then save it
    if (! $this->_setup = Madeam_Cache::read($cacheName, - 1)) {

      // define callbacks
      $this->_setup['beforeFilter'] = $this->_setup['beforeRender'] = $this->_setup['afterRender'] = array();

      // reflection
      $this->_reflection = new ReflectionClass($this);

      // check methods for callbacks
      $methods = $this->_reflection->getMethods(ReflectionMethod::IS_PUBLIC | !ReflectionMethod::IS_FINAL);
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

          $this->_setup[$matches[1]][] = $callback;
          
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
          
          $this->_setup[$matches[0]] = $action;
        }
      }
      
      // save cache
      if (Madeam_Config::get('cache_controllers') === true) {
        Madeam_Cache::save($cacheName, $this->_setup, true);
      }
      
      // we should be done with the reflection at this point so let's kill it to save memory
      unset($this->_reflection);
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

  public function &__get($name) {
    $match = array();
    if (preg_match("/^[A-Z]{1}/", $name, $match)) {
      // set model class name
      $modelClassName = 'Model_' . $name;

      // create model instance
      $model = new $modelClassName();
      $this->_data[$name] = $model;
    } elseif (preg_match('/^_[A-Z]{1}/', $name, $match)) {
      // set component class name
      $componentClassName = 'Component_' . $name;

      // create component instance
      $component = new $componentClassName($this);
      $this->$name = $component;
      return $component;
    }
    
    if (array_key_exists($name, $this->_data)) {
      return $this->_data[$name];
    } else {
     $this->_data[$name] = null;
     return $this->_data[$name]; 
    }
  }

  public function __call($name, $args) {
    if (! file_exists($this->_view)) {
      throw new Madeam_Exception_MissingAction('Missing Action <strong>' . substr($name, 0, -6) . '</strong> in <strong>' . get_class($this) . '</strong> controller.' 
      . "\n Create a view called <strong>" . substr($name, 0, -6) . ".html</strong> OR Create a method called <strong>$name</strong>");
    }
  }

  public function __set($name, $value) {
    if (!preg_match('/^(?:_[A-Z])/', $name)) {
      $this->_data[$name] = $value;
    }
  }
  
  public function __iseet($name) {
    if (isset($this->_data[$name])) {
      return true;
    } else {
      return false;
    }
  }
  

  public function __unset($name) {
    unset($this->_data[$name]);
  }

  final public function process() {

    $output = null;
    
    // beforeFilter callbacks
    $this->_callback('beforeFilter');
    
    // action
    $action = Madeam_Inflector::camelize($this->params['action']) . 'Action';
    
    $params = array();
    if (isset($this->_setup[$action])) {      
      foreach ($this->_setup[$action] as $param => $value) {
      	if (isset($this->params[$param])) {
      		$params[] = "\$this->params['$param']";
      	} else {
      		$params[] = "\$this->_setup['$action']['$param']";
      	}
      }
    }
    
    if (preg_match('/[a-zA-Z_]*/', $action)) {
    	eval('$output = $this->' . $action . "(" . implode(',', $params) . ");");
  	} else {
  	  exit('Invalid Action Characters');
  	}
  	
  	
    // render
    if ($output == null) {
    	// beforeRender callbacks
      $this->_callback('beforeRender');
    	
      $output = $this->render(array('view' => $this->_view, 'layout' => $this->layout, 'data' => $this->_data));
    }
    
    // afterRender callbacks
    $this->_callback('afterRender');

    // return response
    return $output;
  }

  /**
   * Enter description here...
   *
   * @param string $name
   */
  final private function _callback($name) {
    foreach ($this->_setup[$name] as $callback) {
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
   * @param string $view
   */
  final public function view($view) {
    $this->_view = PATH_TO_VIEW . str_replace('/', DS, low($view)) . '.' . $this->params['format'];
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
  final public function render($settings) {
    	      
    // create builder instance
    try {      
      $builderClassName = 'Builder_' . ucfirst($this->params['format']);
      $builder = new $builderClassName($this);
    } catch (Madeam_Exception_AutoloadFail $e) {
      Madeam_Exception::catchException($e, array('message' => 'Unknown format "' . $this->params['format'] . '". Missing class <strong>' . $builderClassName . '</strong>'));
    }
          
    if (isset($settings['data'])) {
      $builder->data = $settings['data'];
    } elseif (isset($settings['collection'])) {
      $builder->collection = $settings['collection'];
    }
  
    if ($builder->data !== false || $builder->collection != false) {
     
      if (isset($settings['view'])) {
        $builder->view = $settings['view'];
      } elseif (isset($settings['partial'])) {
        $partial = explode('/', $settings['partial']);
        $partialName = array_pop($partial);
        $builder->view = PATH_TO_VIEW . implode(DS, $partial) . DS . '' . $partialName . '.' . $this->params['format'];
      }
  	 
      if (file_exists($builder->view)) {
        if (!empty($builder->collection)) {
          $builder->buildPartial();
        } else {
          // render the view
          $builder->buildView();
        }
      } else {
        
      	if (in_array(Madeam_Inflector::pluralize(low($this->_represent)), array_keys($builder->data))) {
      		$builder->data = $builder->data[Madeam_Inflector::pluralize(low($this->_represent))];
      	} elseif (in_array(Madeam_Inflector::singalize(low($this->_represent)), array_keys($builder->data))) {
      		$builder->data = $builder->data[Madeam_Inflector::singalize(low($this->_represent))];
      	}
      	
        $builder->missingView();
      }

      // set builder layout
      if (isset($settings['layout'])) {
        $builder->layout = $settings['layout'];
      } elseif (isset($settings['partial'])) {
        $builder->layout = array();
      }
      
      // render layouts with builder
      $builder->buildLayouts();

      // set final output
      $output = $builder->output;
    } else {
      // set final output as null
      $output = null;
    }

    return $output;
  }

}
