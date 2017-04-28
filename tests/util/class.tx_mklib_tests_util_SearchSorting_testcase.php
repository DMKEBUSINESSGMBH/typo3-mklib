<?php
/**
 * @package tx_mklib
 * @subpackage tx_mklib_tests_util
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
 * benötigte Klassen einbinden
 */

tx_rnbase::load('tx_mklib_util_SearchSorting');

/**
 * Kindklasse der eigentlichen UtilDB, um die Variable $log von setzen zu können
 */
class tx_mklib_util_testSearchSorting extends tx_mklib_util_SearchSorting
{
    private static $test = false;
    public static $data = array(
                'tableAliases' => '', 'joinedFields' => '',
                'customFields' => '', 'options' => '',
            );
    public static function registerSortingAliases(array $tableAliases)
    {
        // klassenname überschreiben
        self::$className = 'tx_mklib_util_testSearchSorting';
        // hooked zurücksetzen
        self::$hooked = false;
        parent::registerSortingAliases($tableAliases);
    }
    public static function callHook($bTest = false)
    {
        self::$test = $bTest;
        // hook aufrufen!!
        tx_rnbase_util_Misc::callHook('rn_base', 'searchbase_handleTableMapping', self::$data);

        return self::$test === false;
    }
    public static function handleTableMapping(&$params, &$searcher)
    {
        if (self::$test) {
            self::$test = false;

            return true;
        }

        return parent::handleTableMapping($params, $searcher);
    }
}

/**
 * SearchSorting util tests
 * @package tx_mklib
 * @subpackage tx_mklib_tests_util
 */
class tx_mklib_tests_util_SearchSorting_testcase extends Tx_Phpunit_TestCase
{
    private static $hooks = array();

    public function setUp()
    {
        // alle vorherigen hooks löschen
        self::$hooks = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rn_base']['searchbase_handleTableMapping'];
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rn_base']['searchbase_handleTableMapping'] = array();
        unset($GLOBALS['T3_VAR']['callUserFunction']['&tx_mklib_util_testSearchSorting->handleTableMapping']);
        unset($GLOBALS['T3_VAR']['callUserFunction']['&tx_mklib_util_SearchSorting->handleTableMapping']);
    }
    public function tearDown()
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rn_base']['searchbase_handleTableMapping'] = self::$hooks;
    }

    /**
     * Testen ob der Hook richtig registriert wurde und ordenltich aufgerufen wird.
     */
    public function testRegisterHook()
    {
        tx_mklib_util_testSearchSorting::registerSortingAliases(array('TESTALIAS'));
        $isHook = tx_mklib_util_testSearchSorting::callHook(true);
        self::assertTrue($isHook, 'Der Hook wurde nicht richtig registriert oder aufgerufen.');
    }

    /**
     * Füllt der Hook die options richtig?
     */
    public function testAddSorting()
    {
        $tableAliases = array('TESTALIAS' => array(), 'ALIAS3' => array(), 'ALIAS4' => array());
        $options = array();
        $tableMappings = array(
            'table1' => 'ALIAS4'
        );
        tx_mklib_util_testSearchSorting::$data = array(
                'tableAliases' => &$tableAliases, 'joinedFields' => '',
                'customFields' => '', 'options' => &$options,
                'tableMappings' => $tableMappings
            );
        tx_mklib_util_testSearchSorting::registerSortingAliases(
            array(
                'TESTALIAS', 'ALIAS2', 'ALIAS3' => 'title',
                'ALIAS4.table1' => 'sorting', 'ALIAS4.table2' => 'sorting2'
            )
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

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tests/util/class.tx_mklib_tests_util_SearchSorting_testcase.php']) {
    include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tests/util/class.tx_mklib_tests_util_SearchSorting_testcase.php']);
}
