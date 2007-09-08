<?php

define('PARAM_INT', '[\d]');
define('PARAM_WORD', '[\w]');

$router = new madeam_router;

// admin routes
  //$router->connect(':controller/:action/:id.:format', array('controller' => 'admin\/[^\/\.]+'));
  //$router->connect(':controller/:action/:id.:format', array('controller' => 'test\/[^\/\.]+'));

// default routes
  $router->connect(':controller/:action/:id.:format');
  
?>