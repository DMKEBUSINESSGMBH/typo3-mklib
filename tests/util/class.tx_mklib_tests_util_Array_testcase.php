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
 * benötigte Klassen einbinden.
 */
tx_rnbase::load('tx_mklib_util_Array');

/**
 * Array util tests.
 */
class tx_mklib_tests_util_Array_testcase extends Tx_Phpunit_TestCase
{
    /**
     * Prüfen ob alle leeren Elemente außer dem array gelöscht werden
     * und keys unberührt bleiben.
     */
    public function testRemoveEmptyValues()
    {
        $aArray = array('ich', 'bin', 1, '', 0, null, 'Array' => array(), 'Test', true, false);
        $aNoEmptyValues = tx_mklib_util_Array::removeEmptyValues($aArray);

        self::assertTrue(is_array($aNoEmptyValues), 'No array given.');
        self::assertEquals(count($aNoEmptyValues), 6, 'Wrong count of entries.');
        //auf die keys im zurück gegebenen und initialen array achten!!!
        self::assertEquals('ich', $aNoEmptyValues[0], '1. wert falsch');
        self::assertEquals('bin', $aNoEmptyValues[1], '2. wert falsch');
        self::assertEquals(1, $aNoEmptyValues[2], '3. wert falsch');
        self::assertEquals(array(), $aNoEmptyValues['Array'], '4. wert falsch');
        self::assertEquals('Test', $aNoEmptyValues[6], '5. wert falsch');
        self::assertEquals(true, $aNoEmptyValues[7], '6. wert falsch');
    }

    /**
     * Prüfen ob alle leeren Elemente auch das array gelöscht werden
     * und keys zurückgesetzt werden.
     */
    public function testRemoveEmptyArrayValuesSimple()
    {
        $aArray = array('ich', 'bin', 1, '', 0, null, 'Array' => array(), 'Test', true, false);
        $aNoEmptyValues = tx_mklib_util_Array::removeEmptyArrayValuesSimple($aArray);

        self::assertTrue(is_array($aNoEmptyValues), 'No array given.');
        self::assertEquals(count($aNoEmptyValues), 5, 'Wrong count of entries.');
        //auf die keys im zurück gegebenen und initialen array achten!!!
        self::assertEquals('ich', $aNoEmptyValues[0], '1. wert falsch');
        self::assertEquals('bin', $aNoEmptyValues[1], '2. wert falsch');
        self::assertEquals(1, $aNoEmptyValues[2], '3. wert falsch');
        self::assertEquals('Test', $aNoEmptyValues[3], '4. wert falsch');
        self::assertEquals(true, $aNoEmptyValues[4], '5. wert falsch');
    }

    public function testFieldsToArray()
    {
        $aArray = array(
                        tx_rnbase::makeInstance('tx_rnbase_model_base', array('uid' => 2, 'name' => 'Model Nr. 2')),
                        tx_rnbase::makeInstance('tx_rnbase_model_base', array('uid' => 5, 'name' => 'Model Nr. 5')),
                        tx_rnbase::makeInstance('tx_rnbase_model_base', array('uid' => 6, 'name' => 'Model Nr. 6')),
                    );
        $aFields = tx_mklib_util_Array::fieldsToArray($aArray, 'name');
        self::assertTrue(is_array($aFields), 'No array given.');
        self::assertEquals(count($aFields), 3, 'Array has a wrong count of entries.');
        self::assertEquals('Model Nr. 2', $aFields[0], 'Wrong name in array key 0.');
        self::assertEquals('Model Nr. 5', $aFields[1], 'Wrong name in array key 1.');
        self::assertEquals('Model Nr. 6', $aFields[2], 'Wrong name in array key 2.');
    }

    public function testinArray()
    {
        $aArray = array('wert1' => 1, 'zwei', 3, 'wert4' => 'vier', '5');

        self::assertTrue(tx_mklib_util_Array::inArray(1, $aArray), '1 wurde nicht gefunden.');
        self::assertTrue(tx_mklib_util_Array::inArray(1, $aArray, true), '1 wurde nicht gefunden.');
        self::assertFalse(tx_mklib_util_Array::inArray(2, $aArray), '2 wurde gefunden.');
        self::assertFalse(tx_mklib_util_Array::inArray(2, $aArray, true), '2 wurde gefunden.');
        self::assertTrue(tx_mklib_util_Array::inArray('zwei', $aArray), 'zwei wurde nicht gefunden.');
        self::assertTrue(tx_mklib_util_Array::inArray('zwei', $aArray, true), 'zwei wurde nicht gefunden.');
        self::assertTrue(tx_mklib_util_Array::inArray(3, $aArray), '3 wurde nicht gefunden.');
        self::assertTrue(tx_mklib_util_Array::inArray(3, $aArray, true), '3 wurde nicht gefunden.');
        self::assertTrue(tx_mklib_util_Array::inArray('3', $aArray), '3 wurde nicht gefunden.');
        self::assertFalse(tx_mklib_util_Array::inArray('3', $aArray, true), '3 wurde gefunden.');
        self::assertTrue(tx_mklib_util_Array::inArray('vier', $aArray), 'vier wurde nicht gefunden.');
        self::assertTrue(tx_mklib_util_Array::inArray('vier', $aArray, true), 'vier wurde nicht gefunden.');
        self::assertTrue(tx_mklib_util_Array::inArray('5', $aArray), '5 wurde nicht gefunden.');
        self::assertTrue(tx_mklib_util_Array::inArray('5', $aArray, true), '5 wurde nichtgefunden.');
        self::assertTrue(tx_mklib_util_Array::inArray(5, $aArray), '5 wurde nicht gefunden.');
        self::assertFalse(tx_mklib_util_Array::inArray(5, $aArray, true), '5 wurde gefunden.');

        self::assertTrue(tx_mklib_util_Array::inArray(array('zwei', 5), $aArray), 'zwei oder 5 wurde nicht gefunden.');
        self::assertFalse(tx_mklib_util_Array::inArray(array('3', 5), $aArray, true), '3 oder 5 wurde gefunden.');
    }

    public function testFieldsToString()
    {
        $aArray = array(
                        tx_rnbase::makeInstance('tx_rnbase_model_base', array('uid' => 2, 'name' => 'Model Nr. 2')),
                        tx_rnbase::makeInstance('tx_rnbase_model_base', array('uid' => 5, 'name' => 'Model Nr. 5')),
                        tx_rnbase::makeInstance('tx_rnbase_model_base', array('uid' => 6, 'name' => 'Model Nr. 6')),
                    );
        $sFields = tx_mklib_util_Array::fieldsToString($aArray, 'name', '<>');

        self::assertTrue(is_string($sFields), 'No string given.');
        self::assertEquals($sFields, 'Model Nr. 2<>Model Nr. 5<>Model Nr. 6', 'Wrong string given.');
    }

    /**
     * @group unit
     */
    public function testCastObjectToArray()
    {
        $object = new CastObjectToArrayTest();
        $object->dynamicVariable = 'dynamicVariable';
        $objectArray = tx_mklib_util_Array::castObjectToArray($object);

        self::assertEquals(
            'publicVariable',
            $objectArray['publicVariable'],
            'publicVariable falsch gecastet'
        );
        self::assertEquals(
            'protectedVariable',
            $objectArray['protectedVariable'],
            'protectedVariable falsch gecastet'
        );
        self::assertEquals(
            'privateVariable',
            $objectArray['privateVariable'],
            'privateVariable falsch gecastet'
        );
        self::assertEquals(
            'publicStaticVariable',
            $objectArray['publicStaticVariable'],
            'publicStaticVariable falsch gecastet'
        );
        self::assertEquals(
            'protectedStaticVariable',
            $objectArray['protectedStaticVariable'],
            'protectedStaticVariable falsch gecastet'
        );
        self::assertEquals(
            'privateStaticVariable',
            $objectArray['privateStaticVariable'],
            'privateStaticVariable falsch gecastet'
        );
        self::assertEquals(
            'dynamicVariable',
            $objectArray['dynamicVariable'],
            'dynamicVariable falsch gecastet'
        );
    }
}

/**
 * @author Hannes Bochmann
 */
class CastObjectToArrayTest
{
    public $publicVariable = 'publicVariable';
    protected $protectedVariable = 'protectedVariable';
    private $privateVariable = 'privateVariable';
    public static $publicStaticVariable = 'publicStaticVariable';
    protected static $protectedStaticVariable = 'protectedStaticVariable';
    private static $privateStaticVariable = 'privateStaticVariable';
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tests/util/class.tx_mklib_tests_util_Array_testcase.php']) {
    include_once $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tests/util/class.tx_mklib_tests_util_Array_testcase.php'];
}
