<?php

/**
 * Madeam PHP Framework <http://www.madeam.com/>
 * Copyright (c)	2009, Joshua Davey
 *								202-212 Adeliade St. W, Toronto, Ontario, Canada
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright		Copyright (c) 2009, Joshua Davey
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
  	"the Quality Assurance team is unimpressed.",
  	"maybe you should consider a new profession?",
  	"oopsy",
  	"overated PHP software?",
  	'is this a candidate for <a href="http://thedailywtf.com">The Daily WTF</a>?',
  	'click "OK" to continue.',
  	"this looks ready to launch. /sarcasm"
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
    if (Madeam_Config::get('inline_errors') === true && in_array($code, array(2, 4, 8, 32, 128, 256, 1024, 2048, 4096, 8192, 16384))) {
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
      $params = array(
        '_controller' => Madeam_Framework::errorController, 
        '_action'     => 'debug',
        '_method'     => 'get',
        '_layout'     => 1,
        '_format'     => 'html',
        'error'       => nl2br($message),
        'backtrace'   => $e->getTraceAsString(),
        'snippet'     => $snippet,
        'line'        => $line,
        'code'        => $code,
        'file'        => $file
      );
      
      echo Madeam_Framework::control($params);
      exit();
    } else {
      // return 404 error page
      $params = array(
        '_controller' => Madeam_Framework::errorController, 
        '_action'     => 'http404',
        '_method'     => 'get',
        '_layout'     => 1,
        '_format'     => 'html'
      );
      
      echo Madeam_Framework::control($params);
      exit();
    }
  }
}
