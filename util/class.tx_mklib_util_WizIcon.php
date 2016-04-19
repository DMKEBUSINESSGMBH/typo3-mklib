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
 ***************************************************************/
tx_rnbase::load('Tx_Rnbase_Utility_T3General');
tx_rnbase::load('tx_rnbase_util_Typo3Classes');

/**
 * Diese Klasse fügt das Wizzard Icon hinzu
 *
 *
 * Folgendes muss in die ext_tables.php, um das Icon zu registrieren!
 * // Wizzard Icon
 * if (TYPO3_MODE=='BE') {
 * 	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_mklib_util_WizIcon'] = tx_rnbase_util_Extensions::extPath($_EXTKEY).'util/class.tx_mklib_util_WizIcon.php';
 * }
 * in der locallang_db.xml der Extension müssen/sollten folgende label gesetzt sein:
 * 		plugin.mklib.label
 * 		plugin.mklib.description
 *
 * @package tx_mklib
 * @subpackage tx_mklib_util
 * @author Michael Wagner
 */
class tx_mklib_util_WizIcon {

	/**
	 * Das muss von der Kindklasse überschrieben werden!!
	 *
	 * @var 	string
	 */
	protected $extKey = 'mklib';

	/**
	 * Der Pfad zum Icon, kann Überschrieben werden, wenn nötig.
	 *
	 * @var 	string
	 */
	protected $iconPath = '/ext_icon.gif';

	/**
	 * Adds the plugin wizard icon
	 *
	 * @param 	array 	Input array with wizard items for plugins
	 * @return 	array 	Modified input array, having the items for plugin added.
	 */
	public function proc($wizardItems)	{
		global $LANG;

		$LL = $this->includeLocalLang();

		$wizardItems['plugins_tx_' . $this->extKey] = array(
			'icon'			=>	tx_rnbase_util_Extensions::extRelPath($this->extKey) . $this->iconPath,
			'title'			=>	$LANG->getLLL('plugin.' . $this->extKey . '.label', $LL),
			'description'	=>	$LANG->getLLL('plugin.' . $this->extKey . '.description', $LL),
			'params'		=>	'&defVals[tt_content][CType]=list&defVals[tt_content][list_type]=tx_' . $this->extKey
		);

		return $wizardItems;
	}

	/**
	 * Laden der Lokalisierung.
	 *
	 * @return 	array
	 */
	public function includeLocalLang()	{
		$llFile = $this->getLocalLangFilePath();
		if (tx_rnbase_util_TYPO3::isTYPO47OrHigher()) {
			$localizationParser = tx_rnbase::makeInstance(
				tx_rnbase_util_Typo3Classes::getLocalizationParserClass()
			);
			$LOCAL_LANG = $localizationParser->getParsedData($llFile, $GLOBALS['LANG']->lang);
		} else {
			$LOCAL_LANG = Tx_Rnbase_Utility_T3General::readLLXMLfile($llFile, $GLOBALS['LANG']->lang);
		}

		return $LOCAL_LANG;
	}

	/**
	 * Der Pfad zur ll, kann Überschrieben werden, wenn nötig.
	 *
	 * @return string
	 */
	protected function getLocalLangFilePath()
	{
		return tx_rnbase_util_Extensions::extPath($this->extKey) . 'locallang_db.xml';
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mkdownloads/util/class.tx_mklib_util_WizIcon.php'])	{
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mkdownloads/util/class.tx_mklib_util_WizIcon.php']);
}