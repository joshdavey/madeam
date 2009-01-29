<?php
class Breadcrumbr extends Htmlr {

  static $breadcrumbs = array();

  public static function create($crumbs = array(), $params = array()) {
    $breadcrumb = null;
    $breadcrumbs = array_merge($crumbs, self::$breadcrumbs);
    
    $crumbCount = count($breadcrumbs);
    
    $breadcrumb .= self::openTag('ol', array_merge(array('class' => 'breadcrumb'), $params));
    
    $x = 0;
    foreach ($breadcrumbs as $crumb => $url) {
      $x++;
      if ($url != null) {
        $link = self::link($crumb, $url);
      } else {
        $link = $crumb;
      }
      if ($x == $crumbCount) {
        $breadcrumb .= self::wrappingTag('li', $link, array('class' => 'current'));
      } else {
        $breadcrumb .= self::wrappingTag('li', $link);
      }
    }
    $breadcrumb .= self::closedTag('ol');
    return $breadcrumb;
  }

}