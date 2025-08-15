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
 * Array Service.
 *
 * @author hbochmann
 */
class tx_mklib_util_Array
{
    /**
     * Bereinigt ein Array von allen Werten die leer sind.
     * Leere Arrays innerhalb des zu bereinigenden Arrays werden ebenfalls entfernt.
     * Die Keys werden zurückgesetzt.
     * Nicht für Assoziative Arrays geeignet.
     *
     * @see tx_mklib_tests_util_Array_testcase::testRemoveEmptyArrayValuesSimple
     *
     * @author 2011 hbochmann
     *
     * @param array $emptys alle Werte, die einen leeren Zustand definieren
     * @param bool  $strict Gibt an, ob die Werte Strict (===) verglichen werden oder nicht (==)
     */
    public static function removeEmptyArrayValuesSimple(
        array $array,
        array $emptys = ['', 0, '0', null, false, []],
        $strict = true,
    ): array {
        $ret = [];
        foreach ($array as $value) {
            if (!in_array($value, $emptys, $strict)) {
                $ret[] = $value;
            }
        }

        return $ret;
    }

    /**
     * Bereinigt ein Array von allen Werten die leer sind.
     * Leere Arrays innerhalb des zu bereinigenden Arrays bleiben unberührt.
     * Die Keys werden by default nicht zurückgesetzt!
     * Keys von Assoziative Arrays bleiben bestehen.
     *
     * @see tx_mklib_tests_util_Array_testcase::testRemoveEmptyValues
     *
     * @author 2011 mwagner
     *
     * @param mixed $emptys Single value for empty or array with multiple empty values
     * @param bool  $strict Gibt an, ob die Werte Strict (===) verglichen werden oder nicht (==)
     */
    public static function removeEmptyValues(
        array $array,
        $resetIndex = false,
        mixed $emptys = false,
        $strict = false,
    ): array {
        $emptyKeys = array_keys($array, $emptys, $strict);
        foreach ($emptyKeys as $key) {
            unset($array[$key]);
        }

        return $resetIndex ? array_merge($array) : $array;
    }

    /**
     * Prüft ob ein oder mehrere Werte in einem Array vorhanden sind.
     *
     * @author 2011 mwagner
     *
     * @param bool $bStrict
     */
    public static function inArray($mNeedle, array $aHaystack, $bStrict = false)
    {
        if (!is_array($mNeedle)) {
            return in_array($mNeedle, $aHaystack, $bStrict);
        }

        foreach ($mNeedle as $sNeedle) {
            if (in_array($sNeedle, $aHaystack, $bStrict)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Erstellt anhand einer Liste von Models/Arrays ein Array mit Werten einer Spalte.
     *
     * @author 2011 mwagner
     */
    public static function fieldsToArray($aObj, $sAttr = 'uid'): array
    {
        $fieldsArray = [];
        foreach ($aObj as $oObj) {
            $aRecord = is_object($oObj) ? $oObj->getProperty() : (is_array($oObj) ? $oObj : []);
            if (isset($aRecord[$sAttr])) {
                $fieldsArray[] = $aRecord[$sAttr];
            }
        }

        return $fieldsArray;
    }

    /**
     * Erstellt anhand einer Liste von Models/Arrays ein String mit Werten einer Spalte.
     *
     * @author 2011 mwagner
     */
    public static function fieldsToString($aObj, $sAttr = 'uid', $sDelimiter = ','): string
    {
        $fieldsArray = self::fieldsToArray($aObj, $sAttr);

        return implode($sDelimiter, self::removeEmptyValues($fieldsArray));
    }

    /**
     * @param object $object
     */
    public static function castObjectToArray($object): array
    {
        if (class_exists('ReflectionObject')) {
            return self::castObjectToArrayViaReflection($object);
        }

        $result = (array) $object;
        foreach ($result as $key => $value) {
            $key = self::fixProtectedInstanceVariableNames($key);
            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * @param object $object
     */
    public static function castObjectToArrayViaReflection($object): array
    {
        $result = [];

        $reflectedObject = new ReflectionObject($object);

        $properties = $reflectedObject->getProperties();

        foreach ($properties as $property) {
            $result[$property->getName()] = $property->getValue($object);
        }

        return $result;
    }

    /**
     * Variablennamen von protected Variablen werden beim cast zum array mit
     * x00*x00 geprefixed. Das entfernen wir.
     *
     * @param string $variableName
     *
     * @return string
     */
    protected static function fixProtectedInstanceVariableNames($variableName)
    {
        $matches = [];
        preg_match('/^\x00(?:.*?)\x00(.+)/', $variableName, $matches);

        return [] !== $matches ? $matches[1] : $variableName;
    }
}
