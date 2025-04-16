<?php

/*
 * Copyright notice
 *
 * (c) DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
 * All rights reserved
 *
 * This file is part of the "mklib" Extension for TYPO3 CMS.
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GNU Lesser General Public License can be found at
 * www.gnu.org/licenses/lgpl.html
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 */

/**
 * iso tests.
 *
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class Tx_Mklib_Domain_Model_Iso_SwiftBicTest extends Sys25\RnBase\Testing\BaseTestCase
{
    /**
     * Test the validate method.
     */
    #[PHPUnit\Framework\Attributes\DataProvider('getValidateData')]
    public function testValidate(?string $value, bool $valid): void
    {
        $model = Tx_Mklib_Domain_Model_Iso_SwiftBic::getInstance($value);
        self::assertInstanceOf(Tx_Mklib_Domain_Model_Iso_SwiftBic::class, $model);
        self::assertSame($model->validate(), $valid);
    }

    /**
     * Gets the array for the testValidate testcase.
     */
    public static function getValidateData(): array
    {
        return [
            'invalid_bic' => [
                'value' => 'CE1EL2LLFFF',
                'valid' => false,
            ],
            'valid_bic_1' => [
                'value' => 'RBOSGGSX',
                'valid' => true,
            ],
            'valid_bic_2' => [
                'value' => 'CEDELULLXXX',
                'valid' => true,
            ],
        ];
    }
}
