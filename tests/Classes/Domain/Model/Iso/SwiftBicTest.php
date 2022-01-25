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

/**
 * iso tests.
 *
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class Tx_Mklib_Domain_Model_Iso_SwiftBicTest extends \Sys25\RnBase\Testing\BaseTestCase
{
    /**
     * Test the validate method.
     *
     * @param string $value
     * @param bool   $valid
     *
     * @group unit
     * @test
     * @dataProvider getValidateData
     */
    public function testValidate($value, $valid)
    {
        $model = Tx_Mklib_Domain_Model_Iso_SwiftBic::getInstance($value);
        self::assertInstanceOf(Tx_Mklib_Domain_Model_Iso_SwiftBic::class, $model);
        self::assertSame($model->validate(), $valid);
    }

    /**
     * Gets the array for the testValidate testcase.
     *
     * @return array
     */
    public function getValidateData()
    {
        return [
            // invalid SwiftBic
            __LINE__ => [
                'value' => 'CE1EL2LLFFF',
                'valid' => false,
            ],
            __LINE__ => [
                'value' => 'E31DCLLFFF',
                'valid' => false,
            ],
            __LINE__ => [
                'value' => '',
                'valid' => false,
            ],
            __LINE__ => [
                'value' => '  ',
                'valid' => false,
            ],
            __LINE__ => [
                'value' => null,
                'valid' => false,
            ],
            // valid SwiftBic
            __LINE__ => [
                'value' => 'RBOSGGSX',
                'valid' => true,
            ],
            __LINE__ => [
                'value' => ' RZTIAT22263',
                'valid' => true,
            ],
            __LINE__ => [
                'value' => 'BCEELULL',
                'valid' => true,
            ],
            __LINE__ => [
                'value' => 'MARKDEFF',
                'valid' => true,
            ],
            __LINE__ => [
                'value' => 'GENODEF1JEV',
                'valid' => true,
            ],
            __LINE__ => [
                'value' => 'UBSWCHZH80A',
                'valid' => true,
            ],
            __LINE__ => [
                'value' => 'CEDELULLXXX',
                'valid' => true,
            ],
        ];
    }
}
