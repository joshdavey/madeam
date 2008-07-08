<?php
/**
 * Welcome to the Madeam Routes configuration center.
 *
 * Routes allow you to configure your urls so they look pretty. You can define parameters in your
 * urls by prefixing them with ":" and seperating them with "/".
 */

// admin routes
  //Madeam_Router::connect(':controller/:action/:id', array('controller' => 'admin\/[^\/]+'));

// default routes
  Madeam_Router::connect(':controller/:action/:id');
