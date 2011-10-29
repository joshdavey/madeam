<?php
namespace madeam\helper;
class Format {

  public static $dateFormat = 'd F o';

  public static $datetimeFormat = 'd F o \a\t g:i:s a';

  public static $moneyFormat = '%n';

  public static $numberThousands = ',';

  public static $numberDecimals = 0;

  public static $numberDecimal = '.';

  /**
   * Enter description here...
   *
   * @param string $date
   * @param string $format
   * @return string
   */
  static public function date($date = false, $format = false) {
    // set format if not defined
    if ($format === false) {
      $format = self::$dateFormat;
    }
    if ($date === false) {
      return date($format, time());
    } else {
      if (($time = strtotime($date)) !== false) {
        return date($format, $time);
      } else {
        return false;
      }
    }
  }

  /**
   * Enter description here...
   *
   * @param string $date
   * @param string $format
   * @return string
   */
  static public function datetime($date = false, $format = false) {
    // set format if not defined
    if ($format === false) {
      $format = self::$datetimeFormat;
    }
    if ($date === false) {
      return date($format, time());
    } else {
      if (($time = strtotime($date)) !== false) {
        return date($format, $time);
      } else {
        return false;
      }
    }
  }

  /**
   * undocumented
   *
   * @author Joshua Davey
   */
  static public function timeAgo($date) {
    $ts = time() - strtotime($date);

    if ($ts>31536000)     $val = round($ts/31536000,0).' year';
    elseif ($ts>2419200)  $val = round($ts/2419200,0).' month';
    elseif ($ts>604800)   $val = round($ts/604800,0).' week';
    elseif ($ts>86400)    $val = round($ts/86400,0).' day';
    elseif ($ts>3600)     $val = round($ts/3600,0).' hour';
    elseif ($ts>60)       $val = round($ts/60,0).' minute';
    else                  $val = $ts.' second';

    if($val>1) $val .= 's';
    return $val;
  }

  /**
   * undocumented
   *
   * @author Joshua Davey
   */
  static public function timeUntil($date) {
    $ts = strtotime($date) - time();

    if ($ts>31536000)     $val = round($ts/31536000,0).' year';
    elseif ($ts>2419200)  $val = round($ts/2419200,0).' month';
    elseif ($ts>604800)   $val = round($ts/604800,0).' week';
    elseif ($ts>86400)    $val = round($ts/86400,0).' day';
    elseif ($ts>3600)     $val = round($ts/3600,0).' hour';
    elseif ($ts>60)       $val = round($ts/60,0).' minute';
    else                  $val = $ts.' second';

    if($val>1) $val .= 's';
    return $val;
  }

  /**
   * Enter description here...
   *
   * @param integer $number
   * @param string $format
   * @return string
   */
  static public function money($number, $format = false) {
    // set format if not defined
    if ($format === false) {
      $format = self::$moneyFormat;
    }
    if (function_exists('money_format')) {
      return money_format($format, (float) $number);
    } else {
      return number_format((float) $number, 2, '.', ',');
    }
  }

  /**
   * Enter description here...
   *
   * @param unknown_type $number
   * @param unknown_type $decimals
   * @param unknown_type $decimalPoint
   * @param unknown_type $thousands
   * @return unknown
   */
  static public function number($number, $decimals = false, $decimalPoint = false, $thousands = false) {
    // set formats if not defined
    if ($decimals === false) {
      $decimals = self::$numberDecimals;
    }
    if ($decimalPoint === false) {
      $decimalPoint = self::$numberDecimal;
    }
    if ($thousands === false) {
      $thousands = self::$numberThousands;
    }
    return number_format((int) $number, $decimals, $decimalPoint, $thousands);
  }
}