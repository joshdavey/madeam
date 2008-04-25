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
class Help_Html {

  public static function link($label, $link = null, $_params = array()) {
    $params = array();
    $params['href'] = Madeam::url($link);
    $params['id'] = Madeam_Inflector::underscorize(low(strip_tags($label))) . '_link';

    $params = array_merge($params, $_params);

    return self::wrappingTag('a', $label, $params);
  }

  public static function img($src, $_params = array()) {
    $params = array();
    $params['alt'] = Madeam_Inflector::underscorize($src);
    $params['src'] = Madeam::url($src);


    $params = array_merge($params, $_params);

    return self::tag('img', $params);
  }

  public static function imgResize($src, $toWidth = false, $toHeight = false, $_params = array()) {
    $params = array();
    $params['alt'] = Madeam_Inflector::underscorize($src);
    $params['src'] = Madeam::url($src);

		if ($toWidth !== false || $toHeight !== false) {
			list($width, $height) = getimagesize(PATH_TO_PUBLIC . $src);

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
    return '<link rel="stylesheet" href="' . Madeam::url($src) . '.css" type="text/css" media="screen" />';
  }

  public static function js($src, $_params = array()) {
    return '<script src="' . Madeam::url($src) . '.js" type="text/javascript"></script>';
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
  protected static function paramsToHtml($params = array()) {
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