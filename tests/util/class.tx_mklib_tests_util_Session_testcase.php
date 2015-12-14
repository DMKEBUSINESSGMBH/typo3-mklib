<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_tests_util
 *  @author Hannes Bochmann
 *
 *  Copyright notice
 *
 *  (c) 2010 Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
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
tx_rnbase::load('tx_mklib_util_Session');

/**
 * Generic form view test
 * @package tx_mklib
 * @subpackage tx_mklib_tests_util
 */
class tx_mklib_tests_util_Session_testcase extends tx_phpunit_testcase {

	/**
	 * @var array
	 */
	private $cookiesBackup = array();

	/**
	 * @var array
	 */
	private $feUserBackUp = array();

	/**
	 * (non-PHPdoc)
	 * @see PHPUnit_Framework_TestCase::setUp()
	 */
	protected function setUp() {
		$this->cookiesBackup = $_COOKIE;
		$this->feUserBackUp = $GLOBALS['TSFE']->fe_user;

		tx_mklib_tests_Util::prepareTSFE(array('initFEuser' => TRUE, 'force' => TRUE));
	}

	/**
	 * (non-PHPdoc)
	 * @see PHPUnit_Framework_TestCase::tearDown()
	 */
	protected function tearDown() {
		$_COOKIE = $this->cookiesBackup;
		tx_mklib_util_Session::removeSessionValue('checkCookieIsSet');
		if(isset($GLOBALS['TSFE']->fe_user)) {
			$GLOBALS['TSFE']->fe_user = $this->feserBackUp;
		}

	}

	/**
	 * @group unit
	 * @dataProvider getCookies
	 */
	public function testAreCookiesActivated($cookies, $expectedReturnValue, $setCheckCookieSetSessionValue){
		if ($setCheckCookieSetSessionValue) {
			tx_mklib_util_Session::setSessionValue('checkCookieIsSet', TRUE);
		}
		$_COOKIE = $cookies;
		$this->assertEquals(
			$expectedReturnValue, tx_mklib_util_Session::areCookiesActivated(),
			'falscher return'
		);
	}

	/**
	 * @return array
	 */
	public function getCookies(){
		return array(
			array(array('fe_typo_user' => ''), TRUE, FALSE),
			array(array('fe_typo_user' => '123'), TRUE, FALSE),
			array(array(), FALSE, TRUE),
			array(array('fe_typo_user' => '123'), TRUE, TRUE),
		);
	}

	/**
	 * @group unit
	 */
	public function testSetSessionIdSetsIdAndEmptiesSessionData(){
		$oldRandomSessionId = uniqid();
		$GLOBALS['TSFE']->fe_user->id = $oldRandomSessionId;
		$GLOBALS['TSFE']->fe_user->sesData = array('something');

		$newRandomSessionId = uniqid();
		tx_mklib_util_Session::setSessionId($newRandomSessionId);

		$this->assertEquals(
			$newRandomSessionId, $GLOBALS['TSFE']->fe_user->id,
			'falsche neue session id'
		);

		$this->assertEquals(
			array(), $GLOBALS['TSFE']->fe_user->sesData,
			'session data für neue id nicht leer'
		);
	}

	/**
	 * @group unit
	 */
	public function testSetSessionIdCallsFetchSessionDataOnFeUser(){
		$GLOBALS['TSFE']->fe_user = $this->getMock(
			tx_rnbase_util_Typo3Classes::getFrontendUserAuthenticationClass(), array('fetchSessionData')
		);
		$GLOBALS['TSFE']->fe_user->expects($this->once())
			->method('fetchSessionData');

		tx_mklib_util_Session::setSessionId(456);
	}

	/**
	 * @group unit
	 */
	public function testSetStoreAndGetSessionValue(){
		tx_mklib_util_Session::setSessionValue('mklibTest', 'testValue');
		tx_mklib_util_Session::storeSessionData();
		$this->assertEquals(
			'testValue', tx_mklib_util_Session::getSessionValue('mklibTest')
		);
	}

	/**
	 * @group unit
	 */
	public function testSetStoreAndGetSessionValueWhenSessionIdSet() {
		$sessionIdBackup = tx_mklib_util_Session::getSessionId();
		// erstmal Session ID wechseln und Wert setzen
		$newSessionId = $this->getRandomHexString();
		tx_mklib_util_Session::setSessionId($newSessionId);
		tx_mklib_util_Session::setSessionValue('mklibTest', 'testValue');
		tx_mklib_util_Session::storeSessionData();

		// dann wir eigentliche Session ID Wert setzen
		tx_mklib_util_Session::setSessionId($sessionIdBackup);
		tx_mklib_util_Session::setSessionValue('mklibTest', 'initialTestValue');
		tx_mklib_util_Session::storeSessionData();

		// dann wieder auf neue Session ID wechseln und prüfen ob
		// Werte korrekt geliefert wernde
		tx_mklib_util_Session::setSessionId($newSessionId);

		$this->assertEquals(
			'testValue', tx_mklib_util_Session::getSessionValue('mklibTest')
		);

		tx_mklib_util_Session::setSessionId($sessionIdBackup);
	}

	/**
	 * @return string
	 */
	protected function getRandomHexString() {
		return tx_rnbase_util_TYPO3::isTYPO60OrHigher() ?
			\TYPO3\CMS\Core\Utility\GeneralUtility::getRandomHexString(32) :
			t3lib_div::getRandomHexString(32);
	}
}
