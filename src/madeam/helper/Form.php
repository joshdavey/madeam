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
class Breadcrumb extends madeam\helper\Html {
  
  public static function open($action = null, $_params = array()) {
    $params = array();
    
    if (isset($_params['file'])) {
      $params['enctype'] = 'multipart/form-data';
      unset($_params['file']);
    }
    
    if (!isset($_params['method'])) {
      $_params['method'] = 'post';
    }
    
    $params['action'] = Madeam::url($action);
    $params['id'] = 'form_' . Inflector::underscorize(madeam\Inflector::slug($params['action']));
    
    $params = array_merge($params, $_params);
    
    if ($_params['method'] == 'put' || $_params['method'] == 'delete') {
      $params['method'] = 'post';
      return self::openTag('form', $params) . "\n" . self::hidden('_method', $_params['method']);
    } else {
      return self::openTag('form', $params);
    }
  }

  public static function close() {
    return self::closedTag('form', array());
  }

  public static function hidden($name, $value = null, $_params = array()) {
    $params = array();
    $params['name'] = self::fieldName($name);
    $params['type'] = 'hidden';
    $params['value'] = self::fieldValue($name, $value);
    $params['id'] = self::nameToId($name);
    $params = array_merge($params, $_params);
    return self::tag('input', $params);
  }

  public static function checkbox($name, $checked_value = null, $source_value = null, $_params = array()) {
    static $nameid = array();
    if (isset($nameid[$name])) {
      $name .= '.' . $nameid[$name] ++;
    } else {
      $nameid[$name] = 0;
      $name .= '.' . $nameid[$name] ++;
    }
    $params = array();
    $params['name'] = self::fieldName($name);
    $params['type'] = 'checkbox';
    $params['id'] = self::nameToId($name);
    $source_value = self::fieldValue($name, $source_value);
    if ($checked_value == $source_value) {
      $params['checked'] = true;
    } else {
      $params['checked'] = false;
    }
    $params['value'] = self::fieldValue($name, $checked_value);
    $params = array_merge($params, $_params);
    return self::tag('input', $params);
  }

  public static function radio($name, $checked_value = null, $source_value = null, $_params = array()) {
    $params = array();
    $params['name'] = self::fieldName($name);
    $params['type'] = 'radio';
    $params['id'] = self::nameToId($name);
    $source_value = self::fieldValue($name, $source_value);
    if ($checked_value == $source_value) {
      $params['checked'] = true;
    } else {
      $params['checked'] = false;
    }
    $params['value'] = $checked_value;
    $params = array_merge($params, $_params);
    return self::tag('input', $params);
  }

  public static function input($name, $value = null, $_params = array()) {
    $params = array();
    $params['name'] = self::fieldName($name);
    $params['type'] = 'text';
    $params['id'] = self::nameToId($name);
    $params['value'] = self::fieldValue($name, $value);
    $params = array_merge($params, $_params);
    return self::tag('input', $params);
  }

  public static function label($name, $value = null, $_params = array()) {
    $params = array();
    $params['for'] = self::fieldName($name);
    $params['value'] = $value;
    $params = array_merge($params, $_params);
    return self::wrappingTag('label', $value, $params);
  }
  
  public static function text($name, $value = null, $params = array()) {
    return self::input($name, $value, $params);
  }

  public static function password($name, $value = null, $_params = array()) {
    $params = array();
    $params['name'] = self::fieldName($name);
    $params['type'] = 'password';
    $params['id'] = self::nameToId($name);
    $params['value'] = self::fieldValue($name, $value);
    $params = array_merge($params, $_params);
    return self::tag('input', $params);
  }

  public static function file($name, $_params = array()) {
    $params = array();
    $params['name'] = self::fieldName($name); // don't need to give it a form name
    $params['type'] = 'file';
    $params['id'] = self::nameToId($name);
    $params = array_merge($params, $_params);
    return self::tag('input', $params);
  }

  public static function textarea($name, $value = null, $_params = array()) {
    $params = array();
    $params['name'] = self::fieldName($name);
    $params['id'] = self::nameToId($name);
    $params['rows'] = 6;
    $params['cols'] = 30;
    $params = array_merge($params, $_params);
    return self::wrappingTag('textarea', self::fieldValue($name, $value), $params);
  }

  public static function button($value = null, $_params = array()) {
    $params = array();
    if (! isset($params['name'])) {
      $params['name'] = $value;
    }
    $params['value'] = $value;
    $params['id'] = self::nameToId($value . '_btn');
    $params['type'] = 'button';
    $params = array_merge($params, $_params);
    return self::tag('input', $params);
  }

  public static function submit($value = 'Submit', $_params = array()) {
    $params = array();
    if (! isset($params['name'])) {
      $params['name'] = $value;
    }
    $params['value'] = $value;
    $params['id'] = self::nameToId($value . '_btn');
    $params['type'] = 'submit';
    $params = array_merge($params, $_params);
    return self::tag('input', $params);
  }

  //re-named submit
  public static function send($value = 'Submit', $_params = array()) {
    return self::submit($value, $_params);
  }

  public static function reset($name = 'Reset', $_params = array()) {
    $params = array();
    $params['name'] = $name;
    $params['id'] = self::nameToId($name);
    $params['type'] = 'reset';
    $params = array_merge($params, $_params);
    return self::tag('input', $params);
  }

  public static function day($name, $value = null, $_params = array()) {
    $params = array();
    $params['class'] = 'day';
    
    $days = array();
    for ($i=1; $i <= 31; $i++) { 
      $days[$i] = $i;
    }
    
    $value = strtotime($value);
    
    if ($value == false) { 
      $value = date('d'); 
    } else {
      $value = date('d', $value);
    }
    
    $params = array_merge($params, $_params);
    return self::dropdown($name, $value, $days, $params);
  }
  
  public static function month($name, $value = null, $format = 'F', $_params = array()) {
    $params = array();
    $params['class'] = 'month';
    
    $months = array();
    for ($i=1; $i <= 12; $i++) { 
      $months[$i] = date($format, mktime(0, 0, 0, $i, 1, 1986));
    }
    
    $value = strtotime($value);
    
    if ($value == false) { 
      $value = date('m'); 
    } else {
      $value = date('m', $value);
    }
    
    $params = array_merge($params, $_params);
    return self::dropdown($name, $value, $months, $params);
  }
  
  public static function year($name, $value = null, $_params = array()) {
    $params = array();
    $params['size'] = '4';
    $params['maxlength'] = '4';
    $params['class'] = 'year';
    
    $value = strtotime($value);
    
    if ($value == false) { 
      $value = date('Y'); 
    } else {
      $value = date('Y', $value);
    }
    
    $params = array_merge($params, $_params);
    return self::input($name, $value, $params);
  }

  public static function date($name, $value = null, $_params = array()) {
    $returned = array();
    
    if ($value == null) { $value = time(); }
    
    // day
    $returned[] = self::day($name . Madeam::associationJoint . 'day', $value);
    
    // month
    $returned[] = self::month($name . Madeam::associationJoint . 'month', $value);
    
    // year
    $returned[] = self::year($name . Madeam::associationJoint . 'year', $value);
    
    return implode($returned);
  }
  

  public static function second($name, $value = null, $_params = array()) {
    $params = array();
    $params['class'] = 'month';
    
    $seconds = array();
    for ($i=0; $i < 60; $i++) {
      if (strlen($i) < 2) { $i = '0' . $i; }
      $seconds[$i] = $i;
    }
    
    $value = strtotime($value);
    
    if ($value == false) { 
      $value = date('s'); 
    } else {
      $value = date('s', $value);
    }
    
    $params = array_merge($params, $_params);
    return self::dropdown($name, $value, $seconds, $params);
  }
  
  public static function minute($name, $value = null, $_params = array()) {
    $params = array();
    $params['class'] = 'month';
    
    $minutes = array();
    for ($i=0; $i < 60; $i++) {
      if (strlen($i) < 2) { $i = '0' . $i; }
      $minutes[$i] = $i;
    }
    
    $value = strtotime($value);
    
    if ($value == false) { 
      $value = date('i'); 
    } else {
      $value = date('i', $value);
    }
    
    $params = array_merge($params, $_params);
    return self::dropdown($name, $value, $minutes, $params);
  }
  
  public static function hour($name, $value = null, $format = 'g a', $_params = array()) {
    $params = array();
    $params['class'] = 'month';    
    
    $hours = array();
    for ($i=0; $i < 24; ++$i) {
      $hours[$i] = date($format, mktime($i, 0, 0, 1, 1, 1986));
    }
    
    $value = strtotime($value);
    
    if ($value == false) { 
      $value = date('G'); 
    } else {
      $value = date('G', $value);
    }
    
    $params = array_merge($params, $_params);
    return self::dropdown($name, $value, $hours, $params);
  }
  
  public static function time($name, $value = null, $_params = array()) {
    $returned = array();
    
    if ($value == null) { $value = time(); }
    
    // hour
    $returned[] = self::hour($name . Madeam::associationJoint . 'hour', $value);
    
    // minute
    $returned[] = self::minute($name . Madeam::associationJoint . 'minute', $value);
    
    // second
    $returned[] = self::second($name . Madeam::associationJoint . 'second', $value);
    
    return implode($returned);
  }

  public static function datetime($name, $value = null, $_params = array()) {
    $returned = array();
    
    if ($value == null) { $value = time(); }
    
    // date
    $returned[] = self::date($name, $value);
    
    // time
    $returned[] = self::time($name, $value);
    
    return implode($returned);
  }
  

  public static function dropdown($name, $selected, $values = array(), $_params = array()) {
    $params         = array();
    $params['name'] = self::fieldName($name);
    $params['id']   = self::nameToId($name);
    $contents = array();
    $selected = self::fieldValue($name, $selected);
    
    if (! empty($values)) {
      foreach ($values as $key => $label) {
        $o_params = array();
        if ($selected == $key) { $o_params['selected'] = true; }
        $o_params['value'] = $key;
        $contents[] = self::wrappingTag('option', $label, $o_params);
      }
    }
    
    $params = array_merge($params, $_params);
    return self::wrappingTag('select', implode($contents), $params);
  }

  public static function magic($model, $action, $data = array(), $select_fields = array()) {
    // output definition
    $output = null;
    // create model instance
    $modelname = madeam\Inflector::model_classize($model);
    $inst = new $modelname(1);
    // get fields
    // don't use this! use $Model->describe(); instead!
    $model_setup = $inst->get_setup();
    $fields = $model_setup['schema'];
    // open Form
    $output = self::open_file($model, $action, 'post', array('enctype' => 'multipart/form-data')) . "\n";
    // create fields
    foreach ($fields as $field_name => $settings) {
      // filter out fields if the developer has chosen only specific fields
      if (! empty($select_fields) && ! in_array($field_name, $select_fields)) {
        continue;
      }
      // field type
      $field_type = $settings['type'];
      // field label
      if ($settings['label']) {
        $field_label = $settings['label'];
      } else {
        $field_label = madeam\Inflector::sentencize($field_name);
      }
      // field parameters
      $params = array();
      if ($settings['class']) {
        $params['class'] = $settings['class'];
      }
      if (in_array('auto_increment', $settings) || in_array('hidden', $settings)) {
        // hidden field
        $output .= self::hidden($field_name, $data[$field_name], $params) . "\n";
      } else {
        $output .= '<p>' . "\n";
        // add label
        $output .= '  <label for="' . $model . '_' . $field_name . '">' . $field_label . '</label>' . "\n  ";
        if (in_array('password', $settings)) {
          // password fields
          $output .= self::password($field_name, null, $params) . "\n";
        } elseif (in_array('file', $settings)) {
          // file fields
          $output .= self::file($field_name, $params) . "\n";
        } else {
          if ($field_type == FIELD_VARCHAR || $field_type == FIELD_CHAR || $field_type == FIELD_TINYINT || $field_type == FIELD_INT || $field_type == FIELD_MEDIUMINT || $field_type == FIELD_BIGINT) {
            if ($settings['size']) {
              $params['maxlength'] = $settings['size'];
            }
            $output .= self::text($field_name, $data[$field_name], $params) . "\n";
          } elseif ($field_type == FIELD_DATETIME) {
            $output .= self::datetime($field_name, $data[$field_name], $params) . "\n";
          } elseif ($field_type == FIELD_DATE) {
            $output .= self::date($field_name, $data[$field_name], $params) . "\n";
          } elseif ($field_type == FIELD_TINYTEXT || $field_type == FIELD_MEDIUMTEXT || $field_type == FIELD_TEXT || $field_type == FIELD_LONGTEXT || $field_type == FIELD_TINYBLOB || $field_type == FIELD_MEDIUMBLOB || $field_type == FIELD_BLOB || $field_type == FIELD_LONGBLOB) {
            $output .= self::textarea($field_name, $data[$field_name], $params) . "\n";
          } elseif ($field_type == FIELD_ENUM) {
            $output .= self::dropdown($field_name, $data[$field_name], $settings['values'], $params) . "\n";
          } elseif ($field_type == FIELD_SET) {
            // need to create group of checkboxes
          }
        }
        // add an error tag
        $output .= Help_Error::single($field_name);
        $output .= '</p>' . "\n";
      }
    }
    // save & cancel button
    $output .= '<p>' . "\n";
    $output .= '  <label for="Save">Save</label>' . "\n  ";
    $output .= self::send('Save');
    $output .= self::button('Cancel', 'Cancel', array('onclick' => "window.location.href = '" . $_SERVER['HTTP_REFERER'] . "'"));
    $output .= '</p>' . "\n";
    // close form
    $output .= self::close();
    // return magical form
    return $output;
  }

  /**
   * Protected functions.
   * =======================================================================
   */
  protected static function fieldName($fieldName) {
    $nodes = explode(Madeam::associationJoint, $fieldName);
    $name = array_shift($nodes);
    if (! empty($nodes)) {
      $name .= '[' . implode('][', $nodes) . ']';
    }
    return $name;
  }

  protected static function fieldValue($fieldName, $setValue) {
    // get nodes of field name to identify it's value
    $nodes = explode(Madeam::associationJoint, $fieldName);
    // get root of value
    $value = $_POST;
    foreach ($nodes as $node) {
      if (isset($value[$node])) {
        $value = $value[$node];
      } else {
        $value = null;
        break;
      }
    }
    // if the value is empty then set it to the $setValue
    if (empty($value)) {
      $value = $setValue;
    }
    return stripslashes($value);
  }
}