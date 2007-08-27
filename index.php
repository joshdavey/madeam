<?php
// define script file name
	define('SCRIPT_FILENAME', basename(__FILE__));

// define foreign path -- necessary for when including the script from outside the public directory
	define('FOREIGN_PATH', dirname(__FILE__));

// include dispatcher in web root
	require_once 'public/index.php';
?>