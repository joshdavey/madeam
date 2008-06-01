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
  private $output = null;

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

  public function __construct($requestGet = array(), $requestPost = array(), $requestCookie = array(), $requestMethod = 'GET') {
    // load represented model
    if (is_string($this->represent)) {
      $this->represent = Madeam_Inflector::modelNameize($this->represent);
    }

    // set request information
    $this->requestGet       = $requestGet;
    $this->requestPost      = $requestPost;
    $this->requestCookie    = $requestCookie;
    $this->requestMethod    = $requestMethod;

    // for consideration...
    // combine all request information into a single variable
    $this->params = array_merge($requestGet, $requestPost, $requestCookie);
    $this->params['method'] = $requestMethod;

    $this->data['params'] = $this->params;

    // define setup
    $this->setup['beforeFilter'] = $this->setup['beforeRender'] = $this->setup['afterRender'] = array();

    // scaffold config
    $this->scaffoldController = $this->requestGet['controller'];

    // set view
    $this->view($this->requestGet['controller'] . '/' . $this->requestGet['action']);

    // set layout
    // check to see if the layout param is set to true or false. If it's false then don't render the layout
    if (isset($this->requestGet['useLayout']) && ($this->requestGet['useLayout'] == '0' || $this->requestGet['useLayout'] == 'false')) {
      $this->layout(false);
    } else {
      $this->layout($this->layout);
    }

    // create parser instance
    $parserClassName = 'Parser_' . ucfirst($this->requestGet['format']);
    $this->parser = new $parserClassName($this);

    // reflection
    $this->reflection = new ReflectionClass($this);

    $properties = $this->reflection->getProperties(ReflectionProperty::IS_PUBLIC);
    foreach ($properties as $property) {
      $matches = array();
      if (preg_match('/^(beforeFilter|beforeRender|afterRender)_([a-zA-Z0-9]*)(_except)?/', $property->getName(), $matches)) {
        $this->setup[$matches[1]][] = $matches[2];
      }
    }
  }

  /*
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
      if (!class_exists($modelClassName, false)) {
        eval("class $modelClassName extends Madeam_ActiveRecord2 {}");
      }

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
      throw new Madeam_Exception_MissingAction('Missing Action <b>' . $name . '</b> in <b>' . get_class($this) . '</b> controller');
    }
  }

  public function __set($name, $value) {
    if (!preg_match('/^(?:_[A-Z]|[A-Z]){1}/', $name)) {
      $this->data[$name] = $value;
    }
  }

  public function __unset($name) {
    unset($this->data[$name]);
  }

  final public function process() {

    // beforeFilter callbacks
    foreach ($this->setup['beforeFilter'] as $callback) {
      $this->$callback();
    }

    // action
    $this->{$this->params['action'].'Action'}();

    // beforeRender callbacks
    foreach ($this->setup['beforeRender'] as $callback) {
      $this->$callback();
    }

    // render
    $this->render();

    // afterRender callbacks
    foreach ($this->setup['afterRender'] as $callback) {
      $this->$callback();
    }

    // return response
    return $this->output;
  }

  final public function request($uri, $params) {
    if (!isset($params['get']))     { $params['get']    = $this->requestGet; }
    if (!isset($params['post']))    { $params['post']   = $this->requestPost; }
    if (!isset($params['cookie']))  { $params['cookie'] = $this->requestCookie; }

    return Madeam::makeRequest($uri, $params['get'], $params['post'], $params['cookie']);
  }

  final public function partial($path, $data, $start = 0, $limit = false) {
    if (! empty($data)) {
      // internal counter can be accessed in the view
      $_num = $start;
      /**
       * IMPORTANT! We should have our render logic in here!
       * I want to be able to use partials as templates!!!!!!!!!
       */
      // get partial name
      $partial = explode('/', $partialPath);
      $partialName = array_pop($partial);
      // splice array so that it is within the range defined by $start and $limit
      if ($limit !== false) {
        $data = array_splice($data, $start, $limit);
      } else {
        $data = array_splice($data, $start);
      }
      // set variables
      if (is_list($data)) {
        foreach ($data as $key => $$partialName) {
          $_num ++;
          include (PATH_TO_VIEW . implode(DS, $partial) . DS . '_' . $partialName . '.' . $this->requestGet['format']);
        }
      } else {
        $$partialName = $data;
        $_num ++;
        include (PATH_TO_VIEW . implode(DS, $partial) . DS . '_' . $partialName . '.' . $this->requestGet['format']);
      }
    }
    return false;
  }

  final public function view($view) {
    $this->view = PATH_TO_VIEW . str_replace('/', DS, low($view)) . '.' . $this->requestGet['format'];
  }

  final public function layout($layouts) {
    $this->layout = array();
    if (func_num_args() < 2) {
      if (is_string($layouts)) {
        $this->layout[] = PATH_TO_LAYOUT . $layouts . '.layout.' . $this->requestGet['format'];
      } elseif (is_array($layouts)) {
        foreach ($layouts as $layout) {
          $this->layout[] = PATH_TO_LAYOUT . $layout . '.layout.' . $this->requestGet['format'];
        }
      } else {
        $this->layout = array(false);
      }
    } else {
      foreach (funcget_args() as $layout) {
        $this->layout[] = PATH_TO_LAYOUT . $layout . '.layout.' . $this->requestGet['format'];
      }
    }
  }

  final public function render($data = true) {
    if ($data !== false) {
      if (file_exists($this->view)) {
        $this->parser->renderView();
      } else {
        if (isset($this->{Madeam_Inflector::singalize($this->requestGet['controller'])})) {
          $this->data[Madeam_Inflector::singalize($this->requestGet['controller'])] = $this->{Madeam_Inflector::singalize($this->requestGet['controller'])};
        } elseif (isset($this->{Madeam_Inflector::pluralize($this->requestGet['controller'])})) {
          $this->data[Madeam_Inflector::pluralize($this->requestGet['controller'])] = $this->{Madeam_Inflector::pluralize($this->requestGet['controller'])};
        }

        $this->parser->missingView();
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
      $this->output = $this->parser->getOutput();
    } else {
      // set final output as null
      $this->output = null;
    }

    return true;
  }

  final public function scaffold($action) {
    require (SCAFFOLD_PATH . $this->scaffold . '/action/' . $action . '.php');
  }

}
