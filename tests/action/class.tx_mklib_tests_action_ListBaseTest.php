<?php
/*
 *
 *  Copyright notice
 *
 *  (c) 2011 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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

//in abstrakten klassen lassen sich keine nicht-abstrakten methoden mocken
// @todo remove. since phpunit 5 this is working
use PHPUnit\Framework\TestCase;

abstract class ListBaseWithNewFilterMode extends tx_mklib_action_ListBase
{
    protected function isOldFilterMode()
    {
        return false;
    }
}

/**
 * Enter description here ...
 *
 * @author Hannes Bochmann
 */
class tx_mklib_tests_action_ListBaseTest extends tx_rnbase_tests_BaseTestCase
{
    protected function getConfigurations($aConfig = array())
    {
        $configurations = tx_rnbase::makeInstance('Tx_Rnbase_Configuration_Processor');
        $parameters = tx_rnbase::makeInstance('tx_rnbase_parameters');

        $configurations->init(
            $aConfig,
            $configurations->getCObj(1),
            'mklib',
            'mklib'
        );

        $configurations->setParameters($parameters);

        return $configurations;
    }

    protected function getSrvMock($expectedFields = array(), $returnValue = array('result' => array('firstItem')))
    {
        $oSrv = $this->getMock('dummySrv', array('search'));

        if (!empty($expectedFields)) {
            $this->expectedFields = $expectedFields;
        } else {
            $this->expectedFields = array('ANOTHERTEST.ANOTHERFIELD' => array(OP_EQ => 'anotherValue'), 'test' => 'value');
        }

        $this->expectedOptions = array('orderby' => 'someOrderBy');
        $oSrv->expects(self::once())
        ->method('search')
        //damit testen wir ob search mit den daten aus dem filter aufgerufen wird
        ->with($this->expectedFields, $this->expectedOptions)
        //kommt alles korrekt in den view daten an? test weiter unten!
        ->will(self::returnValue($returnValue));

        return $oSrv;
    }

    /**
     * konfigurieren wie oft welche methoden aufgerufen werden sollten.
     *
     * @param unknown_type $oSearchSrv
     * @param unknown_type $sClassToMock
     */
    protected function getActionMock($oSearchSrv, $sClassToMock = 'tx_mklib_action_ListBase', $isOldFilterMode = true)
    {
        $oListBase = $this->getMockForAbstractClass(
            $sClassToMock,
            [],
            '',
            true,
            true,
            true,
            ['getService', 'getTsPathPageBrowser', 'getTemplateName', 'isOldFilterMode']
        );
        //abstrakte methoden mocken
        $oListBase->expects(self::once())
            ->method('getService')
            ->will(self::returnValue($oSearchSrv));

        $oListBase->expects(self::any())
            ->method('getTsPathPageBrowser')
            ->will(self::returnValue('pagebrowser'));

        //damit die konfig stimmt
        $oListBase->expects(self::any())
            ->method('getTemplateName')
            ->will(self::returnValue('dummyconfig'));

        $oListBase->expects(self::any())
            ->method('isOldFilterMode')
            ->will(self::returnValue($isOldFilterMode));

        return $oListBase;
    }

    public function testHandleRequestCallsFilterPageBrowserAndSearchServiceCorrectWithOldFilterMode()
    {
        self::markTestIncomplete("Exception: No extension key found for classname: tx_mklib_tests_fixtures_classes_DummyFilter");

        //mock für den searcher
        $oSrv = $this->getSrvMock();

        //mock für die zu testende action
        $oListBase = $this->getActionMock($oSrv);

        //jetzt den aufruf der action simulieren
        $aConfig = array(
            'dummyconfig.' => array(
                'filter' => 'tx_mklib_tests_fixtures_classes_DummyFilter',
                //fields und options sollte übernommen werden
                'fields.' => array(
                    'anotherTest.' => array(
                        'anotherField.' => array(
                            'OP_EQ' => 'anotherValue',
                        ),
                    ),
                ),
                'options.' => array(
                    'orderby' => 'someOrderBy',
                ),
            ),
        );
        $configurations = $this->getConfigurations($aConfig);

        //handle request sollte nichts zurück geben
        self::assertNull($oListBase->handleRequest($configurations->getParameters(), $configurations, $configurations->getViewData()), 'handleRequest hat doch etwas zurück gegeben!');

        //view daten mit den daten des searchers gefüllt?
        self::assertEquals(array('result' => array('firstItem')), $configurations->getViewData()->offsetGet('items'), 'View daten falsch!');

        $pageBrowserConfig = $configurations->getViewData()->offsetGet('pageBrowserConfig');
        //daten die dem pagebrowser übergeben wurden korrekt?
        self::assertEquals('dummyconfig.pagebrowser', $pageBrowserConfig['confid'], 'daten für den page browser falsch: confid falsch!');
        self::assertEquals($configurations, $pageBrowserConfig['config'], 'daten für den page browser falsch: config falsch!');
        self::assertEquals($this->expectedFields, $pageBrowserConfig['fields'], 'daten für den page browser falsch: fields falsch!');
        self::assertEquals($this->expectedOptions, $pageBrowserConfig['options'], 'daten für den page browser falsch: options falsch!');
        self::assertEquals(array('searchcallback' => array($oSrv, 'search')), $pageBrowserConfig['cfg'], 'daten für den page browser falsch: cfg falsch!');
    }

    public function testHandleRequestCallsFilterPageBrowserAndSearchServiceCorrectWithNewFilterMode()
    {
        self::markTestIncomplete("Exception: No extension key found for classname: tx_mklib_tests_fixtures_classes_DummyFilter");

        //mock für den searcher
        $oSrv = $this->getSrvMock();

        //mock für die zu testende action
        $oListBase = $this->getActionMock($oSrv, 'ListBaseWithNewFilterMode', false);

        //jetzt den aufruf der action simulieren
        $aConfig = array(
            'dummyconfig.' => array(
                'filter.' => array(
                    'class' => 'tx_mklib_tests_fixtures_classes_DummyFilter',
                    //fields und options sollte übernommen werden
                    'fields.' => array(
                        'anotherTest.' => array(
                            'anotherField.' => array(
                                'OP_EQ' => 'anotherValue',
                            ),
                        ),
                    ),
                    'options.' => array(
                        'orderby' => 'someOrderBy',
                    ),
                ),
            ),
        );
        $configurations = $this->getConfigurations($aConfig);

        //handle request sollte nichts zurück geben
        self::assertNull($oListBase->handleRequest($configurations->getParameters(), $configurations, $configurations->getViewData()), 'handleRequest hat doch etwas zurück gegeben!');

        //view daten mit den daten des searchers gefüllt?
        self::assertEquals(array('result' => array('firstItem')), $configurations->getViewData()->offsetGet('items'), 'View daten falsch!');

        $pageBrowserConfig = $configurations->getViewData()->offsetGet('pageBrowserConfig');
        //daten die dem pagebrowser übergeben wurden korrekt?
        self::assertEquals('dummyconfig.pagebrowser', $pageBrowserConfig['confid'], 'daten für den page browser falsch: confid falsch!');
        self::assertEquals($configurations, $pageBrowserConfig['config'], 'daten für den page browser falsch: config falsch!');
        self::assertEquals($this->expectedFields, $pageBrowserConfig['fields'], 'daten für den page browser falsch: fields falsch!');
        self::assertEquals($this->expectedOptions, $pageBrowserConfig['options'], 'daten für den page browser falsch: options falsch!');
        self::assertEquals(array('searchcallback' => array($oSrv, 'search')), $pageBrowserConfig['cfg'], 'daten für den page browser falsch: cfg falsch!');
    }

    public function testHandleRequestSetsNoViewDataIfServiceReturnsNothing()
    {
        self::markTestIncomplete("Exception: No extension key found for classname: tx_mklib_tests_fixtures_classes_DummyFilter");

        //mock für den searcher
        $oSrv = $this->getSrvMock(null, null);

        //mock für die zu testende action
        $oListBase = $this->getActionMock($oSrv);

        //jetzt den aufruf der action simulieren
        $aConfig = array(
            'dummyconfig.' => array(
                'filter' => 'tx_mklib_tests_fixtures_classes_DummyFilter',
                //fields und options sollte übernommen werden
                'fields.' => array(
                    'anotherTest.' => array(
                        'anotherField.' => array(
                            'OP_EQ' => 'anotherValue',
                        ),
                    ),
                ),
                'options.' => array(
                    'orderby' => 'someOrderBy',
                ),
            ),
        );
        $configurations = $this->getConfigurations($aConfig);

        //handle request sollte nichts zurück geben
        self::assertNull($oListBase->handleRequest($configurations->getParameters(), $configurations, $configurations->getViewData()), 'handleRequest hat doch etwas zurück gegeben!');

        //view daten mit den daten des searchers leer?
        self::assertEmpty($configurations->getViewData()->offsetGet('items'), 'View daten falsch!');

        $pageBrowserConfig = $configurations->getViewData()->offsetGet('pageBrowserConfig');
        //daten die dem pagebrowser übergeben wurden korrekt?
        self::assertEquals('dummyconfig.pagebrowser', $pageBrowserConfig['confid'], 'daten für den page browser falsch: confid falsch!');
        self::assertEquals($configurations, $pageBrowserConfig['config'], 'daten für den page browser falsch: config falsch!');
        self::assertEquals($this->expectedFields, $pageBrowserConfig['fields'], 'daten für den page browser falsch: fields falsch!');
        self::assertEquals($this->expectedOptions, $pageBrowserConfig['options'], 'daten für den page browser falsch: options falsch!');
        self::assertEquals(array('searchcallback' => array($oSrv, 'search')), $pageBrowserConfig['cfg'], 'daten für den page browser falsch: cfg falsch!');
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionCode 4001
     * @expectedExceptionMessage Der Service dummySrv muss die Methode search unterstützen!
     */
    public function testHandleRequestThrowsExceptionIfServiceDoesNotSupportSearchCallback()
    {
        self::markTestIncomplete("Failed asserting that 1 is equal to expected exception code 4001.");

        //mock für den searcher
        $oSrv = $this->getMock('dummySrv', array('__toString'));
        $oSrv->expects(self::once())
        ->method('__toString')
        //kommt die exception message richtig ist
        ->will(self::returnValue('dummySrv'));

        //mock für die zu testende action
        $oListBase = $this->getMockForAbstractClass('tx_mklib_action_ListBase');
        //abstrakte methoden mocken
        $oListBase->expects(self::once())
        ->method('getService')
        ->will(self::returnValue($oSrv));

        $configurations = $this->getConfigurations();

        //handle request sollte nichts zurück geben
        $oListBase->handleRequest($configurations->getParameters(), $configurations, $configurations->getViewData());
    }

    public function testHandleRequestCallsNotSearchIfFilterInitReturnedFalse()
    {
        self::markTestIncomplete("Exception: No extension key found for classname: tx_mklib_tests_fixtures_classes_DummyFilterWithReturnFalse");

        //mock für den searcher
        $oSrv = $this->getMock('dummySrv', array('search'));
        $oSrv->expects(self::never())
            ->method('search');

        //mock für die zu testende action
        $oListBase = $this->getActionMock($oSrv);

        //jetzt den aufruf der action simulieren
        $aConfig = array(
            'dummyconfig.' => array(
                'filter' => 'tx_mklib_tests_fixtures_classes_DummyFilterWithReturnFalse',
            ),
        );
        $configurations = $this->getConfigurations($aConfig);

        //handle request sollte nichts zurück geben
        self::assertNull($oListBase->handleRequest($configurations->getParameters(), $configurations, $configurations->getViewData()), 'handleRequest hat doch etwas zurück gegeben!');

        //view daten mit den daten des searchers leer?
        self::assertEmpty($configurations->getViewData()->offsetGet('items'), 'View daten falsch!');
    }
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/interface/class.tx_mklib_interface_IZipCountry.php']) {
    include_once $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/interface/class.tx_mklib_interface_IZipCountry.php'];
}
