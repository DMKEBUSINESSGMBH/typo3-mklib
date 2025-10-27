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
 * Testfälle für tx_mklib_validator_ZipCode.
 *
 * @author   Michael Wagner <michael.wagner@dmk-ebusiness.de>
 *
 * @group integration
 */
class tx_mklib_tests_validator_ZipCodeTest extends Sys25\RnBase\Testing\BaseTestCase
{
    /**
     * (non-PHPdoc).
     *
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp(): void
    {
        if (!TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('static_info_tables')) {
            $this->markTestSkipped('static_info_tables nicht installiert');
        }

        // zur Sicherheit die Zip Code Rules einfügen
        $sqlFilename = Sys25\RnBase\Utility\Files::getFileAbsFileName(
            TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath(
                'mklib',
                'ext_tables_static_update.sql'
            )
        );
        if (@is_file($sqlFilename)) {
            try {
                // alle statements importieren
                DMK\Mklib\Utility\Tests::queryDB($sqlFilename, false, true);
            } catch (RuntimeException) {
                $this->markTestSkipped('ext_tables_static_update failed.');
            }
        }
    }

    /**
     * @group integration
     */
    public function testValidateGermanZips(): void
    {
        $this->checkStaticCountries();

        $country = $this->getStaticCountryModel(54);

        self::assertTrue(is_object($country), 'No model given.');
        self::assertTrue($country->isValid(), 'No valid model given.');
        self::assertEquals('DE', $country->getISO2(), 'No or wrong iso 2 given.');
        self::assertEquals(5, $country->getZipLength(), 'No or wrong  zip length given.');
        self::assertEquals(4, $country->getZipRule(), 'No or wrong  zip rule given.');

        $validator = tx_mklib_validator_ZipCode::getInstance();

        $zips = ['09113', '14482'];
        foreach ($zips as $zip) {
            self::assertTrue(
                $validator->validate($country, $zip),
                $zip.' -> '.$validator->getFormatInfo($country)
            );
        }

        $zips = ['9120', 'O9113'];
        foreach ($zips as $zip) {
            self::assertFalse(
                $validator->validate($country, $zip),
                $zip.' -> '.$validator->getFormatInfo($country)
            );
        }
    }

    /**
     * @param int  $countryUid
     * @param bool $result
     */
    #[PHPUnit\Framework\Attributes\DataProvider('providerValidatorRules')]
    public function testValidatorRules(string $zip, $countryUid, $result): void
    {
        $this->checkStaticCountries();
        $country = $this->getStaticCountryModel($countryUid);
        $validator = tx_mklib_validator_ZipCode::getInstance();
        self::assertEquals(
            $result,
            $validator->validate($country, $zip),
            $zip.' -> '.$validator->getFormatInfo($country)
        );
    }

    /**
     * @return multitype:multitype:string number boolean
     */
    public static function providerValidatorRules(): array
    {
        $return = [];
        foreach ([
            // array($iZip, $country, $result),
            1 => ['09113', 54 /* DE */, true],
            2 => ['6666666', 46 /* CN */, false],
        ] as $key => $row) {
            $key = 'Line:'.strtolower((string) $key).' Zip:'.$row[0].' Country:'.intval($row[1]).' Return:'.intval($row[2]);
            $return[$key] = $row;
        }

        return $return;
    }

    /**
     * Wurden die ZipRules geladen?
     */
    private function checkStaticCountries(): void
    {
        $cnt = Sys25\RnBase\Database\Connection::getInstance()->doSelect('COUNT(uid) as cnt', 'static_countries', ['enablefieldsoff' => 1, /* 'debug'=>1, */ 'where' => 'zipcode_rule > 0']);
        $loaded = intval($cnt[0]['cnt']) > 0;

        if (!$loaded) {
            // zur Sicherheit die Zip Code Rules einfügen
            $sqlFilename = Sys25\RnBase\Utility\Files::getFileAbsFileName(TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('mklib', 'ext_tables_static_update.sql'));
            if (@is_file($sqlFilename)) {
                DMK\Mklib\Utility\Tests::queryDB($sqlFilename, false, true); // alle statements importieren
            }
        }
    }

    /**
     * @return tx_mklib_model_StaticCountry
     */
    private function getStaticCountryModel($rowOrUid): object
    {
        return TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mklib_model_StaticCountry', $rowOrUid);
    }
}
