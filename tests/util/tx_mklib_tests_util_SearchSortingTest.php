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
 * Kindklasse der eigentlichen UtilDB, um die Variable $log von setzen zu können.
 */
class tx_mklib_util_testSearchSorting extends tx_mklib_util_SearchSorting
{
    private static bool $test = false;

    public static $data = [
        'tableAliases' => '', 'joinedFields' => '',
        'customFields' => '', 'options' => '',
    ];

    public static function registerSortingAliases(array $tableAliases): void
    {
        // klassenname überschreiben
        self::$className = 'tx_mklib_util_testSearchSorting';
        // hooked zurücksetzen
        self::$hooked = false;
        parent::registerSortingAliases($tableAliases);
    }

    public static function callHook(bool $bTest = false): bool
    {
        self::$test = $bTest;
        // hook aufrufen!!
        Sys25\RnBase\Utility\Misc::callHook('rn_base', 'searchbase_handleTableMapping', self::$data);

        return false === self::$test;
    }

    public static function handleTableMapping(array &$params, &$searcher): void
    {
        if (self::$test) {
            self::$test = false;
        } else {
            parent::handleTableMapping($params, $searcher);
        }
    }
}

/**
 * SearchSorting util tests.
 */
class tx_mklib_tests_util_SearchSortingTest extends Sys25\RnBase\Testing\BaseTestCase
{
    private static mixed $hooks = [];

    protected function setUp(): void
    {
        // alle vorherigen hooks löschen
        self::$hooks = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rn_base']['searchbase_handleTableMapping'] ?? [];
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rn_base']['searchbase_handleTableMapping'] = [];
        unset($GLOBALS['T3_VAR']['callUserFunction']['&tx_mklib_util_testSearchSorting->handleTableMapping']);
        unset($GLOBALS['T3_VAR']['callUserFunction']['&tx_mklib_util_SearchSorting->handleTableMapping']);
    }

    protected function tearDown(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rn_base']['searchbase_handleTableMapping'] = self::$hooks;
    }

    /**
     * Testen ob der Hook richtig registriert wurde und ordenltich aufgerufen wird.
     */
    public function testRegisterHook(): void
    {
        tx_mklib_util_testSearchSorting::registerSortingAliases(['TESTALIAS.test' => 'col']);
        $isHook = tx_mklib_util_testSearchSorting::callHook(true);
        self::assertTrue($isHook, 'Der Hook wurde nicht richtig registriert oder aufgerufen.');
    }

    /**
     * Füllt der Hook die options richtig?
     */
    public function testAddSorting(): void
    {
        self::markTestIncomplete('InvalidArgumentException: No class named &tx_mklib_util_testSearchSorting');

        $tableAliases = ['TESTALIAS' => [], 'ALIAS3' => [], 'ALIAS4' => []];
        $options = [];
        $tableMappings = [
            'table1' => 'ALIAS4',
        ];
        tx_mklib_util_testSearchSorting::$data = [
            'tableAliases' => &$tableAliases, 'joinedFields' => '',
            'customFields' => '', 'options' => &$options,
            'tableMappings' => $tableMappings,
        ];
        tx_mklib_util_testSearchSorting::registerSortingAliases(
            [
                'TESTALIAS', 'ALIAS2', 'ALIAS3' => 'title',
                'ALIAS4.table1' => 'sorting', 'ALIAS4.table2' => 'sorting2',
            ]
        );
        tx_mklib_util_testSearchSorting::callHook();

        self::assertTrue(is_array($options['orderby']), 'orderby wurde nicht gesetzt.');
        self::assertCount(3, $options['orderby'], 'Falsche anzahl an orderbys.');
        self::assertArrayHasKey(
            'TESTALIAS.sorting',
            $options['orderby'],
            'orderby TESTALIAS.sorting wurde nicht gesetzt.'
        );
        self::assertArrayHasKey(
            'ALIAS3.title',
            $options['orderby'],
            'orderby ALIAS3.title wurde nicht gesetzt.'
        );
        self::assertArrayHasKey(
            'ALIAS4.sorting',
            $options['orderby'],
            'orderby ALIAS4.sorting wurde nicht gesetzt.'
        );
        self::assertArrayNotHasKey(
            'ALIAS4.sorting2',
            $options['orderby'],
            'orderby ALIAS4.sorting2 wurde doch gesetzt.'
        );
    }
}
