<?php
namespace madeam\session;

class PHP {

  static public function start($id) {
    // load session by ID
    if ($id !== false) {
      session_id($id);
    }

    // start session
    session_start();
  }

  static public function set($key, $value) {
    $_SESSION[$key] = $value;
  }

  static public function get($key) {
    return $_SESSION[$key];
  }

  static public function exists($key) {
    return isset($_SESSION[$key]);
  }

  static public function delete($key) {
    unset($_SESSION[$key]);
  }

  static public function destroy() {
    session_destroy();
  }

}