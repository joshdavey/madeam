<?php
// use unicode
  if (function_exists('mb_internal_encoding')) { mb_internal_encoding("UTF-8"); }

// set content type to utf8
  header('Content-type: text/html; charset=utf-8');

// set default timezone
  date_default_timezone_set('America/Toronto');
    
// if Madeam is in our local lib, include it. Otherwise use the one in the PHP include path
  $lib = getcwd() . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR;
  require file_exists($lib . 'Madeam') ? $lib . 'Madeam' . DIRECTORY_SEPARATOR . 'Framework.php' : 'Madeam' . DIRECTORY_SEPARATOR . 'Framework.php';
  
// set include paths  
  set_include_path(implode(PATH_SEPARATOR, Madeam::paths(getcwd() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR)) . PATH_SEPARATOR . get_include_path());
  
// configure madeam
  Madeam::basicSetup();