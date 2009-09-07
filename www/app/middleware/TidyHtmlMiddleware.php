<?php
class TidyHtmlMiddleware extends madeam\Middleware {
  
  static public function beforeResponse($request, $response) {
    
    if ($request['_method'] == 'html') {
      $tidy = new Tidy;
      $tidy->parseString($response, array(
        'wrap'  => 200,
        'indet' => true
      ), 'utf8');
      $response = $tidy->cleanRepair();
    }
    
    return $response;
  }
  
}