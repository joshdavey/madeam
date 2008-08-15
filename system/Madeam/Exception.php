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
class Madeam_Exception extends Exception {

  private static $funSnippets = array(
  	"don't worry, be happy. It could be worse.",
  	"did someone make a boo boo?",
  	"you should really fix this.",
  	"this is neither a horse, or a stable.",
  	"what have you done!?",
  	"oh @%&#!",
  	"the tech bubble burst! Run, save yourself!",
  	"is this your idea of web 3.0?",
  	"the Quality Assurance team is unimpressed."
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

  public function __construct($message = null, $code = 1) {
    parent::__construct($message, $code);
  }

  public static function catchException(Exception $e, $override = array()) {

    if (isset($override['message'])) {
      $message = $override['message'];
    } else {
      $message = $e->getMessage();
    }

    if (isset($override['code'])) {
      $code = $override['code'];
    } else {
      $code = $e->getCode();
    }

    if (isset($override['line'])) {
      $line = $override['line'];
    } else {
      $line = $e->getLine();
    }

    if (isset($override['file'])) {
      $file = $override['file'];
    } else {
      $file = $e->getFile();
    }

    // check if inline errors are enabled and the error is not fatal
    if (Madeam_Config::get('inline_errors') === true && !in_array($code, array(1, 16, 64, 256, 6143))) {
      echo nl2br($message);
      echo '<br />' . $file . ' on line ' . $line;
      return;
    }

    // clean output buffer
    if (ob_get_level() > 0) { ob_clean(); }

    if (Madeam_Config::get('enable_debug') == true) {
      // get random snippet
      $snippet = self::$funSnippets[rand(0, count(self::$funSnippets) - 1)];
      // call error controller and pass information
      echo Madeam::request(Madeam_Config::get('error_controller') . '/debug?error=' . urlencode(nl2br($message)) . '&backtrace=' . urlencode($e->getTraceAsString()) . '&snippet=' . urlencode($snippet) . '&line=' . urlencode($line) . '&code=' . urlencode($code) . '&file=' . urlencode($file) . '&documentation=' . 'comingsoong&layout=1');
      exit();
    } else {
      // return 404 error page
      echo Madeam::request(Madeam_Config::get('error_controller') . '/http404?layout=1');
      exit();
    }
  }
}
