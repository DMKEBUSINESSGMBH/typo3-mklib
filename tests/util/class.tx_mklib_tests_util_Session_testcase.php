<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_tests_util
 *  @author Hannes Bochmann
 *
 *  Copyright notice
 *
 *  (c) 2010 Hannes Bochmann <hannes.bochmann@das-medienkombinat.de>
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
	}

	/**
	 * (non-PHPdoc)
	 * @see PHPUnit_Framework_TestCase::tearDown()
	 */
	protected function tearDown() {
		$_COOKIE = $this->cookiesBackup;
		if(isset($GLOBALS['TSFE']->fe_user)) {
			$GLOBALS['TSFE']->fe_user = $this->feserBackUp;
		}
	}

	/**
	 * @group unit
	 * @dataProvider getCookies
	 */
	public function testAreCookiesActivatedInFrontend(
		$cookies, $expectedReturnValue
	){
		$_COOKIE = $cookies;
		$this->assertEquals(
			$expectedReturnValue, tx_mklib_util_Session::areCookiesActivatedInFrontend(),
			'falscher return'
		);
	}

	/**
	 * @return array
	 */
	public function getCookies(){
		return array(
			array(array('fe_typo_user' => ''), true),
			array(array('fe_typo_user' => '123'), true),
			array(array(), false)
		);
	}

	/**
	 * @group unit
	 */
	public function testSetSessionIdSetsIdAndEmptiesSessionData(){
		tx_mklib_tests_Util::prepareTSFE(array('initFEuser' => true));

		$GLOBALS['TSFE']->fe_user->id = 123;
		$GLOBALS['TSFE']->fe_user->sesData = array('something');

		tx_mklib_util_Session::setSessionId(456);

		$this->assertEquals(
			456, $GLOBALS['TSFE']->fe_user->id,
			'falsche id'
		);

		$this->assertEquals(
			array(), $GLOBALS['TSFE']->fe_user->sesData,
			'session data nicht leer'
		);
	}

	/**
	 * @group unit
	 */
	public function testSetSessionIdCallsFetchSessionDataOnFeUser(){
		tx_mklib_tests_Util::prepareTSFE(array('initFEuser' => true));

		$GLOBALS['TSFE']->fe_user = $this->getMock('tslib_feUserAuth', array('fetchSessionData'));
		$GLOBALS['TSFE']->fe_user->expects($this->once())
			->method('fetchSessionData');

		tx_mklib_util_Session::setSessionId(456);
	}
}