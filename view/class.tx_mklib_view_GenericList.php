<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_view
 *
 *  Copyright notice
 *
 *  (c) 2011 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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

tx_rnbase::load('tx_rnbase_view_List');

/**
 * Generic list view
 * @package tx_mklib
 * @subpackage tx_mklib_action
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_mklib_view_GenericList extends tx_rnbase_view_List {

  /**
   * Do the output rendering.
   *
   * As this is a generic view which can be called by
   * many different actions we need the actionConfId in
   * $viewData in order to read its special configuration,
   * including redirection options etc.
   *
   * @param string $template
   * @param ArrayObject	$viewData
   * @param tx_rnbase_configurations	$configurations
   * @param tx_rnbase_util_FormatUtil	$formatter
   * @return mixed Ready rendered output or HTTP redirect
   */
	public function createOutput($template, &$viewData, &$configurations, &$formatter) {
		//View-Daten abholen
		$items =& $viewData->offsetGet('items');
		$confId = $this->getController()->getExtendedConfId();

		$itemPath = $this->getItemPath($configurations, $confId);
		$markerClass = $this->getMarkerClass($configurations, $confId);
		
		//Liste generieren
		$listBuilder = tx_rnbase::makeInstance('tx_rnbase_util_ListBuilder');
		$out = $listBuilder->render($items, $viewData, $template, $markerClass,
			$confId.$itemPath.'.', strtoupper($itemPath), $formatter
		);


		return $out;
	}

	
	/**
	* Set the path of the template file.
	*
	*  You can make use the syntax  EXT:myextension/template.php
	*
	* @param	string		path to the file used as templates
	* @return	void
	*/
	public function setTemplateFile($pathToFile) {
		// wenn leer, das template der extended confid holen!
		if (empty($pathToFile)) {
			$confId = $this->getController()->getExtendedConfId();
			$pathToFile = $this->getController()->getConfigurations()->get($confId.'template.path');
		}
		return parent::setTemplateFile($pathToFile);
	}

	/**
	 * Subpart der im HTML-Template geladen werden soll. Dieser wird der Methode
	 * createOutput automatisch als $template übergeben.
	 *
	 * @return string
	 */
	public function getMainSubpart() {
		$confId = $this->getController()->getExtendedConfId();
		$subpart = $this->getController()->getConfigurations()->get($confId.'template.subpart');
		if(!$subpart) {
			$subpart = '###'. strtoupper(substr($confId, 0, strlen($confId)-1)) . '###';
		}
		return $subpart;
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/view/class.tx_mklib_view_GenericList.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/view/class.tx_mklib_view_GenericList.php']);
}