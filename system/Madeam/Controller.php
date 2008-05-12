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
	public    $finalOutput  = null;

  protected $scaffold     = false;
  protected $layout       = 'master';
  protected $represent    = false;

  protected $actionView;
  protected $isRendered   = false;
  protected $viewParser;

  protected $scaffoldController;
  protected $scaffoldKey;

  public function __construct($params) {
    // load represented model
    if (is_string($this->represent)) {
      $this->represent = Madeam_Inflector::modelNameize($this->represent);
    }

    // set params
    $this->params = $params;
    
    // set request information
    $this->_get;
    $this->_post;
    $this->_session;
    $this->_cookie;
    $this->_requestMethod;
    
    // after controller executes merge session information with existing session
    //$_SESSION = array_merge($_SESSION, $this->_session);
    // ... but how do this make it any less global? I guess it doesn't...
    // what if we tracked all opened controllers and at the end of the request we merge all their session information...??? better...
    // $this->makeRequest('posts/index', $post, $session, $


    // scaffold config
    if ($this->scaffold == true && $this->represent == true) {
      $this->scaffoldController  = $this->params['controller'];
      $this->scaffoldKey         = $this->{$this->represent}->getPrimaryKey();
    }

    // set view
    $this->setView($this->params['controller'] . '/' . $this->params['action']);

    // set layout
    // check to see if the layout param is set to true or false. If it's false then don't render the layout
    if ($this->params['useLayout'] == '0' || $this->params['useLayout'] == 'false') {
      $this->setLayout(false);
    } else {
      $this->setLayout($this->layout);
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

      // create component instance
      $model = new $modelClass();
      $this->$name = $model;
      return $model;
    }
  }

  public function __call($name, $args) {
    if (!file_exists($this->viewFile)) {
      throw new Madeam_Exception('Missing Action <b>' . $name . '</b> in <b>' . get_class($this) . '</b> controller', Madeam_Exception::ERR_ACTION_MISSING);
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
  final protected function callAction($uri) {
    return Madeam::callAction($uri);
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
  final protected function callPartial($partialPath, $data = array(), $start = 0, $limit = false) {
    if (!empty($data)) {
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
          $_num++;
          include(PATH_TO_VIEW . implode(DS, $partial) . DS . '_' . $partialName . '.' . $this->params['format']);
        }
      } else {
        $$partialName = $data;
        $_num++;
        include(PATH_TO_VIEW . implode(DS, $partial) . DS . '_' . $partialName . '.' . $this->params['format']);
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
    $this->viewFile = PATH_TO_VIEW . str_replace('/', DS, low($view)) . '.' . $this->params['format'];
  }

  /**
   * Enter description here...
   *
   * @param string/boolean/array $layouts
   */
  final protected function setLayout($layouts) {
    $this->layout = array();

    if (func_num_args() < 2) {
      if (is_string($layouts)) {
        $this->layout[] = PATH_TO_LAYOUT . $layouts . '.layout.' . $this->params['format'];
      } elseif (is_array($layouts)) {
        foreach ($layouts as $layout) {
          $this->layout[] = PATH_TO_LAYOUT . $layout . '.layout.' . $this->params['format'];
        }
      } else {
        $this->layout = false;
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
   * @param unknown_type $data
   * @param unknown_type $rendered
   * @return unknown
   */
  final protected function render($data = true, $rendered = true) {
    // sometimes the developer may want to tell the view not to render from the controller's action
    if ($data === false) { $this->isRendered = true; }

    // consider: checking if it's rendered based on if there is anything in the output buffer? does that make sense?
    if ($this->isRendered === false) {
      // output buffering
      ob_start();

      foreach($this as $key => $value) { $$key = $value; }
      //extract($this->data, EXTR_OVERWRITE); // which one is faster?


      if ($data === true) {
        // include view's template file
        if (file_exists($this->viewFile)) {
          include($this->viewFile);
        } else {
          throw new Madeam_Exception('Missing View <b>' . substr($this->viewFile, strlen(PATH_TO_VIEW)) . '</b>', Madeam_Exception::ERR_VIEW_MISSING);
        }

				/*
				$parser = $this->params['format'];
				if (method_exists('madeamParser', $parser)) {
					unset($this->data['header_for_layout']);
					unset($this->data['params']);
					madeamParser::$parser($this->viewFile, $this->data);
				}
				*/

        // grab result of inclusion
        $content_for_layout = ob_get_contents();
        // clear output
        ob_clean();
      } elseif (is_string($data)) { // this needs to change
        // set $content_for_layout to $data which is just a string
				$content_for_layout = $data;
				/*
				$parser = $this->params['format'];
        if (method_exists('madeamParser', $parser)) {
					$content_for_layout = madeamParser::$parser($this->viewFile, $data);
				} else {
					$content_for_layout = null;
				}
				*/
      }

      // loop through layouts
      // the layouts are rendered in order they are in the array
      if (is_array($this->layout)) {
        foreach ($this->layout as $layout) {
          if ($layout && file_exists($layout)) {
            // include layout if it exists
            include($layout);
          } else {
            // otherwise just output the content
            echo $content_for_layout;
          }

          // get contents of output buffering
          $content_for_layout = ob_get_contents();

          // clean ob
          ob_clean();
        }
      }

      // end ouptut buffering
      ob_end_clean();

      // mark view as rendered
      $this->isRendered = $rendered;

      $this->finalOutput = $content_for_layout;

      return $this->finalOutput;
    }

    return false;
  }

  /**
   * Scaffold Actions
   * =======================================================================
   */

  public function _scaffold_index() {
    include(SCAFFOLD_PATH . $this->scaffold . '/action/index.php');
  }

  public function _scaffold_show() {
    include(SCAFFOLD_PATH . $this->scaffold . '/action/show.php');
  }

  public function _scaffold_add() {
    include(SCAFFOLD_PATH . $this->scaffold . '/action/add.php');
  }

  public function _scaffold_edit() {
    include(SCAFFOLD_PATH . $this->scaffold . '/action/edit.php');
  }

  public function _scaffold_delete() {
    include(SCAFFOLD_PATH . $this->scaffold . '/action/delete.php');
  }


  /**
   * Callback functions
   * =======================================================================
   */

  /* what about re-naming these like this: _beforeAction() or before_action()? */
  /* come up with a better naming convention for these methods */
  protected function beforeAction() {
  }

  protected function beforeRender() {
  }

  protected function afterRender() {
  }
}
?>