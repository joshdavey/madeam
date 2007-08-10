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
class htmlHelp {

  public static function link($label, $link = null, $_params = array()) {
    $params = array();
    $params['href'] = self::url($link);
    
    $params = array_merge($params, $_params);
    
    return self::wrappingTag('a', $label, $params);
  }

  public static function img($src, $toWidth = false, $toHeight = false, $_params = array()) {
    $params = array();
    $params['alt'] = inflector::underscorize($src);
    $params['src'] = self::url($src);
		
		if ($toWidth !== false || $toHeight !== false) {
			list($width, $height) = getimagesize(PUB_PATH . $src);
    
			$xscale = $width/$toWidth;
			$yscale = $height/$toHeight;
			
			if ($yscale>$xscale) {
				$new_width  = round($width * (1/$yscale));
				$new_height = round($height * (1/$yscale));
			} else {
				$new_width  = round($width * (1/$xscale));
				$new_height = round($height * (1/$xscale));
			}
			
			$params['width'] 	= $new_width;
			$params['height'] = $new_height;
		}
    
    $params = array_merge($params, $_params);
    
    return self::tag('img', $params);
  }


  public static function css($src, $_params = array()) {
    return '<link rel="stylesheet" href="' . self::url($src) . '.css" type="text/css" media="screen" />';
  }

  public static function js($src, $_params = array()) {
    return '<script src="' . self::url($src) . '.js" type="text/javascript"></script>';
  }
	
	
	/**
	 * This method returns URLs that can be either relative or absolute.
	 * If the url starts with "http://" or "https://" or any other protocol then the url is left as is.
	 * If the url starts with "/" then we assume that it's pointing to the public directory. Use "/" to point to static files
	 * If the url starts with none of the above we assume it's poitning to a resource like a controller
	 */
	public static function url($url) {
    if (substr($url, 0, 1) == '/') {
      $url = REL_PATH . substr($url, 1, strlen($url));
    } elseif (!preg_match('/[a-z]+:/', $url, $matchs)) {
			$url == "#" ? $url = "#" : $url = URI_PATH . $url;
		}
    return $url;
  }
  

  /**
   * Protected functions.
   * =======================================================================
   */


  protected static function tag($tag, $params = array()) {
    $params = self::paramsToHtml($params);
    return '<' . $tag . ' ' . $params . ' />';
  }

  protected static function wrappingTag($tag, $contents, $params = array()) {
    return self::openTag($tag, $params) . $contents . self::closedTag($tag);
  }

  protected static function openTag($tag, $params = array()) {
    $params = self::paramsToHtml($params);
    return '<' . $tag . ' ' . $params . '>';
  }

  protected static function closedTag($tag = array()) {
    return '</' . $tag . '>';
  }

  /**
   * Changes "foo.bar" to "foo_bar"
   *
   * @param string $name
   */
  protected static function nameToId($name) {
		return str_replace(MODEL_JOINT, '_', low($name));
  }

  /**
   *
   * [html] == [array]
   * <tag name="josh" />  ==  array('name' => 'josh')
   * <tag name="" />      ==  array('name' => '')
   * <tag selected />     ==  array('selected' => true)
   * <tag />              ==  array('selected' => false)
   *
   * @param array $params
   * @return string
   */
  protected static function paramsToHtml($params) {
    $html = array();
    foreach ($params as $param => $value) {
      if ($value === true) {
        $html[] = $param;
      } elseif ($value !== false) {
        $html[] = $param . '=' . '"' . $value . '"';
      }
    }
    return implode(' ', $html);
  }
	
}
?>