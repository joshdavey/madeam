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
class Madeam_Inflector {
  public static $irregulars = array(
    'amoyese'     => 'amoyese',
    'atlas'       => 'atlases',
		'beef'        => 'beefs',
		'bison'       => 'bison',
		'brother'     => 'brothers',
		'child'       => 'children',
		'corpus'      => 'corpuses',
		'cow'         => 'cows',
		'deer'        => 'deer',
		'fish'        => 'fish',
		'ganglion'    => 'ganglions',
		'genie'       => 'genies',
		'genus'       => 'genera',
		'graffito'    => 'graffiti',
		'hoof'        => 'hoofs',
		'loaf'        => 'loaves',
		'man'         => 'men',
		'measles'     => 'measles',
		'money'       => 'monies',
		'mongoose'    => 'mongooses',
		'move'        => 'moves',
		'mythos'      => 'mythoi',
		'numen'       => 'numina',
		'occiput'     => 'occiputs',
		'octopus'     => 'octopuses',
		'opus'        => 'opuses',
		'ox'          => 'oxen',
		'penis'       => 'penises',
		'person'      => 'people',
		'rice'        => 'rice',
		'sex'         => 'sexes',
		'sheep'       => 'sheep',
		'soliloquy'   => 'soliloquies',
		'testis'      => 'testes',
		'trilby'      => 'trilbys',
		'turf'        => 'turfs',
  );

  /**
   * Pluralizes a word
   *
   * @param string $word
   * @return string
   */
  public static function pluralize($word) {
    if (array_key_exists(low($word), self::$irregulars)) {
      return self::$irregulars[$word];
    } elseif (low($word[strlen($word)-1]) == 's') {
  		return $word;
    } elseif (low($word[strlen($word)-1]) == 'y') {
  		$word = preg_replace('/y/i', 'sei', strrev($word), 1); // add ies backwards so it gets set back later
  		return strrev($word);
  	} else {
  		return $word .= 's';
  	}
  }



  /**
   * Singalizes a word
   *
   * @param string $word
   * @return string
   */
  public static function singalize($word) {
    $plural_irregulars = array_flip(self::$irregulars);
  	if (array_key_exists(low($word), $plural_irregulars)) {
      return $plural_irregulars[$word];
    } elseif (low($word[strlen($word)-1]) != 's') {
  		return $word;
  	} elseif (substr(low($word), -3, 3) == 'ies') {
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
   * @param string $word
   */
	public static function camelize($word) {
	  preg_match_all('/([\/\-\_\s\\\]{1}.{1})/', $word, $matchs);
	  foreach($matchs[0] as $match) {
	    $replacement = up(substr($match, 1, 1));
	    $word = str_replace($match, $replacement, $word);
	  }
  	return $word;
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
	 * @param string $word
	 */
	public static function underscorize($word) {
	  return self::specialize('_', $word);
	}



	/**
	 * This method is pronounced "dash-ize".
	 * Replaces specific characters with dashes
	 *
	 * "fooBar"  => "foo-bar"
	 * "foo_bar" => "foo-bar"
	 * "foo bar" => "foo-bar"
	 *
	 * @param string $word
	 */
	public static function dashize($word) {
	  return self::specialize('-', $word);
	}


	public static function forwardSlashize($word) {
    return self::specialize('/', $word);
	}


	public static function backwordSlashize($word) {
    return self::specialize('\\', $word);
	}

	public static function humanize($string) {
	  return ucfirst(self::specialize(' ', $string));
	}

	public static function modelClassize($string) {
	  return 'Model_' . self::camelize(self::singalize($string));
	}

	public static function modelTableize($string) {
	  $string[0] = low($string[0]);
	  return self::underscorize((self::pluralize($string)));
	}

	public static function modelNameize($string) {
	  $string[0] = low($string[0]);
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
	  $string[0] = low($string[0]);
	  return self::singalize(self::underscorize($string));
	}

	public static function specialize($char, $word) {
	  preg_match_all('/([\/\-\_\s\\\\.]{1}.{1})||([A-Z])/', $word, $matchs);
	  foreach($matchs[0] as $match) {
	    if (strlen($match) == 1) {
	      $replacement = $char . low($match);
	      $word = str_replace($match, $replacement, $word);
	    }
	    $replacement = $char . low(substr($match, 1, 1));
	    $word = str_replace($match, $replacement, $word);
	  }
	  return $word;
	}

	public static function slug($string, $seperator = '-') {
	  $string = low(str_replace(' ', $seperator, trim($string)));
	  $string = preg_replace('/[\$,!\/\\\\&\.]/', '', $string);
	  return $string;
	}

}

?>