<?php 
/**
 * Madeam :  Rapid Development MVC Framework <http://www.madeam.com/>
 * Copyright (c)	2006, Joshua Davey
 *								24 Ridley Gardens, Toronto, Ontario, Canada
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright		Copyright (c) 2006, Joshua Davey
 * @link				http://www.madeam.com
 * @package			madeam
 * @version			0.0.4
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 * @author      Joshua Davey
 */
class openidComponent extends component {
  var $uri;
  var $server;
  
  var $trust_root = 'http://localhost/madeambook/public/';
  var $return_to  = 'http://localhost/madeambook/public/user/auth';
  
  function authenticate($uri) {
    if ($this->server = $this->findserver($this->uri = $uri)) {
      $this->talk();
    } else {
      // unable to find openid server
      return false;
    }
  }
  
  function findserver($uri) {
    $file = file_get_contents('http://' . $uri);
    if (preg_match('/\<link rel="openid.server" href="(.+)" \/\>/', $file, $pats)) {
      return $pats[1];
    } else {
      return false;
    }
  }
  
  function talk() {
    $params = array();
    // urlencode();
		$params['openid.return_to']   = 'http://madeam.com/user/auth';
		$params['openid.return_to']   = $this->return_to;
		$params['openid.mode']        = 'checkid_setup';
		$params['openid.identity']    = 'http://' . $this->uri;
		$params['openid.trust_root']  = $this->trust_root;
		$params['openid.sreg.required'] = 'nickname,email,fullname';
		$params['openid.sreg.optional'] = 'dob,gender,postcode,country,language,timezone';
		
		base64_encode($secret);
		
		$post = array();
		foreach ($params as $name => $value) {
		  $post[] = "$name=$value";
		}
		
		$post_string = implode('&', $post);
		
		// this actually works! Wow!
		header("Content-type: application/x-www-form-urlencoded");
		header("User-Agent: Madeam OpenID Ambassador");
		header("Location: " . $this->server . "?" . $post_string);		
  }
}
?>