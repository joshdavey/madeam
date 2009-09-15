<?php
/**
 * Welcome to the Madeam Routes configuration file.
 *
 * Routes allow you to configure your urls so they look pretty but also help you define
 * powerful APIs for your web services. You can define parameters in your urls by prefixing them
 * with ":" and seperating them with "/".
 * 
 * Routes are matched in the order that they appear and the first matching route will dispatch
 * your request.
 * 
 * = Production Mode =
 * Warning: If you make any changes to your routes and they're cached on your production environment 
 * you'll need to clear your cache before you see the changes take place.
 * 
 * = Route Anatomy =
 * A route consists of a path pattern, default values and rules to match the values against
 * madeam\Router::connect(pattern, defaults, rules);
 * 
 * 
 * = Resource Routes =
 * The Router identifies resources as a set of RESTful routes. By using the Router's resource
 * method a group of routes are generated to represent a resource. 
 * 
 * madeam\Router::resource('posts');
 * 
 * Is the same as:
 * 
 * madeam\Router::connect("posts",            array('_action' => 'index',   '_controller' => 'posts'),  array('_method' => 'get'));
 * madeam\Router::connect("posts/:id",        array('_action' => 'show',    '_controller' => 'posts'),  array('_method' => 'get'));
 * madeam\Router::connect("posts",            array('_action' => 'delete',  '_controller' => 'posts'),  array('_method' => 'delete'));
 * madeam\Router::connect("posts",            array('_action' => 'update',  '_controller' => 'posts'),  array('_method' => 'put'));
 * madeam\Router::connect("posts",            array('_action' => 'create',  '_controller' => 'posts'),  array('_method' => 'post'));
 * 
 * Don't like using an id and would prefer a slug? Try this:
 * 
 * madeam\Router::resource::('posts', array('id' => 'slug', 'pattern' => '[a-z\-]+'));
 * 
 * 
 * = Sub Directory Routes =
 * Sub directories are a great way of organizing your project. For example you may want to keep all your
 * administrative code in a Controller sub directory called "admin". We can create a route for the controllers
 * within the admin directory by using a regular expression to match any controllers that are pre-fixed with
 * "admin". Use the route below as a guideline.
 * 
 * madeam\Router::connect(':_controller/:_action/:id', array(), array('_controller' => 'admin\/[^\/]+'));
 * 
 */
  
  
  
  // Default Routes -- Add new application routes above these routes
  madeam\Router::connect(':_controller/:_action/:id', array(), array('id' => '\d+'));
  madeam\Router::connect(':_controller/:_action/:slug', array(), array('id' => '[a-z\-]+'));
  madeam\Router::connect(':_controller/:_action');
