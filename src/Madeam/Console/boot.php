<?php
// set error reporting to all
  error_reporting(E_ALL);

// really weird bug when I installed zend core made SERVER_NAME inaccessible and threw a notice error
// this is a simple hack to fix that
  if (! isset($_SERVER['SERVER_NAME'])) { $_SERVER['SERVER_NAME'] = 'localhost'; }

// use unicode
  if (function_exists('mb_internal_encoding')) { mb_internal_encoding("UTF-8"); }

// set default timezone
  date_default_timezone_set('America/Toronto');
  
// inlude Madeam
  require 'Madeam/src/Madeam.php';
  
// set include paths  
  set_include_path(implode(PATH_SEPARATOR, Madeam::paths(getcwd() . '/public/')) . PATH_SEPARATOR . get_include_path());