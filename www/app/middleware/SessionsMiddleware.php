<?php
class SessionsMiddleware extends madeam\Middleware {
  
  static public function beforeRequest($request) {
    
    // check if _sessionid exists. If it doesn't set it.
    if (!isset($request['_sessionid'])) {
      $request['_sessionid'] = madeam\Session::key();
      setcookie('_sessionid', $request['_sessionid']);
    }
    
    // initiate session
    madeam\Session::start($request['_sessionid']);
    
    return $request;
  }
  
}