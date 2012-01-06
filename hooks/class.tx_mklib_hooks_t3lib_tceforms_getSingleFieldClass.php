<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_hooks
 *  @author Hannes Bochmann
 *
 *  Copyright notice
 *
 *  (c) 2011 Hannes Bochmann <hannes.bochmann@das-medienkombinat.de>
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
 * Hooks für das Bearbeiten von einzelnen TCA Feldern.
 */
class tx_mklib_hooks_t3lib_tceforms_getSingleFieldClass {

	/**
	 * Stellt die required eval Funktionalität für select felder in TCA zur Verfügung.
	 * Wenn in einem Select Feld eval = 'required' gesetzt wird
	 * und maxitems = minitems = 1 dann darf kein leerer string gewählt werden.
	 * Beispiel:
	 * 'config' => array (
	 * 		'type' => 'select',
	 * 		'items' => array (
	 * 			array('bitte wählen', ''),
	 * 		),
	 * 		'minitems' => 1,
	 * 		'maxitems' => 1,
	 * 		'eval' => 'required'
	 * )
	 *
	 * @param string $table
	 * @param string $field
	 * @param array $row
	 * @param string $out
	 * @param array $PA Field Konfiguration
	 * @param t3lib_TCEforms $parent
	 * @return void
	 */
	public function getSingleField_postProcess($table, $field, $row, $out, $PA, $parent) {
		//required für select felder
		//wenn eval = required und maxitems = mintitems = 1 gesetzt ist,
		//dann darf kein leerer wert gewählt werden
		if(
			$PA['fieldConf']['config']['type'] == 'select' &&
			preg_match('/required/', $PA['fieldConf']['config']['eval']) &&
			$PA['fieldConf']['config']['minitems'] == 1 &&
			$PA['fieldConf']['config']['maxitems'] == 1){
			$parent->requiredFields[$table.'_'.$row['uid'].'_'.$field] = 'data['.$table.']['.$row['uid'].']['.$field.']';
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/hooks/class.tx_mklib_hooks_t3lib_tceforms_getSingleFieldClass.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/hooks/class.tx_mklib_hooks_t3lib_tceforms_getSingleFieldClass.php']);
}