<?php
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
class Madeam_Controller {

  /**
   * Layouts
   * @var string/array
   */
  public $_layout = 'master';

  /**
   * Path to view file from View directory including view's name (without file extension)
   * example: posts/index
   * @var string
   */
  public $_view = null;

  /**
   * Data to be sent to view
   * @var array
   */
  public $_data = array();
  
  /**
   * Output of the request 
   * @var string
   */
  public $_output = null;
  
  /**
   * List of all models used at time of request.
   * @var array
   */
  public $_models = array();

  /**
   * Configuration for this controller
   * @var array
   */
  public $_setup = array();
  
  /**
   * 
   * @var boolean
   */
  public $_mapExtras = false;
  
  /**
   * This holds the view's final output's content so that it can be included into a layout
   * for example: <?php echo $this->_content; ?>
   * @var string
   */
  public $_content = null;
  
  /**
   * Request parameters
   * @var array
   */
  public $params = array();
    
  /**
   * List the formats that this controller returns
   */
  public $_returns = array('html');
   
  /**
   * A map of all the file formats to their associated serialization method
   * @author Joshua Davey
   */
  public $_formats = array(
    'xml'   => array('Madeam_Serialize_Xml',  'encode'),
    'json'  => array('Madeam_Serialize_Json', 'encode'),
    'sphp'  => array('Madeam_Serialize_Sphp', 'encode')
  );
  
  /**
   * View directory
   * @var string
   */
  public static $viewPath = null;
  
  
  /**
   * Controller __construct. Preps the controller.
   * - set params
   * - set view
   * - set layout
   * - check for setup cache
   * - load setup (callbacks, etc...)
   *
   * @param array $params
   * @author Joshua Davey
   */
  final public function __construct($params) {
    // check for expected params
    $diff = array_diff(array('_controller', '_action', '_format', '_layout', '_method'), array_keys($params));
    if (!empty($diff)) {
      throw new Madeam_Controller_Exception_MissingExpectedParam('Missing expected Request Parameter(s): ' . implode(', ', $diff));
    }
    
    // set params
    $this->params = $params;
    
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
    $cacheName = Madeam::$environment . '.madeam.controller.' . strtolower(get_class($this)) . '.setup';

    // check cache for setup. if cache doesn't exist define it and then save it
    if (! $this->_setup = Madeam_Cache::read($cacheName, - 1, Madeam_Config::get('cache_controllers'))) {

      // define callbacks
      $this->_setup['beforeFilter'] = $this->_setup['beforeRender'] = $this->_setup['afterRender'] = array();

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
      
      // save cache
      if (Madeam_Config::get('cache_controllers') === true) {
        Madeam_Cache::save($cacheName, $this->_setup, true);
      }
      
      // we should be done with the reflection at this point so let's kill it to save memory
      unset($reflection);
    }
  }


  /**
   * 
   * @param string $name
   * @author Joshua Davey
   */
  final public function &__get($name) {
    $match = array();
    
    if (array_key_exists($name, $this->_models)) {
      return $this->_models[$name];
    } elseif (preg_match("/^[A-Z]{1}/", $name, $match)) {
      // set model class name
      $modelClassName = 'Model_' . $name;

      // create model instance
      $model = new $modelClassName();
      $this->_models[$name] = $model;
      return $this->_models[$name];
    }
    
    if (array_key_exists($name, $this->_data)) {
      return $this->_data[$name];
    } else {
      $this->_data[$name] = null;
      return $this->_data[$name];
    }
  }


  /**
   * 
   * @param string $name
   * @param string $value
   */
  final public function __set($name, $value) {
    if (!preg_match('/^(?:_[A-Z])/', $name)) {
      $this->_data[$name] = $value;
    }
  }

  
  /**
   * 
   * @param string $name
   * @return boolean
   */
  final public function __isset($name) {
    if (isset($this->_data[$name])) {
      return true;
    } else {
      return false;
    }
  }
  
  
  /**
   * 
   * @param string $name
   */
  final public function __unset($name) {
    unset($this->_data[$name]);
  }
  
  
  /**
   * 
   * @return string
   */
  final public function process() {

    $this->_output = null;
    
    // beforeFilter callbacks
    $this->callback('beforeFilter');
    
    // action
    $action = Madeam_Inflector::camelize($this->params['_action']) . 'Action';
    
    $params = array();
    // check to see if method/action exists
    if (isset($this->_setup[$action])) {
      foreach ($this->_setup[$action] as $param => $value) {
        if (isset($this->params[$param])) {
          $params[] = $this->params[$param];
        } else {
          $params[] = $this->_setup[$action][$param];
        }
      }
      
      if ($this->_mapExtras === false) {
        $this->_output = call_user_func_array(array($this, $action), $params);
      } else {
        $this->_output = call_user_func_array(array($this, $action), explode('/', $this->params['_extra']));
      }
      
    } else {
      if (!file_exists(Madeam_Controller::$viewPath . str_replace('/', DS, strtolower($this->_view)) . '.' . $this->params['_format'])) {
        throw new Madeam_Controller_Exception_MissingAction('Missing Action <strong>' . substr($action, 0, -6) . '</strong> in <strong>' . get_class($this) . '</strong> controller.' 
        . "\n Create the view <strong>View/" . $this->params['_controller'] . '/' . Madeam_Inflector::dashize(lcfirst(substr($action, 0, -6))) . '.' . $this->params['_format'] . "</strong> OR Create a method called <strong>" . lcfirst($action) . "</strong> in <strong>" . get_class($this) . "</strong> class."
        . " \n <code>public function " . lcfirst($action) . "() {\n\n}</code>");
      }
    }
    
    // render
    if ($this->_output == null) {
      // beforeRender callbacks
      $this->callback('beforeRender');
      
      $this->_output = $this->render(array('view' => $this->_view, 'layout' => $this->_layout, 'data' => $this->_data));
    }
    
    // afterRender callbacks
    $this->callback('afterRender');

    // return response
    return $this->_output;
  }
  
  
  /**
   * Perform a callback.
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
   * This is a helper method to help the user accept the formats an action returns.
   *
   * @param string $formats 
   * @return array
   */
  final public function returns($formats) {
    $this->_returns = array();
    if (func_num_args() < 2) {
      if (is_string($formats)) {
        $this->_returns[] = $formats;
      } elseif (is_array($formats)) {
        $this->_returns = $formats;
      } else {
        $this->_returns = array(); // doens't return anthing
      }
    } else {
      $this->_returns = func_get_args();
    }
    return $this->_returns;
  }

  /**
   * Set the name of the view file (ignoring the file extension) 
   *
   * @param string $view
   * @return string
   */
  final public function view($view) {
    $this->_view = $view;
    return $this->_view;
  }
  
  /**
   * Set the format this should be rendered as
   *
   * @param string $format 
   * @return string
   */
  final public function format($format) {
    $this->params['_format'] = $format;
    return $this->params['_format'];
  }


  /**
   * Set the layout(s) the layout should be rapped in.
   *
   * @param string/array $layouts
   * @return array
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
   * Redirect function
   *
   * @param string $url 
   * @param string $exit 
   * @return void
   * @author Joshua Davey
   */
   /*
  final public function redirect($url, $exit = true) {
    if (! headers_sent()) {
      header('Location:  ' . Madeam::url($url));
      if ($exit) {
        exit();
      }
    } else {
      throw new Madeam_Exception_HeadersSent('Tried redirecting when headers already sent. (Check for echos before redirects)');
    }
  }
  */


  /**
   * Renders the output of the request. Can also be used to render partials and other views.
   * examples: 
   *  $this->render(array('partial' => 'posts/_comment'));
   *  $this->render(array('view' => 'posts/read', 'data' => array('post' => $post)));
   *  $this->render(array('controller' => 'posts', 'action' => 'read'));
   * @return string
   */
  final public function render($settings) {
    
    if (!isset($settings['controller'])) {
      $settings['controller'] = $this->params['_controller'];
    }
    
    if (!isset($settings['view'])) {
      $settings['view'] = $settings['controller'] . '/' . $this->params['_action'];
    }
    
    if (!isset($settings['layout'])) {
      $settings['layout'] = $this->_layout;
    }
    
    if (!is_array($settings['layout'])) {
      $settings['layout'] = $this->layout($settings['layout']);
    }
    
    
    if (isset($settings['action'])) {
      $params = $this->params;
      $params['_action']      = $settings['action'];
      $params['_controller']  = $settings['controller'];
      
      return Madeam::control($params);
    }
    
    
    // set view file name
    if (isset($settings['partial'])) {
      $partial = explode('/', $settings['partial']);
      $partialName = array_pop($partial);
      $viewFile = implode(DS, $partial) . DS . strtolower($partialName) . '.' . $this->params['_format'];
    } else {
      $viewFile = str_replace('/', DS, strtolower($settings['view'])) . '.' . $this->params['_format'];
    }
    
    // full path to view
    $view = Madeam_Controller::$viewPath . $viewFile;
    
    if (!isset($settings['data'])) {
      $settings['data'] = array();
    }
    
    // check if the view exists
    // if the view doesn't exist we need to serialize it.
    if (file_exists($view)) {
      // extract data to view and layout
      extract(array_merge((array) $this->_data, (array) $this, $settings['data']));
      
      // render view's content
      ob_start();
        include($view);
        $_content = ob_get_contents();
      ob_end_clean();
      
      // apply layout to view's content
      if (!isset($settings['partial']) && $settings['layout'] !== false && isset($settings['layout'])) {
        foreach ($settings['layout'] as $_layout) {
          $_layout = Madeam_Controller::$viewPath . $_layout . '.layout.' . $this->params['_format'];
          
          // render layouts with builder
          ob_start();
            include($_layout);
            $_content = ob_get_contents();
          ob_clean();
        }
      }
    } else {
      // serialize output
      $format = $this->params['_format'];
      $class = false;
      $method = false;
      if (isset($this->_formats[$format])) {
        $class  = $this->_formats[$format][0];
        $method = $this->_formats[$format][1];
      }
      if (!in_array($format, $this->_returns)) {
        throw new Madeam_Controller_Exception_MissingView('Unaccepted Format "<strong>' . $format . '</strong>" in the controller <strong>' . get_class($this) . '</strong>.' . "\n
        Add the following to <strong>" . get_class($this) . "</strong><code>public \$_returns = array('" . implode("', '", array_merge($this->_returns, array($format))) . "');</code>");
      } elseif (method_exists($class, $method)) {
        $_content = call_user_func($class .'::' . $method, $settings['data']);
      } else {
        throw new Madeam_Controller_Exception_MissingView('Missing View: <strong>' . $viewFile . "</strong> and unknown serialization format \"<strong>" . $this->params['_format'] . '</strong>"' . "\n Create File: <strong>app/src/View/" . $viewFile . "</strong>");
      }
    }
    
    return $_content;
  }

}
