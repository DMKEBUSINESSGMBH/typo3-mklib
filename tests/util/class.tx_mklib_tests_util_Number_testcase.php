<?php
/**
 * @package tx_mklib
 * @subpackage tx_mklib_util
 *
 * (c) 2013 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 */

tx_rnbase::load('tx_mklib_util_Number');

/**
 * Numeric Util Tests
 *
 * @package tx_mklib
 * @subpackage tx_mklib_tests_util
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_mklib_tests_util_Number_testcase extends tx_phpunit_testcase {

	private $oldLocal = NULL;

	public function setUp() {
		parent::setUp();
		$this->oldLocal = setlocale(LC_ALL, 0);
	}
	public function tearDown() {
		parent::tearDown();
		setlocale(LC_ALL, $this->oldLocal);
	}

	/**
	 *
	 * @dataProvider providerFloatVal
	 */
	public function testFloatVal($expected, $actual, $config) {
		if (!is_array($config)) $config = array();

		// bei einem normalen float sollte nun eine Kommazahl herauskommen.
// 		$this->assertEquals('5,43', (string) (float) '5.43');

		$this->assertEquals($expected, tx_mklib_util_Number::floatVal($actual, $config));
	}
	/**
	 *
	 * @dataProvider providerFloatVal
	 */
	public function testFloatValLcDe($expected, $actual, $config) {
		// Locale auf deutsch stellen.
		// Damit sind Beispielsweise die Dezimaltrennzeichen falsch (,anstatt.)
		setlocale(LC_ALL, 'de_DE@euro', 'de_DE', 'deu_deu', 'de', 'ge');

		$this->testFloatVal($expected, $actual, $config);
	}

	public function providerFloatVal() {
		return array(
			// über die parseFloat, sollte genau das herauskommen, was wir benötigen
			// ein Float mit einem Punkt als Dezimaltrennzeichen.
			'Line:'.__LINE__ => array('5.43', '5.43', array()),
			'Line:'.__LINE__ => array('-5.43', '-5.43', array()),
			'Line:'.__LINE__ => array('5.43', '5,43', array()),
			'Line:'.__LINE__ => array('-5.43', '-5,43', array()),
			// hierzu muss erst der Todo aus parseFloat abgearbeidet werden.
// 			'Line:'.__LINE__ => array('5435.55', '5.435,55', array()),
// 			'Line:'.__LINE__ => array('5435.55', '5,435.55', array()),
			// Jetzt wollen wir eine Pipe als Dezimaltrennzeichen, nur so zum Spaß ;)
			'Line:'.__LINE__ => array('5|43', '5.43', array('decimal_point'=>'|')),
		);
	}

}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tests/util/class.tx_mklib_tests_util_Number_testcase.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tests/util/class.tx_mklib_tests_util_Number_testcase.php']);
}