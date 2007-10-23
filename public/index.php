<?php
/**
 * Madeam :  Rapid Development MVC Framework <http://www.madeam.com/>
 * Copyright (c)	2006, Joshua Davey
 *								24 Ridley Gardens, Toronto, Ontario, Canada
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright		Copyright (c) 2006, Joshua Davey
 * @link				http://www.madeam.com
 * @package			madeam
 * @version			0.0.4
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 * @author      Joshua Davey
 */

// start benchmark
$time_start = microtime(true);

// The script file name is the name of the script that includes the bootstrap and runs the framework
if (!defined('SCRIPT_FILENAME')) { define('SCRIPT_FILENAME', basename(__FILE__)); }

// set forein path
if (!defined('FOREIGN_PATH')) { define('FOREIGN_PATH', dirname(__FILE__)); }

// include boostrap and include all of the madeam core files and configurations
require_once 'bootstrap.php';

// dispatch calls the framework and returns the resulting output
echo madeam::dispatch();


// end benchmark
$time_end = microtime(true);
$time = $time_end - $time_start;

echo $time . '<br />';

// log execution time
//madeam_logger::log("Execution time: $time seconds" . $_SERVER['REQUEST_URI']);

/*

echo 'display_errors = ' . ini_get('display_errors') . "<br />";
echo 'register_globals = ' . ini_get('register_globals') . "<br />";
echo 'post_max_size = ' . ini_get('post_max_size') . "<br />";
echo 'post_max_size+1 = ' . (ini_get('post_max_size')+1) . "<br />";
echo 'short_open_tag = ' . ini_get('short_open_tag') . "<br />";
*/
$x=0;
foreach(get_included_files() as $file) {
  echo ++$x . ' ' . $file . "<br />";
}


/*
// developer mode
echo '<div id="madeam_dev_bar">The Developer Bar - Loadtime: ' . $time . ' seconds </div>';
<style>
#madeam_dev_bar { position: absolute; left: 0; bottom: 0; right: 0; padding: 4px; height: 100px; border: solid 1px #000; background: darkblue; color: #fff; }
</style>
*/

//ini_set('include_path',
// session.save_path
// split sessions into multiple directories
/*
$a['b']['c'] = array();
// slow 2 extra hash lookups per access
for($i = 0; $i < 5; $i++)
$a['b']['c'][$i] = $i;

// much faster reference based approach
$ref =& $a['b']['c'];
for($i = 0; $i < 5; $i++)
$ref[$i] = $i;
*/
//register_shutdown_function();
?>

