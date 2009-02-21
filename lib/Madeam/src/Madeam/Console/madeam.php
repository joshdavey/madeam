<?php

error_reporting(E_ALL);

// really weird bug when I installed zend core made SERVER_NAME inaccessible and threw a notice error
// this is a simple hack to fix that
  if (! isset($_SERVER['SERVER_NAME'])) { $_SERVER['SERVER_NAME'] = 'localhost'; }

// use unicode
  if (function_exists('mb_internal_encoding')) { mb_internal_encoding("UTF-8"); }

// set content type to utf8
  header('Content-type: text/html; charset=utf-8');

// set default timezone
  date_default_timezone_set('America/Toronto');
  
  echo getcwd();
  
// if Madeam is in our local lib, include it. Otherwise use the one in the PHP include path
  $lib = getcwd() . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR;
  require file_exists($lib . 'Madeam') ? $lib . 'Madeam' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Madeam.php' : dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'Madeam.php';
  
  $public = realpath('public');
  
  echo $public;
  
  if (!file_exists($public)) {
    $public = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . DIRECTORY_SEPARATOR . 'public';
  }
  
// set include paths  
  set_include_path(implode(PATH_SEPARATOR, Madeam::paths($public . DIRECTORY_SEPARATOR)) . PATH_SEPARATOR . get_include_path());

if (!file_exists($lib . 'Madeam/src/Madeam/Console/' . basename(__FILE__))) {
  $_SERVER['argv'][1] = 'make/app';
} else {
  // setup madeam
  Madeam::basicSetup();
}

// initiated console
$console = new Madeam_Console($_SERVER['argv']);