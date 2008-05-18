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
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
class Madeam_Error {

  const ERR_CRITICAL = 0;

  const ERR_NOT_FOUND = 50;

  private static $funSnippets = array(
  	"Don't worry, be happy. It could be worse.", 
  	"Oops. Did someone make a boo boo?", 
  	"You should really fix this.", 
  	"Just blame Josh Davey.", 
  	"This is the last time I trust open source software.", 
  	"Did you intend on launching a nuclear missile? Because it's too late to stop it now.", 
  	"This is neither a horse, or a stable.", 
  	"What have you done!?", "Oh @%&#", 
  	"The tech bubble burst! Run, save yourself!", 
  	"Is this your idea of web 3.0?"
  );

  public static function catchException(Exception $exception, $code = 100) {
    // get config
    $config = Madeam_Registry::get('config');
    
    // clean output buffer
    if (ob_get_level() > 0) { ob_clean(); }
    
    if (MADEAM_ENABLE_DEBUG == true) {
      // get random snippet
      $snippet = self::$funSnippets[rand(0, count(self::$funSnippets) - 1)];
      // call error controller and pass information
      echo Madeam::makeRequest($config['error_controller'] . '/debug?error=' . urlencode($exception->getMessage()) . '&backtrace=' . urlencode($exception->getTraceAsString()) . '&snippet=' . urlencode($snippet) . '&line=' . urlencode($exception->getLine()) . '&code=' . urlencode($code) . '&file=' . urlencode($exception->getFile()) . '&documentation=' . 'comingsoong');
      exit();
    } else {
      // return 404 error page
      echo Madeam::makeRequest($config['error_controller'] . '/http404');
      exit();
    }
  }
}
