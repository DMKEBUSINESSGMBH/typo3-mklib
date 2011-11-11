<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_util
 *  @author Hannes Bochmann
 *
 *  Copyright notice
 *
 *  (c) 2010 Hannes Bochmann <hannes.bochmann@das-medienkombinat.de>
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
 * Array Service
 *
 * @author hbochmann
 * @package tx_mklib
 * @subpackage tx_mklib_util
 */
class tx_mklib_util_Array {

	/**
	 * Bereinigt ein Array von allen Werten die leer sind. 
	 * Leere Arrays innerhalb des zu bereinigenden Arrays werden ebenfalls entfernt.
	 * Die Keys werden zurückgesetzt.
	 * 
	 * @see tx_mklib_tests_util_Array_testcase::testRemoveEmptyArrayValuesSimple
	 * 
	 * @author 2011 hbochmann
   	 *
   	 * @param array 	$aArray
   	 * @param array 	$aEmptys 	Alle Werte, die einen leeren Zustand definieren.
   	 * @param boolean 	$bStrict 	Gibt an, ob die Werte Strict (===) verglichen werden oder nicht (==)
   	 * @return array 
   	 */
  	public static function removeEmptyArrayValuesSimple(array $aArray, array $aEmptys = array('',0,'0',null,false,array()), $bStrict=true) {
  		$aRet = array();
    	foreach($aArray as $mValue) {
    		if(!in_array($mValue, $aEmptys, $bStrict)) { $aRet[] = $mValue; }
    	}
    	return $aRet;
  	}
  	
	/**
	 * Bereinigt ein Array von allen Werten die leer sind. 
	 * Leere Arrays innerhalb des zu bereinigenden Arrays bleiben unberührt.
	 * Die Keys werden by default nicht zurückgesetzt!
	 * 
	 * @see tx_mklib_tests_util_Array_testcase::testRemoveEmptyValues
	 * 
	 * @author 2011 mwagner
   	 *
   	 * @param array 	$aArray
   	 * @param boolean 	$bResetIndex	Setzt die Array Keys zurück, falls sie numerisch sind.
   	 * @return array 
   	 */
  	public static function removeEmptyValues(array $aArray, $bResetIndex=false) {
  		$aEmptyElements = array_keys($aArray, '');
  		foreach($aEmptyElements as $key)
			unset($aArray[$key]);
  		return $bResetIndex ? array_merge($aArray) : $aArray;
  	}

	/**
	 * Entfernt alle Keys welche nicht in needle vorhanden sind
	 *
	 * @param 	array	$aData		Zu filternde Daten
	 * @param 	array	$aNeedle	Enthält die erlaubten Keys
	 * @return 	array				
	 */
	public static function removeNotIn(array $aData, array $aNeedle) {
		if (!empty($aNeedle)) {
			foreach (array_keys($aData) as $column) {
				if (!in_array($column, $aNeedle)) {
					unset($aData[$column]);
				}
			}
		}
		return $aData;
	}

  	/**
  	 * Prüft ob ein oder mehrere Werte in einem Array vorhanden sind.
  	 * 
	 * @author 2011 mwagner
	 * 
  	 * @param mixed 	$mNeedle
  	 * @param array 	$aHaystack
  	 * @param boolean 	$bStrict
  	 */
  	public static function inArray($mNeedle, array $aHaystack, $bStrict = false) {
		if(!is_array($mNeedle)) {
			return in_array($mNeedle, $aHaystack, $bStrict);
		}
		foreach($mNeedle as $sNeedle) {
			if(in_array($sNeedle, $aHaystack, $bStrict)){
				return TRUE;
			}
		}
		return FALSE;
  	}
	/**
	 * Erstellt anhand einer Liste von Models/Arrays ein Array mit Werten einer Spalte
	 * 
	 * @author 2011 mwagner
   	 * 
	 * @param tx_rnbase_model_base|array 	$objs
	 * @param string 						$attr
	 * @return array
	 */
	public static function fieldsToArray($aObj, $sAttr) {
		$fieldsArray = array();
		foreach ($aObj As $oObj) {
			$aRecord = is_object($oObj) ? $oObj->record : (is_array($oObj) ? $oObj : array());
			if(isset($aRecord[$sAttr])) {
				$fieldsArray[] = $aRecord[$sAttr];
			}
		}
		return $fieldsArray;
	}
	
	/**
	 * Erstellt anhand einer Liste von Models/Arrays ein String mit Werten einer Spalte
	 * 
	 * @author 2011 mwagner
	 * 
	 * @param tx_rnbase_model_base|array 	$objs
	 * @param string 						$attr
	 * @param string 						$delimiter
	 * @return string
	 */
	public static function fieldsToString($aObj, $sAttr, $sDelimiter=',') {
		$fieldsArray = self::fieldsToArray($aObj, $sAttr);
		return implode($sDelimiter, self::removeEmptyValues($fieldsArray));
	}
	
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/util/class.tx_mklib_util_Array.php']) {
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/util/class.tx_mklib_util_Array.php']);
}
