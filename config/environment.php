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
	 * mod_rewrite
	 *	true: 	mod_rewrite is enabled  -- URLS look like: http://example.com/index
	 *	false: 	mod_rewrite is disabled -- URLs look like: http://example.com/index.php/index
	 *
	 * madeam_dir
	 *	Path to madeam directory relative to applications directory
	 */

// development configuration
// ===================================================

	$cfg['development']['db_servers'][]   = 'mysql://root:password@localhost?name=application';
	$cfg['development']['madeam_dir'] 		= 'madeam';
	$cfg['development']['mod_rewrite'] 		= true;
	$cfg['development']['debug_mode']			= true;
	$cfg['development']['disable_cache'] 	= false;




// production configuration
// ===================================================

	$cfg['development']['db_servers'][]   = 'mysql://root:password@localhost?name=application';
	$cfg['production']['madeam_dir'] 			= 'madeam';
	$cfg['production']['mod_rewrite'] 		= true;
	$cfg['production']['debug_mode']			= false;
	$cfg['production']['disable_cache'] 	= false;




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




// format configuration
// ===================================================

	// Map view file formats to parsers
	$cfg['format_parsers'] = array();




// loader configuration
// ===================================================

	// this array of loaders allows you to dynamically include objects when they're instantiated
	// exmple: array('loader_name' => 'regular_expression')
	$cfg['loaders'] = array(
		//'example'	=> '/^Example_/'
	);




	// loaders
	function loader_example($class, $matchs) {
		return VENDOR_LIB_PATH . 'Example' . DS . str_replace('Example_', null, ucfirst($class)) . '.php';
	}

?>