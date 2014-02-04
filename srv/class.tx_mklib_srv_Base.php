<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_srv
 *
 *  Copyright notice
 *
 *  (c) 2010 - 2013 das MedienKombinat GmbH <kontakt@das-medienkombinat.de>
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
tx_rnbase::load('tx_mklib_util_ServiceRegistry');
tx_rnbase::load('tx_rnbase_util_SearchBase');

/**
 * Base service class
 *
 * @package tx_mklib
 * @subpackage tx_mklib_srv
 * @author Hannes Bochmann <hannes.bochmann@das-medienkombinat.de>
 * @author Michael Wagner <michael.wagner@das-medienkombinat.de>
 */
abstract class tx_mklib_srv_Base extends t3lib_svbase {

	// 0: Hide record; 1: Soft-delete (via "deleted" field) record; 2: Really DELETE
	const DELETION_MODE_HIDE = 0;
	const DELETION_MODE_SOFTDELETE = 1;
	const DELETION_MODE_REALLYDELETE = 2;

	/**
	 * Return name of search class
	 *
	 * @return string
	 */
	abstract public function getSearchClass();

	/**
	 *
	 * @return 	tx_rnbase_util_SearchBase
	 */
	protected function getSearcher(){
		return tx_rnbase_util_SearchBase::getInstance($this->getSearchClass());
	}
	/**
	 *
	 * @TODO: 	Achtung,
	 * 			tx_rnbase_util_SearchBase::getWrapperClass() ist eigentlich protected!
	 *
	 * @return 	string
	 */
	protected function getWrapperClass(){
		return $this->getSearcher()->getWrapperClass();
	}

	/**
	 * Search database
	 *
	 * @param array $fields
	 * @param array $options
	 * @return array[tx_rnbase_model_base]
	 */
	public function search($fields, $options) {
		$this->handleEnableFieldsOptions($fields, $options);
		$this->handleLanguageOptions($fields, $options);
		return $this->getSearcher()->search($fields, $options);
	}

	/**
	 * On default, return hidden and deleted fields in backend
	 * @TODO: realy return deleted fields? make Konfigurable!
	 *
	 * @param array $fields
	 * @param array $options
	 */
	protected function handleEnableFieldsOptions(&$fields, &$options) {
		if (
			TYPO3_MODE == 'BE' &&
			!isset($options['enablefieldsoff']) &&
			!isset($options['enablefieldsbe']) &&
			!isset($options['enablefieldsfe'])
		) {
			$options['enablefieldsoff'] = true;
		}
	}

	/**
	 * Setzt eventuelle Sprachparameter,
	 * damit nur valide Daten für die aktuelle Sprache ausgelesen werden.
	 *
	 * @param array $fields
	 * @param array $options
	 */
	protected function handleLanguageOptions(&$fields, &$options) {
		if (
			!isset($options['i18n'])
			&& !isset($options['ignorei18n'])
			&& !isset($options['enablefieldsoff'])
		) {
			$tableName = $this->getDummyModel()->getTableName();
			$languageField = tx_mklib_util_TCA::getLanguageField($tableName);
			// Die Sprache prüfen wir nur, wenn ein Sprachfeld gesetzt ist.
			if (!empty($languageField)) {
				$tsfe = tx_rnbase_util_TYPO3::getTSFE();
				$languages = array();
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
				$options['i18n'] = implode(',', $languages);
			}
		}
	}

	/**
	 * @param array $fields
	 * @param array $options
	 * @return null || tx_rnbase_model_base
	 */
	public function searchSingle($fields, $options) {
		$options['limit'] = 1;
		$result = $this->search($fields, $options);

		return $result ? $result[0] : null;
	}



	/**
	 * Liefert das erste Element aus dem Ergebniss.
	 * Es wird eine Warnung in die Devlog geschrieben,
	 * wenn mehr als ein Element enthalten ist.
	 *
	 * @param array $items
	 * @return tx_rnbase_model_base
	 */
	protected function getFirstResult(array $items) {
		$result = $this->limitResults($items, 1);
		return empty($result) ? NULL : reset($result);
	}

	/**
	 * Kürzt die Elemente auf die gewünschte Größe.
	 *
	 * Es wird eine Warnung in die Devlog geschrieben,
	 * wenn mehr als die angegebene Anzahl Element enthalten ist.
	 *
	 * Wenn keine Warnung erzeugt werden soll,
	 * muss bei dem $this->search() Aufruf ein Limit mitgegebenwerden
	 * um die Elemente zu beschränken! Oder auch $this->searchSingle aufrufen.
	 *
	 * @param array $items
	 * @param unknown_type $limit
	 * @param array $options
	 * @return array
	 */
	protected function limitResults(array $items, $limit, array $options = array()) {
		// Leer, wir haben nichts zu tun.
		if (empty($items)) {
			return $items;
		}
		$count = count($items);
		// Nur bearbeiten, wenn mehr Elemente vorhanden sind, als notwendig.
		if ($count > $limit) {
			// Wir kürzen die Elemente.
			$items = array_slice($items, 0, $limit);
			// Wir Schreiben einen Log-Eintrag um den Fehler zu melden.
			tx_rnbase::load('tx_rnbase_util_Logger');
			tx_rnbase_util_Logger::warn(
					'There are more elements('.$count.') for limitResults supplied than expected('.$limit.').',
					'mkhoga'
			);
		}
		return $items;
	}

	/**
	 * Search the item for the given uid
	 *
	 * @TODO: das liefert immer ein moddel, auch wenn kein datensatz existiert!
	 * 		  es sollte NULL zurückgegeben werden, wenn kein datensatz existiert!
	 *
	 * @param int $ct
	 * @return tx_rnbase_model_base
	 */
	public function get($uid) {
		return tx_rnbase::makeInstance($this->getWrapperClass(), $uid);
	}

	/**
	 * Find all records
	 *
	 * @return array[tx_rnbase_model_base]
	 */
	public function findAll(){
		return $this->search(array(), array());
	}


	/************************
	 * Manipulation methods *
	 ************************/


	/**
	 * Dummy model instance
	 *
	 * @var tx_rnbase_model_base
	 */
	protected $dummyModel;

	/**
	 * Return an instantiated dummy model without any content
	 *
	 * This is used only to access several model info methods like
	 * getTableName(), getColumnNames() etc.
	 *
	 * @return tx_rnbase_model_base
	 */
	protected function getDummyModel() {
		if (!$this->dummyModel) {
			$this->dummyModel = tx_rnbase::makeInstance($this->getWrapperClass(), array('uid' => 0));
		}
		return $this->dummyModel;
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
		$model = $this->getDummyModel();
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
	 * @return tx_rnbase_model_base Updated model
	 */
	public function handleUpdate(tx_rnbase_model_base $model, array $data, $where='') {

		$table = $model->getTableName(); $uid = $model->getUid();

		if (!$where)
		$where = '1=1 AND `'.$table . '`.`uid`='.$GLOBALS['TYPO3_DB']->fullQuoteStr($uid, $table);

		// remove uid if exists
		if(array_key_exists('uid',$data))
		unset($data['uid']);

		// Eleminate columns not in TCA
		tx_rnbase::load('tx_mklib_util_TCA');
		$data = tx_mklib_util_TCA::eleminateNonTcaColumns($model, $data);
		$data = $this->secureFromCrossSiteScripting($model, $data);

		tx_rnbase::load('tx_mklib_util_DB');
		tx_mklib_util_DB::doUpdate($table, $where, $data);

		$model->reset();
		return $model;
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
	 */
	protected function delete($table, $where, $mode) {
		tx_rnbase::load('tx_mklib_util_DB');
		switch ($mode) {
			// Hide model
			case self::DELETION_MODE_HIDE:
				global $GLOBALS;
				// Set hidden field according to $TCA
				if (!isset($GLOBALS['TCA'][$table]['ctrl']['enablecolumns']['disabled']))
				throw new Exception("tx_mklib_srv_base->delete(): Cannot hide records in table $table - no \$TCA entry found!");

				//else
				$data = array($GLOBALS['TCA'][$table]['ctrl']['enablecolumns']['disabled'] => 1);
				tx_mklib_util_DB::doUpdate($table, $where, $data);
				break;

				// Soft-delete model
			case self::DELETION_MODE_SOFTDELETE:
				global $GLOBALS;
				// Set deleted field according to $TCA
				if (!isset($GLOBALS['TCA'][$table]['ctrl']['delete']))
				throw new Exception("tx_mklib_srv_base->delete(): Cannot soft-delete records in table $table - no \$TCA entry found!");

				//else
				$data = array($GLOBALS['TCA'][$table]['ctrl']['delete'] => 1);
				tx_mklib_util_DB::doUpdate($table, $where, $data);
				break;

				// Really hard-delete model
			case self::DELETION_MODE_REALLYDELETE:
				tx_mklib_util_DB::doDelete($table, $where);
				break;

			default:
				throw new Exception("tx_mklib_srv_base->delete(): Unknown deletion mode ($mode)");

		}
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
		$model = $this->get(
			$this->create($data)
		);
		return $model;
	}
	/**
	 * Clears the complete table.
	 */
	public function truncate() {
		$table = $this->getDummyModel()->getTableName();
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
	private function secureFromCrossSiteScripting($model, array $data) {
		if(!method_exists($model,'getFieldsToBeStripped')) return $data;
		$tags = method_exists($model,'getTagsToBeIgnoredFromStripping') ? $model->getTagsToBeIgnoredFromStripping() : null;
		foreach($model->getFieldsToBeStripped() as $field) {
			if(isset($data[$field])) $data[$field] = strip_tags($data[$field],$tags);
		}
		return $data;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/srv/class.tx_mklib_srv_Base.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/srv/class.tx_mklib_srv_Base.php']);
}