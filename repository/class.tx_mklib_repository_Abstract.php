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
tx_rnbase::load('tx_mklib_interface_Repository');

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
	implements tx_mklib_interface_Repository, t3lib_Singleton
{

	// 0: Hide record; 1: Soft-delete (via "deleted" field) record; 2: Really DELETE
	const DELETION_MODE_HIDE = 0;
	const DELETION_MODE_SOFTDELETE = 1;
	const DELETION_MODE_REALLYDELETE = 2;

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
		tx_rnbase::load('tx_rnbase_util_SearchBase');
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
	 * @return array[tx_rnbase_model_base]
	 */
	public function findAll() {
		return $this->search(array(), array());
	}

	/**
	 * Search database
	 *
	 * @param array $fields
	 * @param array $options
	 * @return array[tx_rnbase_model_base]
	 */
	public function search(array $fields, array $options) {
		$this->prepareFieldsAndOptions($fields, $options);
		$items = $this->getSearcher()->search($fields, $options);
		return $this->prepareItems($items, $options);
	}

	/**
	 * Search database
	 *
	 * @param array $fields
	 * @param array $options
	 * @return tx_rnbase_model_base
	 */
	public function searchSingle(array $fields = array(), array $options = array()) {
		$options['limit'] = 1;
		$items =  $this->search($fields, $options);
		return !empty($items[0]) ? $items[0] : NULL;
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
			$tableName = $this->getEmptyModel()->getTableName();
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
			// seperate master and overlays
			$master = $overlay = array();
			/* @var $item tx_rnbase_model_base */
			foreach ($items as $item) {
				$uid = (int) $item->getUid();
				$realUid = (int) $item->getProperty('uid');
				if ($uid === $realUid) {
					$master[$uid] = $item;
				} else {
					$overlay[$uid] = $item;
				}
			}
			// merge master and overlays and keep the order!
			$new = array();
			// uniquemode can be master or overlay!
			$preferOverlay = empty($options['uniquemode']) || strtolower($options['uniquemode']) !== 'master';
			foreach ($items as $item) {
				$uid = (int) $item->getUid();
				$new[$uid] = !empty($overlay[$uid]) && $preferOverlay ? $overlay[$uid] : $master[$uid];
			}
			$items = array_values($new);
		}
		return $items;
	}


	/************************
	 * Manipulation methods *
	 ************************/

	/**
	 * Return an instantiated dummy model without any content
	 *
	 * This is used only to access several model info methods like
	 * getTableName(), getColumnNames() etc.
	 *
	 * @deprecated
	 * @return tx_rnbase_model_base
	 */
	protected function getDummyModel() {
		t3lib_div::logDeprecatedFunction();
		return $this->getEmptyModel();
	}

	/**
	 * Return an instantiated dummy model without any content
	 *
	 * This is used only to access several model info methods like
	 * getTableName(), getColumnNames() etc.
	 *
	 * @return tx_rnbase_model_base
	 */
	public function getEmptyModel() {
		return tx_rnbase::makeInstance($this->getWrapperClass());
	}

	/**
	 * Liefert die PageId für diese Tabelle.
	 * Dies kann überschrieben werden, um individuelle pid's zu setzen.
	 *
	 * @return 	int
	 */
	protected function getPid(){
		tx_rnbase::load('tx_mklib_util_MiscTools');
		return tx_mklib_util_MiscTools::getPortalPageId();
	}

	/**
	 * Create a new record
	 *
	 * Note that the PID derived from the EXT:mklib constant "portalPageId"
	 * is inserted.
	 *
	 * @param array		$data
	 * @param string	$table
	 * @return int	UID of just created record
	 */
	public function create(array $data) {
		$model = $this->getEmptyModel();
		$table = $model->getTableName();

		tx_rnbase::load('tx_mklib_util_TCA');
		$data = tx_mklib_util_TCA::eleminateNonTcaColumns($model, $data);
		$data = $this->secureFromCrossSiteScripting($model, $data);

		// setzen wir nur, wenn noch nicht gesetzt!
		// Achtung: wird durch eleminateNonTcaColumns entfernt,
		// wenn pid nicht in der tca steht.
		if (empty($data['pid'])) {
			$data['pid'] = $this->getPid();
		}

		tx_rnbase::load('tx_mklib_util_DB');
		$uid = tx_mklib_util_DB::doInsert($table, $data/*, 1*/);

		return $uid;
	}

	/**
	 * Save model with new data
	 *
	 * Overwrite this method to specify a specialised method signature,
	 * and just call THIS method via parent::handleUpdate().
	 * Additionally, the deriving implementation may perform further checks etc.
	 *
	 * @param tx_rnbase_model_base	$model			This model is being updated.
	 * @param array					$data			New data
	 * @param string				$where			Override default restriction by defining an explicite where clause
	 * @param 	int 	$debug = 0 		Set to 1 to debug sql-String
	 * @param 	mixed 	$noQuoteFields 	Array or commaseparated string with fieldnames
	 * @return tx_rnbase_model_base Updated model
	 */
	public function handleUpdate(
		tx_rnbase_model_base $model, array $data, $where='',
		$debug = 0, $noQuoteFields = ''
	) {
		$table = $model->getTableName();
		$uid = $model->getUid();

		if (!$where) {
			$where = 	'1=1 AND `' . $table .
						'`.`uid`='.$GLOBALS['TYPO3_DB']->fullQuoteStr($uid, $table);
		}

		// remove uid if exists
		if(array_key_exists('uid',$data)) {
			unset($data['uid']);
		}

		// Eleminate columns not in TCA
		tx_rnbase::load('tx_mklib_util_TCA');
		$data = tx_mklib_util_TCA::eleminateNonTcaColumns($model, $data);
		$data = $this->secureFromCrossSiteScripting($model, $data);

		$databaseUtility = $this->getDatabaseUtility();
		$databaseUtility::doUpdate($table, $where, $data, $debug, $noQuoteFields);

		$model->reset();
		return $model;
	}

	/**
	 * @return string
	 */
	protected function getDatabaseUtility() {
		tx_rnbase::load('tx_mklib_util_DB');
		return tx_mklib_util_DB;
	}

	/**
	 * Wrapper for actual deletion
	 *
	 * Delete records according to given ready-constructed "where" condition and deletion mode
	 * @TODO: use tx_mklib_util_TCA::getEnableColumn to get enablecolumns!
	 *
	 * @param string	$table
	 * @param string	$where		Ready-to-use where condition containing uid restriction
	 * @param int		$mode		@see self::handleDelete()
	 *
	 * @return int anzahl der betroffenen zeilen
	 */
	public static function delete($table, $where, $mode) {
		tx_rnbase::load('tx_mklib_util_DB');
		return tx_mklib_util_DB::delete($table, $where, $mode);
	}

	/**
	 * Delete one model
	 *
	 * Overwrite this method to specify a specialised method signature,
	 * and just call THIS method via parent::handleDelete().
	 * Additionally, the deriving implementation may perform further checks etc.
	 *
	 * @param tx_rnbase_model_base	$model		This model is being updated.
	 * @param string				$where		Override default restriction by defining an explicite where clause
	 * @param int					$mode		Deletion mode with the following options: 0: Hide record; 1: Soft-delete (via "deleted" field) record; 2: Really DELETE record.
	 * @param int					$table		Wenn eine Tabelle angegeben wird, wird die des Models missachtet (wichtig für temp anzeigen)
	 * @return tx_rnbase_model_base				Updated (on success actually empty) model.
	 */
	public function handleDelete(tx_rnbase_model_base $model, $where='', $mode=0, $table=null) {
		if(empty($table)) {
			$table = $model->getTableName();
		}

		$uid = $model->getUid();

		if (!$where) {
			$where = '1=1 AND `'.$table.'`.`uid`='.$GLOBALS['TYPO3_DB']->fullQuoteStr($uid, $table);
		}

		$this->delete($table, $where, $mode);

		$model->reset();
		return $model;
	}

	/**
	 * Einen Datensatz in der DB anlegen
	 *
	 * Diese Methode kann in Child-Klassen einfach überschrieben werden um zusätzliche Funktionen
	 * zu implementieren. Dann natürlich nicht vergessen diese Methode via parent::handleCreation()
	 * aufzurufen ;)
	 *
	 * @param 	array 					$data
	 * @return 	tx_rnbase_model_base				Created model.
	 */
	public function handleCreation(array $data){
		// datensatz anlegen and model holen
		$model = $this->findByUid(
			$this->create($data)
		);
		return $model;
	}
	/**
	 * Clears the complete table.
	 */
	public function truncate() {
		$table = $this->getEmptyModel()->getTableName();
		tx_rnbase::load('tx_mklib_util_DB');
		return tx_mklib_util_DB::doQuery('TRUNCATE TABLE ' . $table);
	}

	/**
	 * Schützt die Felder vor Cross-Site-Scripting
	 *
	 * @TODO: model has to implement interface!
	 *
	 * @param tx_rnbase_model_base $model
	 * @param array $data
	 * @return array
	 */
	protected function secureFromCrossSiteScripting($model, array $data) {
		if(!method_exists($model,'getFieldsToBeStripped')) return $data;
		$tags = method_exists($model,'getTagsToBeIgnoredFromStripping') ? $model->getTagsToBeIgnoredFromStripping() : null;
		foreach($model->getFieldsToBeStripped() as $field) {
			if(isset($data[$field])) $data[$field] = strip_tags($data[$field],$tags);
		}
		return $data;
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['tx_mklib_repository_Abstract']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['tx_mklib_repository_Abstract']);
}
