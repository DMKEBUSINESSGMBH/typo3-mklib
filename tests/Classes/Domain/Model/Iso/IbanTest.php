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

use PHPUnit\Framework\Attributes\DataProvider;

/**
 * iban tests.
 *
 * @author Michael Wagner
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class Tx_Mklib_Domain_Model_Iso_IbanTest extends Sys25\RnBase\Testing\BaseTestCase
{
    /**
     * Test the validate method.
     */
    #[DataProvider('getValidateData')]
    public function testValidate(string $iban, bool $valid): void
    {
        if (!function_exists('bcmod')) {
            self::markTestSkipped('BC-Math module not installed.');
        }

        $model = Tx_Mklib_Domain_Model_Iso_Iban::getInstance($iban);
        self::assertInstanceOf(Tx_Mklib_Domain_Model_Iso_Iban::class, $model);
        self::assertSame($model->validate(), $valid);
    }

    /**
     * Gets the array for the testValidate testcase.
     */
    public static function getValidateData(): array
    {
        return [
            'invalid_iban' => [
                'iban' => 'AD1200012030200359100120',
                'valid' => false,
            ],
            'valid_iban_1' => [
                'iban' => 'AT611904300234573201',
                'valid' => true,
            ],
            'valid_iban_2' => [
                'iban' => 'DE21301204000000015228',
                'valid' => true,
            ],
        ];
    }
}
