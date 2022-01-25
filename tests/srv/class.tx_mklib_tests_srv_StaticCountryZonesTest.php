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
class tx_mklib_tests_srv_StaticCountryZonesTest extends \Sys25\RnBase\Testing\BaseTestCase
{
    /**
     * (non-PHPdoc).
     *
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {
        if (!\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('static_info_tables')) {
            $this->markTestSkipped('static_info_tables nicht installiert.');
        }
    }

    /**
     * @group integration
     */
    public function testGetByZnCode()
    {
        $service = tx_mklib_util_ServiceRegistry::getStaticCountryZonesService();
        $models = $service->getByZnCode('al'); //Alabama
        $model = $models[0];

        self::assertInstanceOf(
            'tx_mklib_model_StaticCountryZone',
            $model,
            'Statemodel hat falsche Klasse'
        );
        self::assertEquals(
            'Alabama',
            $model->getZnNameLocal(),
            'Bundesland falsch.'
        );
    }
}
