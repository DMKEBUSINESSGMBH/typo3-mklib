<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_util
 *  @author Hannes Bochmann
 *
 *  Copyright notice
 *
 *  (c) 2010 das MedienKombinat GmbH <kontakt@das-medienkombinat.de>
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
 * @errorcodebase 3000
 * @author	Hannes Bochmann
 * @author	Michael Wagner <michael.wagner@das-medienkombinat.de>
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
	 * @FIXME: Nicht alle felder stehen unter ctrlo.enablecolumns. siehe: tstamp, crdate, cruser_id, delete, ...
	 * @TODO: wirft keine exception, wenn ein default wert gegeben ist. das ist allerdings falsch.
	 *
	 * @param 	string 	$tableName
	 * @param 	string 	$column (disabled, starttime, endtime, fe_group')
	 * @param 	string 	$default
	 *
	 * @throws Exception
	 *
	 * @return 	string
	 */
	public static function getEnableColumn($tableName, $column, $default = null){
//		$allowed = array('fe_group', 'delete', 'disabled', 'starttime', 'endtime');
		$fields = self::getCtrlField(
			$tableName,
			'enablecolumns',
			// wenn ein defaultwert definiert ist,
			// wollen wir als fallback immer ein array!
			$default === null ? null: array()
		);
		if(!(is_array($fields) && isset($fields[$column])) && $default === null) {
			throw new LogicException (
				'The enablecolumn "'.$column.'" does not exists in TCA for for table "'.$tableName.'".',
				intval( ERROR_CODE_MKLIB . 3002 )
			);
		}
		return isset($fields[$column]) ? $fields[$column] : $default;
	}

	/**
	 * Liefert den Spaltenname aus dem ctrl der TCA
	 *
	 * @param 	string 	$sTableName
	 * @param 	string 	$sFallback
	 * @return 	string
	 */
	private static function getCtrlField($tableName, $field, $default = null){
		global $TCA; t3lib_div::loadTCA($tableName);
		if(!isset($TCA[$tableName])){
			if ($default !== null) {
				return $default;
			}
			throw new LogicException (
				'The table "'.$tableName.'" not found in TCA!',
				intval( ERROR_CODE_MKLIB . 3001 )
			);
		}
		return isset($TCA[$tableName]['ctrl'][$field])
			? $TCA[$tableName]['ctrl'][$field]
			: $default
		;
	}

	/**
	 * Liefert den Spaltenname für das sys_language_uid feld.
	 *
	 * @param string $tableName
	 * @param mixed $default
	 * @return mixed
	 */
	public static function getLanguageField($tableName, $default = null) {
		return self::getCtrlField($tableName, 'languageField', $default);
	}
	/**
	 * Liefert den Spaltenname für das l18n_parent feld.
	 *
	 * @param string $tableName
	 * @param mixed $default
	 * @return mixed
	 */
	public static function getTransOrigPointerField($tableName, $default = null) {
		return self::getCtrlField($tableName, 'transOrigPointerField', $default);
	}
	/**
	 * Liefert den Spaltenname für das l18n_diffsource feld.
	 *
	 * @param string $tableName
	 * @param mixed $default
	 * @return mixed
	 */
	public static function getTransOrigDiffSourceField($tableName, $default = null) {
		return self::getCtrlField($tableName, 'transOrigDiffSourceField', $default);
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
				$wizards['edit'] =
					t3lib_div::array_merge_recursive_overrule(
						$wizards['edit'], $options['edit']
					);
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
				$wizards['add'] =
					t3lib_div::array_merge_recursive_overrule(
						$wizards['add'], $options['add']
					);
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
				$wizards['list'] =
					t3lib_div::array_merge_recursive_overrule(
						$wizards['list'], $options['list']
					);
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
				$wizards['suggest'] =
					t3lib_div::array_merge_recursive_overrule(
						$wizards['suggest'], $options['suggest']
					);
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

		if(isset($options['link'])) {
			$wizards['link'] = Array(
				'type' => 'popup',
				'title' => 'LLL:EXT:cms/locallang_ttc.xml:header_link_formlabel',
				'icon' => 'link_popup.gif',
				'script' => 'browse_links.php?mode=wizard',
				'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1',
				'params' => Array(
					'blindLinkOptions' => '',
				)
			);
			if (is_array($options['link'])) {
				$wizards['link'] =
					t3lib_div::array_merge_recursive_overrule(
						$wizards['link'], $options['link']
					);
			}
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

	/**
	 * Die Länge kann in $tcaTableInformation['config']['labelLength'] angegeben werden.
	 * Default ist 80 Zeichen.
	 *
	 * @param array $tcaTableInformation
	 *
	 * @return void
	 */
	public static function cropLabels(array &$tcaTableInformation) {
		$items = &$tcaTableInformation['items'];
		$labelLength = self::getLabelLength($tcaTableInformation);

		if(!empty($items)) {
			foreach($items as &$item) {
				$label = &$item[0];
				$label = $GLOBALS['LANG']->csConvObj->crop(
					$GLOBALS['LANG']->charSet, $label, $labelLength, '...'
				);
			}
		}
	}

	/**
	 * @param array $tcaTableInformation
	 *
	 * @return array
	 */
	private static function getLabelLength(array $tcaTableInformation) {
		$labelLength = 80;
		if(
			isset($tcaTableInformation['config']['labelLength']) &&
			intval($tcaTableInformation['config']['labelLength']) > 0
		) {
			$labelLength = $tcaTableInformation['config']['labelLength'];
		}

		return $labelLength;
	}

	/**
	 *
	 * @param boolean $required
	 *
	 * @return array
	 */
	public static function getGermanStatesField($isRequired = false) {
		$tcaFieldConfig = array (
			'exclude'	=> 1,
			'label'		=> 'LLL:EXT:mklib/locallang_db.xml:tt_address.region',
			'config'	=> array (
				'type'	=> 'select',
				'items'	=> array (
					array('LLL:EXT:mklib/locallang_db.xml:please_choose', ''),
				),
				'foreign_table'			=> 'static_country_zones',
				'foreign_table_where' 	=> ' AND static_country_zones.zn_country_iso_nr = 276 ORDER BY static_country_zones.zn_name_local',
				'size' 					=> 1,
			)
		);

		if($isRequired) {
			$tcaFieldConfig['config']['minitems'] = 1;
			$tcaFieldConfig['config']['maxitems'] = 1;
			$tcaFieldConfig['config']['eval'] = 'required';
		}

		return $tcaFieldConfig;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/util/class.tx_mklib_util_TCA.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/util/class.tx_mklib_util_TCA.php']);
}