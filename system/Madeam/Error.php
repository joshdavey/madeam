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

  /**
   * Error Codes
   *
   * 1      | E_ERROR n
   * 2      | E_WARNING y
   * 4      | E_PARSE y
   * 8      | E_NOTICE y
   * 16     | E_CORE_ERROR n
   * 32     | E_CORE_WARNNING y
   * 64     | E_COMPILE_ERROR n
   * 128    | E_COMPILE_WARNING y
   * 256    | E_USER_ERROR n
   * 512    | E_USER_WARNING y
   * 1024   | E_USER_NOTICE y
   * 6143   | E_ALL n
   * 2048   | E_STRICT y
   * 4096   | E_RECOVERABLE_ERROR y
   */

  public static function catchException(Exception $exception) {
    // check if inline errors are enabled and the error is not fatal
    if (MADEAM_INLINE_ERRORS === true && !in_array($exception->getCode(), array(1, 16, 64, 256, 6143))) {
      echo nl2br($exception->getMessage());
      echo '<br />' . $exception->getFile() . ' on line ' . $exception->getLine();
      return;
    }

    // clean output buffer
    if (ob_get_level() > 0) { ob_clean(); }

    if (MADEAM_ENABLE_DEBUG == true) {
      // get random snippet
      $snippet = self::$funSnippets[rand(0, count(self::$funSnippets) - 1)];
      // call error controller and pass information
      echo Madeam::makeRequest(Madeam_Config::get('error_controller') . '/debug?error=' . urlencode(nl2br($exception->getMessage())) . '&backtrace=' . urlencode($exception->getTraceAsString()) . '&snippet=' . urlencode($snippet) . '&line=' . urlencode($exception->getLine()) . '&code=' . urlencode($exception->getCode()) . '&file=' . urlencode($exception->getFile()) . '&documentation=' . 'comingsoong&useLayout=1');
      exit();
    } else {
      // return 404 error page
      echo Madeam::makeRequest(Madeam_Config::get('error_controller') . '/http404');
      exit();
    }
  }
}
