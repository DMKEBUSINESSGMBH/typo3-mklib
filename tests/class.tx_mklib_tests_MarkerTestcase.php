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
 * Testklasse für Marker Klassen, die ihre Ausgabe über
 * \Sys25\RnBase\Frontend\Marker\Templates::substituteMarkerArrayCached erstellen.
 *
 * Bei den Tests sollte der Cache NIE genutzt werden!
 */
class tx_mklib_tests_MarkerTestcase extends Sys25\RnBase\Testing\BaseTestCase
{
    /**
     * (non-PHPdoc).
     *
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp(): void
    {
        Sys25\RnBase\Frontend\Marker\Templates::disableSubstCache();
    }

    /**
     * (non-PHPdoc).
     *
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    protected function tearDown(): void
    {
        Sys25\RnBase\Frontend\Marker\Templates::enableSubstCache();
    }

    /**
     * @group dummytest
     */
    public function testDummy(): void
    {
    }
}
