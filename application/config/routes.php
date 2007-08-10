<?php

define('PARAM_INT', '[\d]');
define('PARAM_WORD', '[\w]');

$router = new router;

// admin routes
  //$router->connect(':controller/:action/:id.:format', array('controller' => 'admin\/[^\/\.]+'));  

// default routes
  $router->connect(':controller/:action/:id.:format');
  
?>