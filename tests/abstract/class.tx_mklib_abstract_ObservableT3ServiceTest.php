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

tx_rnbase::load('tx_mklib_tests_fixtures_classes_ObservableT3Service');

/**
 * Enter description here ...
 *
 * @author Hannes Bochmann
 */
class tx_mklib_tests_abstract_ObservableT3ServiceTest extends tx_rnbase_tests_BaseTestCase
{
    /**
     * Enter description here ...
     *
     * @var tx_mklib_tests_fixtures_classes_ObservableT3Service
     */
    protected $oObservable;

    /**
     * (non-PHPdoc).
     *
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    public function setUp()
    {
        $this->oObservable = tx_rnbase::makeInstance('tx_mklib_tests_fixtures_classes_ObservableT3Service');
    }

    public function testRegisterNotifyAndUnregisterObservers()
    {
        $oFirstObserver = tx_rnbase::makeInstance('tx_mklib_tests_fixtures_classes_FirstObserver');
        $this->oObservable->registerObserver($oFirstObserver);
        $oSecondObserver = tx_rnbase::makeInstance('tx_mklib_tests_fixtures_classes_SecondObserver');
        $this->oObservable->registerObserver($oSecondObserver);

        $this->oObservable->doSomething(['firstTestData' => 'john doe']);
        $this->oObservable->doSomething(['secondTestData' => 'fran doe']);

        //richtige observer registiert?
        self::assertEquals([
            'tx_mklib_tests_fixtures_classes_FirstObserver' => $oFirstObserver,
            'tx_mklib_tests_fixtures_classes_SecondObserver' => $oSecondObserver,
        ], $this->oObservable->getObservers(), 'Falsche Observer registiert!');

        //jetzt registreirung löschen um zu sehen ob nur noch der eine observer
        //benachrichtigt wird
        $this->oObservable->unregisterObserver($oSecondObserver);

        //richtige observer registiert?
        self::assertEquals([
            'tx_mklib_tests_fixtures_classes_FirstObserver' => $oFirstObserver,
        ], $this->oObservable->getObservers(), 'Falsche Observer registiert!');

        $this->oObservable->doSomething(['thirdTestData' => 'jimmy doe']);

        //richtige Daten in notify angekommen?
        self::assertEquals([
            ['firstTestData' => 'john doe'],
            ['secondTestData' => 'fran doe'],
            ['thirdTestData' => 'jimmy doe'],
        ], $oFirstObserver->aNotified, 'Die daten wurden nicht korrekt an den ersten observer übergeben!');
        self::assertEquals(3, $oFirstObserver->iNotified, 'notify wurde beim ersten Observer nicht oft genug aufgerufen!');

        self::assertEquals([
            ['firstTestData' => 'john doe'],
            ['secondTestData' => 'fran doe'],
        ], $oSecondObserver->aNotified, 'Die daten wurden nicht korrekt an den zweiten observer übergeben!');
        self::assertEquals(2, $oSecondObserver->iNotified, 'notify wurde beim zweiten Observer nicht oft genug aufgerufen!');
    }
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/interface/class.tx_mklib_interface_IZipCountry.php']) {
    include_once $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/interface/class.tx_mklib_interface_IZipCountry.php'];
}
