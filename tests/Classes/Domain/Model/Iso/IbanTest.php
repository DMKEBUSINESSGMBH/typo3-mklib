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
tx_rnbase::load('Tx_Mklib_Domain_Model_Iso_Iban');

/**
 * iban tests.
 *
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class Tx_Mklib_Domain_Model_Iso_IbanTest extends tx_rnbase_tests_BaseTestCase
{
    /**
     * Test the validate method.
     *
     * @param string $iban
     * @param bool   $valid
     *
     * @group unit
     * @test
     * @dataProvider getValidateData
     */
    public function testValidate($iban, $valid)
    {
        if (!function_exists('bcmod')) {
            self::markTestSkipped('BC-Math module not installed.');
        }

        $model = Tx_Mklib_Domain_Model_Iso_Iban::getInstance($iban);
        self::assertInstanceOf(Tx_Mklib_Domain_Model_Iso_Iban, $model);
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
            // invalid ibans
            __LINE__ => array(
                'iban' => 'AD1200012030200359100120',
                'valid' => false,
            ),
            __LINE__ => array(
                'iban' => 'AT611904300234573221',
                'valid' => false,
            ),
            __LINE__ => array(
                'iban' => 'BA39129007940028494',
                'valid' => false,
            ),
            __LINE__ => array(
                'iban' => 'BE685390047034',
                'valid' => false,
            ),
            __LINE__ => array(
                'iban' => 'AA611904300234573201',
                'valid' => false,
            ),
            // valid ibans
            __LINE__ => array(
                'iban' => 'AT611904300234573201',
                'valid' => true,
            ),
            __LINE__ => array(
                'iban' => ' HU42117730161111101800000000',
                'valid' => true,
            ),
            __LINE__ => array(
                'iban' => 'MK07250120000058984',
                'valid' => true,
            ),
            __LINE__ => array(
                'iban' => 'MT84MALT011000012345MTLCAST001S',
                'valid' => true,
            ),
            __LINE__ => array(
                'iban' => 'BE68539007547034',
                'valid' => true,
            ),
            __LINE__ => array(
                'iban' => 'NO9386011117947',
                'valid' => true,
            ),
            __LINE__ => array(
                'iban' => 'SM86U0322509800000000270100',
                'valid' => true,
            ),
            __LINE__ => array(
                'iban' => 'LV80BANK0000435195001 ',
                'valid' => true,
            ),
            __LINE__ => array(
                'iban' => 'GB29NWBK60161331926819',
                'valid' => true,
            ),
            __LINE__ => array(
                'iban' => 'TR330006100519786457841326',
                'valid' => true,
            ),
            __LINE__ => array(
                'iban' => 'IE29AIBK93115212345678',
                'valid' => true,
            ),
            __LINE__ => array(
                'iban' => 'DE21301204000000015228',
                'valid' => true,
            ),
        );
    }
}
