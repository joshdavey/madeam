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
class Help_Form extends Help_Html {
  
  public static function open($action = null, $_params = array()) {
    
    if (isset($_params['file'])) {
      $params['enctype'] = 'multipart/form-data';
    }
    
    if (!isset($_params['method'])) {
      $_params['method'] = 'post';
    }
    
    $params['action'] = Madeam::url($action);
    $params['id'] = 'form_' . Madeam_Inflector::underscorize(Madeam_Inflector::slug($params['action']));
    
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
    $params['name'] = $name; // don't need to give it a form name
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

  public static function button($name, $value = null, $_params = array()) {
    $params = array();
    $params['name'] = $name;
    $params['type'] = 'button';
    $params['id'] = self::nameToId($name);
    $params['value'] = self::fieldValue($name, $value);
    $params = array_merge($params, $_params);
    return self::tag('input', $params);
  }

  public static function submit($value = 'Submit', $_params = array()) {
    $params = array();
    if (! isset($params['name'])) {
      $params['name'] = 'Submit';
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

  public static function date($name, $value = null, $_params = array()) {
    $returned = array();
    // set name of IDs
    $idname = self::nameToId($name);
    if (is_string($value)) {
      preg_match_all('/(\d{4})\-(\d{2})\-(\d{2})/', $value, $matchs);
      $value = array();
      $value[$name . Madeam::association_joint . 'year'] = $matchs[1][0];
      $value[$name . Madeam::association_joint . 'month'] = $matchs[2][0];
      $value[$name . Madeam::association_joint . 'day'] = $matchs[3][0];
    }
    //date('Y-m-d H:i:s');
    // month
    $month_params = array();
    $month_params['class'] = 'date_month';
    $month_params['id'] = $idname . '_date_month';
    if (@$value[$name . Madeam::association_joint . 'month'] == null) {
      @$value[$name . Madeam::association_joint . 'month'] = date('m');
    }
    $returned[] = self::dropdown($name . Madeam::association_joint . 'month', $value[$name . Madeam::association_joint . 'month'], array('01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'), $month_params);
    // day
    $day_params = array();
    $day_params['class'] = 'date_day';
    $day_params['id'] = $idname . '_date_day';
    if (@$value[$name . Madeam::association_joint . 'day'] == null) {
      @$value[$name . Madeam::association_joint . 'day'] = date('d');
    }
    $returned[] = self::dropdown($name . Madeam::association_joint . 'day', $value[$name . Madeam::association_joint . 'day'], array('01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30', '31'), $day_params);
    // year
    $year_params = array();
    $year_params['size'] = '4';
    $year_params['maxlength'] = '4';
    $year_params['class'] = 'date_year';
    $year_params['id'] = $idname . '_date_year';
    if (@$value[$name . Madeam::association_joint . 'year'] == null) {
      @$value[$name . Madeam::association_joint . 'year'] = date('Y');
    }
    $returned[] = self::input($name . Madeam::association_joint . 'year', $value[$name . Madeam::association_joint . 'year'], $year_params);
    return implode($returned);
  }

  public static function datetime($name, $value, $_params = array()) {
    $returned = array();
    $returned[] = self::date($name, $value, $_params);
    // set name of IDs
    $idname = self::nameToId($name);
    if (is_string($value)) {
      preg_match_all('/(\d{2})\:(\d{2})\:(\d{2})/', $value, $matchs);
      $value = array();
      $value[$name . Madeam::association_joint . 'hour'] = $matchs[1][0];
      $value[$name . Madeam::association_joint . 'minute'] = $matchs[2][0];
      $value[$name . Madeam::association_joint . 'second'] = $matchs[3][0];
    }
    //date('Y-m-d H:i:s');
    // hour
    $hour_params = array();
    $hour_params['class'] = 'datetime_hour';
    $hour_params['id'] = $idname . '_datetime_hour';
    if (@$value[$name . Madeam::association_joint . 'hour'] == null) {
      $value[$name . Madeam::association_joint . 'hour'] = date('H');
    }
    //if ($value[$name . Madeam::association_joint . 'hour'] == null) { $value[$name . Madeam::association_joint . 'hour'] = '12'; }
    $returned[] = self::dropdown($name . Madeam::association_joint . 'hour', $value[$name . Madeam::association_joint . 'hour'], array('00', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23'), $hour_params) . ' : ';
    // minute
    $min_params = array();
    $min_params['class'] = 'datetime_minute';
    $min_params['id'] = $idname . '_datetime_minute';
    if (@$value[$name . Madeam::association_joint . 'minute'] == null) {
      $value[$name . Madeam::association_joint . 'minute'] = date('i');
    }
    //if ($value[$name . Madeam::association_joint . 'minute'] == null) { $value[$name . Madeam::association_joint . 'minute'] = '00'; }
    $returned[] = self::dropdown($name . Madeam::association_joint . 'minute', $value[$name . Madeam::association_joint . 'minute'], array('00', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30', '31', '32', '33', '34', '35', '36', '37', '38', '39', '40', '41', '42', '43', '44', '45', '46', '47', '48', '49', '50', '51', '52', '53', '54', '55', '56', '57', '58', '59', '60'), $min_params) . ' : ';
    // second
    $sec_params = array();
    $sec_params['class'] = 'datetime_second';
    $sec_params['id'] = $idname . '_datetime_second';
    if (@$value[$name . Madeam::association_joint . 'second'] == null) {
      $value[$name . Madeam::association_joint . 'second'] = date('s');
    }
    //if ($value[$name . Madeam::association_joint . 'second'] == null) { $value[$name . Madeam::association_joint . 'second'] = '00'; }
    $returned[] = self::dropdown($name . Madeam::association_joint . 'second', $value[$name . Madeam::association_joint . 'second'], array('00', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30', '31', '32', '33', '34', '35', '36', '37', '38', '39', '40', '41', '42', '43', '44', '45', '46', '47', '48', '49', '50', '51', '52', '53', '54', '55', '56', '57', '58', '59', '60'), $sec_params);
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
    $modelname = Madeam_Inflector::model_classize($model);
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
        $field_label = Madeam_Inflector::sentencize($field_name);
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
    $nodes = explode(Madeam::association_joint, $fieldName);
    $name = $nodes[0];
    array_shift($nodes);
    if (! empty($nodes)) {
      $name .= '[' . implode('][', $nodes) . ']';
    }
    return $name;
  }

  protected static function fieldValue($fieldName, $setValue) {
    // get nodes of field name to identify it's value
    $nodes = explode(Madeam::association_joint, $fieldName);
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