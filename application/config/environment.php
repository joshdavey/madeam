<?php
// Change this to configure the framework to the environment you're in
	define('ENVIRONMENT', 'development');


	/**
	 * Environment configuration variables:
	 * ------------------------------------------------ 
	 *
	 * db_user
	 *	Database username
	 *
	 * db_pass
	 *	Database password
	 *
	 * db_name
	 *	Database name
	 *
	 * db_host
	 *	Database host -- normally "localhost"
	 *
	 * db_connect
	 *	Database Connection String -- mysql://[username]:[password]@[host]/[database]
	 *
	 * mod_rewrite
	 *	true: 	mod_rewrite is enabled  -- URLS look like: http://example.com/index
	 *	false: 	mod_rewrite is disabled -- URLs look like: http://example.com/index.php/index
	 *
	 * madeam_dir
	 *	Path to madeam directory relative to applications directory
	 */
	 
// development configuration
// ===================================================

	$cfg['development']['db_host'] 			= 'localhost';
	$cfg['development']['db_user'] 			= 'root';
	$cfg['development']['db_pass'] 			= '';
	$cfg['development']['db_name'] 			= 'application';	
	$cfg['development']['db_connect']		= 'mysql://username:password@localhost/application';
	$cfg['development']['madeam_dir'] 	= 'madeam';
	$cfg['development']['mod_rewrite'] 	= true;	
	$cfg['development']['show_errors']	= true;
	$cfg['development']['system_check']	= true;




// production configuration
// ===================================================

	$cfg['production']['db_host'] 			= 'localhost';
	$cfg['production']['db_user'] 			= 'root';
	$cfg['production']['db_pass'] 			= '';
	$cfg['production']['db_name'] 			= 'application';
	$cfg['production']['db_connect']		= 'mysql://username:password@localhost/application';
	$cfg['production']['madeam_dir'] 		= 'madeam';
	$cfg['production']['mod_rewrite'] 	= true;
	$cfg['production']['show_errors']		= false;
	$cfg['production']['system_check']	= true;
	



// defaults configuration
// ===================================================

	// The dafault controller called by the framework
	define('DEFAULT_CONTROLLER', 'index');

	// The dafault controller action called by the framework
	define('DEFAULT_ACTION', 'index');

	// The default file extension of Views and Layouts
	// You don't need to add the ".". Just the name of the file extension
	define('DEFAULT_FORMAT', 'html');




// logs configuration
// ===================================================	

	// Edit this to change the naming format of the log files
	define('LOG_FORMAT', 'Y-m');




// ajax configuration
// ===================================================	

	// When this is set to "false" and you are making an AJAX call no layout will be displayed
	define('AJAX_LAYOUT', false);	




// loader configuration
// ===================================================	
	
	// this array of loaders allows you to dynamically include objects when they're instantiated
	// exmple: array('loader_name' => 'regular_expression')
	$cfg['loaders'] = array(
		//'example'	=> '/^Example_/'
	);
	
	
	// handlers
	function loader_example($class, $matchs) {
		return VENDOR_LIB_PATH . 'Example' . DS . str_replace('Example_', null, ucfirst($class)) . '.php';
	}

?>