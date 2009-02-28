<?php
// use unicode
  if (function_exists('mb_internal_encoding')) { mb_internal_encoding("UTF-8"); }

// set content type to utf8
  header('Content-type: text/html; charset=utf-8');

// set default timezone
  date_default_timezone_set('America/Toronto');
    
// if Madeam is in our local lib, include it. Otherwise use the one in the PHP include path
  $lib = getcwd() . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR;
  require file_exists($lib . DIRECTORY_SEPARATOR . 'Madeam') ? $lib . DIRECTORY_SEPARATOR . 'Madeam' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Madeam.php' : 'Madeam' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Madeam.php';
  
// set include paths  
  set_include_path(implode(PATH_SEPARATOR, Madeam::paths(getcwd() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR)) . PATH_SEPARATOR . get_include_path());
  
// configure madeam
  Madeam::basicSetup();