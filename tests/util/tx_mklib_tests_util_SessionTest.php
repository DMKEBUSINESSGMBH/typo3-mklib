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
 * tx_mklib_tests_util_SessionTest.
 *
 * @author          Hannes Bochmann
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class tx_mklib_tests_util_SessionTest extends Sys25\RnBase\Testing\BaseTestCase
{
    private array $cookiesBackup;

    /**
     * (non-PHPdoc).
     *
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp(): void
    {
        self::markTestSkipped("Error: Class 'TYPO3\CMS\Core\TimeTracker\NullTimeTracker' not found");

        $this->cookiesBackup = $_COOKIE;
        DMK\Mklib\Utility\Tests::prepareTSFE(['initFEuser' => true, 'force' => true]);
    }

    /**
     * (non-PHPdoc).
     *
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    protected function tearDown(): void
    {
        if (isset($this->cookiesBackup)) {
            $_COOKIE = $this->cookiesBackup;
            tx_mklib_util_Session::removeSessionValue('checkCookieIsSet');
            tx_mklib_util_Session::removeSessionValue('mklibTest');
        }
    }

    #[PHPUnit\Framework\Attributes\DataProvider('getCookies')]
    public function testAreCookiesActivated(array $cookies, bool $expectedReturnValue, bool $setCheckedIfCookiesAreActivatedGetParameter): void
    {
        if ($setCheckedIfCookiesAreActivatedGetParameter) {
            $_GET['checkedIfCookiesAreActivated'] = true;
        }

        $_COOKIE = $cookies;
        self::assertEquals(
            $expectedReturnValue,
            tx_mklib_util_Session::areCookiesActivated(),
            'falscher return'
        );
    }

    public static function getCookies(): array
    {
        return [
            [['fe_typo_user' => ''], true, false],
            [['fe_typo_user' => '123'], true, false],
            [[], false, true],
            [['fe_typo_user' => '123'], true, true],
        ];
    }

    /**
     * @group unit
     */
    public function testSetSessionIdSetsIdAndEmptiesSessionData(): void
    {
        $oldRandomSessionId = uniqid();
        $GLOBALS['TYPO3_REQUEST']->getAttribute('frontend.user')->id = $oldRandomSessionId;
        $GLOBALS['TYPO3_REQUEST']->getAttribute('frontend.user')->sesData = ['something'];

        $newRandomSessionId = uniqid();
        tx_mklib_util_Session::setSessionId($newRandomSessionId);

        self::assertEquals(
            $newRandomSessionId,
            $GLOBALS['TYPO3_REQUEST']->getAttribute('frontend.user')->id,
            'falsche neue session id'
        );

        self::assertEquals(
            [],
            $GLOBALS['TYPO3_REQUEST']->getAttribute('frontend.user')->sesData,
            'session data für neue id nicht leer'
        );
    }

    /**
     * @group unit
     */
    public function testSetSessionIdCallsFetchSessionDataOnFeUser(): void
    {
        $this->markTestSkipped(
            'fetchSessionData was removed in TYPO3 8'
        );
        $GLOBALS['TSFE']->fe_user = $this->getMock(
            Sys25\RnBase\Utility\Typo3Classes::getFrontendUserAuthenticationClass(),
            ['fetchSessionData']
        );
        $GLOBALS['TYPO3_REQUEST']->getAttribute('frontend.user')->expects(self::once())
            ->method('fetchSessionData');

        tx_mklib_util_Session::setSessionId(456);
    }

    /**
     * @group unit
     */
    public function testSetSessionIdCallsFetchUserSessionOnFeUser(): void
    {
        $this->markTestSkipped(
            'fetchUserSession is only present since TYPO3 8'
        );
        $GLOBALS['TSFE']->fe_user = $this->getMock(
            Sys25\RnBase\Utility\Typo3Classes::getFrontendUserAuthenticationClass(),
            ['fetchUserSession']
        );
        $GLOBALS['TYPO3_REQUEST']->getAttribute('frontend.user')->expects(self::once())
            ->method('fetchUserSession');

        tx_mklib_util_Session::setSessionId(456);
    }

    /**
     * @group unit
     */
    public function testSetStoreAndGetSessionValue(): void
    {
        tx_mklib_util_Session::setSessionValue('mklibTest', 'testValue');
        tx_mklib_util_Session::storeSessionData();
        self::assertEquals(
            'testValue',
            tx_mklib_util_Session::getSessionValue('mklibTest')
        );
    }

    /**
     * @group unit
     */
    public function testSetStoreAndGetSessionValueWhenSessionIdSet(): void
    {
        $sessionIdBackup = tx_mklib_util_Session::getSessionId();
        // erstmal Session ID wechseln und Wert setzen
        $newSessionId = $this->getRandomHexString();
        tx_mklib_util_Session::setSessionId($newSessionId);
        tx_mklib_util_Session::setSessionValue('mklibTest', 'testValue');
        tx_mklib_util_Session::storeSessionData();

        // dann den eigentliche Session ID Wert setzen
        tx_mklib_util_Session::setSessionId($sessionIdBackup);
        tx_mklib_util_Session::setSessionValue('mklibTest', 'initialTestValue');
        tx_mklib_util_Session::storeSessionData();

        // dann wieder auf neue Session ID wechseln und prüfen ob
        // Werte korrekt geliefert wernde
        tx_mklib_util_Session::setSessionId($newSessionId);

        self::assertEquals(
            'testValue',
            tx_mklib_util_Session::getSessionValue('mklibTest')
        );

        tx_mklib_util_Session::setSessionId($sessionIdBackup);
    }

    protected function getRandomHexString(): string
    {
        return TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(TYPO3\CMS\Core\Crypto\Random::class)->generateRandomHexString(32);
    }
}
