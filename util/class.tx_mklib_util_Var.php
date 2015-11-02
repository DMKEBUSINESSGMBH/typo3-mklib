<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_util
 *  @author Michael Wagner
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
require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');

/**
 * Klasse enthält allgemeine Funktionen für Variablen
 *
 * @author mwagner
 * @package tx_mklib
 * @subpackage tx_mklib_util
 */
class tx_mklib_util_Var {

	/**
	 * Prüft, ob der Wert TRUE ist
	 *
	 * @author mwagner
	 *
	 * @param 	mixed		Der zu prüfende Wert
	 * @return	boolean 	Ist der Wert TRUE
	 */
	public static function isTrueVal($mVal) {
		return (($mVal === TRUE) || ($mVal == "1") || (strtoupper($mVal) == "TRUE"));
	}

	/**
	 * Prüft, ob der Wert FALSE ist
	 *
	 * @author mwagner
	 *
	 * @param 	mixed		Der zu prüfende Wert
	 * @return	boolean 	Ist der Wert FALSE
	 */
	public static function isFalseVal($mVal) {
		return (($mVal == FALSE) || ($mVal == "0") || (strtoupper($mVal) == "FALSE"));
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/util/class.tx_mklib_util_Var.php']) {
  include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/util/class.tx_mklib_util_Var.php']);
}
