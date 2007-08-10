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

class yaml extends object {
  // Collection indicators:
    const ci_key_indic            = '? ';
    const ci_value_indic          = ': ';
    const ci_nested_series_indic  = '- ';
    const ci_ilb_entry_seperator  = ', ';    // Sperate in-line branch entries
    const ci_ilsb_l_delimiter     = '[';     // Surround in-line series branch @see ilsb_r_delimiter
    const ci_ilsb_r_delimiter     = ']';     // Surround in-line series branch @see ilsb_l_delimiter
    const ci_ilkb_l_delimiter     = '{';     // Surround in-line keyed branch  @see ilkb_r_delimiter
    const ci_ilkb_r_delimiter     = '}';     // Surround in-line keyed branch  @see ilkb_l_delimiter

  // Scalar indicators:
    const si_ilus_delimiter       = '\'';    // Surround in-line unescaped scalar ('' escaped ')
    const si_iles_delimiter       = '"';     // Surround in-line escaped scalar
    const si_block_scalar_indic   = '|';     // Block scalar indicator
    const si_folded_scalar_indic  = '>';     // Folded scalar indicator
    const si_strip_chomp_mod      = '-';     // Strip chomp modifier ('|-' or '>-')
    const si_keep_chomp_mod       = '+';     // Keep chomp modifier ('|+' or '>+')

  // Alias indicators:
    const ai_anchor_property      = '&';     // Anchor property
    const ai_alias_indic          = '*';     // Alias indicator

  // Tag Property:
    const tag_property            = '|';     // Tag property

  // Document indicators:
    const di_directive_indic       = '%';    // Directive indicator
    const di_document_header       = '___';  // Document header
    const di_document_terminator   = '...';  // Document terminator

  // Misc indicators:
    const mi_throwaway_com_indic   = ' #';   // Throwaway comment indicator

  // Special Keys:
    const sk_def_val_mapping       = '=';    // Default "value" mapping key
    const sk_merge_mappings        = '<<';   // Merge keys from another mapping

  // Core Types:
    const ct_map                   = '!!map';  // { Hash table, dictionary, mapping }
    const ct_seq                   = '!!seq';  // { List, array, tuple, vector, sequence }
    const ct_str                   = '!!str';  // Unicode string

  // More Types:
    const mt_set                   = '!!set';  // { cherries, plums, apples }
    const mt_omap                  = '!!omap';  // [ one: 1, two: 2 ]

    /**
     * Takes a YAML string and turns it into a PHP array
     *
     * @param string yaml
     * @return array
     */
    public static function load($yaml) {
      $lines = explode("\n", $yaml);

      foreach ($lines as $line) {
        test(yaml::_getLineIndent($line));
        test($line);
      }
    }

    /**
     * Returns the size of a line indentation
     *
     * @param string $line
     * @return int
     */
    private function _getLineIndent($line) {
      preg_match_all('/^([\s]{4}\S)/', $line, $match); // need to re-write regex
      return count($match[0]);
    }
}
?>