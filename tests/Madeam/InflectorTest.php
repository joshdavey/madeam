<?php
namespace madeam;
require_once 'Bootstrap.php';
class InflectorTest extends \PHPUnit_Framework_TestCase {
  
  public function testSingalizeIrregular() {
    $this->assertEquals('person', Inflector::singalize('people'));
  }
  
  public function testSingalizeRegular() {
    $this->assertEquals('test', Inflector::singalize('tests'));
  }
  
  public function testPluralizeIrregular() {
    $this->assertEquals('people', Inflector::pluralize('person'));
  }
  
  public function testPluralizeRegular() {
    $this->assertEquals('tests', Inflector::pluralize('test'));
  }
  
  public function testPluralizeEndingWithYPreceededByAVowel() {
    $this->assertEquals('boys', Inflector::pluralize('boy'));
  }
  
  public function testPluralizeEndingWithYPreceededByAConsonant() {
    $this->assertEquals('ladies', Inflector::pluralize('lady'));
  }
  
  /**
   * Words ending with "s" should end with "ses"
   */
  public function testPluralizeEndingWithS() {
    $this->assertEquals('messes', Inflector::pluralize('mess'));
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
    $this->assertEquals('heroes', Inflector::pluralize('hero'));
  }
  
  public function testCamelize() {
    $this->assertEquals('fooBar',     Inflector::camelize('fooBar'),         'fooBar');
    $this->assertEquals('fooBar',     Inflector::camelize('foo\bar'),        'foo\bar');
    $this->assertEquals('fooBar',     Inflector::camelize('foo  Bar'),       'foo  Bar');
    $this->assertEquals('fooBar',     Inflector::camelize('foo\Bar'),        'foo\Bar');
    $this->assertEquals('fooBarBar',  Inflector::camelize('foo\Bar\Bar'),    'foo\Bar\Bar');
    $this->assertEquals('fooBarBar',  Inflector::camelize('foo\bar\bar'),    'foo\bar\bar');
  }
  
  public function testDashize() {
    $this->assertEquals('foo-bar',      Inflector::dashize('fooBar'),      'fooBar');
    $this->assertEquals('foo-bar',      Inflector::dashize('foo\bar'),     'foo\bar');
    $this->assertEquals('foo-bar',      Inflector::dashize('foo Bar'),     'foo Bar');
    $this->assertEquals('foo-bar',      Inflector::dashize('foo\Bar'),     'foo\Bar');
    $this->assertEquals('foo-bar-bar',  Inflector::dashize('fooBarBar'),   'fooBarBar');
    $this->assertEquals('foo-barbar',   Inflector::dashize('fooBARBAR'),   'fooBARBAR');
    $this->assertEquals('foo-bar',      Inflector::dashize('fooBAR'),      'fooBAR');
  }
  
  public function testUnderscorize() {
    $this->assertEquals('foo_bar',      Inflector::underscorize('fooBar'),      'fooBar');
    $this->assertEquals('foo_bar',      Inflector::underscorize('foo\bar'),     'foo\bar');
    $this->assertEquals('foo_bar',      Inflector::underscorize('foo Bar'),     'foo Bar');
    $this->assertEquals('foo_bar',      Inflector::underscorize('foo\Bar'),     'foo\Bar');
    $this->assertEquals('foo_bar_bar',  Inflector::underscorize('fooBarBar'),   'fooBarBar');
    $this->assertEquals('foo_barbar',   Inflector::underscorize('fooBARBAR'),   'fooBARBAR');
    $this->assertEquals('foo_bar',      Inflector::underscorize('fooBAR'),      'fooBAR');
  }
  
  public function testForwardSlashize() {
    $this->assertEquals('foo/bar',      Inflector::forwardSlashize('fooBar'),      'fooBar');
    $this->assertEquals('foo/bar',      Inflector::forwardSlashize('foo\bar'),     'foo\bar');
    $this->assertEquals('foo/bar',      Inflector::forwardSlashize('foo Bar'),     'foo Bar');
    $this->assertEquals('foo/bar',      Inflector::forwardSlashize('foo\Bar'),     'foo\Bar');
    $this->assertEquals('foo/bar/bar',  Inflector::forwardSlashize('fooBarBar'),   'fooBarBar');
    $this->assertEquals('foo/barbar',   Inflector::forwardSlashize('fooBARBAR'),   'fooBARBAR');
    $this->assertEquals('foo/bar',      Inflector::forwardSlashize('fooBAR'),      'fooBAR');
  }
  
  public function testBackwardSlashize() {
    $this->assertEquals('foo\bar',      Inflector::backSlashize('fooBar'),      'fooBar');
    $this->assertEquals('foo\\bar',     Inflector::backSlashize('foo\bar'),     'foo\bar');
    $this->assertEquals('foo\bar',      Inflector::backSlashize('foo Bar'),     'foo Bar');
    $this->assertEquals('foo\\\bar',    Inflector::backSlashize('foo\Bar'),     'foo\Bar');
    $this->assertEquals('foo\bar\bar',  Inflector::backSlashize('fooBarBar'),   'fooBarBar');
    $this->assertEquals('foo\barbar',   Inflector::backSlashize('fooBARBAR'),   'fooBARBAR');
    $this->assertEquals('foo\bar',      Inflector::backSlashize('fooBAR'),      'fooBAR');
  }
  
  public function testHumanize() {
    $this->assertEquals('Foo Bar',      Inflector::humanize('fooBar'),      'fooBar');
    $this->assertEquals('Foo Bar',      Inflector::humanize('foo\bar'),     'foo\bar');
    $this->assertEquals('Foo Bar',      Inflector::humanize('foo Bar'),     'foo Bar');
    $this->assertEquals('Foo Bar',      Inflector::humanize('foo\Bar'),     'foo\Bar');
    $this->assertEquals('Foo Bar Bar',  Inflector::humanize('fooBarBar'),   'fooBarBar');
    $this->assertEquals('Foo Barbar',   Inflector::humanize('fooBARBAR'),   'fooBARBAR');
    $this->assertEquals('Foo Bar',      Inflector::humanize('fooBAR'),      'fooBAR');
  }
  
  /**
   * All characters except alpha numeric characters, underscores and the seperator chracter should be removed
   */
  public function testSlugSpecialCharactersAreRemovedExceptSeparatorAndUnderscores() {
    $separator = '-';
    $this->assertEquals($separator, Inflector::slug('$,!/?\\&.#[]()+=-\'/<>{}|%^*~`', $separator));
  }
  
  /**
   * All spaces should be replaced by the separator character. All characters other than alpha numeric characters
   * should be removed
   */
  public function testSlugSpacesAreReplacedWithSeparator() {
    $separator = '-';
    $this->assertEquals('it' . $separator . 'works', Inflector::slug('it works!', $separator));
  }

}