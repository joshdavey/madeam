<?php
// define script file name
	define('SCRIPT_FILENAME', basename(__FILE__));

// define foreign path -- necessary for when including the script from outside the public directory
	define('FOREIGN_PATH', dirname(__FILE__));

// set public path
	define('PUB_PATH', FOREIGN_PATH . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);

// include dispatcher in web root
	require_once 'public/index.php';
?>