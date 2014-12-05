<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_tests_util
 *  @author Michael Wagner
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
require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');
tx_rnbase::load('tx_mklib_util_MiscTools');
tx_rnbase::load('tx_mklib_tests_Util');

/**
 * Model util tests
 * @package tx_mklib
 * @subpackage tx_mklib_tests_util
 */
class tx_mklib_tests_util_MiscTools_testcase extends tx_phpunit_testcase {

	/**
	 * setUp() = Extension-Konfiguration speichern
	 */
	public function setUp() {
		tx_mklib_tests_Util::storeExtConf('mklib');
		tx_mklib_tests_Util::storeExtConf('mktest');
	}

	/**
	 * tearDown() = Extension-Konfiguration zurückspielen
	 */
	public function tearDown () {
		tx_mklib_tests_Util::restoreExtConf('mklib');
		tx_mklib_tests_Util::restoreExtConf('mktest');
	}

	/**
	 * Prüfen ob die richtig Extension Konfiguration geliefert wird
	 */
	public function testGetProxyBeUserId(){
		tx_mklib_tests_Util::setExtConfVar('proxyBeUserId', 2, 'mklib');

		$val = tx_mklib_util_MiscTools::getProxyBeUserId();
		$this->assertEquals($val, 2, 'Falscher BE-User geliefert.');
		$this->assertTrue(is_int($val), 'Es wurde kein Integer geliefert.');

		$val = tx_mklib_util_MiscTools::getProxyBeUserId('mktest');
		$this->assertEquals($val, 2, 'Falscher BE-User geliefert.');
		$this->assertTrue(is_int($val), 'Es wurde kein Integer geliefert.');

		$val = tx_mklib_util_MiscTools::getProxyBeUserId('mktest', false);
		$this->assertEquals($val, 0, 'Es wurde ein BE-User geliefert.');

		tx_mklib_tests_Util::setExtConfVar('proxyBeUserId', '5', 'mktest');

		$val = tx_mklib_util_MiscTools::getProxyBeUserId();
		$this->assertEquals($val, 2, 'Falscher BE-User geliefert.');
		$this->assertTrue(is_int($val), 'Es wurde kein Integer geliefert.');


		$val = tx_mklib_util_MiscTools::getProxyBeUserId('mktest');
		$this->assertEquals($val, 5, 'Falscher BE-User geliefert.');
		$this->assertTrue(is_int($val), 'Es wurde kein Integer geliefert.');
	}

	/**
	 * Prüfen ob die richtig Extension Konfiguration geliefert wird
	 */
	public function testGetPicturesUploadPath(){
		tx_mklib_tests_Util::setExtConfVar('picturesUploadPath', 'uploads/tx_mklib', 'mklib');

		$this->assertEquals(tx_mklib_util_MiscTools::getPicturesUploadPath(), 'uploads/tx_mklib', 'Falscher Pfad geliefert.');
		$this->assertEquals(tx_mklib_util_MiscTools::getPicturesUploadPath(array()), 'uploads/tx_mklib', 'Falscher Pfad geliefert.');
		$this->assertEquals(tx_mklib_util_MiscTools::getPicturesUploadPath('mktest'), 'uploads/tx_mklib', 'Falscher Pfad geliefert.');
		$this->assertFalse(tx_mklib_util_MiscTools::getPicturesUploadPath('mktest', false), 'Es wurde ein Pfad geliefert.');

		tx_mklib_tests_Util::setExtConfVar('picturesUploadPath', 'uploads/tx_mktest', 'mktest');

		$this->assertEquals(tx_mklib_util_MiscTools::getPicturesUploadPath(), 'uploads/tx_mklib', 'Falscher Pfad geliefert.');
		$this->assertEquals(tx_mklib_util_MiscTools::getPicturesUploadPath('mktest'), 'uploads/tx_mktest', 'Falscher Pfad geliefert.');
	}

	/**
	 * Prüfen ob die richtig Extension Konfiguration geliefert wird
	 */
	public function testGetPortalPageId(){
		tx_mklib_tests_Util::setExtConfVar('portalPageId', 2, 'mklib');

		$val = tx_mklib_util_MiscTools::getPortalPageId();
		$this->assertEquals($val, 2, 'Falsche Page-ID geliefert.');
		$this->assertTrue(is_int($val), 'Es wurde kein Integer geliefert.');

		$val = tx_mklib_util_MiscTools::getPortalPageId('mktest');
		$this->assertEquals($val, 2, 'Falsche Page-ID geliefert.');
		$this->assertTrue(is_int($val), 'Es wurde kein Integer geliefert.');

		$val = tx_mklib_util_MiscTools::getPortalPageId('mktest', false);
		$this->assertEquals($val, 0, 'Es wurde eine Page-ID geliefert.');

		tx_mklib_tests_Util::setExtConfVar('portalPageId', '5', 'mktest');

		$val = tx_mklib_util_MiscTools::getPortalPageId();
		$this->assertEquals($val, 2, 'Falsche Page-ID geliefert.');
		$this->assertTrue(is_int($val), 'Es wurde kein Integer geliefert.');


		$val = tx_mklib_util_MiscTools::getPortalPageId('mktest');
		$this->assertEquals($val, 5, 'Falsche Page-ID geliefert.');
		$this->assertTrue(is_int($val), 'Es wurde kein Integer geliefert.');

	}


}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tests/util/class.tx_mklib_tests_util_MiscTools_testcase.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tests/util/class.tx_mklib_tests_util_MiscTools_testcase.php']);
}