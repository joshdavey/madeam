<?php
namespace madeam;

class Controller {

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
  static public $_formats = array(
    'xml'   => array('madeam\serialize\Xml',  'encode'),
    'json'  => array('madeam\serialize\Json', 'encode'),
    'sphp'  => array('madeam\serialize\Sphp', 'encode')
  );
  
  /**
   * View directory
   * @var string
   */
  static public $_viewPath = 'app/views/';

  
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
  private function _setup() {
    
    // define callbacks
    $this->_setup['beforeFilter'] = $this->_setup['beforeRender'] = $this->_setup['afterRender'] = array();

    // reflection
    $reflection = new \ReflectionObject($this);

    // check methods for callbacks
    $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC | !\ReflectionMethod::IS_FINAL);

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
        
        // get action parameters
        $parameters = $method->getParameters();
        
        // unset first parameter which is the reuqest object
        unset($parameters[0]);
        
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
    
    // we should be done with the reflection at this point so let's kill it to save memory
    unset($reflection);
  }


  /**
   * 
   * @param string $name
   * @author Joshua Davey
   */
  public function &__get($name) {
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
  public function __set($name, $value) {
    $this->_data[$name] = $value;
  }

  
  /**
   * 
   * @param string $name
   * @return boolean
   */
  public function __isset($name) {
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
  public function __unset($name) {
    unset($this->_data[$name]);
  }
  
  /**
   * 
   * @return string
   */
  public function process($request) {
    
    // temp...
    $this->_setup();
    
    // check for expected params
    $diff = array_diff(array('_controller', '_action', '_format', '_layout', '_method'), array_keys($request));
    if (!empty($diff)) {
      throw new controller\exception\MissingExpectedParam('Missing expected Request Parameter(s): ' . implode(', ', $diff));
    }
    
    // set view
    $this->view($request['_controller'] . '/' . $request['_action']);
    
    // set layout
    // check to see if the layout param is set to true or false. If it's false then don't render the layout
    if (isset($request['_layout']) && ($request['_layout'] == 0)) {
      $this->layout(false);
    } else {
      $this->layout($this->_layout);
    }
    
    // beforeFilter callbacks
    $this->callback('beforeFilter');
    
    // action
    $action = Inflector::camelize($request['_action']) . 'Action';

    $params = array();
    // check to see if method/action exists
    if (isset($this->_setup[$action])) {
      foreach ($this->_setup[$action] as $param => $value) {
        if (isset($request[$param])) {
          $params[] = $request[$param];
        } else {
          $params[] = $this->_setup[$action][$param];
        }
      }
      
      // set request data so that it can be passed on to the view as data
      $this->_data['request'] = $request;
      
      // add request param
      array_unshift($params, $request);
      
      if ($this->_mapExtras === false) {
        $this->_output = call_user_func_array(array($this, $action), $params);
      } else {
        $this->_output = call_user_func_array(array($this, $action), explode('/', $request['_extra']));
      }
      
    } else {
      if (!file_exists(str_replace('/', DIRECTORY_SEPARATOR, strtolower($this->_view)) . '.' . $request['_format'])) {
        throw new controller\exception\MissingAction('Missing Action <strong>' . substr($action, 0, -6) . '</strong> in <strong>' . get_class($this) . '</strong> controller.' 
        . "\n Create the view <strong>View/" . $request['_controller'] . '/' . Inflector::dashize(substr($action, 0, -6)) . '.' . $request['_format'] . "</strong> OR Create a method called <strong>" . $action . "</strong> in <strong>" . get_class($this) . "</strong> class."
        . " \n <code>public function " . $action . "() {\n\n}</code>");
      }
    }
    
    // render
    if ($this->_output == null) {
      // beforeRender callbacks
      $this->callback('beforeRender');
      
      if (!in_array($request['_format'], $this->_returns)) {
        throw new controller\exception\MissingView('Unaccepted Format "<strong>' . $request['_format'] . '</strong>" in the controller <strong>' . get_class($this) . '</strong>.' . "\n
        Add the following to <strong>" . get_class($this) . "</strong><code>public \$_returns = array('" . implode("', '", array_merge($this->_returns, array($request['_format']))) . "');</code>");
      }
      
      $this->_output = self::render(array('template' => $this->_view, 'layout' => $this->_layout, 'data' => $this->_data, 'format' => $request['_format']));
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
  public function callback($name, $controller = false, $action = false) {
    foreach ($this->_setup[$name] as $callback) {
      // there has to be a better algorithm for this....
      if (empty($callback['include']) || (in_array($request['_controller'] . '/' . $request['_action'], $callback['include']) || in_array($request['_controller'], $callback['include']))) {
        if (empty($callback['exclude']) || (!in_array($request['_controller'] . '/' . $request['_action'], $callback['exclude']) && !in_array($request['_controller'], $callback['exclude']))) {
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
  public function returns($formats) {
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
  public function view($view) {
    $this->_view = $view;
    return $this->_view;
  }
  
  /**
   * Set the format this should be rendered as
   *
   * @param string $format 
   * @return string
   */
  public function format($format) {
    $this->_format = $format;
    return $this->_format;
  }


  /**
   * Set the layout(s) the layout should be rapped in.
   *
   * @param string/array $layouts
   * @return array
   */
  public function layout($layouts) {
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
   * Renders the output of the request. Can also be used to render partials and other views.
   * examples: 
   *  madeam\Controller::render(array('partial' => 'posts/_comment'));
   *  madeam\Controller::render(array('view' => 'posts/read', 'data' => array('post' => $post)));
   * 
   *  madeam\Controller::render(array('template' => 'posts/read', 'data' => array('post' => $post)));
   *  madeam\Controller::render(array('template' => 'posts/read', 'layout' => array('master'), 'data' => array('post' => $post)));
   * 
   *  madeam\View::render(array('template' => 'posts/read', 'data' => array(), 'layout' => array()));
   * 
   *  madeam\Framework::request(array('_controller' => 'posts', '_action' => 'read'));
   *  madeam\Framework::request('posts/read/32');
   * @return string
   */
  static public function render($_settings) {
    // set template
    $_template = self::$_viewPath . str_replace('/', DIRECTORY_SEPARATOR, strtolower($_settings['template'])) . '.' . $_settings['format'];
    
    // set layout
    !isset($_settings['layout']) ?: 
    
    // set default value for data
    isset($_settings['data']) ?: $_settings['data'] = array();
    
    // check if the view exists
    // if the view doesn't exist we need to serialize it.
    if (file_exists($_template)) {
      // extract data to view and layout
      extract($_settings['data']);
      
      // render view's content
      ob_start();
        include($_template);
        $_content = ob_get_contents();
      ob_end_clean();
      
      // apply layout to view's content
      if (isset($_settings['layout'])) {
        foreach ($_settings['layout'] as $_layout) {
          $_layout = self::$_viewPath . $_layout . '.layout.' . $_settings['format'];
          
          // render layouts
          ob_start();
            include($_layout);
            $_content = ob_get_contents();
          ob_clean();
        }
      }
    } else {
      // serialize output
      if (isset(self::$_formats[$_settings['format']]) && method_exists(self::$_formats[$_settings['format']][0], self::$_formats[$_settings['format']][1])) {
        $_content = call_user_func(self::$_formats[$_settings['format']], $_settings['data']);
      } else {
        throw new controller\exception\MissingView('Missing View: <strong>' . $_template . "</strong> and unknown serialization format \"<strong>" . $_settings['format'] . '</strong>"' . "\n Create File: <strong>" . $_template . "</strong>");
      }
    }
    
    return $_content;
  }

}
