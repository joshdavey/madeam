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
 * @version			0.1.0
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 * @author      Joshua Davey
 */

// start benchmark
  $benchmarkStart = microtime(true);

// set the public directory as our current working directory
  chdir(dirname(__FILE__));

// include boostrap and include all of the madeam core files and configurations
  require '..' . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR . 'bootstrap.php';
  
// dispatch calls the framework and returns the resulting output
  $output = Madeam::dispatch();

// end benchmark
  $benchmarkEnd = microtime(true);
  $time = $benchmarkEnd - $benchmarkStart;

// interesting stuff
  //echo 'Time: ' . $time . '<br />'; foreach (get_included_files() as $file) { isset($x) ? ++$x : $x = 1; echo $x . ' ' . $file . '<br />'; }
?>
<?php if (Madeam_Config::get('enable_toolbar') === true) : ?>
<style>
#madeam-debug-toolbar { color: #000; position: fixed; top: 0; height: 20px; z-index: 10000; overflow: auto; left: 0; right: 0; background: #fef38b; padding: 2px; }
#madeam-debug-output { position: fixed; top: 20px; bottom: 0; overflow: auto; left: 0; right: 0; }
</style>

<div id="madeam-debug-toolbar">
  <p><strong>Time:</strong> <?php echo '' . round($time, 4); ?> | <strong>Includes:</strong> <?php echo count(get_included_files()); ?> | <strong>Queries:</strong> 0</p>
</div>
<div id="madeam-debug-output">
  <?php echo $output; ?>
</div>
<?php else: ?>
  <?php echo $output; ?>
<?php endif; ?>