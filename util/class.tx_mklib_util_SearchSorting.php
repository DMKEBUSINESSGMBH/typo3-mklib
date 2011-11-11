<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_util
 *  @author Michael Wagner
 *
 *  Copyright notice
 *
 *  (c) 2011 Michael Wagner <michael.wagner@das-medienkombinat.de>
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
 ***************************************************************/
require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');

/**
 * Search Sorting.
 * Die Klasse registriert einen Hook für rnbase,
 * um SQL-Anfragen mit einer sortierung zu versehen.
 * 
 * @see tx_rnbase_util_SearchBase::search -> searchbase_handleTableMapping
 *
 * Nützlich ist dies, wenn Einträge immer nach dem Titel sortiert werden sollen,
 * oder die Ausgabe im FE wie im BE mittels der sorting Spalte sortiert werden sollen.
 *
 * @author mwagner
 * @package tx_mklib
 * @subpackage tx_mklib_util
 */
class tx_mklib_util_SearchSorting {
	
	/**
	 * Liefert den Name dieser Klasse.
	 * Dies ist für Kinds-Klassen wichtig, da die Methoden alle statisch aufgerufen werden.
	 * @TODO: 	wie lässt sich die klasse bei kindsklassen herausfinden!?
	 * 			Statische dinge können leider nicht überschrieben werden.
	 * 			Einzige möglichkeit ist zurzeit, diese Variable durch überschreiben von
	 * 			self::registerSortingAliases zu setzen.
	 * @var 	string
	 */
	protected static $className = __CLASS__;
	
	/**
	 * Wurde der hook bereits gesetzt?
	 * 
	 * @var boolean
	 */
	protected static $hooked = false;
	
	/**
	 * Enthält TableAliases, welche sortiert werden sollen.
	 * 
	 * @var 	array
	 */
	private static $sortingTables = array();
	
	/**
	 * Fügt Tabellen für das Sortieren hinzu und registriert den Hook
	 * 
	 * @TODO: 	das müsste noch umgestellt werden. 
	 * 			Zu übergebende Daten:
	 * 				# $tablename
	 * 				# $tablealias
	 * 				# $sortingfield (optional)
	 * 			Wird kein sorting übergeben, wird es aus der TCA gelesen.
	 * 				($TCA[$tablename]['ctrl']['sortby'])
	 * 
	 * @param array $tableAliases
	 */
	public static function registerSortingAliases(array $tableAliases) {
		if(count($tableAliases)) {
			foreach($tableAliases as $tableAlias => $sortingCol) {
				// wenn der key numeric ist, wurde keine sorting col übergeben!
				if(is_numeric($tableAlias) && $sortingCol) {
					$tableAlias = $sortingCol;
					$sortingCol = 'sorting';
				}
				if(empty($tableAlias)) {
					continue;
				}
				self::$sortingTables[] = array(
						'alias' 	=> $tableAlias,
						'column' 	=> $sortingCol,
					);
			}
			// den hook registrieren
			if(count(self::$sortingTables)) {
				self::registerHook();
			}
		}
	}
	
	/**
	 * Registriert den Hook für rnbase, um die sortierung hinzuzufügen
	 */
	private static function registerHook(){
		if(!self::$hooked) {
			// $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rn_base']['searchbase_handleTableMapping'][] = 'EXT:mklib/util/class.tx_mklib_util_SearchSorting.php:&tx_mklib_util_SearchSorting->handleTableMapping';
			
			// Die Klasse ist schon geladen, wir brauchen den Pfad also nicht.
			$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rn_base']['searchbase_handleTableMapping'][] = '&'.self::$className.'->handleTableMapping';
					
			self::$hooked = true;
		}
	}
	
	/**
	 * Wird von tx_rnbase_util_SearchBase aufgerufen um die Sortierung hinzuzufügen.
	 * 
	 * @param 	array 						$params
	 * @param 	tx_rnbase_util_SearchBase 	$searcher
	 */
	public static function handleTableMapping(&$params, &$searcher) {
		if(count(self::$sortingTables)) {

			$tableAliases 	= & $params['tableAliases'];
			//@TODO: $joinedFields && $customFields zusätzlich zu den $tableAliases beachten!!!
			//$joinedFields 	= & $params['joinedFields'];
			//$customFields 	= & $params['customFields'];
			$options 		= & $params['options'];
		
			// sortierung nur bei self::$sortingTables aufrufen
			foreach(self::$sortingTables as $tableData) {
				$tableAlias = $tableData['alias'];
				$sortingCol = $tableData['column'];
				$field = $tableAlias.'.'.$sortingCol;
				if(isset($tableAliases[$tableAlias]) && !isset($options['orderby'][$field])) {
					// orderby muss ein array sein 
					if(!is_array($options['orderby'])) {
						$options['orderby'] = array();
					}
					// immer zuerst anhand von sorting sortieren!!!
					$options['orderby'] = array($field => 'ASC') + $options['orderby'];
				}
			}
		}
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/util/class.tx_mklib_util_SearchSorting.php']) {
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/util/class.tx_mklib_util_SearchSorting.php']);
}
