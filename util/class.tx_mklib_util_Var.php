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

/**
 * Klasse enthält allgemeine Funktionen für Variablen.
 *
 * @author mwagner
 */
class tx_mklib_util_Var
{
    /**
     * Prüft, ob der Wert TRUE ist.
     *
     * @author mwagner
     *
     * @param   mixed       Der zu prüfende Wert
     *
     * @return bool Ist der Wert TRUE
     */
    public static function isTrueVal($mVal)
    {
        return (true === $mVal) || ('1' == $mVal) || ('TRUE' == strtoupper($mVal));
    }

    /**
     * Prüft, ob der Wert FALSE ist.
     *
     * @author mwagner
     *
     * @param   mixed       Der zu prüfende Wert
     *
     * @return bool Ist der Wert FALSE
     */
    public static function isFalseVal($mVal)
    {
        return (false == $mVal) || ('0' == $mVal) || ('FALSE' == strtoupper($mVal));
    }
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/util/class.tx_mklib_util_Var.php']) {
    include_once $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/util/class.tx_mklib_util_Var.php'];
}
