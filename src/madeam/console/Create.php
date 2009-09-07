<?php
class madeam\Console_Create extends madeam\Console {

  public $description = 'The create console allows you to generate models, views and controllers';

  /**
   * Creates a controller
   *
   * @param array $params
   * @return boolean
   */
  public function controller($name, $extends = 'AppController', $views = 'index,read,new,edit', $actions = 'index,read,new,create,edit,update,delete', $scaffold = false) {
    
    // set controller name and class name
    $nameNodes = explode('/', $name);
    foreach ($nameNodes as &$node) {
      $node = ucfirst($node);
    }
    
    $controllerClassName      = 'Controller_' . implode('_', $nameNodes);
    $controllerViewFilePath   = Framework::$pathToApp . 'src' . DS . 'View' . DS . strtolower(implode(DS, $nameNodes));
    $controllerName           = array_pop($nameNodes);
    $controllerClassFilePath  = Framework::$pathToApp . 'src' . DS . 'Controller' . DS . implode(DS, $nameNodes);    
    
    
    // Send message to user that we are creating the controller
    madeam\console\CLI::outCreate('Controller ' . $controllerClassName);
    
    // Create Class directory
    $this->createDir($controllerClassFilePath);
    
    // Create View directory
    $this->createDir($controllerViewFilePath);

    // define controller class in controller file contents
    $controllerContents = "<?php\nclass $controllerClassName extends " . $extends . " {\n";
    
    // create action
    $actions = preg_split('/[\s\,]/', $actions);
    foreach ($actions as $action) {
      $action = trim($action);
      // add action method to class
      $controllerContents .= "\n  public function $action" . "Action() {\n    \n  }\n";
    }
    
    // create views
    $views = preg_split('/[\s\,]/', $views);
    foreach ($actions as $view) {        
      $view = trim($view);
      // create view file
      $this->createFile(strtolower($view) . '.' . Framework::defaultFormat, $controllerViewFilePath . DS, "$view view");
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
  public function model($name, $extends = 'madeam\Model_ActiveRecord') {
    // set model name and class name
    $modelName = madeam\Inflector::modelNameize($name);
    $modelClassName = madeam\Inflector::modelClassize($modelName);
    // define model class
    $modelContents = "<?php\nclass $modelClassName extends " . $extends . " {";
    // close class definition
    $modelContents .= "\n\n}";
    
    // save file
    if ($this->createFile($modelName . '.php', Framework::$pathToApp . 'src' . DS . 'Model' . DS, $modelContents) === true) {
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
  public function view($name, $format = 'html') {
    // set file contents
    $viewContents = $name . ' view';
    
    // this needs a re-write because it should allow depth greater than 1 directory
    // make controller directory if it does not already exist
    if (! file_exists(Framework::$pathToApp . 'src' . DS . 'View' . DS . dirname($name))) {
      mkdir(Framework::$pathToApp . 'View' . DS . dirname($name));
    }
    // save contents to new view file
    if ($this->createFile($name . '.' . $format, Framework::$pathToApp . 'src' . DS . 'View' . DS, $viewContents) === true) {
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
    $controllerName = madeam\Inflector::underscorize(strtolower($params['name']));
    $controllerClassName = 'Controller_' . $controllerName;
    
    // Send message to user that we are creating the controller
    madeam\console\CLI::outCreate('Controller ' . $controllerName);
    
    // determine scaffold directory
    $dir = PATH_TO_ANTHOLOGY . SCAFFOLD_PATH . $scaffold . DS;
    $actions_dir = $dir . 'action' . DS;
    $views_dir = $dir . 'view' . DS;
    
    // define controller class in controller file contents
    $controllerContents = "<?php\nclass $controllerClassName extends AppController {";
    
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
              $model_name = madeam\Inflector::modelNameize($params['represent']);
              $model_class_name = madeam\Inflector::modelClassize($model_name);
              // determine scaffolding key
              $model = new $model_class_name();
              $scaffold_key = $model->get_primary_key();
              $function_code = str_replace('$this->scaffold_key', "'$scaffold_key'", $function_code);
              // replace $this->scaffold_controller with controller name
              $function_code = str_replace('$this->scaffold_controller', "'$controllerName'", $function_code);
              // replace $this->represent with model name
              $function_code = str_replace('madeam\Inflector::pluralize($this->represent)', "'" . madeam\Inflector::pluralize($model_name) . "'", $function_code);
              $function_code = str_replace('{$this->represent}', $model_name, $function_code);
              $function_code = str_replace('$this->represent', "'$model_name'", $function_code);
            } else {
              madeam\console\CLI::outError('This scaffold requires that it represents a model');
              return false;
            }
          }
          // remove .php extension
          $function_name = substr($file, 0, - 4);
          // build function
          $controllerContents .= "\n\n\n  function $function_name() {";
          $controllerContents .= "\n    $function_code";
          $controllerContents .= "\n  }";
          madeam\console\CLI::outCreate('action ' . $function_name);
        }
      }
      
      closedir($dh);
    }
    // close class definition
    $controllerContents .= "\n\n}";
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
              $model_name = madeam\Inflector::modelNameize($params['represent']);
              $model_class_name = madeam\Inflector::modelClassize($model_name);
              // determine scaffolding key
              $model = new $model_class_name();
              $scaffold_key = $model->get_primary_key();
              $view_code = str_replace('$this->scaffold_key', "'$scaffold_key'", $function_code);
              // replace $this->scaffold_controller with controller name
              $view_code = str_replace('$this->scaffold_controller', "'$controller_name'", $function_code);
              // replace $this->represent with model name
              $view_code = str_replace('madeam\Inflector::pluralize($this->represent)', "'" . madeam\Inflector::pluralize($model_name) . "'", $view_code);
              $view_code = str_replace('{$this->represent}', $model_name, $view_code);
              $view_code = str_replace('$this->represent', "'$model_name'", $view_code);
            } else {
              madeam\console\CLI::outError('This scaffold requires that it represents a model');
              return false;
            }
          }
          // remove extension
          $ext = '.' . substr($file, strrpos($file, '.') + 1);
          $view_name = substr($file, 0, - strlen($ext));
          madeam\console\CLI::outCreate('view ' . $view_name);
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