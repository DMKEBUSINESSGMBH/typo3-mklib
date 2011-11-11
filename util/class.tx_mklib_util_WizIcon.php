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

/**
 * Diese Klasse fügt das Wizzard Icon hinzu
 *
 *
 * Folgendes muss in die ext_tables.php, um das Icon zu registrieren!
 * // Wizzard Icon
 * if (TYPO3_MODE=='BE') {
 * 	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_mklib_util_WizIcon'] = t3lib_extMgm::extPath($_EXTKEY).'util/class.tx_mklib_util_WizIcon.php';	
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
			'icon'			=>	t3lib_extMgm::extRelPath($this->extKey) . $this->iconPath,
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
		$llFile = t3lib_extMgm::extPath($this->extKey) . 'locallang_db.xml';
		$LOCAL_LANG = t3lib_div::readLLXMLfile($llFile, $GLOBALS['LANG']->lang);
		return $LOCAL_LANG;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mkdownloads/util/class.tx_mklib_util_WizIcon.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mkdownloads/util/class.tx_mklib_util_WizIcon.php']);
}