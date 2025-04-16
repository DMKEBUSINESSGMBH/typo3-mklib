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
 * Generic form view test.
 *
 * @author Hannes Bochmann
 * @author Michael Wagner
 */
class tx_mklib_tests_srv_FinanceTest extends Sys25\RnBase\Testing\BaseTestCase
{
    /**
     * This method is called before the first test of this test class is run.
     */
    public static function setUpBeforeClass(): void
    {
        if (TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('static_info_tables')) {
            self::prepareLegacyTypo3DbGlobal();
        }
    }

    public function testGetCurrency(): void
    {
        $oSrv = tx_mklib_util_ServiceRegistry::getFinanceService();
        $oCurrency = $oSrv->getCurrency();

        self::assertTrue(is_object($oCurrency));
        self::assertEquals('tx_mklib_model_Currency', $oCurrency::class);
    }

    /**
     * Prüft ob richtig gerundet wird.
     */
    public function testRoundDouble(): void
    {
        $oSrv = tx_mklib_util_ServiceRegistry::getFinanceService();
        self::assertEquals(2.54, $oSrv->roundUpDouble(2.5316, 2, false), 'Die Zahl wurde nicht korrekt gerundet!');
        self::assertEquals(2.54, $oSrv->roundUpDouble(2.5356, 2, false), 'Die Zahl wurde nicht korrekt gerundet!');
        self::assertEquals(2.536, $oSrv->roundUpDouble(2.5356, 3, false), 'Die Zahl wurde nicht korrekt gerundet!');
        self::assertEquals('2,20', $oSrv->roundUpDouble('2.2000', 2, true, ','), 'Die Zahl wurde nicht korrekt gerundet!');
    }

    /**
     * test the getJoins method.
     */
    #[PHPUnit\Framework\Attributes\DataProvider('getValidateVatRegNoData')]
    public function testValidateVatRegNo(string|Sys25\RnBase\Domain\Model\BaseModel $country, string $vatregno, bool $expected): void
    {
        if (!defined('TAB')) {
            define('TAB', '');
        }

        $srv = tx_mklib_util_ServiceRegistry::getFinanceService();
        self::assertSame(
            $expected,
            $srv->validateVatRegNo($country, $vatregno)
        );
    }

    /**
     * Liefert die Daten für den testValidateVatRegNo testcase.
     */
    public static function getValidateVatRegNoData(): array
    {
        return [
            1 => ['country' => TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('static_info_tables') ? '54' : 'de', 'vatregno' => 'DE123456789', 'expected' => true],
            // test country model
            2 => ['country' => Sys25\RnBase\Domain\Model\BaseModel::getInstance(['cn_iso_2' => 'DE']), 'vatregno' => 'DE123456789', 'expected' => true],
            // all the other static tests
            3 => ['country' => 'de', 'vatregno' => 'DE123456789', 'expected' => true],
            4 => ['country' => 'AT', 'vatregno' => 'ATU123456ASDFGH', 'expected' => false],
        ];
    }
}
