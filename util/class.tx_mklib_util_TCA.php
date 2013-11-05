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
 * Util Methoden für die TCA.
 * @author	Hannes Bochmann
 * @package tx_mklib
 * @subpackage tx_mklib_util
 */
class tx_mklib_util_TCA {

	/**
	 * Wurde ext_tables.php der Extension bereits geladen?
	 * @var 	array
	 */
	private static $tcaAdditionsLoaded = array();

	/**
	 * Get DAM TCA for ONE file
	 *
	 *	$options = array(
	 *			'label' => 'Ein Bild',
	 *			'config' => array(
	 *					'maxitems' => 2,
	 *					'size' => 2,
	 *				),
	 *		)
	 *
	 * @param array $ref
	 * @param array $options	These options are merged into the resulting TCA
	 * @return array
	 */
	public static function getDamMediaTCA($ref, $options = array()) {
		if(!is_array($options)) $options = array('type'=>$options);
		tx_rnbase::load('tx_rnbase_util_TSDAM');
		$tca = tx_rnbase_util_TSDAM::getMediaTCA($ref, isset($options['type']) ? $options['type'] : 'image_field');
		unset($options['type']);
		if ($options) {
			foreach ($options as $key=>$option) {
				if (is_array($option)) {
					if (!isset($tca[$key])) $tca[$key] = array();
					foreach ($option as $subkey=>$suboption) $tca[$key][$subkey] = $suboption;
				}
				else $tca[$key] = $option;
			}
		}
		return $tca;
	}
	/**
	 * Get DAM TCA for ONE picture
	 *
	 * @param string $ref
	 * @param array $options	These options are merged into the resulting TCA
	 * @return array
	 */
	public static function getDamMediaTCAOnePic($ref, $options=array()) {
		$options['type'] = 'image_field';
		$options['config']['maxitems'] = 1;
		$options['config']['size'] = 1;
		return self::getDamMediaTCA($ref, $options);

		tx_rnbase::load('tx_rnbase_util_TSDAM');
		$tca = tx_rnbase_util_TSDAM::getMediaTCA($ref, 'image_field');
		$tca['config']['maxitems'] = 1; $tca['config']['size'] = 1;
		if ($options) {
			foreach ($options as $key=>$option) {
				if (is_array($option)) {
					if (!isset($tca[$key])) $tca[$key] = array();
					foreach ($option as $subkey=>$suboption) $tca[$key][$subkey] = $suboption;
				}
				else $tca[$key] = $option;
			}
		}
		return $tca;
	}

	/**
	 * Get DAM TCA for ONE file
	 *
	 * @param array $ref
	 * @param array $options	These options are merged into the resulting TCA
	 * @return array
	 */
	public static function getDamMediaTCAOneFile($ref, $options=array()) {
		$options['type'] = 'media_field';
		$options['config']['maxitems'] = 1;
		$options['config']['size'] = 1;
		return self::getDamMediaTCA($ref, $options);

		tx_rnbase::load('tx_rnbase_util_TSDAM');
		$tca = tx_rnbase_util_TSDAM::getMediaTCA($ref,  'media_field');
		$tca['config']['maxitems'] = 1; $tca['config']['size'] = 1;
		if ($options) {
			foreach ($options as $key=>$option) {
				if (is_array($option)) {
					if (!isset($tca[$key])) $tca[$key] = array();
					foreach ($option as $subkey=>$suboption) $tca[$key][$subkey] = $suboption;
				}
				else $tca[$key] = $option;
			}
		}
		return $tca;
	}

	/**
	 * Eleminate non-TCA-defined columns from given data
	 *
	 * Doesn't do anything if no TCA columns are found.
	 *
	 * @param array					$data	Data to be filtered
	 * @return array						Data now containing only TCA-defined columns
	 */
	public static function eleminateNonTcaColumns(tx_rnbase_model_base $model, array $data) {
		tx_rnbase::load('tx_mklib_util_Array');
		return tx_mklib_util_Array::removeNotIn($data, $model->getColumnNames());
	}
	/**
	 * Eleminate non-TCA-defined columns from given data
	 *
	 * Doesn't do anything if no TCA columns are found.
	 *
	 * @param 	array	$dbColumns		TCA columns
	 * @param 	array	$data			Data to be filtered
	 * @return 	array					Data now containing only TCA-defined columns
	 */
	public static function eleminateNonTcaColumnsByTable($table, array $data) {
		global $TCA; t3lib_div::loadTCA($table);
		tx_rnbase::load('tx_mklib_util_Array');
		return tx_mklib_util_Array::removeNotIn(
				$data,
				isset($TCA[$table]) ? array_keys($TCA[$table]['columns']) : array()
			);
	}

	/**
	 * Liefert den Spaltenname für enablecolumns aus der TCA
	 *
	 * @TODO: t3lib_div::loadTCA($sTableName);
	 * @FIXME: Nicht alle felder stehen unter ctrlo.enablecolumns. siehe: tstamp, crdate, cruser_id, delete, ...
	 *
	 * @param 	string 	$sTableName
	 * @param 	string 	$sColumn (disabled, starttime, endtime, fe_group')
	 * @param 	string 	$sFallback
	 * @return 	string
	 */
	public static function getEnableColumn($sTableName, $sColumn, $sFallback=false){
//		$allowed = array('fe_group', 'delete', 'disabled', 'starttime', 'endtime');
		if(isset($GLOBALS['TCA'][$sTableName])){
			if(isset($GLOBALS['TCA'][$sTableName]['ctrl']['enablecolumns'][$sColumn])) {
				return $GLOBALS['TCA'][$sTableName]['ctrl']['enablecolumns'][$sColumn];
			}
			if($sFallback===false) {
				throw new Exception(__METHOD__.': Enablecolumn "'.$sColumn.'" not found in TCA for table "'.$sTableName.'".');
			}
		}
		if($sFallback===false) {
			throw new Exception(__METHOD__.': Table "'.$sTableName.'" not found in TCA!');
		}
		return $sFallback;
	}

	/**
	 * Taken from tx_div!
	 * Loads TCA additions of other extensions
	 *
	 * Your extension may depend on fields that are added by other
	 * extensions. For reasons of performance parts of the TCA are only
	 * loaded on demand. To ensure that the extended TCA is loaded for
	 * the extensions yours depends you can apply this function.
	 *
	 * @author      Franz Holzinger
	 *
	 * @param 	array 		extension keys which have TCA additions to load
	 * @param 	boolean 	force include
	 * @return 	void
	 */
	public static function loadTcaAdditions($ext_keys, $force=false) {
		global $_EXTKEY, $TCA;
		//Merge all ext_keys
		if (is_array($ext_keys)) {
			for($i = 0; $i < sizeof($ext_keys); $i++){
				if($force || !array_key_exists($ext_keys[$i], self::$tcaAdditionsLoaded)) {
					//Include the ext_table
					$_EXTKEY = $ext_keys[$i];
					include(t3lib_extMgm::extPath($ext_keys[$i], 'ext_tables.php'));
					self::$tcaAdditionsLoaded[$ext_keys[$i]] = 1;
				}
			}
		}
	}

	/**
	 * Liefert Wizard-Daten für die TCA.
	 *
	 * @param 	string 	$sTable
	 * @param 	array 	$options
	 * @return 	array
	 */
	public static function getWizards($sTable, array $options = array()) {
		$bGlobalPid = isset($options['globalPid']) ? $options['globalPid'] : false;
		$wizards = array (
				'_PADDING' => 2,
				'_VERTICAL' => 1,
			);
		if(isset($options['edit'])) {
			$wizards['edit'] = array (
				'type' => 'popup',
				'title' => 'Edit entry', // LLL:EXT:mketernit/locallang.
				'icon' => 'edit2.gif',
				'script' => 'wizard_edit.php',
				'popup_onlyOpenIfSelected' => 1,
				'JSopenParams' => 'height=576,width=720,status=0,menubar=0,scrollbars=1',
			);
			if (is_array($options['edit'])) {
				$wizards['edit']
					= t3lib_div::array_merge_recursive_overrule(
							$wizards['edit'], $options['edit']);
			}
		}
		if(isset($options['add'])) {
			$wizards['add'] = array (
				'type' => 'script',
				'title' => 'Create new entry',
				'icon' => 'add.gif',
				'params' => array (
					'table' => $sTable,
					'pid' => ($bGlobalPid ? '###STORAGE_PID###' : '###CURRENT_PID###'),
					'setValue' => 'prepend',
				),
				'script' => 'wizard_add.php',
			);
			if (is_array($options['add'])) {
				$wizards['add']
					= t3lib_div::array_merge_recursive_overrule(
							$wizards['add'], $options['add']);
			}
		}
		if(isset($options['list'])) {
			$wizards['list'] = array (
				'type' => 'popup',
				'title' => 'List entries',
				'icon' => 'list.gif',
				'params' => array (
					'table' => $sTable,
					'pid' => ($bGlobalPid ? '###STORAGE_PID###' : '###CURRENT_PID###'),
				),
				'script' => 'wizard_list.php',
				'JSopenParams' => 'height=576,width=720,status=0,menubar=0,scrollbars=1',
			);
			if (is_array($options['list'])) {
				$wizards['list']
					= t3lib_div::array_merge_recursive_overrule(
							$wizards['list'], $options['list']);
			}
		}
		if(isset($options['suggest'])) {
			$wizards['suggest'] = array (
				'type' => 'suggest',
				'default' => array(
					'maxItemsInResultList' => 8,
					'searchWholePhrase' => true, // true: LIKE %term% false: LIKE term%
				),
			);
			if (is_array($options['suggest'])) {
				$wizards['suggest']
					= t3lib_div::array_merge_recursive_overrule(
							$wizards['suggest'], $options['suggest']);
			}
		}
		if(isset($options['RTE'])) {
			$wizards['RTE'] = Array(
				'notNewRecords' => 1,
				'RTEonly' => 1,
				'type' => 'script',
				'title' => 'Full screen Rich Text Editing',
				'icon' => 'wizard_rte2.gif',
				'script' => 'wizard_rte.php',
			);
		}
		return $wizards;
	}
	
	/**
	 * @return int
	 */
	public static function getParentUidFromReturnUrl() {
		$parentUid = null;
		
		if(
			($parsedQueryParameters = self::getQueryParametersFromReturnUrl()) &&
			!empty($parsedQueryParameters['P']['uid'])
		) {
			$parentUid = $parsedQueryParameters['P']['uid'];
		} 
		
		return $parentUid;
	}
	
	/**
	 * @return array
	 */
	private static function getQueryParametersFromReturnUrl() {
		$parsedQueryParameters = array();
		
		if(
			($returnUrl = t3lib_div::_GET('returnUrl')) &&
			($parsedUrl = parse_url($returnUrl)) && 
			isset($parsedUrl['query'])
		) {
			parse_str($parsedUrl['query'], $parsedQueryParameters);
		}
		
		return $parsedQueryParameters;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/util/class.tx_mklib_util_TCA.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/util/class.tx_mklib_util_TCA.php']);
}