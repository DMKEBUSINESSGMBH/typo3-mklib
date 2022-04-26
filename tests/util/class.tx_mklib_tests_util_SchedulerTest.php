<?php
/**
 * @author Hannes Bochmann
 *
 *  Copyright notice
 *
 *  (c) 2014 Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
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
 * tx_mklib_tests_util_SchedulerTest.
 *
 * @author          Hannes Bochmann <dev@dmk-ebusiness.de>
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class tx_mklib_tests_util_SchedulerTest extends \Sys25\RnBase\Testing\BaseTestCase
{
    protected $languageBackup;

    /**
     * (non-PHPdoc).
     *
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp(): void
    {
        $this->languageBackup = $GLOBALS['LANG']->lang ?? null;
        $GLOBALS['LANG'] = $GLOBALS['LANG'] ?? new \stdClass();
        $GLOBALS['LANG']->lang = 'default';
    }

    /**
     * (non-PHPdoc).
     *
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    protected function tearDown(): void
    {
        $GLOBALS['LANG']->lang = $this->languageBackup;
    }

    /**
     * @group unit
     */
    public function testGetFormattedTime()
    {
        self::markTestIncomplete('Error: Call to undefined method stdClass::sL()');

        self::assertEquals(
            '1 Stunde ',
            tx_mklib_util_Scheduler::getFormattedTime(3600)
        );
    }
}
