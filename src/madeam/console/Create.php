<?php
namespace madeam\console;
class Create extends \madeam\Console {

  /**
   * Creates a controller
   *
   * @param string $name
   * @param string $extends
   * @param string $views
   * @param string $actions
   */
  public function controller($name, $extends = 'AppController', $views = 'index,read,new,edit', $actions = 'index,read,new,create,edit,update,delete') {
    
    // set controller name and class name
    $names = explode('/', $name);
    
    $controllerName           = $names[count($names) - 1];
    $controllerClassName      = ucfirst($controllerName) . 'Controller';
    $controllerNamespace      = implode('\\', $names);
    $controllerViewFilePath   = getcwd() . '/app/views/' . strtolower(implode(DIRECTORY_SEPARATOR, $names)) . '/';
    array_pop($names);
    if (count($names) == 0) {
      $controllerClassFilePath  = getcwd() . '/app/controllers/';
    } else {
      $controllerClassFilePath  = getcwd() . '/app/controllers/' . implode(DIRECTORY_SEPARATOR, $names) . '/';
    }
    
    // Create Class directory
    `mkdir -p $controllerClassFilePath`;
    
    // Create View directory
    `mkdir -p $controllerViewFilePath`;

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
      $viewFile = $controllerViewFilePath . strtolower($view) . '.html';
      `touch $viewFile`;
      file_put_contents($viewFile, "$view view");
    }

    // close class definition
    $controllerContents .= "\n}";    

    // save file contents to file
    $controllerFile = $controllerClassFilePath . $controllerName .  'Controller.php';
    `touch $controllerFile`;
    file_put_contents($controllerFile, $controllerContents);
  }

  /**
   * Creates a view
   *
   * @param array $name
   * @param array $format
   */
  public function view($name, $format = 'html') {
    // set file contents
    $viewContents = $name . ' view';
    
    $nodes = explode('/', $name);
    
    $viewFileName = strtolower(array_pop($nodes)) . '.' . $format;
    
    // create view directory
    $viewPath = getcwd() . '/app/views/' . implode('/', $nodes) . '/';
    `mkdir -p $viewPath`;
    
    $viewFile = $viewPath . $viewFileName;
    `touch $viewFile`;
    
    file_put_contents($viewFile, $viewContents);
  }
  
}