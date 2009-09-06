<?php
class SessionsMiddleware extends madeam\Middleware {
  
  static public function beforeRequest($request) {
    madeam\Session::start();
    
    return $request;
  }
  
  static public function beforeResponse($response) {
    return $response;
  }
  
}