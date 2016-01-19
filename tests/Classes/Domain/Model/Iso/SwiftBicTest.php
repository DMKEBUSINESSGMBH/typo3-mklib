<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2016 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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
 ***************************************************************/

tx_rnbase::load('tx_rnbase_tests_BaseTestCase');
tx_rnbase::load('Tx_Mklib_Domain_Model_Iso_SwiftBic');

/**
 * iso tests
 *
 * @package tx_mklib
 * @subpackage tx_mklib_tests_util
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class Tx_Mklib_Domain_Model_Iso_SwiftBicTest
	extends tx_rnbase_tests_BaseTestCase
{

	/**
	 * Test the validate method
	 *
	 * @param string $value
	 * @param boolean $valid
	 * @return void
	 *
	 * @group unit
	 * @test
	 * @dataProvider getValidateData
	 */
	public function testValidate($value, $valid)
	{
		$model = Tx_Mklib_Domain_Model_Iso_SwiftBic::getInstance($value);
		self::assertInstanceOf(Tx_Mklib_Domain_Model_Iso_SwiftBic, $model);
		self::assertSame($model->validate(), $valid);
	}

	/**
	 * Gets the array for the testValidate testcase.
	 *
	 * @return array
	 */
	public function getValidateData()
	{
		return array(
			// invalid SwiftBic
			__LINE__ => array(
				'value' => 'CE1EL2LLFFF',
				'valid' => FALSE,
			),
			__LINE__ => array(
				'value' => 'E31DCLLFFF',
				'valid' => FALSE,
			),
			__LINE__ => array(
				'value' => '',
				'valid' => FALSE,
			),
			__LINE__ => array(
				'value' => '  ',
				'valid' => FALSE,
			),
			__LINE__ => array(
				'value' => NULL,
				'valid' => FALSE,
			),
			// valid SwiftBic
			__LINE__ => array(
				'value' => 'RBOSGGSX',
				'valid' => TRUE,
			),
			__LINE__ => array(
				'value' => ' RZTIAT22263',
				'valid' => TRUE,
			),
			__LINE__ => array(
				'value' => 'BCEELULL',
				'valid' => TRUE,
			),
			__LINE__ => array(
				'value' => 'MARKDEFF',
				'valid' => TRUE,
			),
			__LINE__ => array(
				'value' => 'GENODEF1JEV',
				'valid' => TRUE,
			),
			__LINE__ => array(
				'value' => 'UBSWCHZH80A',
				'valid' => TRUE,
			),
			__LINE__ => array(
				'value' => 'CEDELULLXXX',
				'valid' => TRUE,
			),
		);
	}

}
