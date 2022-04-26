<?php
/**
 * @author Hannes Bochmann
 *
 *  Copyright notice
 *
 *  (c) 2010 DMK E-BUSINESS GmbH  <dev@dmk-ebusiness.de>
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
 * @author Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 */
class tx_mklib_tests_mod1_decorator_BaseTest extends \Sys25\RnBase\Testing\BaseTestCase
{
    protected $backup = [];

    protected function setUp(): void
    {
        $this->backup['iconsAvailable'] = $GLOBALS['TBE_STYLES']['spriteIconApi']['iconsAvailable'] ?? null;
        if (!is_array($GLOBALS['TBE_STYLES']['spriteIconApi']['iconsAvailable'] ?? null)) {
            $GLOBALS['TBE_STYLES']['spriteIconApi']['iconsAvailable'] = [];
        }
        $this->backup['beUserAdminState'] = $GLOBALS['BE_USER']->user['admin'] ?? null;

        self::markTestIncomplete('Creating default object from empty value');
        $GLOBALS['LANG']->lang = 'default';
    }

    protected function tearDown(): void
    {
        $GLOBALS['BE_USER']->user['admin'] = $this->backup['beUserAdminState'];
        $GLOBALS['TBE_STYLES']['spriteIconApi']['iconsAvailable'] = $this->backup['iconsAvailable'];
    }

    public function testFormatWithUidColumn()
    {
        // base model liefert als table name immer 0
        // also setzen wir die columns für die tabelle 0
        $GLOBALS['TCA'][0]['ctrl']['enablecolumns']['disabled'] = 'disable';

        $record = ['uid' => 1, 'disable' => 0];
        $result = $this->getDecoratorMock()->format(1, 'uid', $record, $this->getModel($record));
        // Achtung: Das Ergebnis ist Multiline. Leider wird der Modifikator nicht akzeptiert...
        self::assertRegExp('/.*>1<\/span>/', $result, 'Aktiver Datensatz wird falsch geliefert.');

        // jetzt mit versteckt und record fallback
        $record = ['uid' => 1, 'disable' => 1];
        $result = $this->getDecoratorMock()->format(1, 'uid', $record, $this->getModel($record));
        self::assertRegExp('/<del>1<\/del>/', $result, 'Deaktivierter Datensatz wird falsch geliefert.');

        // jetzt mit versteckt und ohne record fallback
        $record = ['uid' => 1, 'disable' => 1];
        $result = $this->getDecoratorMock()->format(1, 'uid', $record, $this->getModel($record));
        self::assertRegExp('/<del>1<\/del>/', $result, 'es wurde nicht der korrekte Wert zurück geliefert');
    }

    public function testFormatWithUidColumnAndNoEnableColumnsConfig()
    {
        // es sollte hidden genommen werden da nicht in enablecolumns konfiguriert
        $GLOBALS['TCA'][0]['ctrl']['enablecolumns']['disabled'] = null; // konfig löschen

        $record = ['uid' => 1, 'disable' => 0];
        $result = $this->getDecoratorMock()->format(1, 'uid', $record, $this->getModel($record));
        self::assertRegExp('/.*>1<\/span>/', $result, 'Aktiver Datensatz wird falsch geliefert.');

        // jetzt mit versteckt im falschen feld
        $record = ['uid' => 1, 'disable' => 1];
        $result = $this->getDecoratorMock()->format(1, 'uid', $record, $this->getModel($record));
        self::assertRegExp('/.*>1<\/span>/', $result, 'Aktiver Datensatz wird falsch geliefert. 2');

        // jetzt mit versteckt im fallback feld
        $record = ['uid' => 1, 'hidden' => 0];
        $result = $this->getDecoratorMock()->format(1, 'uid', $record, $this->getModel($record));
        self::assertRegExp('/.*>1<\/span>/', $result, 'Aktiver Datensatz wird falsch geliefert. 3');

        $record = ['uid' => 1, 'hidden' => 1];
        $result = $this->getDecoratorMock()->format(1, 'uid', $record, $this->getModel($record));
        self::assertRegExp('/<del>1<\/del>/', $result, 'Deaktivierter Datensatz wird falsch geliefert.');
    }

    public function testFormatWithLabelColumn()
    {
        $model = $this->getModel(
            [
                'uid' => 57,
                'header' => 'Home',
                'crdate' => 1433158465,
                'tstamp' => 1434158465,
            ]
        )->setTablename('tt_content');
        $result = $this->getDecoratorMock()->format('Content', 'label', $model->getRecord(), $model);

        self::assertContains('>Home</span>', $result, 'Falsches Label erzeugt');
        self::assertContains('<span title="UID: 57', $result, 'UID fehlt.');
        self::assertContains('Creation: 2015-06-01T11:34:25+00:00', $result, 'CRDATE fehlt.');
        self::assertContains('Last Change: 2015-06-13T01:21:05+00:00', $result, 'TSTAMP fehlt.');
    }

    public function testFormatWithSysLanguageUidColumn()
    {
        // @TODO: test the language output on typo3 8 .78 lts!
        $this->markTestIncomplete(
            'The IconFactory builds a diffrent output.'.
            ' The test must be refactored!'
        );

        // this test needs the typo3 db
        $this->prepareLegacyTypo3DbGlobal();

        $GLOBALS['TBE_STYLES']['spriteIconApi']['iconsAvailable'][] = 'flags-multiple';
        $model = $this->getModel(
            [
                'uid' => 57,
                'header' => 'Home',
                'sys_language_uid' => '0',
            ]
        )->setTablename('tt_content');

        $result = $this->getDecoratorMock()->format('0', 'sys_language_uid', $model->getRecord(), $model);

        self::assertContains(
            '<span class="t3-icon t3-icon-flags t3-icon-flags-multiple t3-icon-multiple">'.
            ' </span>&nbsp;Default',
            $result,
            'Falsches oder fehlendes Icon erzeugt.'
        );

        $model->setProperty('sys_language_uid', '-1');
        $result = $this->getDecoratorMock()->format('0', 'sys_language_uid', $model->getRecord(), $model);

        self::assertContains(
            '<span class="t3-icon t3-icon-flags t3-icon-flags-multiple t3-icon-multiple">'.
            ' </span>&nbsp;[All]',
            $result,
            'Falsches oder fehlendes Icon erzeugt.'
        );

        // @TODO: write test for syslang uid > 0 !!!
    }

    public function testFormatWithActionsColumnBeingAdminDoesReturnDeleteLink()
    {
        $GLOBALS['BE_USER']->user['admin'] = 1;
        // base model liefert als table name immer 0
        // also setzen wir die columns für die tabelle 0
        $GLOBALS['TCA'][0]['ctrl']['enablecolumns']['disabled'] = 'disable';

        $record = ['uid' => 1, 'disable' => 0];
        $result = $this->getDecoratorMock()->format('', 'actions', $record, $this->getModel($record));

        self::assertContains('data[0][1][disable]=1', $result);
        self::assertContains('cmd[0][1][delete]=1', $result);

        // schon versteckt
        $record = ['uid' => 1, 'disable' => 1];
        $result = $this->getDecoratorMock()->format('', 'actions', $record, $this->getModel($record));
        self::assertContains('data[0][1][disable]=0', $result);
        self::assertContains('cmd[0][1][delete]=1', $result);
    }

    public function testFormatWithActionsColumnBeingNoAdminDoesNotReturnDeleteLink()
    {
        $GLOBALS['BE_USER']->user['admin'] = 0;
        // base model liefert als table name immer 0
        // also setzen wir die columns für die tabelle 0
        $GLOBALS['TCA'][0]['ctrl']['enablecolumns']['disabled'] = 'disable';

        $record = ['uid' => 1, 'disable' => 0];
        $result = $this->getDecoratorMock()->format('', 'actions', $record, $this->getModel($record));

        self::assertContains('data[0][1][disable]=1', $result);
        self::assertNotContains('cmd[0][1][delete]=1', $result);

        // schon versteckt
        $record = ['uid' => 1, 'disable' => 1];
        $result = $this->getDecoratorMock()->format('', 'actions', $record, $this->getModel($record));

        self::assertContains('data[0][1][disable]=0', $result);
        self::assertNotContains('cmd[0][1][delete]=1', $result);
    }

    public function testFormatWithActionsColumnAndNoEnableColumnConfigBeingAdmin()
    {
        $GLOBALS['BE_USER']->user['admin'] = 1;
        // base model liefert als table name immer 0
        // also setzen wir die columns für die tabelle 0
        $GLOBALS['TCA'][0]['ctrl']['enablecolumns']['disabled'] = null; // konfig löschen

        $record = ['uid' => 1, 'hidden' => 0];
        $result = $this->getDecoratorMock()->format('', 'actions', $record, $this->getModel($record));

        self::assertNotContains('data[0][1][disable]', $result);
        self::assertContains('data[0][1][hidden]=1', $result);
        self::assertContains('cmd[0][1][delete]=1', $result);

        // schon versteckt
        $record = ['uid' => 1, 'hidden' => 1];
        $result = $this->getDecoratorMock()->format('', 'actions', $record, $this->getModel($record));

        self::assertNotContains('data[0][1][disable]', $result);
        self::assertContains('data[0][1][hidden]=0', $result);
        self::assertContains('cmd[0][1][delete]=1', $result);
    }

    /**
     * @return tx_mklib_mod1_decorator_Base
     */
    protected function getDecoratorMock()
    {
        $mod = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mklib_tests_fixtures_classes_DummyMod');
        // zurücksetzen aus anderen Tests
        tx_mklib_tests_mod1_Util::unsetSorting($mod);

        return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mklib_mod1_decorator_Base', $mod);
    }

    protected function replaceForCliAndremoveVcAndFormToken($string)
    {
        tx_mklib_tests_mod1_Util::replaceForCli($string);
        tx_mklib_tests_mod1_Util::removeVcAndFormToken($string);

        return $string;
    }
}
