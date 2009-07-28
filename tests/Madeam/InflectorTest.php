<?php
require_once 'Bootstrap.php';

class madeam\InflectorTest extends PHPUnit_Framework_TestCase {
  
  public function testSingalizeIrregular() {
    $this->assertEquals('person', madeam\Inflector::singalize('people'));
  }
  
  public function testSingalizeRegular() {
    $this->assertEquals('test', madeam\Inflector::singalize('tests'));
  }
  
  public function testPluralizeIrregular() {
    $this->assertEquals('people', madeam\Inflector::pluralize('person'));
  }
  
  public function testPluralizeRegular() {
    $this->assertEquals('tests', madeam\Inflector::pluralize('test'));
  }
  
  public function testPluralizeEndingWithYPreceededByAVowel() {
    $this->assertEquals('boys', madeam\Inflector::pluralize('boy'));
  }
  
  public function testPluralizeEndingWithYPreceededByAConsonant() {
    $this->assertEquals('ladies', madeam\Inflector::pluralize('lady'));
  }
  
  /**
   * Words ending with "s" should end with "ses"
   */
  public function testPluralizeEndingWithS() {
    $this->assertEquals('messes', madeam\Inflector::pluralize('mess'));
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
    $this->assertEquals('heroes', madeam\Inflector::pluralize('hero'));
  }
  
  public function testCamelize() {
    $this->assertEquals('fooBar',     madeam\Inflector::camelize('fooBar'),         'fooBar');
    $this->assertEquals('fooBar',     madeam\Inflector::camelize('foo\bar'),        'foo\bar');
    $this->assertEquals('fooBar',     madeam\Inflector::camelize('foo  Bar'),       'foo  Bar');
    $this->assertEquals('fooBar',     madeam\Inflector::camelize('foo\Bar'),        'foo\Bar');
    $this->assertEquals('fooBarBar',  madeam\Inflector::camelize('foo\Bar\Bar'),    'foo\Bar\Bar');
    $this->assertEquals('fooBarBar',  madeam\Inflector::camelize('foo\bar\bar'),    'foo\bar\bar');
  }
  
  public function testDashize() {
    $this->assertEquals('foo-bar',      madeam\Inflector::dashize('fooBar'),      'fooBar');
    $this->assertEquals('foo-bar',      madeam\Inflector::dashize('foo\bar'),     'foo\bar');
    $this->assertEquals('foo-bar',      madeam\Inflector::dashize('foo Bar'),     'foo Bar');
    $this->assertEquals('foo-bar',      madeam\Inflector::dashize('foo\Bar'),     'foo\Bar');
    $this->assertEquals('foo-bar-bar',  madeam\Inflector::dashize('fooBarBar'),   'fooBarBar');
    $this->assertEquals('foo-barbar',   madeam\Inflector::dashize('fooBARBAR'),   'fooBARBAR');
    $this->assertEquals('foo-bar',      madeam\Inflector::dashize('fooBAR'),      'fooBAR');
  }
  
  public function testUnderscorize() {
    $this->assertEquals('foo_bar',      madeam\Inflector::underscorize('fooBar'),      'fooBar');
    $this->assertEquals('foo_bar',      madeam\Inflector::underscorize('foo\bar'),     'foo\bar');
    $this->assertEquals('foo_bar',      madeam\Inflector::underscorize('foo Bar'),     'foo Bar');
    $this->assertEquals('foo_bar',      madeam\Inflector::underscorize('foo\Bar'),     'foo\Bar');
    $this->assertEquals('foo_bar_bar',  madeam\Inflector::underscorize('fooBarBar'),   'fooBarBar');
    $this->assertEquals('foo_barbar',   madeam\Inflector::underscorize('fooBARBAR'),   'fooBARBAR');
    $this->assertEquals('foo_bar',      madeam\Inflector::underscorize('fooBAR'),      'fooBAR');
  }
  
  public function testForwardSlashize() {
    $this->assertEquals('foo/bar',      madeam\Inflector::forwardSlashize('fooBar'),      'fooBar');
    $this->assertEquals('foo/bar',      madeam\Inflector::forwardSlashize('foo\bar'),     'foo\bar');
    $this->assertEquals('foo/bar',      madeam\Inflector::forwardSlashize('foo Bar'),     'foo Bar');
    $this->assertEquals('foo/bar',      madeam\Inflector::forwardSlashize('foo\Bar'),     'foo\Bar');
    $this->assertEquals('foo/bar/bar',  madeam\Inflector::forwardSlashize('fooBarBar'),   'fooBarBar');
    $this->assertEquals('foo/barbar',   madeam\Inflector::forwardSlashize('fooBARBAR'),   'fooBARBAR');
    $this->assertEquals('foo/bar',      madeam\Inflector::forwardSlashize('fooBAR'),      'fooBAR');
  }
  
  public function testBackwardSlashize() {
    $this->assertEquals('foo\bar',      madeam\Inflector::backSlashize('fooBar'),      'fooBar');
    $this->assertEquals('foo\\bar',     madeam\Inflector::backSlashize('foo\bar'),     'foo\bar');
    $this->assertEquals('foo\bar',      madeam\Inflector::backSlashize('foo Bar'),     'foo Bar');
    $this->assertEquals('foo\\\bar',    madeam\Inflector::backSlashize('foo\Bar'),     'foo\Bar');
    $this->assertEquals('foo\bar\bar',  madeam\Inflector::backSlashize('fooBarBar'),   'fooBarBar');
    $this->assertEquals('foo\barbar',   madeam\Inflector::backSlashize('fooBARBAR'),   'fooBARBAR');
    $this->assertEquals('foo\bar',      madeam\Inflector::backSlashize('fooBAR'),      'fooBAR');
  }
  
  public function testHumanize() {
    $this->assertEquals('Foo Bar',      madeam\Inflector::humanize('fooBar'),      'fooBar');
    $this->assertEquals('Foo Bar',      madeam\Inflector::humanize('foo\bar'),     'foo\bar');
    $this->assertEquals('Foo Bar',      madeam\Inflector::humanize('foo Bar'),     'foo Bar');
    $this->assertEquals('Foo Bar',      madeam\Inflector::humanize('foo\Bar'),     'foo\Bar');
    $this->assertEquals('Foo Bar Bar',  madeam\Inflector::humanize('fooBarBar'),   'fooBarBar');
    $this->assertEquals('Foo Barbar',   madeam\Inflector::humanize('fooBARBAR'),   'fooBARBAR');
    $this->assertEquals('Foo Bar',      madeam\Inflector::humanize('fooBAR'),      'fooBAR');
  }
  
  /**
   * All characters except alpha numeric characters, underscores and the seperator chracter should be removed
   */
  public function testSlugSpecialCharactersAreRemovedExceptSeparatorAndUnderscores() {
    $separator = '-';
    $this->assertEquals($separator, madeam\Inflector::slug('$,!/?\\&.#[]()+=-\'/<>{}|%^*~`', $separator));
  }
  
  /**
   * All spaces should be replaced by the separator character. All characters other than alpha numeric characters
   * should be removed
   */
  public function testSlugSpacesAreReplacedWithSeparator() {
    $separator = '-';
    $this->assertEquals('it' . $separator . 'works', madeam\Inflector::slug('it works!', $separator));
  }

}