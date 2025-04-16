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
 * benötigte Klassen einbinden.
 */

/**
 * Generic form view test.
 */
class tx_mklib_tests_util_TCATest extends Sys25\RnBase\Testing\BaseTestCase
{
    /**
     * @var string
     */
    private mixed $returnUrlBackup;

    /**
     * (non-PHPdoc).
     *
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp(): void
    {
        $this->returnUrlBackup = $_GET['returnUrl'] ?? '';
    }

    /**
     * (non-PHPdoc).
     *
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    protected function tearDown(): void
    {
        $_GET['returnUrl'] = $this->returnUrlBackup;
        unset($GLOBALS['TCA']['tt_mktest_table']);
    }

    public function testEleminateNonTcaColumnsByTable(): void
    {
        $GLOBALS['TCA']['dummy_table']['columns'] = ['title' => [], 'description' => []];
        $data = [
            'title' => true,
            'description' => 0,
            'ich-muss-raus' => true,
            'ich-auch' => false,
        ];
        $res = tx_mklib_util_TCA::eleminateNonTcaColumnsByTable('dummy_table', $data);
        self::assertEquals(2, count($res), 'falsche array größe');
        self::assertTrue($res['title'], 'blacklsited Feld ist nicht korrekt!');
        self::assertEquals(0, $res['description'], 'whitelisted Feld ist nicht korrekt!');
        self::assertFalse(isset($res['ich-muss-raus']), 'ich-muss-raus Feld wurde nicht entfernt!');
        self::assertFalse(isset($res['ich-auch']), 'ich-auch Feld wurde nicht entfernt!');
    }

    /**
     * @group unit
     */
    public function testGetEnableColumnReturnsDeletedForDisabled(): void
    {
        $expected = 'deleted';
        $GLOBALS['TCA']['tt_mktest_table']['ctrl']['enablecolumns']['disabled'] = $expected;
        $actual = tx_mklib_util_TCA::getEnableColumn('tt_mktest_table', 'disabled');
        self::assertEquals($expected, $actual);
    }

    /**
     * @group unit
     */
    public function testGetEnableColumnThrowsExceptionForNonExcitingTable(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionCode(4003001);
        tx_mklib_util_TCA::getEnableColumn('tt_mktest_table_does_not_exists', 'disabled');
    }

    /**
     * @group unit
     */
    public function testGetEnableColumnThrowsExceptionForNonExcitingColumn(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionCode(4003002);
        $GLOBALS['TCA']['tt_mktest_table']['ctrl']['enablecolumns'] = [];
        tx_mklib_util_TCA::getEnableColumn('tt_mktest_table', 'disabled');
    }

    /**
     * @group unit
     */
    public function testGetEnableColumnReturnsDefaultValueForDisabled(): void
    {
        $expected = 'removed';
        $actual = tx_mklib_util_TCA::getEnableColumn('tt_mktest_table', 'disabled', $expected);
        self::assertEquals($expected, $actual);
    }

    /**
     * @group unit
     */
    public function testGetLanguageFieldReturnsRightValue(): void
    {
        $expected = 'sys_language_identifier';
        $GLOBALS['TCA']['tt_mktest_table']['ctrl']['languageField'] = $expected;
        $actual = tx_mklib_util_TCA::getLanguageField('tt_mktest_table');
        self::assertEquals($expected, $actual);
    }

    /**
     * @group unit
     */
    public function testGetLanguageThrowsExceptionForNonExcitingTable(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionCode(4003001);
        tx_mklib_util_TCA::getLanguageField('tt_mktest_table_does_not_exists');
    }

    /**
     * @group unit
     */
    public function testGetParentUidFromReturnUrlReturnsNullIfNoReturnUrl(): void
    {
        self::assertNull(
            tx_mklib_util_TCA::getParentUidFromReturnUrl(),
            'parent uid zu Beginn nicht leer'
        );
    }

    /**
     * @group unit
     */
    public function testGetParentUidFromReturnUrlReturnsNullIfParentUidNotExistentInReturnUrl(): void
    {
        $_GET['returnUrl'] = 'typo3/wizard_add.php';

        self::assertNull(
            tx_mklib_util_TCA::getParentUidFromReturnUrl(),
            'parent uid zu Beginn nicht leer'
        );
    }

    /**
     * @group unit
     */
    public function testGetParentUidFromReturnUrlReturnsNullIfParentUidNotSetInReturnUrl(): void
    {
        $_GET['returnUrl'] = 'typo3/wizard_add.php?&P[uid]=';

        self::assertNull(
            tx_mklib_util_TCA::getParentUidFromReturnUrl(),
            'parent uid zu Beginn nicht leer'
        );
    }

    /**
     * @group unit
     */
    public function testGetParentUidFromReturnUrlReturnsCorrectParentUid(): void
    {
        $_GET['returnUrl'] = 'typo3/wizard_add.php?&P[uid]=2';

        self::assertEquals(
            2,
            tx_mklib_util_TCA::getParentUidFromReturnUrl(),
            'parent uid nicht korrekt'
        );
    }

    /**
     * @group unit
     */
    public function testCropLabelsWithDefaultLengthOf80CharsCorrect(): void
    {
        $labelWith81Chars = 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmodss';
        $tcaTableInformation = ['items' => [0 => [0 => $labelWith81Chars]]];

        tx_mklib_util_TCA::cropLabels($tcaTableInformation);

        $labelWith80CharsAnd3Dots = 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmods...';
        self::assertEquals(
            $labelWith80CharsAnd3Dots,
            $tcaTableInformation['items'][0][0],
            'Label nicht richtig gekürzt'
        );
    }

    /**
     * @group unit
     */
    public function testCropLabelsWithEmptyItems(): void
    {
        self::markTestIncomplete('This test did not perform any assertions!');

        $tcaTableInformation = ['items' => []];
        tx_mklib_util_TCA::cropLabels($tcaTableInformation);
        unset($tcaTableInformation['items']);
        tx_mklib_util_TCA::cropLabels($tcaTableInformation);
    }

    /**
     * @group unit
     */
    public function testCropLabelsWithConfiguredLengthOf40Chars(): void
    {
        $labelWith81Chars = 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmodss';
        $tcaTableInformation = [
            'items' => [0 => [0 => $labelWith81Chars]],
            'config' => ['labelLength' => 40],
        ];

        tx_mklib_util_TCA::cropLabels($tcaTableInformation);

        $labelWith40CharsAnd3Dots = 'Lorem ipsum dolor sit amet, consetetur s...';
        self::assertEquals(
            $labelWith40CharsAnd3Dots,
            $tcaTableInformation['items'][0][0],
            'Label nicht richtig gekürzt'
        );
    }

    /**
     * @group unit
     */
    public function testCropLabelsUsesDefaultLengthIfConfiguredLengthIsNoIntegerGreaterThan0(): void
    {
        $labelWith81Chars = 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmodss';
        $tcaTableInformation = [
            'items' => [0 => [0 => $labelWith81Chars]],
            'config' => ['labelLength' => 'test'],
        ];

        tx_mklib_util_TCA::cropLabels($tcaTableInformation);

        $labelWith80CharsAnd3Dots = 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmods...';
        self::assertEquals(
            $labelWith80CharsAnd3Dots,
            $tcaTableInformation['items'][0][0],
            'Label nicht richtig gekürzt'
        );
    }

    /**
     * @group unit
     */
    public function testGetGermanStatesFieldWithoutRequired(): void
    {
        $expectedGermanStatesField = [
            'exclude' => 1,
            'label' => 'LLL:EXT:mklib/Resources/Private/Language/locallang_db.xlf:tt_address.region',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['LLL:EXT:mklib/Resources/Private/Language/locallang_db.xlf:please_choose', ''],
                ],
                'foreign_table' => 'static_country_zones',
                'foreign_table_where' => ' AND static_country_zones.zn_country_iso_nr = 276 ORDER BY static_country_zones.zn_name_local',
                'size' => 1,
            ],
        ];

        $germanStatesField = tx_mklib_util_TCA::getGermanStatesField();

        self::assertEquals(
            $expectedGermanStatesField,
            $germanStatesField,
            'TCA Feld falsch'
        );
    }

    /**
     * @group unit
     */
    public function testGetGermanStatesFieldWithRequired(): void
    {
        $expectedGermanStatesField = [
            'exclude' => 1,
            'label' => 'LLL:EXT:mklib/Resources/Private/Language/locallang_db.xlf:tt_address.region',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['LLL:EXT:mklib/Resources/Private/Language/locallang_db.xlf:please_choose', ''],
                ],
                'foreign_table' => 'static_country_zones',
                'foreign_table_where' => ' AND static_country_zones.zn_country_iso_nr = 276 ORDER BY static_country_zones.zn_name_local',
                'size' => 1,
                'minitems' => 1,
                'maxitems' => 1,
                'eval' => 'required',
            ],
        ];

        $germanStatesField = tx_mklib_util_TCA::getGermanStatesField(true);

        self::assertEquals(
            $expectedGermanStatesField,
            $germanStatesField,
            'TCA Feld falsch'
        );
    }
}
