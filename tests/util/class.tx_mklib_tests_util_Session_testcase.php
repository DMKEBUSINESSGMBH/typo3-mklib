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
	 * (non-PHPdoc)
	 * @see PHPUnit_Framework_TestCase::setUp()
	 */
	protected function setUp() {
		$this->cookiesBackup = $_COOKIE;
	}

	/**
	 * (non-PHPdoc)
	 * @see PHPUnit_Framework_TestCase::tearDown()
	 */
	protected function tearDown() {
		$_COOKIE = $this->cookiesBackup;
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
}