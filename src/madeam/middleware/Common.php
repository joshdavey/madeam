<?php
namespace madeam\middleware;
class Common extends \madeam\Middleware {
  
  static public function beforeRequest($request) {
    // remove _uri from request
    // _uri is defined in the public/.htaccess file. Many developers may not notice it because of
    // it's transparency during development. We unset it here incase developers are using the query string
    // for any reason. An example of where it might be an unexpected problem is when taking the hash of the query
    // string to identify the page. This problem was first noticed in some OpenID libraries
    unset($_GET['_uri']);
    unset($_REQUEST['_uri']);

    // remove it from the query string as well
    if (isset($_SERVER['QUERY_STRING'])) {
      $_SERVER['QUERY_STRING'] = preg_replace('/&?_uri=[^&]*&?/', null, $_SERVER['QUERY_STRING']);
    }
    
    return $request;
  }
  
}