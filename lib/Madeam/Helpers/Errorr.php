<?php

/**
 * Madeam PHP Framework <http://www.madeam.com/>
 * Copyright (c)	2009, Joshua Davey
 *								202-212 Adeliade St. W, Toronto, Ontario, Canada
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright		Copyright (c) 2009, Joshua Davey
 * @link				http://www.madeam.com
 * @package			madeam
 * @version			0.0.4
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 * @author      Joshua Davey
 */
class Errorr extends Htmlr {

  public static function all($errors = array(), $_params = array()) {
    $errors_box = null;
    if (!empty($errors)) {
      $params['name'] = $name;
      $params['id'] = self::nameToId($name);
      $params['class'] = 'errors';
      // create list
      $errors_box = self::openTag('div', $params);
      $errors_box .= self::wrappingTag('h1', 'Errors');
      $errors_box .= self::openTag('ul');
      foreach ($errors as $field => $errors) {
        $errors_box .= self::wrappingTag('li', $field);
        $errors_box .= self::openTag('ul');
        foreach ($errors as $error) {
          $errors_box .= self::wrappingTag('li', $error);
        }
        $errors_box .= self::closedTag('ul');
      }
      $errors_box .= self::closedTag('ul');
      $errors_box .= self::closedTag('div');
      return $errors_box;
    } else {
      return $errors_box;
    }
  }

  public static function single($name, $errors = array(), $message = false) {
    $error_tag = null;
    if (isset($errors[$name]) && !empty($errors[$name])) {
      $error_tag .= self::openTag('ul', array('class' => 'error'));
      foreach ($errors[$name] as $error) {
        if ($message) {
          $error_tag .= self::wrappingTag('li', $message);
        } else {
          $error_tag .= self::wrappingTag('li', $error);
        }
      }
      $error_tag .= self::closedTag('ul');
    }
    return $error_tag;
  }
}