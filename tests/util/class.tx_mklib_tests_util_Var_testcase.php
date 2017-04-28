<?php
/**
 * @package tx_mklib
 * @subpackage tx_mklib_util
 * @author Michael Wagner
 *
 *  Copyright notice
 *
 *  (c) 2011 Michael Wagner <michael.wagner@dmk-ebusiness.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 */

/**
 * benötigte Klassen einbinden
 */

tx_rnbase::load('tx_mklib_util_Var');
    
/**
 * Generic form view test
 * @package tx_mklib
 * @subpackage tx_mklib_tests_util
 */
class tx_mklib_tests_util_Var_testcase extends Tx_Phpunit_TestCase
{
    
    /**
     * isTrue testen
     */
    public function testIsTrueVal()
    {
        self::assertTrue(tx_mklib_util_Var::isTrueVal(true));
        self::assertTrue(tx_mklib_util_Var::isTrueVal('true'));
        self::assertTrue(tx_mklib_util_Var::isTrueVal('TrUe'));
        self::assertTrue(tx_mklib_util_Var::isTrueVal('1'));
        self::assertTrue(tx_mklib_util_Var::isTrueVal(1));
        self::assertFalse(tx_mklib_util_Var::isTrueVal(false));
        self::assertFalse(tx_mklib_util_Var::isTrueVal('false'));
        self::assertFalse(tx_mklib_util_Var::isTrueVal('0'));
        self::assertFalse(tx_mklib_util_Var::isTrueVal(0));
    }
    /**
     * isFalseVal testen
     */
    public function testIsFalseVal()
    {
        self::assertTrue(tx_mklib_util_Var::isFalseVal(false));
        self::assertTrue(tx_mklib_util_Var::isFalseVal('false'));
        self::assertTrue(tx_mklib_util_Var::isFalseVal('0'));
        self::assertTrue(tx_mklib_util_Var::isFalseVal(0));
        self::assertFalse(tx_mklib_util_Var::isFalseVal(true));
        self::assertFalse(tx_mklib_util_Var::isFalseVal('true'));
        self::assertFalse(tx_mklib_util_Var::isFalseVal('TrUe'));
        self::assertFalse(tx_mklib_util_Var::isFalseVal('1'));
        self::assertFalse(tx_mklib_util_Var::isFalseVal(1));
    }
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tests/util/class.tx_mklib_tests_util_Var_testcase.php']) {
    include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tests/util/class.tx_mklib_tests_util_Var_testcase.php']);
}
