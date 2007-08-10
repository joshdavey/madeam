<?php
// The dafault controller called by the framework
	define('DEFAULT_CONTROLLER', 'index');

// The dafault controller action called by the framework
	define('DEFAULT_ACTION', 'index');

// The default file extension of Views and Layouts
// You don't need to add the ".". Just the name of the file extension
	define('DEFAULT_FORMAT', 'html');
	
// When this is set to "false" and you are making an AJAX call no layout will be displayed
	define('AJAX_LAYOUT', false);

// Edit this to change the naming format of the log files
	define('LOG_FORMAT', 'Y-m');

// Change this to configure the framework to the environment you're in
	define('ENVIRONMENT', 'development');

// development configuration
// ===================================================
	// database user name
	$cfg['development']['db_user'] 				= 'root';
	
	// database password
	$cfg['development']['db_pass'] 				= '';
	
	// database name
	$cfg['development']['db_name'] 				= APP_NAME;
	
	// database host name
	$cfg['development']['db_host'] 				= 'localhost';
	
	// set to true if you are using apache's rewrite module. false otherwise.
	$cfg['development']['mod_rewrite'] 		= true;
	
	// relative path to madeam's core libraries relative to the application directory
	$cfg['development']['madeam_dir'] 		= 'madeam';

// production configuration
// ===================================================
	$cfg['production']['db_user'] 				= 'root';
	$cfg['production']['db_pass'] 				= '';
	$cfg['production']['db_name'] 				= APP_NAME;
	$cfg['production']['db_host'] 				= 'localhost';
	$cfg['production']['mod_rewrite'] 		= true;
	$cfg['production']['madeam_dir'] 			= 'madeam';

?>