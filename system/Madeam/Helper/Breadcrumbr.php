<?php
class Breadcrumbr extends Htmlr {

  static $breadcrumbs = array();

  public static function create($crumbs = array()) {
    $breadcrumb = null;
    $breadcrumbs = array_merge($crumbs, self::$breadcrumbs);
    $breadcrumb .= self::openTag('ul', array('class' => 'breadcrumb'));
    foreach ($breadcrumbs as $crumb => $url) {
      if ($url != null) {
        $link = self::link($crumb, $url);
      } else {
        $link = $crumb;
      }
      $breadcrumb .= self::wrappingTag('li', $link);
    }
    $breadcrumb .= self::closedTag('ul');
    return $breadcrumb;
  }

}