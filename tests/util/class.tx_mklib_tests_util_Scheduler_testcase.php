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
 * benötigte Klassen einbinden
 */

tx_rnbase::load('tx_mklib_util_Scheduler');
tx_rnbase::load('tx_rnbase_tests_BaseTestCase');

/**
 * tx_mklib_tests_util_Scheduler_testcase
 *
 * @package         TYPO3
 * @subpackage      mklib
 * @author          Hannes Bochmann <dev@dmk-ebusiness.de>
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
class tx_mklib_tests_util_Scheduler_testcase extends tx_rnbase_tests_BaseTestCase
{
    protected $languageBackup;


    /**
     * (non-PHPdoc)
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {
        $this->languageBackup = $GLOBALS['LANG']->lang;

        $GLOBALS['LANG']->lang = 'default';
    }

    /**
     * (non-PHPdoc)
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    protected function tearDown()
    {
        $GLOBALS['LANG']->lang = $this->languageBackup;
    }

    /**
     * @group unit
     */
    public function testGetFormattedTime()
    {
        self::assertEquals(
            '1 Stunde ',
            tx_mklib_util_Scheduler::getFormattedTime(3600)
        );
    }
}
