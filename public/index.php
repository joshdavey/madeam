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
  $benchmarkStart = microtime(true);

// set the public directory as our current working directory
  chdir(dirname(__FILE__));

// include boostrap and include all of the madeam core files and configurations
  require '..' . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR . 'bootstrap.php';

// dispatch calls the framework and returns the resulting output
  echo Madeam::dispatch();

// end benchmark
  $benchmarkEnd = microtime(true);
  $time = $benchmarkEnd - $benchmarkStart;

// interesting stuff
  //echo 'Execution Time: ' . $time . '<br />'; foreach (get_included_files() as $file) { isset($x) ? ++$x : $x = 1; echo $x . ' ' . $file . '<br />'; }


