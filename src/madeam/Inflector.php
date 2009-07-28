<?php
namespace madeam;
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
 * @license      http://www.opensource.org/licenses/mit-license.php The MIT License
 */
class Inflector {

  public static $irregulars = array('amoyese' => 'amoyese', 'atlas' => 'atlases', 'beef' => 'beeves', 'bison' => 'bison', 'brother' => 'brothers', 'canto', 'child' => 'children', 'corpus' => 'corpuses', 'cow' => 'cows', 'deer' => 'deer', 'fish' => 'fish', 'ganglion' => 'ganglions', 'genie' => 'genies', 'genus' => 'genera', 'graffito' => 'graffiti', 'hoof' => 'hoofs', 'loaf' => 'loaves', 'man' => 'men', 'measles' => 'measles', 'money' => 'monies', 'mongoose' => 'mongooses', 'move' => 'moves', 'mythos' => 'mythoi', 'numen' => 'numina', 'occiput' => 'occiputs', 'octopus' => 'octopuses', 'opus' => 'opuses', 'ox' => 'oxen', 'penis' => 'penises', 'person' => 'people', 'rice' => 'rice', 'sex' => 'sexes', 'sheep' => 'sheep', 'soliloquy' => 'soliloquies', 'testis' => 'testes', 'trilby' => 'trilbys', 'turf' => 'turfs');

  /**
   * Pluralizes a string
   *
   * @param string $string
   * @return string
   */
  public static function pluralize($word) {
    $lastLetter = substr($word, -1);
    if (array_key_exists(strtolower($word), self::$irregulars)) {
      return self::$irregulars[$word];
    } elseif (in_array($lastLetter, array('s', 'z', 'x')) || in_array(substr($word, -2), array('sh', 'ch'))) {
      return $word . 'es';
      } elseif (in_array(substr($word, -2, 1), array('a', 'e', 'i', 'o', 'u')) && $lastLetter == 'y') {
        return $word . 's';
    } elseif ($lastLetter == 'y') {
      return substr_replace($word, 'ies', -1, 1);
    } elseif ($lastLetter == 'o' && in_array(substr($word, -2, 1), array('b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'm', 'n', 'p', 'q', 'r', 's', 't', 'v', 'w', 'x', 'y', 'z'))) {
      return $word . 'es';
    } else {
      return $word . 's';
    }
  }

  /**
   * Singalizes a string
   *
   * @param string $string
   * @return string
   */
  public static function singalize($word) {
    $pluralIrregulars = array_flip(self::$irregulars);
    if (array_key_exists(strtolower($word), $pluralIrregulars)) {
      return $pluralIrregulars[$word];
    } elseif (strtolower($word[strlen($word) - 1]) != 's') {
      return $word;
    } elseif (substr(strtolower($word), - 3, 3) == 'ies') {
      $word = preg_replace('/sei/i', 'y', strrev($word), 1);
      return strrev($word);
    } else {
      $word = preg_replace('/s/i', '', strrev($word), 1);
      return strrev($word);
    }
  }

  /**
   * Camelizes a string seperated by any of these characters: "_", "-", " ", "/", "\"
   *
   * "foo-bar" => "fooBar"
   * "foo_bar" => "fooBar"
   * "foo bar" => "fooBar"
   * "foo/bar" => "fooBar"
   * "foo\bar" => "fooBar"
   *
   * @param string $string
   */
  public static function camelize($string) {
    return preg_replace('/[^a-zA-Z0-9]([a-z]*)/e', "ucfirst('\\1')", $string);
  }

  /**
   * Replaces specific charactes with underscores
   *
   * "fooBar"  => "foo_bar"
   * "foo-bar" => "foo_bar"
   * "foo bar" => "foo_bar"
   * "foo/bar" => "foo_bar"
   * "foo\bar" => "foo_bar"
   *
   * @param string $string
   */
  public static function underscorize($string) {
    return self::specialize('_', $string);
  }

  /**
   * This method is pronounced "dash-ize".
   * Replaces special characters with dashes
   *
   * "fooBar"  => "foo-bar"
   * "foo_bar" => "foo-bar"
   * "foo bar" => "foo-bar"
   *
   * @param string $string
   */
  public static function dashize($string) {
    return self::specialize('-', $string);
  }

  /**
   * Replaces special characters with forward slashes
   *
   * "fooBar"  => "foo/bar"
   * "foo_bar" => "foo/bar"
   * "foo bar" => "foo/bar"
   *
   * @param string $string
   */
  public static function forwardSlashize($string) {
    return self::specialize('/', $string);
  }

  /**
   * Replaces special characters with back slashes
   *
   * "fooBar"  => "foo\bar"
   * "foo_bar" => "foo\bar"
   * "foo bar" => "foo\bar"
   *
   * @param string $string
   */
  public static function backSlashize($string) {
    return self::specialize('\\', $string);
  }

  /**
   * This method makes strings more readable to human
   *
   * "fooBar"  => "Foo Bar"
   * "foo_bar" => "Foo Bar"
   * "foo bar" => "Foo Bar"
   *
   * @param string $string
   */
  public static function humanize($string) {
    return ucwords(preg_replace('/\s\s+/', ' ', self::specialize(' ', $string)));
  }

  public static function modelClassize($string) {
    return 'Model_' . self::camelize(self::singalize($string));
  }

  public static function modelTableize($string) {
    $string[0] = strtolower($string[0]);
    return self::underscorize((self::pluralize($string)));
  }

  public static function modelNameize($string) {
    $string[0] = strtolower($string[0]);
    return ucfirst(self::camelize((self::singalize($string))));
  }

  /**
   * Takes 2 tables and determine's their has and belongs to many table name
   *
   * @param string $table1
   * @param string $table2
   * @return string
   */
  public static function modelHabtm($table1, $table2) {
    $models = array(self::modelTableize($table1), self::modelTableize($table2));
    asort($models);
    $models = array_values($models);
    return $models[0] . '_' . $models[1];
  }

  public static function modelForeignKey($string) {
    $string[0] = strtolower($string[0]);
    return self::singalize(self::underscorize($string)) . '_id';
  }

  public static function specialize($char, $string) {
    $escapedChar = preg_quote($char, '/');
    // pad all capitalized strings with replacement character and make the text lowercase.
    $string = strtolower(preg_replace('/[^a-zA-Z0-9' . $escapedChar . ']*([A-Z]+)/', preg_quote($char)  . '${1}', $string));
    $string = preg_replace('/([^a-z0-9' . $escapedChar . ']+)/', $char, $string);
    
    // return formatted string
    return strtolower($string);
  }

  /**
   * This method returns a string formatted so that it is appropriate to
   * use in a URL.
   *
   * @param string $string 
   * @param string $separator 
   * @return string
   * @author Joshua Davey
   */
  public static function slug($string, $separator = '-') {
    $string = strtolower(str_replace(' ', $separator, trim($string)));
    $string = preg_replace('/[^a-z0-9' . preg_quote($separator) . '_]/', '', $string);
    return $string;
  }
  
  public static function map($string, $maps = array()) {
    foreach ($maps as $pattern => $replacement) {
      $string = preg_replace('/' . $pattern . '/', $replacement, $string); 
    }
    return $string;
  }
}
