<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 das MedienKombinat GmbH <kontakt@das-medienkombinat.de>
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
 * // Liefert das fertige Model. Intern wird getCurrentUid aufgerufen!
 * $linker->getCurrentItem($mod);
 *
 * @TODO: UnitTests!!!
 *
 * @author Michael Wagner <michael.wagner@das-medienkombinat.de>
 */
class tx_mklib_mod1_linker_ShowDetails {

	private $moduleDataConfId = null;

	/**
	 *
	 * @throws InvalidArgumentException
	 * @param string $moduleDataConfId
	 */
	public function __construct($moduleDataConfId) {
		if (empty($moduleDataConfId)) {
			throw new InvalidArgumentException(
				'Constructor needs a valid moduleDataConfId'
			);
		}
		$this->moduleDataConfId = $moduleDataConfId;
	}

	/**
	 *
	 * @param tx_rnbase_model_base $item
	 * @param tx_rnbase_util_FormTool $formTool
	 * @param array $options
	 * @return string
	 */
	public function makeLink(
			tx_rnbase_model_base $item,
			tx_rnbase_util_FormTool $formTool,
			$options=array()
	) {
		$out = $formTool->createSubmit(
				'showDetails['.get_class($item).']['.$item->getUid().']',
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
			tx_rnbase_model_base $item,
			tx_rnbase_util_FormTool $formTool,
			$options=array()
	) {
		$out = $formTool->createSubmit(
				'showDetails['.get_class($item).'][clear]',
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
			$this->moduleDataConfId.'_class' => '',
			$this->moduleDataConfId.'_id' => '0',
		);

		$params = t3lib_div::_GP('showDetails');
		$params = is_array($params) ? $params : array();
		list($model, $uid) = each($params);
		if (is_array($uid)) {
			list($uid, ) = each($uid);
		}

		if (
			!(empty($model) || empty($uid))
			&& $uid === 'clear'
		){
			t3lib_BEfunc::getModuleData(
				$modSettings,
				$modSettings,
				$mod->getName()
			);
			return 0;
		}
		// else

		$uid = intval($uid);
		$data = t3lib_BEfunc::getModuleData(
			$modSettings,
			(!empty($model) && !empty($uid))
				? array(
					$this->moduleDataConfId.'_class' => $model,
					$this->moduleDataConfId.'_id' => $uid,
				) : array(),
			$mod->getName()
		);

		return intval($data[$this->moduleDataConfId.'_id']);
	}

	/**
	 * Returns the currently selected company or false
	 * @throws InvalidArgumentException
	 * @return tx_mkhoga_models_CouponGroup
	 */
	public function getCurrentItem(
		tx_rnbase_mod_IModule $mod
	) {
		$uid = $this->getCurrentUid($mod);
		if (!$uid) {
			return null;
		}

		$data = t3lib_BEfunc::getModuleData(
				array(),
				array(),
				$mod->getName()
		);

		if (
			   empty($data[$this->moduleDataConfId.'_class'])
			|| empty($data[$this->moduleDataConfId.'_id'])
		) {
			return null;
		}

		$item = tx_rnbase::makeInstance(
			$data[$this->moduleDataConfId.'_class'],
			$data[$this->moduleDataConfId.'_id']
		);

		if(!$item->isValid()) {
			throw new InvalidArgumentException(
				'Model "'.$this->getModelClass().'" with uid ('. $uid .') is invalid.'
			);
		}

		return $item;

	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/mod1/linker/class.tx_mklib_mod1_linker_ShowDetails.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/mod1/linker/class.tx_mklib_mod1_linker_ShowDetails.php']);
}
