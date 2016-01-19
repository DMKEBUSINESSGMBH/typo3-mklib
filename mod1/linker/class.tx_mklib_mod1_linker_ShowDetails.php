<?php
/***************************************************************
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
 ***************************************************************/


/**
 * Generischer Linker für eine Detailseite
 *
 * // Linker Instanz
 * $linker = tx_rnbase::makeInstance(
 *			'tx_mklib_mod1_linker_ShowDetails',
 *			'couponGroup'
 *		)
 * // einen Button, welcher auf die Detailseite zeigt erstellen.
 * $linker->makeLink($item, $mod->getFormTool());
 * // einen Button, welcher wieder auf die Übersichtsseite zeigt erstellen.
 * $linker->makeClearLink($item, $mod->getFormTool());
 *
 * // Liefert die Uid des Datensatzes für die Detailseite, falls vorhanden?
 * // Die ID wird aus den Parametern oder aud den Modul-Daten geholt
 * // Dabei wird gleichzeitig das Clear Event geprüft!
 * $linker->getCurrentUid($mod);
 *
 * @TODO: UnitTests!!!
 *
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_mklib_mod1_linker_ShowDetails {

	private $identifier = null;

	/**
	 *
	 * @throws InvalidArgumentException
	 * @param string $identifier (model or tablename)
	 * 		Wird zum Speichern in den Moduldaten,
	 * 		zum erzeugen der buttons und
	 * 		zum auslesend er Parameter verwendet
	 */
	public function __construct($identifier) {
		if (empty($identifier)) {
			throw new InvalidArgumentException(
				'Constructor needs a valid identifier'
			);
		}
		$this->identifier = $identifier;
	}

	/**
	 *
	 * @param Tx_Rnbase_Domain_Model_RecordInterface $item
	 * @param tx_rnbase_util_FormTool $formTool
	 * @param array $options
	 * @return string
	 */
	public function makeLink(
			Tx_Rnbase_Domain_Model_RecordInterface $item,
			tx_rnbase_util_FormTool $formTool,
			$options=array()
	) {
		$out = $formTool->createSubmit(
				'showDetails['.$this->identifier.']['.$item->getUid().']',
				isset($options['label']) ? $options['label'] : '###LABEL_SHOW_DETAILS###',
				isset($options['confirm']) ? $options['confirm'] : '',
				$options
			);
		return $out;
	}

	/**
	 *
	 * @param tx_rnbase_util_FormTool $formTool
	 * @param array $options
	 * @return string
	 */
	public function makeClearLink(
			// wird eigentlich nicht benötigt.
			Tx_Rnbase_Domain_Model_RecordInterface $item,
			tx_rnbase_util_FormTool $formTool,
			$options=array()
	) {
		$out = $formTool->createSubmit(
				'showDetails['.$this->identifier.'][clear]',
				isset($options['label']) ? $options['label'] : '###LABEL_BTN_NEWSEARCH###',
				isset($options['confirm']) ? $options['confirm'] : '',
				$options
			);
		return $out;
	}

	/**
	 *
	 * @param tx_rnbase_mod_IModule $mod
	 */
	public function getCurrentUid(
		tx_rnbase_mod_IModule $mod
	) {

		$modSettings = array(
			$this->identifier => '0',
		);

		$params = tx_rnbase_parameters::getPostOrGetParameter('showDetails');
		$params = is_array($params) ? $params : array();
		list($model, $uid) = each($params);
		if (is_array($uid)) {
			list($uid, ) = each($uid);
		}

		if (
			!empty($uid)
			&& $uid === 'clear'
		){
			Tx_Rnbase_Backend_Utility::getModuleData(
				$modSettings,
				$modSettings,
				$mod->getName()
			);
			return 0;
		}
		// else

		$uid = intval($uid);
		$data = Tx_Rnbase_Backend_Utility::getModuleData(
			$modSettings,
			$uid
				? array(
					$this->identifier => $uid,
				) : array(),
			$mod->getName()
		);

		return intval($data[$this->identifier]);
	}

}


if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/mod1/linker/class.tx_mklib_mod1_linker_ShowDetails.php'])	{
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/mod1/linker/class.tx_mklib_mod1_linker_ShowDetails.php']);
}
