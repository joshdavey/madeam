<?php
class Script_Create extends Madeam_Console_Script {

	public $description = 'The create console allows you to generate models, views and controllers';

	public $required = array(
		'controller' => array(
			'name' => 'Please enter a name for this controller'
		),
		'model' => array(
		  'name' => 'Please enter a name for this model'
		),
		'view' => array(
		  'name' => 'Please enter a name for this view'
		),
		'scaffold' => array(
		  'controller'  => 'Please enter the name of the controller',
		  'represent'   => 'Please enter a name of the model this controller will represent',
		  'from'        => 'Please enter from which scaffold pattern you would like to build from'
		)
	);

	public $validate = array(
		'controller' => array(
			'name' 			=> '/[a-z\/]/',
			'model' 		=> '/[a-z\/]/',
			'scaffold'	=> '/[a-z]/'
		)
	);

  /**
   * Creates a controller
   *
   * @param array $params
   * @return boolean
   */
	function controller($params) {
	  // set scaffold setting
		if (isset($params['scaffold'])) {
		  $scaffold = $params['scaffold'];
		} else {
		  $scaffold = 'standard';
		}

		// set controller name and class name
		$controller_name = Madeam_Inflector::underscorize(low($params['name']));
		$controller_class_name = 'Controller_' . $controller_name;

		// Send message to user that we are creating the controller
		$this->out_create('controller ' . $controller_name);

		// define controller class in controller file contents
		$controller_contents = "<?php\nclass $controller_class_name extends controller_app {";

    // close class definition
    $controller_contents .= "\n\n}\n?>";

    // save file contents to file
    $this->create_file($controller_class_name . '.php', PATH_TO_CONTROLLER, $controller_contents);

    // completed with no errors
	  return true;
	}


  /**
   * Creates a model
   *
   * @param array $params
   * @return boolean
   */
	function model($params) {
    // set model type
	  if (!isset($params['type'])) { $type = 'activerecord'; } else { $type = $params['type']; }

    // set model name and class name
    $model_name = Madeam_Inflector::modelNameize($params['name']);
    $model_class_name = Madeam_Inflector::modelClassize($model_name);

    // define model class
	  $model_contents = "<?php\nclass $model_class_name extends madeam_$type {";

	  // close class definition
    $model_contents .= "\n\n}\n?>";

    // save file
    if ($this->create_file($model_class_name . '.php', PATH_TO_APP . 'model' . DS, $model_contents) === true) {
      return true;
    }

    return false;
	}


  /**
   * Creates a view
   *
   * @param array $params
   * @return boolean
   */
	function view($params) {
	  // set view name
	  $view_name = $params['name'];

	  // set file contents
	  $view_contents = $view_name . ' view';

	  // set view format
	  if (!isset($params['format'])) { $view_format = 'html'; } else { $view_format = $params['format']; }


	  // this needs a re-write because it should allow depth greater than 1 directory
	  // make controller directory if it does not already exist
    if (!file_exists(PATH_TO_APP . 'view' . DS . dirname($view_name))) {
      mkdir(PATH_TO_APP . 'view' . DS . dirname($view_name));
    }

	  // save contents to new view file
    if ($this->create_file($view_name . '.' . $view_format, PATH_TO_APP . 'view' . DS, $view_contents) === true) {
      return true;
    }

    return false;
	}


	/**
	 * Scaffold a new controller and views that represent a model
	 *
	 * @param array $params
	 * @return boolean
	 */
	function scaffold($params) {
    // set scaffold setting
		if (isset($params['scaffold'])) {
		  $scaffold = $params['scaffold'];
		} else {
		  $scaffold = 'standard';
		}

		// set controller name and class name
		$controller_name = Madeam_Inflector::underscorize(low($params['name']));
		$controller_class_name = 'controller_' . $controller_name;

		// Send message to user that we are creating the controller
		$this->out_create('controller ' . $controller_name);

		// determine scaffold directory
		$dir = PATH_TO_ANTHOLOGY . SCAFFOLD_PATH . $scaffold . DS;
		$actions_dir = $dir . 'action' . DS;
		$views_dir = $dir . 'view' . DS;

		// define controller class in controller file contents
		$controller_contents = "<?php\nclass $controller_class_name extends controller_app {";

    // read scaffold directory for actions
		if ($dh = opendir($actions_dir)) {
      while (($file = readdir($dh)) !== false) {
        if ($file != '.' && $file != '..' && $file != '.svn') {
          $function_code = file_get_contents($actions_dir . $file);

          /* remove <?php and ?> */
          $function_code = substr($function_code, 5);
          $function_code = substr($function_code, 0, -2);

          // add indents at every line break
          $function_code = str_replace("\n", "\n    ", trim($function_code));

          if (substr_count($function_code, '$this->represent') > 0) {
            if (isset($params['represent'])) {
              // set model name and class name
              $model_name = Madeam_Inflector::modelNameize($params['represent']);
  		        $model_class_name = Madeam_Inflector::modelClassize($model_name);

              // determine scaffolding key
              $model = new $model_class_name;
              $scaffold_key = $model->get_primary_key();
              $function_code = str_replace('$this->scaffold_key', "'$scaffold_key'", $function_code);

              // replace $this->scaffold_controller with controller name
              $function_code = str_replace('$this->scaffold_controller', "'$controller_name'", $function_code);

              // replace $this->represent with model name
              $function_code = str_replace('Madeam_Inflector::pluralize($this->represent)', "'" . Madeam_Inflector::pluralize($model_name) . "'", $function_code);
              $function_code = str_replace('{$this->represent}', $model_name, $function_code);
              $function_code = str_replace('$this->represent', "'$model_name'", $function_code);
            } else {
              $this->out_error('This scaffold requires that it represents a model');
              return false;
            }
          }

          // remove .php extension
          $function_name = substr($file, 0, -4);

          // build function
          $controller_contents .= "\n\n\n  function $function_name() {";
          $controller_contents .= "\n    $function_code";
          $controller_contents .= "\n  }";

          $this->out_create('action ' . $function_name);
        }
      }
      closedir($dh);
    }

    // close class definition
    $controller_contents .= "\n\n}\n?>";

    // save file contents to file
    $this->create_file($controller_class_name . '.php', PATH_TO_APP . 'controller' . DS, $controller_contents);

    // read scaffold directory for views
		if ($dh = opendir($views_dir)) {
      while (($file = readdir($dh)) !== false) {
        if ($file != '.' && $file != '..' && $file != '.svn') {
          $view_code = file_get_contents($views_dir . $file);

          // add indents at every line break
          $view_code = str_replace("\n", "\n    ", trim($view_code));

          if (substr_count($function_code, '$this->represent') > 0) {
            if (isset($params['represent'])) {
              // set model name and class name
              $model_name = Madeam_Inflector::modelNameize($params['represent']);
  		        $model_class_name = Madeam_Inflector::modelClassize($model_name);

              // determine scaffolding key
              $model = new $model_class_name;
              $scaffold_key = $model->get_primary_key();
              $view_code = str_replace('$this->scaffold_key', "'$scaffold_key'", $function_code);

              // replace $this->scaffold_controller with controller name
              $view_code = str_replace('$this->scaffold_controller', "'$controller_name'", $function_code);

              // replace $this->represent with model name
              $view_code = str_replace('Madeam_Inflector::pluralize($this->represent)', "'" . Madeam_Inflector::pluralize($model_name) . "'", $view_code);
              $view_code = str_replace('{$this->represent}', $model_name, $view_code);
              $view_code = str_replace('$this->represent', "'$model_name'", $view_code);
            } else {
              $this->out_error('This scaffold requires that it represents a model');
              return false;
            }
          }

          // remove extension
          $ext = '.' . substr($file, strrpos($file, '.') + 1);
          $view_name = substr($file, 0, -strlen($ext));

          $this->out_create('view ' . $view_name);

          // make controller directory if it does not already exist
          if (!file_exists(PATH_TO_VIEW . $controller_name)) {
            mkdir(PATH_TO_VIEW . $controller_name);
          }

          $this->create_file($view_name . $ext, PATH_TO_VIEW . $controller_name . DS, $view_code);
        }
      }
      closedir($dh);
    }

    // completed with no errors
	  return true;
	}

}
?>