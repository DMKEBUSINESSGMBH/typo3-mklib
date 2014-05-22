<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_util
 *  @author Michael Wagner
 *
 *  Copyright notice
 *
 *  (c) 2011 Michael Wagner <michael.wagner@das-medienkombinat.de>
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
require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');
tx_rnbase::load('tx_mklib_util_Var');
	
/**
 * Generic form view test
 * @package tx_mklib
 * @subpackage tx_mklib_tests_util
 */
class tx_mklib_tests_util_Var_testcase extends tx_phpunit_testcase {
	
	/**
	 * isTrue testen
	 */
	public function testIsTrueVal(){
		$this->assertTrue(tx_mklib_util_Var::isTrueVal(true));
		$this->assertTrue(tx_mklib_util_Var::isTrueVal('true'));
		$this->assertTrue(tx_mklib_util_Var::isTrueVal('TrUe'));
		$this->assertTrue(tx_mklib_util_Var::isTrueVal('1'));
		$this->assertTrue(tx_mklib_util_Var::isTrueVal(1));
		$this->assertFalse(tx_mklib_util_Var::isTrueVal(false));
		$this->assertFalse(tx_mklib_util_Var::isTrueVal('false'));
		$this->assertFalse(tx_mklib_util_Var::isTrueVal('0'));
		$this->assertFalse(tx_mklib_util_Var::isTrueVal(0));
	}
	/**
	 * isFalseVal testen
	 */
	public function testIsFalseVal(){
		$this->assertTrue(tx_mklib_util_Var::isFalseVal(false));
		$this->assertTrue(tx_mklib_util_Var::isFalseVal('false'));
		$this->assertTrue(tx_mklib_util_Var::isFalseVal('0'));
		$this->assertTrue(tx_mklib_util_Var::isFalseVal(0));
		$this->assertFalse(tx_mklib_util_Var::isFalseVal(true));
		$this->assertFalse(tx_mklib_util_Var::isFalseVal('true'));
		$this->assertFalse(tx_mklib_util_Var::isFalseVal('TrUe'));
		$this->assertFalse(tx_mklib_util_Var::isFalseVal('1'));
		$this->assertFalse(tx_mklib_util_Var::isFalseVal(1));
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tests/util/class.tx_mklib_tests_util_Var_testcase.php']) {
  include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tests/util/class.tx_mklib_tests_util_Var_testcase.php']);
}