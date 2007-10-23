<?php
class console_generate extends madeam_console {

	public $description = 'The generate console allows you to generate models, views and controllers';

	public $command_requires_root	= array('controller', 'model', 'view');
	
	public $require = array(
		'controller' => array(
			'name' => 'Please choose a name for this controller'
		)
	);

	public $validate = array(
		'controller' => array(
			'name' 			=> '/[a-z\/]/',
			'model' 		=> '/[a-z\/]/',
			'scaffold'	=> '/[a-z]/'
		)
	);



	function controller($params) {
	  // set scaffold setting
		if (isset($params['scaffold'])) {
		  $scaffold = $params['scaffold'];
		} else {
		  $scaffold = 'standard';
		}

		// set controller name and class name
		$controller_name = madeam_inflector::underscorize(low($params['name']));
		$controller_class_name = 'controller_' . $controller_name;

		// Send message to user that we are creating the controller
		out('Generating controller ' . $controller_name);

		// determine scaffold directory
		$dir = VENDOR_PATH . SCAFFOLD_PATH . $scaffold . DS;
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
              $model_name = madeam_inflector::model_nameize($params['represent']);
  		        $model_class_name = madeam_inflector::model_classize($model_name);

              // determine scaffolding key
              $model = new $model_class_name;
              $scaffold_key = $model->get_primary_key();
              $function_code = str_replace('$this->scaffold_key', "'$scaffold_key'", $function_code);

              // replace $this->scaffold_controller with controller name
              $function_code = str_replace('$this->scaffold_controller', "'$controller_name'", $function_code);

              // replace $this->represent with model name
              $function_code = str_replace('madeam_inflector::pluralize($this->represent)', "'" . madeam_inflector::pluralize($model_name) . "'", $function_code);
              $function_code = str_replace('{$this->represent}', $model_name, $function_code);
              $function_code = str_replace('$this->represent', "'$model_name'", $function_code);
            } else {
              out('Oops. This scaffold requires that it represents a model');
              return false;
            }
          }

          // remove .php extension
          $function_name = substr($file, 0, -4);

          // build function
          $controller_contents .= "\n\n\n  function $function_name() {";
          $controller_contents .= "\n    $function_code";
          $controller_contents .= "\n  }";

          out('Generating function ' . $function_name);
        }
      }
      closedir($dh);
    }

    // close class definition
    $controller_contents .= "\n\n}\n?>";

    // save file contents to file
    file_put_contents(CONTROLLER_PATH . $controller_class_name . '.php', $controller_contents);


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
              $model_name = madeam_inflector::model_nameize($params['represent']);
  		        $model_class_name = madeam_inflector::model_classize($model_name);

              // determine scaffolding key
              $model = new $model_class_name;
              $scaffold_key = $model->get_primary_key();
              $view_code = str_replace('$this->scaffold_key', "'$scaffold_key'", $function_code);

              // replace $this->scaffold_controller with controller name
              $view_code = str_replace('$this->scaffold_controller', "'$controller_name'", $function_code);

              // replace $this->represent with model name
              $view_code = str_replace('madeam_inflector::pluralize($this->represent)', "'" . madeam_inflector::pluralize($model_name) . "'", $view_code);
              $view_code = str_replace('{$this->represent}', $model_name, $view_code);
              $view_code = str_replace('$this->represent', "'$model_name'", $view_code);
            } else {
              out('Oops. This scaffold requires that it represents a model');
              return false;
            }
          }

          // remove extension
          $ext = '.' . substr($file, strrpos($file, '.') + 1);
          $view_name = substr($file, 0, -strlen($ext));

          out('Generating view ' . $view_name);

          // make controller directory if it does not already exist
          if (!file_exists(APP_PATH . 'view' . DS . $controller_name)) {
            mkdir(APP_PATH . 'view' . DS . $controller_name);
          }

          file_put_contents(APP_PATH . 'view' . DS . $controller_name . DS . $view_name . $ext, $view_code);
        }
      }
      closedir($dh);
    }

    // completed with no errors
	  return true;
	}



	function model($params) {
    if (!isset($params['type'])) { $type = 'activerecord'; } else { $type = $params['type']; }

    // set model name and class name
    $model_name = madeam_inflector::model_nameize($params['name']);
    $model_class_name = madeam_inflector::model_classize($model_name);

    // define model class
	  $model_contents = "<?php\nclass $model_class_name extends madeam_$type {";

	  // close class definition
    $model_contents .= "\n\n}\n?>";

    // save file
    file_put_contents(APP_PATH . 'model' . DS . $model_class_name . '.php', $model_contents);

    out('Generating ' . $model_name . ' model');

    return true;
	}



	function view($params) {
	  // set view name
	  $view_name = $params['name'];

	  // set view format
	  if (!isset($params['format'])) { $view_format = 'html'; } else { $view_format = $params['format']; }

	  // set file contents
	  $view_contents = $view_name . ' view';
	  
	  // this needs a re-write because it should allow depth greater than 1 directory	  
	  // make controller directory if it does not already exist
    if (!file_exists(APP_PATH . 'view' . DS . dirname($view_name))) {
      mkdir(APP_PATH . 'view' . DS . dirname($view_name));
    }

	  // save contents
    file_put_contents(APP_PATH . 'view' . DS . $view_name . '.' . $view_format, $view_contents);

    // save file
    out('Generating ' . $view_name . ' view');

    return true;
	}

}
?>