<?php
namespace madeam\helper;
/**
 * Madeam PHP Framework <http://madeam.com>
 * Copyright (c)  2009, Joshua Davey
 *                202-212 Adeliade St. W, Toronto, Ontario, Canada
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright    Copyright (c) 2009, Joshua Davey
 * @link        http://www.madeam.com
 * @package      madeam
 * @version      0.0.4
 * @license      http://www.opensource.org/licenses/mit-license.php The MIT License
 * @author      Joshua Davey
 */
class Html {

  public static function link($label, $link = null, $_params = array()) {
    $params = array();
    $params['href'] = \madeam\Framework::url($link);
    $params = array_merge($params, $_params);
    return self::wrappingTag('a', $label, $params);
  }

  public static function img($src, $_params = array()) {
    $params = array();
    $params['alt'] = \madeam\Inflector::underscorize($src);
    $params['src'] = \madeam\Framework::url($src);
    $params = array_merge($params, $_params);
    return self::tag('img', $params);
  }

  public static function css($src, $_params = array()) {
    return '<link rel="stylesheet" href="' . \madeam\Framework::url($src) . '.css" type="text/css" media="screen" />';
  }

  public static function js($src, $_params = array()) {
    return '<script src="' . \madeam\Framework::url($src) . '.js" type="text/javascript"></script>';
  }

  public static function gjs($library, $version) {
    static $timesCalled = 0;

    $html = null;

    if ($timesCalled == 0) {
      $html = '<script src="http://www.google.com/jsapi"></script>';
    }

    $html .= '<script>google.load("' . $library . '", "' . $version . '");</script>';

    $timesCalled++;

    return $html;
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
    return str_replace(\Framework::associationJoint, '_', strtolower($name));
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