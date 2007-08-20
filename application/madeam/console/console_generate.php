<?php
class console_generate extends madeam_console {

	public $description = 'The generate console allows you to generate models, views and controllers';
	
	public $root_app_path	= array('controller', 'model', 'view');

	public $require = array(
		'controller' => array(
			'name' => 'Please choose a name for this controller'				
		)
	);
	
	public $validate = array(
		'controller' => array(
			'name' 			=> '/[a-z\/]/',
			'model' 		=> '/[a-z\/]/',
			'scaffold'	=> '/[a-z]/'
		)
	);
	
	function controller($params) {
		out('created a controller called: ' . $params['name']);
		
	}
	
}
?>