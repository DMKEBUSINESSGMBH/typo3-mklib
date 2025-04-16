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
 * Model util tests.
 */
class tx_mklib_tests_util_MiscToolsTest extends Sys25\RnBase\Testing\BaseTestCase
{
    /**
     * setUp() = Extension-Konfiguration speichern.
     */
    protected function setUp(): void
    {
        DMK\Mklib\Utility\Tests::storeExtConf('mklib');
        DMK\Mklib\Utility\Tests::storeExtConf('mktest');
    }

    /**
     * tearDown() = Extension-Konfiguration zurückspielen.
     */
    protected function tearDown(): void
    {
        DMK\Mklib\Utility\Tests::restoreExtConf('mklib');
        DMK\Mklib\Utility\Tests::restoreExtConf('mktest');
    }

    /**
     * Prüfen ob die richtig Extension Konfiguration geliefert wird.
     */
    public function testGetProxyBeUserId(): void
    {
        self::markTestIncomplete(
            'Uncaught require(typo3-mklib/.Build/Web/typo3conf/LocalConfiguration.php)'
        );

        DMK\Mklib\Utility\Tests::setExtConfVar('proxyBeUserId', 2, 'mklib');

        $val = tx_mklib_util_MiscTools::getProxyBeUserId();
        self::assertEquals($val, 2, 'Falscher BE-User geliefert.');
        self::assertTrue(is_int($val), 'Es wurde kein Integer geliefert.');

        $val = tx_mklib_util_MiscTools::getProxyBeUserId('mktest');
        self::assertEquals($val, 2, 'Falscher BE-User geliefert.');
        self::assertTrue(is_int($val), 'Es wurde kein Integer geliefert.');

        $val = tx_mklib_util_MiscTools::getProxyBeUserId('mktest', false);
        self::assertEquals($val, 0, 'Es wurde ein BE-User geliefert.');

        DMK\Mklib\Utility\Tests::setExtConfVar('proxyBeUserId', '5', 'mktest');

        $val = tx_mklib_util_MiscTools::getProxyBeUserId();
        self::assertEquals($val, 2, 'Falscher BE-User geliefert.');
        self::assertTrue(is_int($val), 'Es wurde kein Integer geliefert.');

        $val = tx_mklib_util_MiscTools::getProxyBeUserId('mktest');
        self::assertEquals($val, 5, 'Falscher BE-User geliefert.');
        self::assertTrue(is_int($val), 'Es wurde kein Integer geliefert.');
    }

    /**
     * Prüfen ob die richtig Extension Konfiguration geliefert wird.
     */
    public function testGetPicturesUploadPath(): void
    {
        self::markTestIncomplete(
            'Uncaught require(typo3-mklib/.Build/Web/typo3conf/LocalConfiguration.php)'
        );

        DMK\Mklib\Utility\Tests::setExtConfVar('picturesUploadPath', 'uploads/tx_mklib', 'mklib');

        self::assertEquals(tx_mklib_util_MiscTools::getPicturesUploadPath(), 'uploads/tx_mklib', 'Falscher Pfad geliefert.');
        self::assertEquals(tx_mklib_util_MiscTools::getPicturesUploadPath([]), 'uploads/tx_mklib', 'Falscher Pfad geliefert.');
        self::assertEquals(tx_mklib_util_MiscTools::getPicturesUploadPath('mktest'), 'uploads/tx_mklib', 'Falscher Pfad geliefert.');
        self::assertFalse(tx_mklib_util_MiscTools::getPicturesUploadPath('mktest', false), 'Es wurde ein Pfad geliefert.');

        DMK\Mklib\Utility\Tests::setExtConfVar('picturesUploadPath', 'uploads/tx_mktest', 'mktest');

        self::assertEquals(tx_mklib_util_MiscTools::getPicturesUploadPath(), 'uploads/tx_mklib', 'Falscher Pfad geliefert.');
        self::assertEquals(tx_mklib_util_MiscTools::getPicturesUploadPath('mktest'), 'uploads/tx_mktest', 'Falscher Pfad geliefert.');
    }

    /**
     * Prüfen ob die richtig Extension Konfiguration geliefert wird.
     */
    public function testGetPortalPageId(): void
    {
        self::markTestIncomplete(
            'Uncaught require(typo3-mklib/.Build/Web/typo3conf/LocalConfiguration.php)'
        );

        DMK\Mklib\Utility\Tests::setExtConfVar('portalPageId', 2, 'mklib');

        $val = tx_mklib_util_MiscTools::getPortalPageId();
        self::assertEquals($val, 2, 'Falsche Page-ID geliefert.');
        self::assertTrue(is_int($val), 'Es wurde kein Integer geliefert.');

        $val = tx_mklib_util_MiscTools::getPortalPageId('mktest');
        self::assertEquals($val, 2, 'Falsche Page-ID geliefert.');
        self::assertTrue(is_int($val), 'Es wurde kein Integer geliefert.');

        $val = tx_mklib_util_MiscTools::getPortalPageId('mktest', false);
        self::assertEquals($val, 0, 'Es wurde eine Page-ID geliefert.');

        DMK\Mklib\Utility\Tests::setExtConfVar('portalPageId', '5', 'mktest');

        $val = tx_mklib_util_MiscTools::getPortalPageId();
        self::assertEquals($val, 2, 'Falsche Page-ID geliefert.');
        self::assertTrue(is_int($val), 'Es wurde kein Integer geliefert.');

        $val = tx_mklib_util_MiscTools::getPortalPageId('mktest');
        self::assertEquals($val, 5, 'Falsche Page-ID geliefert.');
        self::assertTrue(is_int($val), 'Es wurde kein Integer geliefert.');
    }
}
