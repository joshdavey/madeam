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
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
class Madeam_Validate {
  
  public static function isEmail($string) {
    return false;
  }
  
  public static function isBetween($string, $maxLength, $minLength = 0) {
    return false;
  }
  
  public static function isPattern($string, $regex) {
    return false;
  }
  
  public static function isNotEmpty($string) {
    if ($string != null) { return true; }
    return false;
  }
  
  public static function isDate($string) {
    return false;
  }
  
  public static function isISBN($string) {
    return false;
  }
  
  public static function isIP($string) {
    return false;
  }
  
  public static function isPhone($string) {
    return false;
  }
  
  public static function isUrl($string) {
    return false;
  }
  
  public static function isNumber($string) {
    return false;
  }
}
?>