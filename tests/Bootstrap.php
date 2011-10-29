<?php
// set error reporting level
  error_reporting(E_ALL);

// define test constants
  define('TESTS_MADEAM_PROJECT_DIRECTORY', __DIR__ . '/madeam/_www/');

// include madeam (required for autoloader)
  require_once dirname(__DIR__) . '/src/Madeam.php';

// set include path
  set_include_path(
    TESTS_MADEAM_PROJECT_DIRECTORY . 'application/models/' . PATH_SEPARATOR .
    TESTS_MADEAM_PROJECT_DIRECTORY . 'application/controllers/' . PATH_SEPARATOR .
    TESTS_MADEAM_PROJECT_DIRECTORY . 'application/middleware/' . PATH_SEPARATOR .
    TESTS_MADEAM_PROJECT_DIRECTORY . 'application/vendor/' . PATH_SEPARATOR .
    TESTS_MADEAM_PROJECT_DIRECTORY . 'application/' . PATH_SEPARATOR .
    get_include_path() . PATH_SEPARATOR .
    __DIR__ . '/'
  );

// reset error and exception handlers
  restore_error_handler();
  restore_exception_handler();

