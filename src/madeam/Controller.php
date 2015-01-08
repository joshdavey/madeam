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
   *
   */
  public $_callbacks = array();

  /**
   * This holds the view's final output's content so that it can be included into a layout
   * for example: <?php echo $this->_content; ?>
   * @var string
   */
  public $_content = null;

  /**
   * List the formats that this controller returns
   */
  public $_returns = array('html');

  /**
   * A map of all the file formats to their associated serialization method
   * @author Joshua Davey
   */
  static public $formats = array(
    'xml'   => array('madeam\serialize\Xml',  'encode'),
    'json'  => array('madeam\serialize\Json', 'encode'),
    'sphp'  => array('madeam\serialize\Sphp', 'encode')
  );

  /**
   *
   * @param string $name
   * @author Joshua Davey
   */
  public function &__get($name) {
    return $this->_data[$name];
    // if (array_key_exists($name, $this->_data)) {
    //   return $this->_data[$name];
    // } else {
    //   $this->_data[$name] = null;
    //   return $this->_data[$name];
    // }
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

    // reflection
    $reflection = new \ReflectionObject($this);

    $parameters = $reflection->getProperties(\ReflectionProperty::IS_PROTECTED);

    foreach ($parameters as $param) {
      $matches = array();
      if (preg_match('/^(beforeAction|beforeRender|afterRender)(?:_([a-zA-Z0-9]*))?/', $param->getName(), $matches)) {
        $this->_callbacks[$matches[1]][$matches[2]] = (array) $this->{$matches[0]};
      }
    }

    // we should be done with the reflection at this point so let's kill it to save memory
    unset($reflection);

    // check for expected params
    $diff = array_diff(array('_controller', '_action'), array_keys($request));
    if (!empty($diff)) {
      throw new controller\exception\MissingExpectedRequestParameter('Missing expected Request Parameter(s): ' . implode(', ', $diff));
    }

    // set format if not set
    isset($request['_format']) ?: $request['_format'] = $this->_returns[0];

    // set method if no set
    isset($request['_method']) ?: $request['_method'] = 'get';

    // set view
    $this->view($request['_controller'] . '/' . $request['_action']);

    // set layout
    // check to see if the layout param is set to true or false. If it's false then don't render the layout
    if (isset($request['_layout']) && $request['_layout'] == '0') {
      $this->layout(false);
    } else {
      $this->layout($this->_layout);
    }

    // beforeFilter callbacks
    $this->callback('beforeAction', $request);

    // action
    $action = Inflector::camelize($request['_action']) . 'Action';

    $params = array();
    // check to see if method/action exists
    if (method_exists($this, $action)) {
      $method = new \ReflectionMethod($this, $action);
      $parameters = $method->getParameters();

      // no need to map $request here. Its done manually
      array_shift($parameters);

      // manually map $request as first parameter
      $params = array($request);

      foreach ($parameters as $param) {
        if (isset($request[$param->getName()])) {
          $params[] = $request[$param->getName()];
        } else {
          if ($param->isOptional()) {
            $params[] = $param->getDefaultValue();
          } else {
            throw new controller\exception\MissingExpectedActionParameter('Missing expected action parameter <strong>$' . $param->getName() . '</strong> for <strong>' . $action . '</strong> method');
          }
        }
      }

      unset($parameters);

      // execute action and check for a return value
      // an _output value of anything other than NULL will skip rendering the view and return the _output as is
      $this->_output = call_user_func_array(array($this, $action), $params);

    } else {
      if (!file_exists(View::$path . str_replace('/', DIRECTORY_SEPARATOR, strtolower($this->_view)) . '.' . $request['_format'])) {
        throw new controller\exception\MissingAction('Missing Action <strong>' . substr($action, 0, -6) . '</strong> in <strong>' . get_class($this) . '</strong> controller.'
        . "\n Create the view <strong>application/views/" . $request['_controller'] . '/' . Inflector::dashize(substr($action, 0, -6)) . '.' . $request['_format'] . "</strong> OR Create a method called <strong>" . $action . "</strong> in <strong>" . get_class($this) . "</strong> class."
        . " \n <code>public function " . $action . "() {\n\n}</code>");
      } else {
        // set data as $request data...? This way we can access partials
        $this->_data += $request;
      }
    }

    if (is_array($this->_output) || is_object($this->_output)) {
      $this->_data = $this->_output;
      $this->_output = null;
    }

    // render
    if ($this->_output === null) {
      // beforeRender callbacks
      $this->callback('beforeRender', $request);

      if (!in_array($request['_format'], $this->_returns)) {
        throw new controller\exception\MissingView('Unaccepted Format "<strong>' . $request['_format'] . '</strong>" in the controller <strong>' . get_class($this) . '</strong>.' . "\n
        Add the following to <strong>" . get_class($this) . "</strong><code>public \$_returns = array('" . implode("', '", array_merge($this->_returns, array($request['_format']))) . "');</code>");
      }

      try {
        // render view
        $this->_output = View::render(array(
          'template'  => $this->_view . '.' . $request['_format'],
          'layout'    => $this->_layout,
          'data'      => $this->_data + (array) $this
        ));
      } catch (controller\exception\MissingView $e) {
        // serialize output
        if (isset(self::$formats[$request['_format']]) && method_exists(self::$formats[$request['_format']][0], self::$formats[$request['_format']][1])) {
          $this->_output = call_user_func(self::$formats[$request['_format']], $this->_data);
        } else {
          throw $e;
        }
      }
    }

    // afterRender callbacks
    $this->callback('afterRender', $request);

    // return response
    return $this->_output;
  }


  /**
   * Perform a callback.
   * @param string $name
   */
  public function callback($name, $request) {
    if (!isset($this->_callbacks[$name])) { return; }
    foreach ($this->_callbacks[$name] as $callback => $exceptions) {
      // there has to be a better algorithm for this....
      if (empty($exceptions['only']) || (in_array($request['_controller'] . '/' . $request['_action'], $exceptions['only']) || in_array($request['_controller'], $exceptions['only']))) {
        if (empty($exceptions['except']) || (!in_array($request['_controller'] . '/' . $request['_action'], $exceptions['except']) && !in_array($request['_controller'], $exceptions['except']))) {
          $this->{$callback}($request);
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

}
