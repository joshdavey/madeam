<?php
/**
 * Welcome to the Madeam Routes configuration center.
 *
 * Routes allow you to configure your urls so they look pretty. You can define parameters in your
 * urls by prefixing them with ":" and seperating them with "/".
 */

// admin routes
  //Madeam_Router::connect(':_controller/:_action/:id', array(), array('_controller' => 'admin\/[^\/]+'));

// default routes
  
  Madeam_Router::connect(':_controller/:_action/:id');
