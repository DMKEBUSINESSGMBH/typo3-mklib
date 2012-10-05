<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_util
 *
 *  Copyright notice
 *
 *  (c) 2011 das MedienKombinat GmbH <kontakt@das-medienkombinat.de>
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

require_once(t3lib_extMgm::extPath('rn_base', 'class.tx_rnbase.php'));
tx_rnbase::load('tx_rnbase_util_Strings');
tx_rnbase::load('tx_mklib_util_Encoding');

/**
 * Class for encodings
 *
 * @package tx_mklib
 * @subpackage tx_mklib_tests
 * @author Michael Wagner <michael.wagner@das-medienkombinat.de>
 */
class tx_mklib_tests_util_Encoding_testcase extends tx_phpunit_testcase {

	/**
	 * Der für die Tests genutzte String
	 * @var string
	 */
	private static $bin = 'ÄäÖöÜüß';
	/**
	 * Dies ist die in Hex umgewandelte Form des Strings
	 * mit der ISO-8859-1 Zeichen codierung.
	 * @var string
	 */
	private static $hexIso88591 = 'c4e4d6f6dcfcdf';
	/**
	 * Dies ist die in Hex umgewandelte Form des Strings
	 * mit der UTF-8 Zeichen codierung.
	 * @var string
	 */
	private static $hexUtf8 = 'c384c3a4c396c3b6c39cc3bcc39f';


	public function test_convert_string_from_ISO_8859_1_to_UTF_8() {
		$string = pack("H*", self::$hexIso88591);

		$this->assertEquals(7, strlen($string),
				'$string ist nicht mit ISO-8859-1 codiert.');

		$string = tx_mklib_util_Encoding::convertEncoding(
				$string, 'UTF-8', 'ISO-8859-1');

		$this->assertEquals(14, strlen($string),
				'$string wurde nicht nach UTF-8 codiert.');
		$this->assertEquals(self::$hexUtf8, bin2hex($string),
				'Der HEX-Wert von $string stimmt nach der codierung nicht.');
	}

	public function test_convert_string_from_UTF_8_to_ISO_8859_1() {
		$string = pack("H*", self::$hexUtf8);

		$this->assertEquals(14, strlen($string),
				'$string ist nicht mit ISO-8859-1 codiert.');

		$string = tx_mklib_util_Encoding::convertEncoding(
				$string, 'ISO-8859-1', 'UTF-8');

		$this->assertEquals(7, strlen($string),
				'$string wurde nicht nach UTF-8 codiert.');
		$this->assertEquals(self::$hexIso88591, bin2hex($string),
				'Der HEX-Wert von $string stimmt nach der codierung nicht.');
	}

	/**
	 *
	 * @depends test_convert_string_from_ISO_8859_1_to_UTF_8
	 */
	public function test_convert_array_from_ISO_8859_1_to_UTF_8() {
		$stringIso = pack("H*", self::$hexIso88591);
		$stringUtf8 = pack("H*", self::$hexUtf8);

		$arrayFrom = array(
			'var' => $stringIso,
			'array' => array(
				'var' => $stringIso,
				'array' => array(
					'var1' => $stringIso,
					'var2' => $stringIso,
				),
			),
		);
		$arrayTo = array(
			'var' => $stringUtf8,
			'array' => array(
				'var' => $stringUtf8,
				'array' => array(
					'var1' => $stringUtf8,
					'var2' => $stringUtf8,
				),
			),
		);

		$arrayFrom = tx_mklib_util_Encoding::convertEncoding(
				$arrayFrom, 'UTF-8', 'ISO-8859-1');

		$this->assertEquals($arrayTo, $arrayFrom,
				'$array wurde nicht richtig nach UTF-8 codiert.');
	}

}

if (defined('TYPO3_MODE')
	&& $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']
		['ext/mklib/tests/util/class.tx_mklib_tests_util_Encoding_testcase.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']
		['ext/mklib/tests/util/class.tx_mklib_tests_util_Encoding_testcase.php']);
}