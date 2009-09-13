<?php
namespace madeam\middleware;
class Tidy extends \madeam\Middleware {
  
  static public function beforeResponse($request, $response) {
    
    if ($request['_format'] == 'html') {
      $tidy = new \Tidy;
      $tidy->parseString($response, array(
        'wrap'  => 200,
        'indent' => true
      ), 'utf8');
      $tidy->cleanRepair();
      $html = $tidy->html();
      $response = $html->value;
    }
    
    return $response;
  }
  
}