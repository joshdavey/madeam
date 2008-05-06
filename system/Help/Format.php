<?php
class Help_Format {

  static public $dateFormat     = 'd F o';
  static public $datetimeFormat = 'd F o H:i:s';
  static public $moneyFormat    = '%';

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

    return money_format($format, (int) $number);
  }

}
?>