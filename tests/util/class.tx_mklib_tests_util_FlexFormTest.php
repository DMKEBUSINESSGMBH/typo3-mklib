<?php
/**
 * @author Michael Wagner
 *
 *  Copyright notice
 *
 *  (c) 2011 Michael Wagner <michael.wagner@dmk-ebusiness.de>
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
 * testcase for the flexform util.
 *
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 * @license http://www.gnu.org/licenses/lgpl.html
 *        GNU Lesser General Public License, version 3 or later
 */
class tx_mklib_tests_util_FlexFormTest extends \Sys25\RnBase\Testing\BaseTestCase
{
    /**
     * Testet die getByRefererCallsSearchCorrect Methode.
     *
     * @group unit
     * @test
     */
    public function testFlexForm()
    {
        self::markTestIncomplete(
            "TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException: A cache with identifier \"cache_runtime\" does not exist."
        );

        $util = tx_mklib_util_FlexForm::getInstance($this->getFlexFormFixture());

        self::assertEquals(
            'tx_mklib_action_AbstractList',
            $util->get('action')
        );
        self::assertContains(
            'notfound = 404 Not Found',
            $util->get('flexformTS', 's_tssetup')
        );
        self::assertEquals(
            'tx_mklib_filter_SingleItem',
            $util->get('abstractlist.filter', 's_abstractlist')
        );
    }

    /**
     * Liefert das Fixture XML.
     *
     * @return string
     */
    protected function getFlexFormFixture()
    {
        return <<<FF
<?xml version="1.0" encoding="utf-8" standalone="yes" ?>
<T3FlexForms>
    <data>
        <sheet index="sDEF">
            <language index="lDEF">
                <field index="action">
                    <value index="vDEF">tx_mklib_action_AbstractList</value>
                </field>
            </language>
        </sheet>
        <sheet index="s_tssetup">
            <language index="lDEF">
                <field index="flexformTS">
                    <value index="vDEF">abstractlist.notfound = 404 Not Found</value>
                </field>
            </language>
        </sheet>
        <sheet index="s_abstractlist">
            <language index="lDEF">
                <field index="abstractlist.filter">
                    <value index="vDEF">tx_mklib_filter_SingleItem</value>
                </field>
            </language>
        </sheet>
    </data>
</T3FlexForms>
FF;
    }
}
