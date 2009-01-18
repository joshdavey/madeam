<?php
class Breadcrumbr extends Htmlr {

  static $breadcrumbs = array();

  public static function create($crumbs = array()) {
    $breadcrumb = null;
    $breadcrumbs = array_merge($crumbs, self::$breadcrumbs);
    
    $crumbCount = count($breadcrumbs);
    
    $breadcrumb .= self::openTag('ul', array('class' => 'breadcrumb'));
    
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
    $breadcrumb .= self::closedTag('ul');
    return $breadcrumb;
  }

}