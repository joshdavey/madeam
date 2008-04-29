<?php
/*
// directory splitter
  define('DS', DIRECTORY_SEPARATOR);

// define script file name
	define('SCRIPT_FILENAME', basename(__FILE__));

// define foreign path -- necessary for when including the script from outside the public directory
	define('PATH_TO_SCRIPT', dirname(__FILE__) . DS);

// set public path
	define('PATH_TO_PUBLIC', PATH_TO_SCRIPT . 'public' . DS);

// include dispatcher in web root
	require PATH_TO_PUBLIC . 'index.php';
*/
	require 'public/index.php';
?>