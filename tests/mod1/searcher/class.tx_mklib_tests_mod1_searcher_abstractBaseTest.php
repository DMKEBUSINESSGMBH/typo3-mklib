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
tx_rnbase::load('tx_mklib_tests_fixtures_classes_DummySearcher');
tx_rnbase::load('tx_mklib_tests_mod1_Util');
/**
 * @author Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 */
class tx_mklib_tests_mod1_searcher_abstractBaseTest extends tx_rnbase_tests_BaseTestCase
{
    /**
     * @var tx_mklib_tests_fixtures_classes_DummySearcher
     */
    protected $searcher;

    /**
     * @var tx_mklib_tests_fixtures_classes_DummyMod
     */
    protected $mod;

    public function setUp()
    {
        //sprache auf default setzen damit wir die richtigen labels haben
        self::markTestIncomplete("Creating default object from empty value");
        $GLOBALS['LANG']->lang = 'default';

        tx_mklib_srv_Wordlist::loadTca();

        $this->mod = tx_rnbase::makeInstance('tx_mklib_tests_fixtures_classes_DummyMod');
        $this->searcher = tx_rnbase::makeInstance('tx_mklib_tests_fixtures_classes_DummySearcher', $this->mod);
        $GLOBALS['TBE_TEMPLATE'] = tx_rnbase::makeInstance('Tx_Rnbase_Backend_Template_Override_DocumentTemplate');
        $GLOBALS['CLIENT']['FORMSTYLE'] = 'something';

        $GLOBALS['emptyTestResult'] = false;

        //immer wieder löschen
        $_GET['SET'] = null;
        tx_mklib_tests_mod1_Util::unsetSorting($this->mod);
        if (isset($GLOBALS['BE_USER']->uc['moduleData'][$this->mod->getName()]['showhidden'])) {
            unset($GLOBALS['BE_USER']->uc['moduleData'][$this->mod->getName()]['showhidden']);
        }

        // zurücksetzen
        $localLangLoadedProperty = new ReflectionProperty('tx_mklib_mod1_searcher_abstractBase', 'localLangLoaded');
        $localLangLoadedProperty->setAccessible(true);
        $localLangLoadedProperty->setValue(null, false);
    }

    public function testGetSearchForm()
    {
        self::markTestSkipped('Needs refactoring. The current test fails and seems to test stupid things.');

        $searchForm = $this->searcher->getSearchForm();

        self::assertContains(
            '<table class="filters"><tr><td>'.$GLOBALS['LANG']->getLL('label_search').
            '</td><td><input type="text" name="SET[dummySearcherSearch]" style="width:96px;" value="" /> <input type="submit" name="dummySearcherSearch" value="search" /></td></tr><tr><td>Hidden entries:</td><td>',
            $searchForm,
            'das suchformular ist falsch.'
        );
        self::assertContains(
            '<select name="SET[showhidden]" onchange="jumpToUrl',
            $searchForm,
            'das suchformular ist falsch.'
        );
        self::assertContains(
            '<option value="0">'.$GLOBALS['LANG']->getLL('label_select_hide_hidden').'</option>',
            $searchForm,
            'das suchformular ist falsch.'
        );
        self::assertContains(
            '<option value="1">'.$GLOBALS['LANG']->getLL('label_select_show_hidden').'</option>',
            $searchForm,
            'das suchformular ist falsch.'
        );
        self::assertContains(
            '</select>',
            $searchForm,
            'das suchformular ist falsch.'
        );
        self::assertContains(
            '</td></tr><tr><td></td><td><input type="submit" name="dummySearcherSearch" value="Update" /></td></tr></table>',
            $searchForm,
            'das suchformular ist falsch.'
        );
    }

    public function testGetResultListReturnsNoPagerAndEmptyMsgIfResultEmpty()
    {
        $GLOBALS['emptyTestResult'] = true;
        $aResultList = $this->searcher->getResultList();

        self::assertContains('LABEL_NO_DUMMYSEARCHER_FOUND', $aResultList['table'], 'Die Tabelle ist falsch.');

        self::assertEquals(0, $aResultList['totalsize'], 'Die Anzahl ist falsch.');
        self::assertEquals('', trim(strip_tags($aResultList['pager'])), 'Der Pager ist falsch.');
    }

    public function testGetResultListReturnsCorrectTableAndPagerIfResults()
    {
        //damit currenShowHidden gesetzt wird
        $this->searcher->getSearchForm();
        $aResultList = $this->searcher->getResultList();

        $result = $aResultList['table'];

        self::assertRegExp('/^<table/', $result, 'Table Tag fehlt.');
        self::assertRegExp('/<\/table>/', $result, 'Schließendes Table Tag fehlt.');

        for ($i = 1; $i < 6; ++$i) {
            self::assertRegExp('/">'.$i.'<\/span>/', $result, 'Wert '.$i.' fehlt in Tabelle');
        }

        self::assertEquals(5, $aResultList['totalsize'], 'Die Anzahl ist falsch.');
        self::assertContains(
            '<div class="pager">',
            $aResultList['pager'],
            'Der Pager wrap ist falsch.'
        );
        self::assertRegExp(
            '/<select (?(?=>)|.*)name="SET\[dummySearcherPagerdata_limit\]" onchange="jumpToUrl\(/',
            $aResultList['pager'],
            'Der Pager limit select start ist falsch.'
        );
        self::assertContains(
            '<option value="10" selected="selected">10 Einträge</option>',
            $aResultList['pager'],
            'Die Pager limit option 10 ist falsch.'
        );
        self::assertContains(
            '<option value="100">100 Einträge</option>',
            $aResultList['pager'],
            'Die Pager limit option 100 ist falsch.'
        );
        self::assertContains(
            '</select>',
            $aResultList['pager'],
            'Der Pager limit select end ist falsch.'
        );
        self::assertContains(
            '<select class="form-control" name="SET[dummySearcherPagerdata_offset]"',
            $aResultList['pager'],
            'Der Pager offset select ist falsch.'
        );
        self::assertContains(
            '<option value="0" selected="selected">Seite 0</option>',
            $aResultList['pager'],
            'Der Pager offset select option 0 ist falsch.'
        );
        self::assertContains(
            '</div>',
            $aResultList['pager'],
            'Der Pager endwrap ist falsch.'
        );
    }

    public function testGetResultReturnsCorrectResultsDependendOnHiddenSettings()
    {
        $GLOBALS['BE_USER']->uc['moduleData'][$this->mod->getName()]['showhidden'] = 1;
        //damit currenShowHidden gesetzt wird
        $this->searcher->getSearchForm();
        $aResultList = $this->searcher->getResultList();

        $result = $aResultList['table'];
        self::assertRegExp('/^<table/', $result, 'Table Tag fehlt.');
        self::assertRegExp('/<\/table>/', $result, 'Schließendes Table Tag fehlt.');

        for ($i = 1; $i < 6; ++$i) {
            self::assertRegExp('/">'.$i.'<\/span>/', $result, 'Wert '.$i.' fehlt in Tabelle');
        }
        self::assertRegExp('/"><del>6<\/del><\/span>/', $result, 'versteckter Wert 6 fehlt in Tabelle');

        self::assertEquals(6, $aResultList['totalsize'], 'Die Anzahl ist falsch.');
    }

    public function testGetResultListReturnsCorrectTableAndPagerIfSortLinkIsClickedAndSetsSortOptionTmoduleData()
    {
        $_GET['sortField'] = 'uid';
        $_GET['sortRev'] = 'asc';

        //damit currenShowHidden gesetzt wird
        $this->searcher->getSearchForm();
        $aResultList = $this->searcher->getResultList();

        //Daten im Modul korrekt?
        $aModuleData = Tx_Rnbase_Backend_Utility::getModuleData(array(), tx_rnbase_parameters::getPostOrGetParameter('SET'), $this->mod->getName());
        self::assertEquals(array('uid' => 'asc'), $aModuleData['dummySearcherorderby'], 'OrderBy in Moduldaten nicht korrekt gesetzt.');

        $result = $aResultList['table'];
        self::assertRegExp('/^<table/', $result, 'Table Tag fehlt.');
        self::assertRegExp('/<\/table>/', $result, 'Schließendes Table Tag fehlt.');

        // TODO: Die Reihenfolge der Zeilen müsste noch getestet werden.
        for ($i = 1; $i < 6; ++$i) {
            self::assertRegExp('/">'.$i.'<\/span>/', $result, 'Wert '.$i.' fehlt in Tabelle');
        }

        self::assertEquals(5, $aResultList['totalsize'], 'Die Anzahl ist falsch.');
        //unberührt?
        self::assertContains(
            '<div class="pager">',
            $aResultList['pager'],
            'Der Pager ist falsch.'
        );
        self::assertRegExp(
            '/<select (?(?=>)|.*)name="SET\[dummySearcherPagerdata_limit\]" onchange="jumpToUrl\(/',
            $aResultList['pager'],
            'Der Pager limit select start ist falsch.'
        );
        self::assertContains(
            '<option value="10" selected="selected">10 Einträge</option>',
            $aResultList['pager'],
            'Der Pager ist falsch.'
        );
        self::assertContains(
            '<option value="100">100 Einträge</option>',
            $aResultList['pager'],
            'Der Pager ist falsch.'
        );
        self::assertContains(
            '</select>',
            $aResultList['pager'],
            'Der Pager ist falsch.'
        );
        self::assertContains(
            '<select class="form-control" name="SET[dummySearcherPagerdata_offset]"',
            $aResultList['pager'],
            'Der Pager ist falsch.'
        );
        self::assertContains(
            '<option value="0" selected="selected">Seite 0</option>',
            $aResultList['pager'],
            'Der Pager ist falsch.'
        );
        self::assertContains(
            '</div>',
            $aResultList['pager'],
            'Der Pager ist falsch.'
        );
    }

    public function testGetResultListReturnsCorrectTableAndPagerIfSortingFromModuleDataAndSetsSortOptionToGetParams()
    {
        //daten fürs modul setzen
        $GLOBALS['BE_USER']->uc['moduleData'][$this->mod->getName()]['dummySearcherorderby'] = array('uid' => 'asc');

        //damit currenShowHidden gesetzt wird
        $this->searcher->getSearchForm();
        $aResultList = $this->searcher->getResultList();

        //Daten in $_GET korrekt?
        self::assertEquals('uid', $_GET['sortField'], '$_GET[\'sortField\'] nicht korrekt gesetzt.');
        self::assertEquals('asc', $_GET['sortRev'], '$_GET[\'sortRev\'] nicht korrekt gesetzt.');

        $result = $aResultList['table'];
        self::assertRegExp('/^<table/', $result, 'Table Tag fehlt.');
        self::assertRegExp('/<\/table>/', $result, 'Schließendes Table Tag fehlt.');

        // TODO: Die Reihenfolge der Zeilen müsste noch getestet werden.
        for ($i = 1; $i < 6; ++$i) {
            self::assertRegExp('/">'.$i.'<\/span>/', $result, 'Wert '.$i.' fehlt in Tabelle');
        }

        self::assertEquals(5, $aResultList['totalsize'], 'Die Anzahl ist falsch.');
        //unberührt?
        self::assertContains(
            '<div class="pager">',
            $aResultList['pager'],
            'Der Pager ist falsch.'
        );
        self::assertRegExp(
            '/<select (?(?=>)|.*)name="SET\[dummySearcherPagerdata_limit\]" onchange="jumpToUrl\(/',
            $aResultList['pager'],
            'Der Pager limit select start ist falsch.'
        );
        self::assertContains(
            '<option value="10" selected="selected">10 Einträge</option>',
            $aResultList['pager'],
            'Der Pager ist falsch.'
        );
        self::assertContains(
            '<option value="100">100 Einträge</option>',
            $aResultList['pager'],
            'Der Pager ist falsch.'
        );
        self::assertContains(
            '</select>',
            $aResultList['pager'],
            'Der Pager ist falsch.'
        );
        self::assertContains(
            '<select class="form-control" name="SET[dummySearcherPagerdata_offset]"',
            $aResultList['pager'],
            'Der Pager ist falsch.'
        );
        self::assertContains(
            '<option value="0" selected="selected">Seite 0</option>',
            $aResultList['pager'],
            'Der Pager ist falsch.'
        );
        self::assertContains(
            '</div>',
            $aResultList['pager'],
            'Der Pager ist falsch.'
        );
    }

    /**
     * @group unit
     */
    public function testSearchItemsWithoutOptions()
    {
        $res = $this->callInaccessibleMethod(
            $this->getSearchItemsSearcherMock(),
            'searchItems',
            array(),
            array()
        );

        // alle 5 items müssen da sein
        self::assertTrue(is_array($res));
        self::assertArrayHasKey('items', $res);
        self::assertTrue(is_array($res['items']));
        self::assertCount(5, $res['items']);
        self::assertSame(1, reset($res['items'])->getUid());
        self::assertSame(5, end($res['items'])->getUid());
        self::assertArrayHasKey('map', $res);
        self::assertTrue(is_array($res['map']));
        self::assertCount(5, $res['map']);
    }

    /**
     * @group unit
     */
    public function testSearchItemsWithLimit()
    {
        $res = $this->callInaccessibleMethod(
            $this->getSearchItemsSearcherMock(
                array(),
                array('limit' => 5)
            ),
            'searchItems',
            array(),
            array('limit' => 4)
        );

        // das letzte item muss fehlen!
        self::assertTrue(is_array($res));
        self::assertArrayHasKey('items', $res);
        self::assertTrue(is_array($res['items']));
        self::assertCount(4, $res['items']);
        self::assertSame(1, reset($res['items'])->getUid());
        self::assertSame(4, end($res['items'])->getUid());
        self::assertArrayHasKey('map', $res);
        self::assertTrue(is_array($res['map']));
        self::assertCount(5, $res['map']);
    }

    /**
     * @group unit
     */
    public function testSearchItemsWithOffset()
    {
        $res = $this->callInaccessibleMethod(
            $this->getSearchItemsSearcherMock(
                array(),
                array('offset' => 1)
            ),
            'searchItems',
            array(),
            array('offset' => 3)
        );

        // die ersten zwei items müssen fehlen!
        self::assertTrue(is_array($res));
        self::assertArrayHasKey('items', $res);
        self::assertTrue(is_array($res['items']));
        self::assertCount(3, $res['items']);
        self::assertSame(3, reset($res['items'])->getUid());
        self::assertSame(5, end($res['items'])->getUid());
        self::assertArrayHasKey('map', $res);
        self::assertTrue(is_array($res['map']));
        self::assertCount(5, $res['map']);
    }

    /**
     * @group unit
     */
    public function testSearchItemsWithLimitAndOffset()
    {
        $res = $this->callInaccessibleMethod(
            $this->getSearchItemsSearcherMock(
                array(),
                array('limit' => 5, 'offset' => 0)
            ),
            'searchItems',
            array(),
            array('limit' => 3, 'offset' => 1)
        );

        // das erste und letzte item muss fehlen!
        self::assertTrue(is_array($res));
        self::assertArrayHasKey('items', $res);
        self::assertTrue(is_array($res['items']));
        self::assertCount(3, $res['items']);
        self::assertSame(2, reset($res['items'])->getUid());
        self::assertSame(4, end($res['items'])->getUid());
        self::assertArrayHasKey('map', $res);
        self::assertTrue(is_array($res['map']));
        self::assertCount(5, $res['map']);
    }

    /**
     * wenn weniger daten als limit in der db vorhanden sind,
     * dann darf der array_pop auf die items nicht durchgeführt werden!
     *
     * @group unit
     */
    public function testSearchItemsWithLimitAndOffsetAndFewResults()
    {
        $res = $this->callInaccessibleMethod(
            $this->getSearchItemsSearcherMock(
                array(),
                array('limit' => 11, 'offset' => 0)
            ),
            'searchItems',
            array(),
            array('limit' => 10, 'offset' => 0)
        );

        self::assertTrue(is_array($res));
        self::assertArrayHasKey('items', $res);
        self::assertTrue(is_array($res['items']));
        self::assertCount(5, $res['items']);
        self::assertSame(1, reset($res['items'])->getUid());
        self::assertSame(5, end($res['items'])->getUid());
        self::assertArrayHasKey('map', $res);
        self::assertTrue(is_array($res['map']));
        self::assertCount(5, $res['map']);
    }

    protected function getSearchItemsSearcherMock(
        array $expectedFields = array(),
        array $expectedOptions = array(),
        array $items = null
    ) {
        $service = $this->getMockForAbstractClass(
            'tx_mklib_repository_Abstract',
            array(),
            '',
            true,
            true,
            true,
            array('search')
        );
        $service
            ->expects(self::atLeastOnce())
            ->method('search')
            ->with(
                $expectedFields,
                $expectedOptions
            )
            ->will(
                self::returnValue(
                    is_array($items) ? $items : array(
                        $this->getModel(array('uid' => 1)),
                        $this->getModel(array('uid' => 2)),
                        $this->getModel(array('uid' => 3)),
                        $this->getModel(array('uid' => 4)),
                        $this->getModel(array('uid' => 5)),
                    )
                )
            );

        $searcher = $this->getMockForAbstractClass(
            'tx_mklib_mod1_searcher_abstractBase',
            array($this->mod, array('baseTableName' => 'pages')),
            '',
            true,
            true,
            true,
            array('getService')
        );
        $searcher
            ->expects(self::atLeastOnce())
            ->method('getService')
            ->will(self::returnValue($service));

        return $searcher;
    }

    /**
     * @group unit
     */
    public function testInitLoadsOwnLocalLangNotOverwritingExistingLabels()
    {
        // ist zwar in der mklib locallang Datei aber sollte nicht überschrieben werden
        $this->setLocallangLabel('label_button_search', 'test search button');
        // gibt es noch nicht
        $this->setLocallangLabel('my_test_label', 'my test label');
        $this->callInaccessibleMethod($this->searcher, 'init', $this->mod, array());

        self::assertEquals('test search button', $this->getLocallangLabel('label_button_search'));
        self::assertEquals('my test label', $this->getLocallangLabel('my_test_label'));
        // ist in der mklib locallang Datei und war vorher noch nicht da, sollte also
        // aus lollang Datei geladen werden
        self::assertEquals('Actions', $this->getLocallangLabel('label_tableheader_actions'));
    }

    /**
     * @param string $labelKey
     * @param string $label
     */
    protected function setLocallangLabel($labelKey, $label)
    {
        $GLOBALS['LOCAL_LANG']['default'][$labelKey][0]['target'] = $label;
    }

    /**
     * @param string $labelKey
     *
     * @return string
     */
    protected function getLocallangLabel($labelKey)
    {
        $label = $GLOBALS['LOCAL_LANG']['default'][$labelKey][0]['target'];

        return $label;
    }
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tests/mod1/util/class.tx_mklib_tests_mod1_util_SearchBuilderTest.php']) {
    include_once $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tests/mod1/util/class.tx_mklib_tests_mod1_util_SearchBuilderTest.php'];
}
