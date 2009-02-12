<?php

class Madeam_Script_Create extends Madeam_Console_Script {

  public $description = 'The create console allows you to generate models, views and controllers';

  public $required = array('controller' => array('name' => 'Please enter a name for this controller'), 'model' => array('name' => 'Please enter a name for this model'), 'view' => array('name' => 'Please enter a name for this view'), 'scaffold' => array('controller' => 'Please enter the name of the controller', 'represent' => 'Please enter a name of the model this controller will represent', 'from' => 'Please enter from which scaffold pattern you would like to build from'));

  public $validate = array('controller' => array('name' => '/[a-z\/]/', 'model' => '/[a-z\/]/', 'scaffold' => '/[a-z]/'));

  /**
   * Creates a controller
   *
   * @param array $params
   * @return boolean
   */
  public function controller($params) {
    // set scaffold setting
    if (isset($params['scaffold'])) {
      $scaffold = $params['scaffold'];
    } else {
      $scaffold = 'standard';
    }
    
    // default params
    $_params = array(
      'extends' => 'Controller_App', 
      'actions' => 'index,show,new,create,edit,update,delete'
    );
    
    // merge new params with default params
    $params = array_merge($_params, $params);

    // set controller name and class name
    $nameNodes = explode('/', $params['name']);
    foreach ($nameNodes as &$node) {
      $node = ucfirst($node);
    }
    $controllerClassName      = 'Controller_' . implode('_', $nameNodes);
    $controllerViewFilePath   = Madeam_Framework::$pathToApp . 'View' . DS . strtolower(implode(DS, $nameNodes));
    $controllerName           = array_pop($nameNodes);
    $controllerClassFilePath  = Madeam_Framework::$pathToApp . 'Controller' . DS . implode(DS, $nameNodes);    
    
    
    // Send message to user that we are creating the controller
    Madeam_Console_CLI::outCreate('Controller ' . $controllerClassName);
    
    // Create Class directory
    $this->createDir($controllerClassFilePath);
    
    // Create View directory
    $this->createDir($controllerViewFilePath);

    // define controller class in controller file contents
    $controllerContents = "<?php\nclass $controllerClassName extends " . $params['extends'] . " {\n";

    if (isset($params['actions']) && $params['actions'] !== true) {
      $actions = preg_split('/[\s\,]/', $params['actions']);
      foreach ($actions as $action) {
        // add action method to class
        $controllerContents .= "\n  public function $action" . "Action() {\n    \n  }\n";
        
        // create view file
        $this->createFile(strtolower($action) . '.' . Madeam_Framework::defaultFormat, $controllerViewFilePath . DS, "$action action's view");
      }
    }

    // close class definition
    $controllerContents .= "\n}";    

    // save file contents to file
    $this->createFile($controllerName . '.php', $controllerClassFilePath . DS, $controllerContents);

    // completed with no errors
    return true;
  }

  /**
   * Creates a model
   *
   * @param array $params
   * @return boolean
   */
  public function model($params) {
    // set model type
    if (! isset($params['type'])) {
      $type = 'activerecord';
    } else {
      $type = $params['type'];
    }
    
    // default params
    $_params = array(
      'extends' => 'Madeam_Model_ActiveRecord',
    );
    
    // merge new params with default params
    $params = array_merge($_params, $params);
    
    
    // set model name and class name
    $modelName = Madeam_Inflector::modelNameize($params['name']);
    $modelClassName = Madeam_Inflector::modelClassize($modelName);
    // define model class
    $modelContents = "<?php\nclass $modelClassName extends " . $params['extends'] . " {";
    // close class definition
    $modelContents .= "\n\n}\n?>";
    // save file
    if ($this->createFile($modelName . '.php', Madeam_Framework::$pathToApp . 'Model' . DS, $modelContents) === true) {
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
  public function view($params) {
    // set view name
    $viewName = $params['name'];
    // set file contents
    $viewContents = $viewName . ' view';
    // set view format
    if (! isset($params['format'])) {
      $viewFormat = 'html';
    } else {
      $viewFormat = $params['format'];
    }
    // this needs a re-write because it should allow depth greater than 1 directory
    // make controller directory if it does not already exist
    if (! file_exists(Madeam_Framework::$pathToApp . 'View' . DS . dirname($viewName))) {
      mkdir(Madeam_Framework::$pathToApp . 'View' . DS . dirname($viewName));
    }
    // save contents to new view file
    if ($this->createFile($viewName . '.' . $viewFormat, Madeam_Framework::$pathToApp . 'View' . DS, $viewContents) === true) {
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
  public function scaffold($params) {
    // set scaffold setting
    if (isset($params['scaffold'])) {
      $scaffold = $params['scaffold'];
    } else {
      $scaffold = 'standard';
    }
    
    // set controller name and class name
    $controllerName = Madeam_Inflector::underscorize(strtolower($params['name']));
    $controllerClassName = 'Controller_' . $controllerName;
    
    // Send message to user that we are creating the controller
    Madeam_Console_CLI::outCreate('Controller ' . $controllerName);
    
    // determine scaffold directory
    $dir = PATH_TO_ANTHOLOGY . SCAFFOLD_PATH . $scaffold . DS;
    $actions_dir = $dir . 'action' . DS;
    $views_dir = $dir . 'view' . DS;
    
    // define controller class in controller file contents
    $controllerContents = "<?php\nclass $controllerClassName extends controller_app {";
    
    // read scaffold directory for actions
    if ($dh = opendir($actions_dir)) {
      while(($file = readdir($dh)) !== false) {
        if ($file != '.' && $file != '..' && $file != '.svn') {
          $function_code = file_get_contents($actions_dir . $file);
          
          /* remove <?php and ?> */
          $function_code = substr($function_code, 5);
          $function_code = substr($function_code, 0, - 2);
          
          // add indents at every line break
          $function_code = str_replace("\n", "\n    ", trim($function_code));
          if (substr_count($function_code, '$this->represent') > 0) {
            if (isset($params['represent'])) {
              // set model name and class name
              $model_name = Madeam_Inflector::modelNameize($params['represent']);
              $model_class_name = Madeam_Inflector::modelClassize($model_name);
              // determine scaffolding key
              $model = new $model_class_name();
              $scaffold_key = $model->get_primary_key();
              $function_code = str_replace('$this->scaffold_key', "'$scaffold_key'", $function_code);
              // replace $this->scaffold_controller with controller name
              $function_code = str_replace('$this->scaffold_controller', "'$controllerName'", $function_code);
              // replace $this->represent with model name
              $function_code = str_replace('Madeam_Inflector::pluralize($this->represent)', "'" . Madeam_Inflector::pluralize($model_name) . "'", $function_code);
              $function_code = str_replace('{$this->represent}', $model_name, $function_code);
              $function_code = str_replace('$this->represent', "'$model_name'", $function_code);
            } else {
              Madeam_Console_CLI::outError('This scaffold requires that it represents a model');
              return false;
            }
          }
          // remove .php extension
          $function_name = substr($file, 0, - 4);
          // build function
          $controllerContents .= "\n\n\n  function $function_name() {";
          $controllerContents .= "\n    $function_code";
          $controllerContents .= "\n  }";
          Madeam_Console_CLI::outCreate('action ' . $function_name);
        }
      }
      
      closedir($dh);
    }
    // close class definition
    $controllerContents .= "\n\n}\n?>";
    // save file contents to file
    $this->createFile($controllerClassName . '.php', PATH_TO_CONTROLLER, $controllerContents);
    // read scaffold directory for views
    if ($dh = opendir($views_dir)) {
      while(($file = readdir($dh)) !== false) {
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
              $model = new $model_class_name();
              $scaffold_key = $model->get_primary_key();
              $view_code = str_replace('$this->scaffold_key', "'$scaffold_key'", $function_code);
              // replace $this->scaffold_controller with controller name
              $view_code = str_replace('$this->scaffold_controller', "'$controller_name'", $function_code);
              // replace $this->represent with model name
              $view_code = str_replace('Madeam_Inflector::pluralize($this->represent)', "'" . Madeam_Inflector::pluralize($model_name) . "'", $view_code);
              $view_code = str_replace('{$this->represent}', $model_name, $view_code);
              $view_code = str_replace('$this->represent', "'$model_name'", $view_code);
            } else {
              Madeam_Console_CLI::outError('This scaffold requires that it represents a model');
              return false;
            }
          }
          // remove extension
          $ext = '.' . substr($file, strrpos($file, '.') + 1);
          $view_name = substr($file, 0, - strlen($ext));
          Madeam_Console_CLI::outCreate('view ' . $view_name);
          // make controller directory if it does not already exist
          if (! file_exists(PATH_TO_VIEW . $controller_name)) {
            mkdir(PATH_TO_VIEW . $controller_name);
          }
          $this->createFile($view_name . $ext, PATH_TO_VIEW . $controller_name . DS, $view_code);
        }
      }
      closedir($dh);
    }
    
    // completed with no errors
    return true;
  }
}