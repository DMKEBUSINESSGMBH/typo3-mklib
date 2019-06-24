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
class tx_mklib_tests_util_TS_testcase extends Tx_Phpunit_TestCase
{
    public function testGetPagesTsConfigLoadsTsAlwaysNewIfTsAdded()
    {
        $pageTSconfig = tx_mklib_util_TS::getPagesTSconfig();

        self::assertFalse(isset($pageTSconfig['plugin.']['tx_mklib']), 'TS schon geladen');

        tx_rnbase_util_Extensions::addPageTSConfig(
            '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:mklib/static/basic/setup.txt">'
        );

        $pageTSconfig = tx_mklib_util_TS::getPagesTSconfig();
        self::assertTrue(isset($pageTSconfig['plugin.']['tx_mklib']), 'TS nicht geladen');
    }
}
