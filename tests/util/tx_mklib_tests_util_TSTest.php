<?php

declare(strict_types=1);

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
 * Generic form view test.
 */
class tx_mklib_tests_util_TSTest extends Sys25\RnBase\Testing\BaseTestCase
{
    public function testGetPagesTsConfigLoadsTsAlwaysNewIfTsAdded(): void
    {
        self::markTestIncomplete(
            "TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException: A cache with identifier \"cache_runtime\" does not exist."
        );

        $pageTSconfig = TYPO3\CMS\Backend\Utility\BackendUtility::getPagesTSconfig();

        self::assertFalse(isset($pageTSconfig['plugin.']['tx_mklib']), 'TS schon geladen');

        TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
            '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:mklib/static/basic/setup.txt">'
        );

        $pageTSconfig = TYPO3\CMS\Backend\Utility\BackendUtility::getPagesTSconfig();
        self::assertTrue(isset($pageTSconfig['plugin.']['tx_mklib']), 'TS nicht geladen');
    }
}
