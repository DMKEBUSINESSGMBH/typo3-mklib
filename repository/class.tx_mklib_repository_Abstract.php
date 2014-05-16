<?php
/***************************************************************
 *  Copyright notice
 *
 * (c) 2014 DMK E-BUSINESS GmbH <kontakt@dmk-ebusiness.de>
 * All rights reserved
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
require_once t3lib_extMgm::extPath('rn_base', 'class.tx_rnbase.php');
tx_rnbase::load('tx_rnbase_util_SearchBase');

/**
 * Abstracte Repository Klasse
 *
 * @package tx_mklib
 * @subpackage tx_mklib_repository
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
abstract class tx_mklib_repository_Abstract
	implements t3lib_Singleton {

	/**
	 * Liefert den Namen der Suchklasse
	 *
	 * @return 	string
	 */
	abstract protected function getSearchClass();

	/**
	 * Liefert den Searcher
	 *
	 * @return 	tx_rnbase_util_SearchBase
	 */
	protected function getSearcher() {
		$searcher = tx_rnbase_util_SearchBase::getInstance($this->getSearchClass());
		if (!$searcher instanceof tx_rnbase_util_SearchBase) {
			throw new Exception(
				get_class($this) . '->getSearchClass() has to return a classname' .
				' of class which extends tx_rnbase_util_SearchBase!'
			);
		}
		return $searcher;
	}

	/**
	 * Liefert die Model Klasse.
	 *
	 * @return 	string
	 */
	protected function getWrapperClass() {
		return $this->getSearcher()->getWrapperClass();
	}

	/**
	 * Holt einen bestimmten Datensatz aus dem Repo.
	 *
	 * @param integer|array $rowOrUid
	 * @return tx_rnbase_model_base|null
	 */
	public function findByUid($rowOrUid) {
		/* @var $model tx_rnbase_model_base */
		$model = tx_rnbase::makeInstance(
			$this->getWrapperClass(),
			$rowOrUid
		);
		return $model->isPersisted() && $model->isValid() ? $model : NULL;
	}

	/**
	 * Search database
	 *
	 * @param array $fields
	 * @param array $options
	 * @return array[tx_rnbase_model_base]
	 */
	public function search($fields, $options) {
		$this->prepareFieldsAndOptions($fields, $options);
		$items = $this->getSearcher()->search($fields, $options);
		return $this->prepareItems($items, $options);
	}

	/**
	 * @return array[tx_rnbase_model_base]
	 */
	public function findAll() {
		return $this->search(array(), array());
	}

	/**
	 * On default, return hidden and deleted fields in backend
	 *
	 * @param array &$fields
	 * @param array &$options
	 * @return void
	 */
	protected function prepareFieldsAndOptions(&$fields, &$options) {
		$this->handleEnableFieldsOptions($fields, $options);
		$this->handleLanguageOptions($fields, $options);
	}


	/**
	 * On default, return hidden and deleted fields in backend
	 *
	 * @param array &$fields
	 * @param array &$options
	 * @return void
	 */
	protected function handleEnableFieldsOptions(&$fields, &$options) {
		if (
			TYPO3_MODE == 'BE' &&
			!isset($options['enablefieldsoff']) &&
			!isset($options['enablefieldsbe']) &&
			!isset($options['enablefieldsfe'])
		) {
			$options['enablefieldsbe'] = TRUE;
		}
	}

	/**
	 * Setzt eventuelle Sprachparameter,
	 * damit nur valide Daten für die aktuelle Sprache ausgelesen werden.
	 *
	 * @param array &$fields
	 * @param array &$options
	 * @return void
	 */
	protected function handleLanguageOptions(&$fields, &$options) {
		if (
			!isset($options['i18n'])
			&& !isset($options['ignorei18n'])
			&& !isset($options['enablefieldsoff'])
		) {
			$model = tx_rnbase::makeInstance($this->getWrapperClass(), array('uid' => 0));
			$tableName = $model->getTableName();
			$languageField = tx_mklib_util_TCA::getLanguageField($tableName);
			// Die Sprache prüfen wir nur, wenn ein Sprachfeld gesetzt ist.
			if (!empty($languageField)) {
				$tsfe = tx_rnbase_util_TYPO3::getTSFE();
				$languages = array();
				if (isset($options['additionali18n'])) {
					$languages = t3lib_div::trimExplode(',', $options['additionali18n'], TRUE);
				}
				$languages[] = '-1'; // for all languages
				// Wenn eine bestimmte Sprache gesetzt ist,
				// laden wir diese ebenfalls.
				if (is_object($tsfe) && $tsfe->sys_language_content) {
					$languages[] = $tsfe->sys_language_content;
				}
				// andernfalls nutzen wir die default sprache
				else {
					$languages[] = '0'; // default language
				}
				$options['i18n'] = implode(',', array_unique($languages, SORT_NUMERIC));
			}
		}
	}

	/**
	 * Modifiziert die Ergebisliste
	 *
	 * @param array $items
	 * @param array $options
	 * @return array[tx_rnbase_model_base]
	 */
	protected function prepareItems($items, $options) {
		if (!is_array($items)) {
			return $items;
		}
		$items = $this->uniqueItems($items, $options);
		return $items;
	}

	/**
	 * Entfernt alle doppelten Datensatze, wenn die Option distinct gesetzt ist.
	 * Dabei werden die Sprachoverlays bevorzugt.
	 *
	 * @param array $items
	 * @param unknown_type $options
	 * @return array[tx_rnbase_model_base]
	 */
	protected function uniqueItems(array $items, $options) {
		// uniqueue, if there are models and the distinct option
		if (
			reset($items) instanceof tx_rnbase_model_base
			&& isset($options['distinct'])
			&& $options['distinct']
		) {
			$master = $overlay = array();
			/* @var $item tx_rnbase_model_base */
			foreach ($items as $item) {
				$uid = $item->getUid();
				if ($uid === $item->uid) {
					$master[$uid] = $item;
				} else {
					$overlay[$uid] = $item;
				}
			}
			// array_merge doesnt work, it resets the numerical keys.
			// so twoe models with the same master uid in both arrays
			// are both existance in the merged array
			// the + array union operator doesnt overide exiting keys
			// and adds only new keys
			$items = ($overlay + $master);
			// we put back the keys
			$items = array_values($items);
		}
		return $items;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['tx_mklib_repository_Abstract']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['tx_mklib_repository_Abstract']);
}