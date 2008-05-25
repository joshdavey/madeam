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
  public $finalOutput = null;

  /**
   * Enter description here...
   *
   * @var unknown_type
   */
  protected $scaffold = false;

  /**
   * Enter description here...
   *
   * @var string/array
   */
  protected $layout = 'master';

  /**
   * Enter description here...
   *
   * @var unknown_type
   */
  protected $view = null;

  /**
   * Enter description here...
   *
   * @var unknown_type
   */
  protected $data = array();

  /**
   * Enter description here...
   *
   * @var unknown_type
   */
  protected $represent = false;

  /**
   * Enter description here...
   *
   * @var unknown_type
   */
  protected $isRendered = false;

  /**
   * Enter description here...
   *
   * @var unknown_type
   */
  protected $viewParser;

  /**
   * Enter description here...
   *
   * @var unknown_type
   */
  protected $scaffoldController;

  /**
   * Enter description here...
   *
   * @var unknown_type
   */
  protected $scaffoldKey;

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
    $this->request = array_merge($requestGet, $requestPost, $requestCookie);
    $this->request['method'] = $requestMethod;

    // scaffold config
    if ($this->scaffold == true && $this->represent == true) {
      $this->scaffoldController = $this->requestGet['controller'];
      $this->scaffoldKey = $this->{$this->represent}->getPrimaryKey();
    }

    // set view
    $this->setView($this->requestGet['controller'] . '/' . $this->requestGet['action']);

    // set layout
    // check to see if the layout param is set to true or false. If it's false then don't render the layout
    if (isset($this->requestGet['useLayout']) && ($this->requestGet['useLayout'] == '0' || $this->requestGet['useLayout'] == 'false')) {
      $this->setLayout(false);
    } else {
      $this->setLayout($this->layout);
    }

    // execute

  }

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

  /**
   * Handle variable gets.
   * This magic method exists to handle instances of models and components when they're called.
   * If the instance of a model or component doesn't exist we create it here.
   * This is so we don't need to pre-load all of them.
   *
   * @param string $name
   * @return object
   */
  public function __get($name) {
    $match = array();
    if (preg_match("/^[A-Z]{1}/", $name, $match)) {
      // set model class name
      $modelClass = 'Model_' . $name;

      // testing idea of not needing to create models in protoptype stage of site
      // this should still check to see if a table exists...
      // or maybe we can just let it throw a SQL error which works just as well
      if (!class_exists($modelClass, false)) {
        eval("class $modelClass extends Madeam_ActiveRecord2 {}");
      }

      // create component instance
      $model = new $modelClass();
      $this->$name = $model;
      return $model;

    }
    return false;
  }

  public function __call($name, $args) {
    if (! file_exists($this->view)) {
      throw new Madeam_Exception_MissingAction('Missing Action <b>' . $name . '</b> in <b>' . get_class($this) . '</b> controller');
    }
  }

  final public function callback($callback) {
    return $this->$callback();
  }

  /**
   * Enter description here...
   *
   * @param unknown_type $uri
   * @return unknown
   */
  final public function callAction($uri, $params = array()) {

    if (!isset($params['get']))     { $params['get']    = $this->requestGet; }
    if (!isset($params['post']))    { $params['post']   = $this->requestPost; }
    if (!isset($params['cookie']))  { $params['cookie'] = $this->requestCookie; }

    return Madeam::makeRequest($uri, $params['get'], $params['post'], $params['cookie']);
  }

  /**
   * Enter description here...
   *
   * @param unknown_type $partialPath
   * @param unknown_type $data
   * @param unknown_type $start
   * @param unknown_type $limit
   * @return unknown
   */
  final public function callPartial($partialPath, $data = array(), $start = 0, $limit = false) {
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

  /**
   * This takes the full path to the view.
   *
   * For example: "posts/show" and not "show"
   *
   * @param string $view
   */
  final protected function setView($view) {
    $this->view = PATH_TO_VIEW . str_replace('/', DS, low($view)) . '.' . $this->requestGet['format'];
  }

  /**
   * Enter description here...
   *
   * @param string/boolean/array $layouts
   */
  final public function setLayout($layouts) {
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

  final public function getView() {
    return $this->view;
  }

  final public function getLayout() {
    return $this->layout;
  }

  final public function getData() {
    return $this->data;
  }

  final protected function render($data = true) {
    if ($data !== false) {

      // create parser instance
      $viewParserClass = 'Parser_' . ucfirst($this->requestGet['format']);
      $viewParser = new $viewParserClass($this);

      if (file_exists($this->view)) {
        // get list of private and protected vars because we don't want them to go to the view
        $refl = new ReflectionClass($this);
        $properties = $refl->getProperties(ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE);

        $excludedProperties = array();
        foreach ($properties as $prop) { $excludedProperties[] = $prop->getName(); }

        // set the data to be passed to the view and exclude private and protected parameters
        foreach ($this as $key => $value) {
          if (!in_array($key, $excludedProperties)) {
            $this->data[$key] = $value;
          }
        }

        $viewParser->renderView();
      } else {
        if (isset($this->{Madeam_Inflector::singalize($this->requestGet['controller'])})) {
          $this->data[Madeam_Inflector::singalize($this->requestGet['controller'])] = $this->{Madeam_Inflector::singalize($this->requestGet['controller'])};
        } elseif (isset($this->{Madeam_Inflector::pluralize($this->requestGet['controller'])})) {
          $this->data[Madeam_Inflector::pluralize($this->requestGet['controller'])] = $this->{Madeam_Inflector::pluralize($this->requestGet['controller'])};
        }

        $viewParser->missingView();
      }

      foreach ($this->layout as $layoutFile) {
        if ($layoutFile) {
          if (file_exists($layoutFile)) {
            // include layout if it exists
            $viewParser->renderLayout($layoutFile);
          } else {
            $viewParser->missingLayout($layoutFile);
          }
        }
      }

      // set final output
      $this->finalOutput = $viewParser->getOutput();
    } else {
      // set final output as null
      $this->finalOutput = null;
    }

    return;
  }

  /**
   * Enter description here...
   *
   * @param unknown_type $data
   * @param unknown_type $rendered
   * @return unknown
   */
  final protected function render2($data = true, $rendered = true) {
    // sometimes the developer may want to tell the view not to render from the controller's action
    if ($data === false) { $this->isRendered = true; }

    // consider: checking if it's rendered based on if there is anything in the output buffer? does that make sense?
    if ($this->isRendered === false) {
      // output buffering
      ob_start();

      if ($data === true) {
        // include view's template file
        if (file_exists($this->view)) {
          $parserClass = 'Parser_' . ucfirst($this->requestGet['format']);
  				$parser = new $parserClass;

  				$this->content_for_layout = $parser->renderView($this->view, $this);
        } else {
          $parser->missingView();
          throw new Madeam_Exception_MissingView('Missing View <strong>' . substr($this->view, strlen(PATH_TO_VIEW)) . '</strong>');
        }
      } elseif (is_string($data)) { // this needs to change
        // set $content_for_layout to $data which is just a string
        $this->content_for_layout = $data;
      }

      // loop through layouts
      // the layouts are rendered in order they are in the array
      foreach ($this->layout as $layoutFile) {
        if ($layoutFile && file_exists($layoutFile)) {
          // include layout if it exists
          $viewParser->renderLayout($layout);
        } else {
          $viewParser->missingLayout();
        }
      }

      // end ouptut buffering
      ob_end_clean();

      // mark view as rendered
      $this->isRendered = $rendered;
      $this->finalOutput = $this->content_for_layout;

      return $this->finalOutput;
    }
    return false;
  }

  /**
   * Scaffold Actions
   * =======================================================================
   */
  public function _scaffold_index() {
    include (SCAFFOLD_PATH . $this->scaffold . '/action/index.php');
  }

  public function _scaffold_show() {
    include (SCAFFOLD_PATH . $this->scaffold . '/action/show.php');
  }

  public function _scaffold_add() {
    include (SCAFFOLD_PATH . $this->scaffold . '/action/add.php');
  }

  public function _scaffold_edit() {
    include (SCAFFOLD_PATH . $this->scaffold . '/action/edit.php');
  }

  public function _scaffold_delete() {
    include (SCAFFOLD_PATH . $this->scaffold . '/action/delete.php');
  }

  /**
   * Callback functions
   * =======================================================================
   */
  /* what about re-naming these like this: _beforeAction() or before_action()? */
  /* come up with a better naming convention for these methods */
  protected function beforeAction() {}

  protected function beforeRender() {}

  protected function afterRender() {}
}
