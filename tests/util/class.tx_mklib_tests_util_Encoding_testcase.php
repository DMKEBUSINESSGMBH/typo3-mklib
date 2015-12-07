<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_util
 *
 *  Copyright notice
 *
 *  (c) 2011 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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

require_once(tx_rnbase_util_Extensions::extPath('rn_base', 'class.tx_rnbase.php'));
tx_rnbase::load('tx_rnbase_util_Strings');
tx_rnbase::load('tx_mklib_util_Encoding');

/**
 * Class for encodings
 *
 * @package tx_mklib
 * @subpackage tx_mklib_tests
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
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

	/**
	 *
	 * @group integration
	 */
	public function test_is_encoding() {
		$strUtf8 = pack("H*", self::$hexUtf8);
		$strIso88591 = pack("H*", self::$hexIso88591);

		if (false)
			echo '<pre>'.var_export(array(
				'iso88591' => array(
					'string' => $strIso88591,
					'utf8 level' => tx_rnbase_util_Strings::isUtf8String($strIso88591),
					'utf8 encoding' => tx_mklib_util_Encoding::detectUtfEncoding($strIso88591),
					'bytelength' => mb_strlen($strIso88591, '8bit'),
					'bin2hex' => bin2hex($strIso88591),
					'is utf8' => tx_mklib_util_Encoding::isEncoding($strIso88591, 'UTF-8'),
					'is iso88591' => tx_mklib_util_Encoding::isEncoding($strIso88591, 'ISO-8859-1'),
				),
				'utf8' => array(
					'string' => $strUtf8,
					'utf8 level' => tx_rnbase_util_Strings::isUtf8String($strUtf8),
					'utf8 encoding' => tx_mklib_util_Encoding::detectUtfEncoding($strUtf8),
					'bytelength' => mb_strlen($strUtf8, '8bit'),
					'bin2hex' => bin2hex($strUtf8),
					'is utf8' => tx_mklib_util_Encoding::isEncoding($strUtf8, 'UTF-8'),
					'is iso88591' => tx_mklib_util_Encoding::isEncoding($strUtf8, 'ISO-8859-1'),
				),
				'DEBUG: '.__FILE__.'&'.__METHOD__.' Line: '.__LINE__
			),true).'</pre>'; // @TODO: remove me

		$this->assertTrue(
			tx_mklib_util_Encoding::isEncoding($strIso88591, 'ISO-8859-1'),
			'$strIso88591 ist NICHT ISO-8859-1'
		);
		$this->assertFalse(
			tx_mklib_util_Encoding::isEncoding($strIso88591, 'UTF-8'),
			'$strIso88591 IST UTF-8'
		);

		$this->assertTrue(
			tx_mklib_util_Encoding::isEncoding($strUtf8, 'UTF-8'),
			'$strUtf8 ist NICHT UTF-8'
		);
		$this->assertFalse(
			tx_mklib_util_Encoding::isEncoding($strUtf8, 'ISO-8859-1'),
			'$strUtf8 IST ISO-8859-1'
		);

	}

	/**
	 *
	 * @group integration
	 */
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

	/**
	 *
	 * @group integration
	 */
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
	 * @group integration
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

	/**
	 *
	 * @depends test_convert_array_from_ISO_8859_1_to_UTF_8
	 * @group integration
	 */
	public function test_convert_ArrayObject_from_ISO_8859_1_to_UTF_8() {
		$stringIso = pack("H*", self::$hexIso88591);
		$stringUtf8 = pack("H*", self::$hexUtf8);

		$modelFrom = new ArrayObject(
			array(
				'uid' => 1,
				'title' => $stringIso,
				'description' => $stringIso,
			)
		);
		$modelTo = new ArrayObject(
			array(
				'uid' => 1,
				'title' => $stringUtf8,
				'description' => $stringUtf8,
			)
		);

		$modelFrom = tx_mklib_util_Encoding::convertEncoding(
				$modelFrom, 'UTF-8', 'ISO-8859-1');

		$this->assertEquals($modelTo->getArrayCopy(), $modelFrom->getArrayCopy(),
				'$array wurde nicht richtig nach UTF-8 codiert.');
	}

	/**
	 *
	 * @depends test_convert_array_from_ISO_8859_1_to_UTF_8
	 * @group integration
	 */
	public function test_convert_model_from_ISO_8859_1_to_UTF_8() {
		$stringIso = pack("H*", self::$hexIso88591);
		$stringUtf8 = pack("H*", self::$hexUtf8);

		$modelFrom = tx_rnbase::makeInstance(
			'tx_rnbase_model_base',
			array(
				'uid' => 1,
				'title' => $stringIso,
				'description' => $stringIso,
			)
		);
		$modelTo = tx_rnbase::makeInstance(
			'tx_rnbase_model_base',
			array(
				'uid' => 1,
				'title' => $stringUtf8,
				'description' => $stringUtf8,
			)
		);

		$modelFrom = tx_mklib_util_Encoding::convertEncoding(
				$modelFrom, 'UTF-8', 'ISO-8859-1');

		$this->assertEquals($modelTo->record, $modelFrom->record,
				'$array wurde nicht richtig nach UTF-8 codiert.');
	}

	/**
	 *
	 * @depends test_convert_array_from_ISO_8859_1_to_UTF_8
	 * @group integration
	 */
	public function test_convert_array_with_model_from_ISO_8859_1_to_UTF_8() {
		$stringIso = pack("H*", self::$hexIso88591);
		$stringUtf8 = pack("H*", self::$hexUtf8);

		$modelTo = tx_rnbase::makeInstance(
			'tx_rnbase_model_base',
			array(
				'uid' => 1,
				'title' => $stringUtf8,
				'description' => $stringUtf8,
			)
		);
		$data = array(
			'one' => tx_rnbase::makeInstance(
					'tx_rnbase_model_base',
					array(
						'uid' => 1,
						'title' => $stringIso,
						'description' => $stringIso,
					)
				),
			'twoe' => tx_rnbase::makeInstance(
					'tx_rnbase_model_base',
					array(
						'uid' => 1,
						'title' => $stringIso,
						'description' => $stringIso,
					)
				),
		);

		$data = tx_mklib_util_Encoding::convertEncoding(
				$data, 'UTF-8', 'ISO-8859-1');

		$this->assertEquals($modelTo->record, $data['one']->record,
				'$array wurde nicht richtig nach UTF-8 codiert.');
		$this->assertEquals($modelTo->record, $data['twoe']->record,
				'$array wurde nicht richtig nach UTF-8 codiert.');
	}

	/**
	 * @expectedException     InvalidArgumentException
	 * @expectedExceptionCode 4005
	 * @group integration
	 */
	public function test_convert_model_throws_exception() {
		// aufruf mittels falschem object
		tx_mklib_util_Encoding::convertEncoding(
			new Exception(),
			'UTF-8', 'ISO-8859-1'
		);
	}

}

if (defined('TYPO3_MODE')
	&& $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']
		['ext/mklib/tests/util/class.tx_mklib_tests_util_Encoding_testcase.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']
		['ext/mklib/tests/util/class.tx_mklib_tests_util_Encoding_testcase.php']);
}