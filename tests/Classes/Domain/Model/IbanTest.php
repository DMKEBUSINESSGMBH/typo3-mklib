<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_tests_util
 *  @author Michael Wagner
 *
 *  Copyright notice
 *
 *  (c) 2016 Michael Wagner
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

tx_rnbase::load('tx_rnbase_tests_BaseTestCase');
tx_rnbase::load('Tx_Mklib_Domain_Model_Iban');

/**
 * iban tests
 *
 * @package tx_mklib
 * @subpackage tx_mklib_tests_util
 */
class Tx_Mklib_Domain_Model_IbanTest
	extends tx_rnbase_tests_BaseTestCase
{

	/**
	 * Test the validate method
	 *
	 * @param string $iban
	 * @param boolean $valid
	 * @return void
	 *
	 * @group unit
	 * @test
	 * @dataProvider getValidateData
	 */
	public function testValidate($iban, $valid)
	{
		$model = Tx_Mklib_Domain_Model_Iban::getInstance($iban);
		$this->assertSame($model->validate(), $valid);
	}

	/**
	 * Gets the array for the testValidate testcase.
	 *
	 * @return array
	 */
	public function getValidateData()
	{
		return array(
			// invalid ibans
			__LINE__ => array(
				'iban' => 'AD1200012030200359100120',
				'valid' => FALSE,
			),
			__LINE__ => array(
				'iban' => 'AT611904300234573221',
				'valid' => FALSE,
			),
			__LINE__ => array(
				'iban' => 'BA39129007940028494',
				'valid' => FALSE,
			),
			__LINE__ => array(
				'iban' => 'BE685390047034',
				'valid' => FALSE,
			),
			__LINE__ => array(
				'iban' => 'AA611904300234573201',
				'valid' => FALSE,
			),
			// valid ibans
			__LINE__ => array(
				'iban' => 'AT611904300234573201',
				'valid' => TRUE,
			),
			__LINE__ => array(
				'iban' => ' HU42117730161111101800000000',
				'valid' => TRUE,
			),
			__LINE__ => array(
				'iban' => 'MK07250120000058984',
				'valid' => TRUE,
			),
			__LINE__ => array(
				'iban' => 'MT84MALT011000012345MTLCAST001S',
				'valid' => TRUE,
			),
			__LINE__ => array(
				'iban' => 'BE68539007547034',
				'valid' => TRUE,
			),
			__LINE__ => array(
				'iban' => 'NO9386011117947',
				'valid' => TRUE,
			),
			__LINE__ => array(
				'iban' => 'SM86U0322509800000000270100',
				'valid' => TRUE,
			),
			__LINE__ => array(
				'iban' => 'LV80BANK0000435195001 ',
				'valid' => TRUE,
			),
			__LINE__ => array(
				'iban' => 'GB29NWBK60161331926819',
				'valid' => TRUE,
			),
			__LINE__ => array(
				'iban' => 'TR330006100519786457841326',
				'valid' => TRUE,
			),
			__LINE__ => array(
				'iban' => 'IE29AIBK93115212345678',
				'valid' => TRUE,
			),
			__LINE__ => array(
				'iban' => 'DE21301204000000015228',
				'valid' => TRUE,
			),
		);
	}

}
