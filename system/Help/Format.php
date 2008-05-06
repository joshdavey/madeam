<?php
class Help_Format {

  static public $dateFormat       = 'd F o';
  static public $datetimeFormat   = 'd F o H:i:s';
  static public $moneyFormat      = '%';
  static public $numberThousands  = ',';
  static public $numberDecimals   = 0;
  static public $numberDecimal    = '.';
  
  /**
   * Enter description here...
   *
   * @param string $date
   * @param string $format
   * @return string
   */
  static public function date($date = false, $format = false) {
    // set format if not defined
    if ($format === false) { $format = self::$dateFormat; }

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
    if ($format === false) { $format = self::$datetimeFormat; }

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
   * @param integer $number
   * @param string $format
   * @return string
   */
  static public function money($number, $format = false) {
    // set format if not defined
    if ($format === false) { $format = self::$moneyFormat; }

    if (function_exists('money_format')) {
      return money_format($format, (int) $number);
    } else {
      return number_format((int) $number, 2, '.', ',');
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
    if ($decimals     === false)  { $decimals     = self::$numberDecimals; }
    if ($decimalPoint === false)  { $decimalPoint = self::$numberDecimal; }
    if ($thousands    === false)  { $thousands    = self::$numberThousands; }
    
    return number_format((int) $number, $decimals, $decimalPoint, $thousands);
  }
  
}
?>