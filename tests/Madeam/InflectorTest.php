<?php
require_once 'Bootstrap.php';

class Madeam_InflectorTest extends PHPUnit_Framework_TestCase {
  
  public function testSingalizeIrregular() {
    $this->assertEquals('person', Madeam_Inflector::singalize('people'));
  }
  
  public function testSingalizeRegular() {
    $this->assertEquals('test', Madeam_Inflector::singalize('tests'));
  }
  
  public function testPluralizeIrregular() {
    $this->assertEquals('people', Madeam_Inflector::pluralize('person'));
  }
  
  public function testPluralizeRegular() {
    $this->assertEquals('tests', Madeam_Inflector::pluralize('test'));
  }
  
  public function testPluralizeEndingWithYPreceededByAVowel() {
    $this->assertEquals('boys', Madeam_Inflector::pluralize('boy'));
  }
  
  public function testPluralizeEndingWithYPreceededByAConsonant() {
    $this->assertEquals('ladies', Madeam_Inflector::pluralize('lady'));
  }
  
  /**
   * Words ending with "s" should end with "ses"
   */
  public function testPluralizeEndingWithS() {
    $this->assertEquals('messes', Madeam_Inflector::pluralize('mess'));
  }
  
  /**
   * Words ending with "z" should end with "zes"
   */
  public function testPluralizeEndingWithZ() {
    $this->markTestIncomplete();
  }
  
  /**
   * Words ending with "x" should end with "xes"
   */
  public function testPluralizeEndingWithX() {
    $this->markTestIncomplete();
  }
  
  /**
   * Words ending with "ch" should end with "ches"
   */
  public function testPluralizeEndingWithCH() {
    $this->markTestIncomplete();
  }
  
  /**
   * Words ending with "sh" should end with "shes"
   */
  public function testPluralizeEndingWithSH() {
    $this->markTestIncomplete();
  }
  
  /**
   * Words ending with "o" should end with "oes"
   */
  public function testPluralizeEndingWithO() {
    $this->assertEquals('heroes', Madeam_Inflector::pluralize('hero'));
  }
  
  public function testCamelize() {
    $this->assertEquals('fooBar',     Madeam_Inflector::camelize('fooBar'),         'fooBar');
    $this->assertEquals('fooBar',     Madeam_Inflector::camelize('foo\bar'),        'foo\bar');
    $this->assertEquals('fooBar',     Madeam_Inflector::camelize('foo  Bar'),       'foo  Bar');
    $this->assertEquals('fooBar',     Madeam_Inflector::camelize('foo\Bar'),        'foo\Bar');
    $this->assertEquals('fooBarBar',  Madeam_Inflector::camelize('foo\Bar\Bar'),    'foo\Bar\Bar');
    $this->assertEquals('fooBarBar',  Madeam_Inflector::camelize('foo\bar\bar'),    'foo\bar\bar');
  }
  
  public function testDashize() {
    $this->assertEquals('foo-bar',      Madeam_Inflector::dashize('fooBar'),      'fooBar');
    $this->assertEquals('foo-bar',      Madeam_Inflector::dashize('foo\bar'),     'foo\bar');
    $this->assertEquals('foo-bar',      Madeam_Inflector::dashize('foo Bar'),     'foo Bar');
    $this->assertEquals('foo-bar',      Madeam_Inflector::dashize('foo\Bar'),     'foo\Bar');
    $this->assertEquals('foo-bar-bar',  Madeam_Inflector::dashize('fooBarBar'),   'fooBarBar');
    $this->assertEquals('foo-barbar',   Madeam_Inflector::dashize('fooBARBAR'),   'fooBARBAR');
    $this->assertEquals('foo-bar',      Madeam_Inflector::dashize('fooBAR'),      'fooBAR');
  }
  
  public function testUnderscorize() {
    $this->assertEquals('foo_bar',      Madeam_Inflector::underscorize('fooBar'),      'fooBar');
    $this->assertEquals('foo_bar',      Madeam_Inflector::underscorize('foo\bar'),     'foo\bar');
    $this->assertEquals('foo_bar',      Madeam_Inflector::underscorize('foo Bar'),     'foo Bar');
    $this->assertEquals('foo_bar',      Madeam_Inflector::underscorize('foo\Bar'),     'foo\Bar');
    $this->assertEquals('foo_bar_bar',  Madeam_Inflector::underscorize('fooBarBar'),   'fooBarBar');
    $this->assertEquals('foo_barbar',   Madeam_Inflector::underscorize('fooBARBAR'),   'fooBARBAR');
    $this->assertEquals('foo_bar',      Madeam_Inflector::underscorize('fooBAR'),      'fooBAR');
  }
  
  public function testForwardSlashize() {
    $this->assertEquals('foo/bar',      Madeam_Inflector::forwardSlashize('fooBar'),      'fooBar');
    $this->assertEquals('foo/bar',      Madeam_Inflector::forwardSlashize('foo\bar'),     'foo\bar');
    $this->assertEquals('foo/bar',      Madeam_Inflector::forwardSlashize('foo Bar'),     'foo Bar');
    $this->assertEquals('foo/bar',      Madeam_Inflector::forwardSlashize('foo\Bar'),     'foo\Bar');
    $this->assertEquals('foo/bar/bar',  Madeam_Inflector::forwardSlashize('fooBarBar'),   'fooBarBar');
    $this->assertEquals('foo/barbar',   Madeam_Inflector::forwardSlashize('fooBARBAR'),   'fooBARBAR');
    $this->assertEquals('foo/bar',      Madeam_Inflector::forwardSlashize('fooBAR'),      'fooBAR');
  }
  
  public function testBackwardSlashize() {
    $this->assertEquals('foo\bar',      Madeam_Inflector::backSlashize('fooBar'),      'fooBar');
    $this->assertEquals('foo\\bar',     Madeam_Inflector::backSlashize('foo\bar'),     'foo\bar');
    $this->assertEquals('foo\bar',      Madeam_Inflector::backSlashize('foo Bar'),     'foo Bar');
    $this->assertEquals('foo\\\bar',    Madeam_Inflector::backSlashize('foo\Bar'),     'foo\Bar');
    $this->assertEquals('foo\bar\bar',  Madeam_Inflector::backSlashize('fooBarBar'),   'fooBarBar');
    $this->assertEquals('foo\barbar',   Madeam_Inflector::backSlashize('fooBARBAR'),   'fooBARBAR');
    $this->assertEquals('foo\bar',      Madeam_Inflector::backSlashize('fooBAR'),      'fooBAR');
  }
  
  public function testHumanize() {
    $this->assertEquals('Foo Bar',      Madeam_Inflector::humanize('fooBar'),      'fooBar');
    $this->assertEquals('Foo Bar',      Madeam_Inflector::humanize('foo\bar'),     'foo\bar');
    $this->assertEquals('Foo Bar',      Madeam_Inflector::humanize('foo Bar'),     'foo Bar');
    $this->assertEquals('Foo Bar',      Madeam_Inflector::humanize('foo\Bar'),     'foo\Bar');
    $this->assertEquals('Foo Bar Bar',  Madeam_Inflector::humanize('fooBarBar'),   'fooBarBar');
    $this->assertEquals('Foo Barbar',   Madeam_Inflector::humanize('fooBARBAR'),   'fooBARBAR');
    $this->assertEquals('Foo Bar',      Madeam_Inflector::humanize('fooBAR'),      'fooBAR');
  }
  
  /**
   * All characters except alpha numeric characters, underscores and the seperator chracter should be removed
   */
  public function testSlugSpecialCharactersAreRemovedExceptSeparatorAndUnderscores() {
    $separator = '-';
    $this->assertEquals($separator, Madeam_Inflector::slug('$,!/?\\&.#[]()+=-\'/<>{}|%^*~`', $separator));
  }
  
  /**
   * All spaces should be replaced by the separator character. All characters other than alpha numeric characters
   * should be removed
   */
  public function testSlugSpacesAreReplacedWithSeparator() {
    $separator = '-';
    $this->assertEquals('it' . $separator . 'works', Madeam_Inflector::slug('it works!', $separator));
  }

}