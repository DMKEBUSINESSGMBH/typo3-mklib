<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_tests_util
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
require_once(t3lib_extMgm::extPath('rn_base', 'class.tx_rnbase.php'));
tx_rnbase::load('tx_mklib_tests_Util');

/**
 * Array util tests
 * @package tx_mklib
 * @subpackage tx_mklib_tests
 */
class tx_mklib_tests_TCA_testcase extends tx_phpunit_testcase {
	
	/**
	 * Wird vor jedem Test aufgerufen.
	 */
	public function setUp(){
		//Die Extension Konfiguration sichern.
		tx_mklib_tests_Util::storeExtConf();
	}
	/**
	 * Wird nach jedem Test aufgerufen.
	 */
	public function tearDown(){
		//Die Extension Konfiguration zurücksetzen.
		tx_mklib_tests_Util::restoreExtConf();
	}
	
	/**
	 * Setzt die TCA zurück und lädt die ext_tables.php erneut
	 */
	private static function loadExtTables(){
		global $TCA;
		unset($TCA['tx_mklib_wordlist']);
		require(t3lib_extMgm::extPath('mklib', 'tca/ext_tables.php'));
	}
	
	/**
	 *	Testen, ob die Wordlist Tabelle in der TCA gesetzt wurde.
	 */
	public function testSkipInkludingTableWordlist(){
		global $TCA;
		
		tx_mklib_tests_Util::setExtConfVar('tableWordlist', 0);
		self::loadExtTables();
		
		$tableWordlist = tx_mklib_util_MiscTools::getExtensionValue('tableWordlist');
		
		$this->assertEquals(0, intval($tableWordlist), 'Die Extension Konfiguration tableWordlist ist falsch gesetzt.');
		$this->assertFalse(array_key_exists('tx_mklib_wordlist',$TCA), 'Die TCA für die Wordlist Tabelle wurde geladen.');
	}
	/**
	 *	Testen, ob die Wordlist Tabelle in der TCA gesetzt wurde.
	 */
	public function testIncludeTableWordlist(){
		global $TCA;
		
		tx_mklib_tests_Util::setExtConfVar('tableWordlist', 1);
		self::loadExtTables();
		
		$tableWordlist = tx_mklib_util_MiscTools::getExtensionValue('tableWordlist');
		
		$this->assertEquals(1, intval($tableWordlist), 'Die Extension Konfiguration tableWordlist ist falsch gesetzt');
		$this->assertTrue(array_key_exists('tx_mklib_wordlist',$TCA), 'Die TCA für die Wordlist Tabelle wurde nicht geladen.');
		$this->assertTrue(array_key_exists('ctrl',$TCA['tx_mklib_wordlist']), 'Die TCA für die Wordlist Tabelle wurde nicht richtig geladen.');
	}
	
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/tests/util/class.tx_mklib_tests_TCA_testcase.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/tests/util/class.tx_mklib_tests_TCA_testcase.php']);
}