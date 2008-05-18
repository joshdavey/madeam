<?php

class Help_Breadcrumb extends Help_Html {

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

  public static function add($label, $url = null) {
    self::$breadcrumbs[$label] = $url;
    return true;
  }

  public static function reset() {
    self::$breadcrumbs = array();
    return true;
  }
}
?>