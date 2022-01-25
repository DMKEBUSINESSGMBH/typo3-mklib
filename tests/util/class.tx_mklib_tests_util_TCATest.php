<?php
/**
 * @author Hannes Bochmann
 *
 *  Copyright notice
 *
 *  (c) 2010 Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
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

/**
 * benötigte Klassen einbinden.
 */

/**
 * Generic form view test.
 */
class tx_mklib_tests_util_TCATest extends \Sys25\RnBase\Testing\BaseTestCase
{
    /**
     * @var string
     */
    private $returnUrlBackup;

    /**
     * (non-PHPdoc).
     *
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {
        $this->returnUrlBackup = $_GET['returnUrl'];
    }

    /**
     * (non-PHPdoc).
     *
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    protected function tearDown()
    {
        $_GET['returnUrl'] = $this->returnUrlBackup;
        unset($GLOBALS['TCA']['tt_mktest_table']);
    }

    public function testEleminateNonTcaColumnsByTable()
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
    public function testGetEnableColumnReturnsDeletedForDisabled()
    {
        $expected = 'deleted';
        $GLOBALS['TCA']['tt_mktest_table']['ctrl']['enablecolumns']['disabled'] = $expected;
        $actual = tx_mklib_util_TCA::getEnableColumn('tt_mktest_table', 'disabled');
        self::assertEquals($expected, $actual);
    }

    /**
     * @group unit
     * @expectedException \LogicException
     * @expectedExceptionCode 4003001
     */
    public function testGetEnableColumnThrowsExceptionForNonExcitingTable()
    {
        tx_mklib_util_TCA::getEnableColumn('tt_mktest_table_does_not_exists', 'disabled');
    }

    /**
     * @group unit
     * @expectedException \LogicException
     * @expectedExceptionCode 4003002
     */
    public function testGetEnableColumnThrowsExceptionForNonExcitingColumn()
    {
        $GLOBALS['TCA']['tt_mktest_table']['ctrl']['enablecolumns'] = [];
        tx_mklib_util_TCA::getEnableColumn('tt_mktest_table', 'disabled');
    }

    /**
     * @group unit
     */
    public function testGetEnableColumnReturnsDefaultValueForDisabled()
    {
        $expected = 'removed';
        $actual = tx_mklib_util_TCA::getEnableColumn('tt_mktest_table', 'disabled', $expected);
        self::assertEquals($expected, $actual);
    }

    /**
     * @group unit
     */
    public function testGetLanguageFieldReturnsRightValue()
    {
        $expected = 'sys_language_identifier';
        $GLOBALS['TCA']['tt_mktest_table']['ctrl']['languageField'] = $expected;
        $actual = tx_mklib_util_TCA::getLanguageField('tt_mktest_table');
        self::assertEquals($expected, $actual);
    }

    /**
     * @group unit
     * @expectedException \LogicException
     * @expectedExceptionCode 4003001
     */
    public function testGetLanguageThrowsExceptionForNonExcitingTable()
    {
        tx_mklib_util_TCA::getLanguageField('tt_mktest_table_does_not_exists');
    }

    /**
     * @group unit
     */
    public function testGetParentUidFromReturnUrlReturnsNullIfNoReturnUrl()
    {
        self::assertNull(
            tx_mklib_util_TCA::getParentUidFromReturnUrl(),
            'parent uid zu Beginn nicht leer'
        );
    }

    /**
     * @group unit
     */
    public function testGetParentUidFromReturnUrlReturnsNullIfParentUidNotExistentInReturnUrl()
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
    public function testGetParentUidFromReturnUrlReturnsNullIfParentUidNotSetInReturnUrl()
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
    public function testGetParentUidFromReturnUrlReturnsCorrectParentUid()
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
    public function testCropLabelsWithDefaultLengthOf80CharsCorrect()
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
    public function testCropLabelsWithEmptyItems()
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
    public function testCropLabelsWithConfiguredLengthOf40Chars()
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
    public function testCropLabelsUsesDefaultLengthIfConfiguredLengthIsNoIntegerGreaterThan0()
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
    public function testGetGermanStatesFieldWithoutRequired()
    {
        $expectedGermanStatesField = [
            'exclude' => 1,
            'label' => 'LLL:EXT:mklib/locallang_db.xml:tt_address.region',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['LLL:EXT:mklib/locallang_db.xml:please_choose', ''],
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
    public function testGetGermanStatesFieldWithRequired()
    {
        $expectedGermanStatesField = [
            'exclude' => 1,
            'label' => 'LLL:EXT:mklib/locallang_db.xml:tt_address.region',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['LLL:EXT:mklib/locallang_db.xml:please_choose', ''],
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
