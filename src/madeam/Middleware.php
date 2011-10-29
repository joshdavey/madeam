<?php
namespace madeam;
class Middleware {

  static public function beforeRequest($request) { return $request; }

  static public function beforeResponse($request, $response) { return $response; }

}