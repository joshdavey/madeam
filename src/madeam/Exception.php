<?php
namespace madeam;
/**
 * Madeam PHP Framework <http://madeam.com>
 * Copyright (c)  2009, Joshua Davey
 *                202-212 Adeliade St. W, Toronto, Ontario, Canada
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright    Copyright (c) 2009, Joshua Davey
 * @link        http://www.madeam.com
 * @package      madeam
 * @license      http://www.opensource.org/licenses/mit-license.php The MIT License
 */
class Exception extends \Exception {

  static public $inlineErrors = false;
  static public $debugMode    = false;

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
    "overated PHP software?",
    'is this a candidate for <a href="http://thedailywtf.com">The Daily WTF</a>?',
    'click "OK" to continue.',
    "this looks ready to launch. &lt;/sarcasm&gt;",
    "choo choo! Here comes the fail train.",
    "&lt;you&gt;fail&lt;/you&gt;",
  );

  public static function handle($e, $override = array()) {

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
    // if (self::$inlineErrors === true && in_array($code, array(2, 4, 8, 32, 128, 256, 1024, 2048, 4096, 8192, 16384))) {
    //   echo nl2br($message);
    //   echo '<br />' . $file . ' on line ' . $line;
    //   return;
    // }


    // clean output buffer
    if (ob_get_level() > 0) { ob_clean(); }

    if (self::$debugMode === true) {
      // get random snippet
      $snippet = self::$funSnippets[rand(0, count(self::$funSnippets) - 1)];

      // call error controller and pass information
      $message = array(
        'exception'   => get_class($e),
        'error'       => nl2br($message),
        'backtrace'   => $e->getTraceAsString(),
        'snippet'     => $snippet,
        'line'        => $line,
        'code'        => $code,
        'file'        => $file
      );

      if (!isset($_SERVER['SHELL'])) {
        require 'madeam/src/madeam/exception/template.html.php';
      } else {
        require 'madeam/src/madeam/exception/template.shell.php';
      }
      exit();
    } else {
      // return 404 error page
      $params = array(
        '_controller' => 'error',
        '_action'     => 'http404',
        '_method'     => 'get',
        '_layout'     => 1,
        '_format'     => 'html'
      );

      echo Framework::control($params);
    }
  }
}