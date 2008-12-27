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
   * @var string/array
   */
  public $_layout = 'master';

  /**
   * Enter description here...
   *
   * @var unknown_type
   */
  public $_view = null;

  /**
   * Enter description here...
   *
   * @var unknown_type
   */
  public $_data = array();

  /**
   * Enter description here...
   *
   * @var unknown_type
   */
  public $_represent = false;

  /**
   * Enter description here...
   *
   * @var unknown_type
   */
  public $_setup = array();
  
  /**
   * This holds the view's final output's content so that it can be included into a layout
   * for example: <?php echo $this->_content; ?>
   */
  public $_content = null;
  
  /**
   * Enter description here...
   *
   * @param unknown_type $params
   */
  final public function __construct($params) {
    
    // check for expected params
    $diff = array_diff(array('_controller', '_action', '_format', '_layout', '_ajax'), array_keys($params));
    if (!empty($diff)) {
      throw new Madeam_Controller_Exception_MissingExpectedParam('Missing expected Request Parameter(s).');
    }
    
    // set params
    $this->params = $params;
    
    if (isset($params['_flash'])) {
      $this->flash = $params['_flash'];
      unset($params['_flash']);
    }
    
    // set resource the controller represents
    if (is_string($this->_represent)) {
      $this->_represent = Madeam_Inflector::modelNameize($this->_represent);
    } else {
      $represent = explode('/', $this->params['_controller']);
      $this->_represent = Madeam_Inflector::modelNameize(array_pop($represent));
    }
    
    // set view
    $this->view($this->params['_controller'] . '/' . $this->params['_action']);
    
    // set layout
    // check to see if the layout param is set to true or false. If it's false then don't render the layout
    if (isset($this->params['_layout']) && ($this->params['_layout'] == 0)) {
      $this->layout(false);
    } else {
      $this->layout($this->_layout);
    }

    // set cache name
    $cacheName = 'madeam.controller.' . low(get_class($this)) . '.setup';

    // check cache for setup. if cache doesn't exist define it and then save it
    if (! $this->_setup = Madeam_Cache::read($cacheName, - 1, Madeam_Config::get('ignore_controllers_cache'))) {

      // define callbacks
      $this->_setup['beforeRequest'] = $this->_setup['afterRequest'] = $this->_setup['beforeFilter'] = $this->_setup['beforeRender'] = $this->_setup['afterRender'] = array();

      // reflection
      $reflection = new ReflectionObject($this);

      // check methods for callbacks
      $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC | !ReflectionMethod::IS_FINAL);
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
      
      // idea -- all protected properties load controller components by name.
      /*
      $properties = $reflection->getProperties(ReflectionProperty::IS_PROTECTED);
      foreach ($properties as $property) {
        test($property->getName());
        
        // set component class name
        $componentClassName = 'Madeam_Controller_Component_' . $name;

        // create component instance
        $component = new $componentClassName($this);
        $this->$name = $component;
        return $component;
      }
      */
      
      // save cache
      if (Madeam_Config::get('ignore_controllers_cache') === false) {
        Madeam_Cache::save($cacheName, $this->_setup, true);
      }
      
      // we should be done with the reflection at this point so let's kill it to save memory
      unset($reflection);
    }
  }

  final public function &__get($name) {
    $match = array();
    if (preg_match("/^[A-Z]{1}/", $name, $match)) {
      // set model class name
      $modelClassName = 'Model_' . $name;

      // create model instance
      $model = new $modelClassName();
      $this->$name = $model;   
    } 
    
    if (array_key_exists($name, $this->_data)) {
      return $this->_data[$name];
    } else {
      $this->_data[$name] = null;
      return $this->_data[$name]; 
    }
  }

  final public function __set($name, $value) {
    if (!preg_match('/^(?:_[A-Z])/', $name)) {
      $this->_data[$name] = $value;
    }
  }
  
  final public function __isset($name) {
    if (isset($this->_data[$name])) {
      return true;
    } else {
      return false;
    }
  }
  
  final public function __unset($name) {
    unset($this->_data[$name]);
  }
  
  final public function process() {

    $output = null;
    
    // beforeFilter callbacks
    $this->callback('beforeFilter');
    
    // action
    $action = Madeam_Inflector::camelize($this->params['_action']) . 'Action';
    
    $params = array();
    // check to see if method/action exists
    if (isset($this->_setup[$action])) {      
      foreach ($this->_setup[$action] as $param => $value) {
      	if (isset($this->params[$param])) {
      		$params[] = "\$this->params['$param']";
      	} else {
      		$params[] = "\$this->_setup['$action']['$param']";
      	}
      }
      
      if (preg_match('/[a-zA-Z_]*/', $action)) {
      	eval('$output = $this->' . $action . "(" . implode(',', $params) . ");");
    	} else {
    	  exit('Invalid Action Characters');
    	}
    } else {
      throw new Madeam_Controller_Exception_MissingAction('Missing Action <strong>' . substr($action, 0, -6) . '</strong> in <strong>' . get_class($this) . '</strong> controller.' 
      . "\n Create the view <strong>View/" . $this->params['_controller'] . '/' . substr($action, 0, -6) . ".html</strong> OR Create a method called <strong>$action</strong> in <strong>" . get_class($this) . "</strong> class."
      . " \n <code>public function $action() {\n}</code>");
    }
    
    // render
    if ($output == null) {
    	// beforeRender callbacks
      $this->callback('beforeRender');
    	
      $output = $this->render(array('view' => $this->_view, 'layout' => $this->_layout, 'data' => $this->_data));
    }
    
    // afterRender callbacks
    $this->callback('afterRender');

    // return response
    return $output;
  }
  
  final public function action($action, $front = false) {

    $output = null;
    
    // beforeFilter callbacks
    $this->callback('beforeFilter');
    
    // action
    $action = Madeam_Inflector::camelize($action) . 'Action';
    
    $params = array();
    // check to see if method/action exists
    if (isset($this->_setup[$action])) {      
      foreach ($this->_setup[$action] as $param => $value) {
      	if (isset($this->params[$param])) {
      		$params[] = "\$this->params['$param']";
      	} else {
      		$params[] = "\$this->_setup['$action']['$param']";
      	}
      }
      
      if (preg_match('/[a-zA-Z_]*/', $action)) {
      	eval('$output = $this->' . $action . "(" . implode(',', $params) . ");");
    	} else {
    	  exit('Invalid Action Characters');
    	}
    } else {
      throw new Madeam_Controller_Exception_MissingAction('Missing Action <strong>' . substr($action, 0, -6) . '</strong> in <strong>' . get_class($this) . '</strong> controller.' 
      . "\n Create the view <strong>View/" . $this->params['_controller'] . '/' . substr($action, 0, -6) . ".html</strong> OR Create a method called <strong>$action</strong> in <strong>" . get_class($this) . "</strong> class."
      . " \n <code>public function $action() {\n}</code>");
    }
    
    // render
    if ($output == null) {
    	// beforeRender callbacks
      $this->callback('beforeRender');
    	
      $output = $this->render(array('view' => $this->_view, 'layout' => $this->_layout, 'data' => $this->_data));
    }
    
    // afterRender callbacks
    $this->callback('afterRender');

    // return response
    return $output;
  }

  /**
   * Enter description here...
   *
   * @param string $name
   */
  final public function callback($name) {
    foreach ($this->_setup[$name] as $callback) {
    	// there has to be a better algorithm for this....
    	if (empty($callback['include']) || (in_array($this->params['_controller'] . '/' . $this->params['_action'], $callback['include']) || in_array($this->params['_controller'], $callback['include']))) {
    		if (empty($callback['exclude']) || (!in_array($this->params['_controller'] . '/' . $this->params['_action'], $callback['exclude']) && !in_array($this->params['_controller'], $callback['exclude']))) {
      		$this->{$callback['name']}();
    		}
    	}
    }
  }

  /**
   * Enter description here...
   *
   * @param string $view
   */
  final public function view($view) {
    $this->_view = $view;
    return $this->_view;
  }

  /**
   * Enter description here...
   *
   * @param string/array $layouts
   */
  final public function layout($layouts) {
    $this->_layout = array();
    if (func_num_args() < 2) {
      if (is_string($layouts)) {
        $this->_layout[] = $layouts;
      } elseif (is_array($layouts)) {
        $this->_layout = $layouts;
      } else {
        $this->_layout = array(); // no layout
      }
    } else {
      $this->_layout = func_get_args();
    }
    return $this->_layout;
  }

  /**
   * 
   */
  final public function render($settings) {
    // set the view file path
    if (isset($settings['partial'])) {
      $partial = explode('/', $settings['partial']);
      $partialName = array_pop($partial);
      $view = Madeam::$pathToApp . 'View' . DS . implode(DS, $partial) . DS . low($partialName) . '.' . $this->params['_format'];
    } else {
      $view = Madeam::$pathToApp . 'View' . DS . str_replace('/', DS, low($settings['view'])) . '.' . $this->params['_format'];
    }
    
    if (!isset($settings['data'])) {
      $settings['data'] = array();
    }
    
    // check if the view exists
    // if the view doesn't exist we need to serialize it.
    if (file_exists($view)) {
      // extract data to view and layout
      extract($settings['data']);
      
      // render view's content
      ob_start();
        include($view);
        $_content = ob_get_contents();
      ob_end_clean();
      
      // apply layout to view's content
      if (!isset($settings['partial']) && $settings['layout'] !== false && isset($settings['layout'])) {
        foreach ($settings['layout'] as $_layout) {
          $_layout = Madeam::$pathToApp . 'View' . DS . $_layout . '.layout.' . $this->params['_format'];
          
          // render layouts with builder
          ob_start();
            include($_layout);
            $_content = ob_get_contents();
          ob_clean();
        }
      }
    } else {
      // serialize output
      $_serializeMethod = 'encode' . ucfirst($this->params['_format']);
      if (method_exists('Madeam_Serialize', $_serializeMethod)) {
        $_content = Madeam_Serialize::$_serializeMethod($settings['data']);
      } else {
        throw new Madeam_Controller_Exception('Unknown serialization format "<strong>' . $this->params['_format'] . '</strong>"' . "\n Create a new view: " . $view);
      }
    }
    
    return $_content;
  }

}
