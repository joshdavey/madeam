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
 * @link			http://www.madeam.com
 * @package			madeam
 * @version			0.0.6
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 * @author      Joshua Davey
 */

class madeam_controller {
	public $output      = null;
  public $data        = array();
  public $params      = array();
  public $scaffold    = false;

  public $name;
  public $layout      = 'standard';
  public $view;  
  public $represent   = false;
  public $rendered    = false;
  public $parser;
  
  public $scaffold_controller;
  public $scaffold_key;

  public function __construct($params) {
    // load represented model
    if ($this->represent == true) {
      $this->represent = madeam_inflector::model_nameize($this->represent);
    }
    
    // assign params passed on from madeam_router
    $this->params = $params;
    
    // scaffold config
    if ($this->scaffold == true && $this->represent == true) {
      $this->scaffold_controller  = $this->params['controller'];
      $this->scaffold_key         = $this->{$this->represent}->get_primary_key();
    }

    // set view
    $this->view($params['action']);

    // set layout
    // check to see if the layout param is set to true or false. If it's false then don't render the layout
    if ($params['layout'] == '0' || $params['layout'] == 'false') {
      $this->layout(false); 
    } else {
      $this->layout($this->layout);
    }
		
		// set header for layout variable
		$this->set('header_for_layout', null);	
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
      $model_class = madeam_inflector::model_classize($name);
      
      // create model instance
      $inst = new $model_class;
      $this->$name = $inst;
      return $inst;
    } else {
      // set model class name
      $comp_class = 'component_' . $name;
			
      // create component instance
      $inst = new $comp_class($this);
      $this->$name = $inst;
      return $inst;
    }
  }
  
  /**
   * Final methods. (Actions cannot have the same name as these methods)
   * =======================================================================
   */
  
  
  final protected function call_action($uri, $cfg = array()) {
    return madeam::call_action($uri, $cfg, $this->data);
  }
  
  final protected function call_partial($partial_path, $data = array(), $start = 0, $limit = false) {
    if (!empty($data)) {
      // internal counter can be accessed in the view
      $_num = $start;

      // get partial name
      $partial = explode('/', $partial_path);
      $partial_name = array_pop($partial);

      // splice array so that it is within the range defined by $start and $limit
      if ($limit !== false) {
        $data = array_splice($data, $start, $limit);
      } else {
        $data = array_splice($data, $start);
      }

      // set variables
      if (isListFormat($data)) {
        foreach ($data as $key => $$partial_name) {
          $_num++;
          if (count($partial) > 0) {
            include(VIEW_PATH . implode('/', $partial) . '/_' . $partial_name . '.' . $this->params['format']);
          } else {
            include(VIEW_PATH . $this->params['controller'] . '/_' . implode($partial) . '.' . $this->params['format']);
          }
        }
      } else {
        $$partial_name = $data;
        $_num++;
        if (count($partial) > 0) {
          include(VIEW_PATH . implode('/', $partial) . '/_' . $partial_name . '.' . $this->params['format']);
        } else {
          include(VIEW_PATH . $this->params['controller'] . '/_' . implode($partial) . '.' . $this->params['format']);
        }
      }
    }

    return false;
  }

  final protected function redirect($uri, $exit = true) {
    if (!headers_sent()) {   
      if (substr_count($uri, 'http://') < 1) {
        header('Location:  ' . URI_PATH . $uri);
      } else {
        header('Location:  ' .$uri);
      }
      if ($exit) { exit; }
    } else {
      madeam_logger::log('Tried redirecting when headers already sent. (Check for echos before script redirects)');
    }
  }

  final protected function flash($msg, $uri, $pause = 5) {
    $this->layout('flash');

    $this->set('pause', $pause);
    $this->set('uri', $uri);
    $this->set('page_title', $msg);

    $this->render($msg);
  }

  final public function view($view) {
    $path_nodes   = explode('/', $view);
    $path_length  = count($path_nodes);

    if ($path_length > 1) {
      $this->view = VIEW_PATH . implode('/', $path_nodes) . '.' . $this->params['format'];
    } else {
      $this->view = VIEW_PATH . $this->params['controller'] . '/' . implode($path_nodes) . '.' . $this->params['format'];
    }
    
    // if scaffolding is true and the view doesn't exist then use the scaffolding view
    if (!file_exists($this->view) && $this->scaffold == true) {
      $this->view = SCAFFOLD_PATH . $this->scaffold . '/views/' . $view . '.' . $this->params['format'];
    }
  }

  /**
   * Enter description here...
   *
   * @param string/boolean/array $layouts
   */
  final public function layout($layouts) {
    $this->layout = array();
    
    if (func_num_args() < 2) {
      if (is_string($layouts)) {
        $this->layout[] = LAYOUT_PATH . $layouts . '.' . $this->params['format'];
      } elseif (is_array($layouts)) {
        foreach ($layouts as $layout) {
          $this->layout[] = LAYOUT_PATH . $layout . '.' . $this->params['format'];
        }
      } else {
        $this->layout = false;
      }
    } else {
      foreach (func_get_args() as $layout) {
        $this->layout[] = LAYOUT_PATH . $layout . '.' . $this->params['format'];
      }
    }
  }
  
  var $_parser = false;
  
  final public function set($name, $value) {
    $this->data[$name] = $value;
    
    /*
    $parser = "parser_' . $this->params['format'];
    if (class_exists($parser)) {
    	if ($this->_parser == false) { $this->_parser = new $parser; }    	
    	$this->_parser->set($name, $value);
  	}
  	*/
  }
  
  final public function render($data = true, $rendered = true) {
    // sometimes the developer may want to tell the view not to render from the controller's action
    if ($data === false) { $this->rendered = true; }

    // consider: checking if it's rendered based on if there is anything in the output buffer? does that make sense?
    if ($this->rendered === false) {
      // output buffering
      ob_start();

      foreach($this->data as $key => $value) { $$key = $value; }
      //extract($this->data, EXTR_OVERWRITE); // which one is faster?

      if ($data === true) {
        // include view's template file
        include($this->view);
				/*
				$parser = $this->params['format'];
				if (method_exists('madeamParser', $parser)) {
					unset($this->data['header_for_layout']);
					unset($this->data['params']);
					madeamParser::$parser($this->view, $this->data);
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
					$content_for_layout = madeamParser::$parser($this->view, $data);
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
      $this->rendered = $rendered;
      
      $this->output = $content_for_layout;

      return $this->output;
    }

    return false;
  }

  /**
   * Scaffold Actions
   * =======================================================================
   */

  public function _scaffold_index() {
    include(SCAFFOLD_PATH . $this->scaffold . '/actions/index.php');
  }

  public function _scaffold_show() {
    include(SCAFFOLD_PATH . $this->scaffold . '/actions/show.php');
  }

  public function _scaffold_add() {
    include(SCAFFOLD_PATH . $this->scaffold . '/actions/add.php');
  }

  public function _scaffold_edit() {
    include(SCAFFOLD_PATH . $this->scaffold . '/actions/edit.php');
  }

  public function _scaffold_delete() {
    include(SCAFFOLD_PATH . $this->scaffold . '/actions/delete.php');
  }


  /**
   * Callback functions
   * =======================================================================
   */

  /* what about re-naming these like this: _beforeAction() or before_action()? */
  /* come up with a better naming convention for these methods */
  public function before_action() {
  }

  public function after_action() {
  }

  public function before_render() {
  }

  public function after_render() {
  }
}
?>