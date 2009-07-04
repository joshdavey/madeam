<?php

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
class Madeam_Helper_Error extends Madeam_Helper_Html {

  public static function all($errors = array(), $_params = array()) {
    $errorsBox = null;
    if (!empty($errors)) {
      $params['name'] = $name;
      $params['id'] = self::nameToId($name);
      $params['class'] = 'errors';
      // create list
      $errorsBox = self::openTag('div', $params);
      $errorsBox .= self::wrappingTag('h1', 'Errors');
      $errorsBox .= self::openTag('ul');
      foreach ($errors as $field => $errors) {
        $errorsBox .= self::wrappingTag('li', $field);
        $errorsBox .= self::openTag('ul');
        foreach ($errors as $error) {
          $errorsBox .= self::wrappingTag('li', $error);
        }
        $errorsBox .= self::closedTag('ul');
      }
      $errorsBox .= self::closedTag('ul');
      $errorsBox .= self::closedTag('div');
      return $errorsBox;
    } else {
      return $errorsBox;
    }
  }

  public static function single($name, $errors = array(), $message = false) {
    $errorTag = null;
    if (isset($errors[$name]) && !empty($errors[$name])) {
      $errorTag .= self::openTag('ul', array('class' => 'error'));
      foreach ($errors[$name] as $error) {
        if ($message) {
          $errorTag .= self::wrappingTag('li', $message);
        } else {
          $errorTag .= self::wrappingTag('li', $error);
        }
      }
      $errorTag .= self::closedTag('ul');
    }
    return $errorTag;
  }
}