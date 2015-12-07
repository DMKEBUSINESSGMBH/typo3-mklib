<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_mod1
 *
 *  Copyright notice
 *
 *  (c) 2012 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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


/**
 *  @author Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 */
class tx_mklib_mod1_util_Helper {

	/**
	 * Die dazu das aktuelle item für eine Detailseite zu holen bzw dieses zurückzusetzen.
	 * Dazu muss den Linker einfach folgendes für den action namen liefern: "show" + den eigentlichen key.
	 * 
	 * Dann brauch man in der Detailansicht noch einen Button nach folgendem Schema:
	 * $markerArray['###NEWSEARCHBTN###'] = $formTool->createSubmit('showHowTo[clear]', '###LABEL_BUTTON_BACK###'); 
	 * 
	 * @param string $key
	 * @param tx_rnbase_mod_IModule $module
	 * 
	 * @return tx_rnbase_model_base
	 */
	public static function getCurrentItem($key, tx_rnbase_mod_IModule $module) {
		$itemid = 0;
		$data = tx_rnbase_parameters::getPostOrGetParameter('show' . $key);
		if($data) {
			list($itemid, ) = each($data);
		}
		$dataKey = 'current' . $key;
		if($itemid === 'clear') {
			$data = Tx_Rnbase_Backend_Utility::getModuleData(
				array($dataKey => ''), array($dataKey => '0'),$module->getName() 
			);
			return false;
		}
		// Daten mit Modul abgleichen
		$changed = $itemid ? array($dataKey => $itemid) : array();
		$data = Tx_Rnbase_Backend_Utility::getModuleData(array($dataKey => ''), $changed, $module->getName() );
		$itemid = $data[$dataKey];
		if(!$itemid) {
			return false;
		}
		$modelData = explode('|', $itemid);
		$item = tx_rnbase::makeInstance($modelData[0], $modelData[1]);
		
		if(!$item->isValid()) {
			$item = null; //auf null setzen damit die Suche wieder angezeigt wird
		}
		
		return $item;
	}
}