<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_util
 *  @author Michael Wagner
 *
 *  Copyright notice
 *
 *  (c) 2011 michael Wagner <michael.wagner@dmk-ebusiness.de>
 *  All rights reserved
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 ***************************************************************/


tx_rnbase::load('tx_rnbase_util_DB');
tx_rnbase::load('tx_mklib_util_TCA');
tx_rnbase::load('tx_rnbase_util_Strings');

/**
 * Beinhaltet Utility-Methoden für Datenbank handling
 *
 * @package tx_mklib
 * @subpackage tx_mklib_util
 * @author Michael Wagner
 */
class tx_mklib_util_DB extends tx_rnbase_util_DB {

	/**
	 * @var int
	 */
	const DELETION_MODE_HIDE = 0;

	/**
	 * @var int
	 */
	const DELETION_MODE_SOFTDELETE = 1;

	/**
	 * @var int
	 */
	const DELETION_MODE_REALLYDELETE = 2;

	/**
	 * Enthält alle Tabellen, für welche die TCA
	 * über tx_mklib_util_DB::loadTCA bereits geladen wurde.

	 * @var array
	 */
	private static $aTCACache = array();

	/**
	 * Is logging enabled? (protected für Tests)
	 * @var 	boolean
	 */
	protected static $log = -1;
	/**
	 * Dont log this tables
	 * @var array
	 */
	protected static $ignoreTables = -1;

	/**
	 * Insert crdate and timestamp into correct field (gotten from TCA)
	 *
	 * @param 	array 	$data
	 * @param 	string 	$tablename
	 * @return 	array
	 */
	private static function insertCrdateAndTimestamp($data, $tablename) {
		global $GLOBALS;
		// Force creation of timestamp
		if (
				isset($GLOBALS['TCA'][$tablename]['ctrl']['crdate'])
			&& !isset($data[$GLOBALS['TCA'][$tablename]['ctrl']['crdate']])
		) {
			$data[$GLOBALS['TCA'][$tablename]['ctrl']['crdate']] = $GLOBALS['EXEC_TIME'];
		}

		return self::insertTimestamp($data, $tablename);
	}

	/**
	 * Insert timestamp into correct field (gotten from TCA)
	 *
	 * @param 	array 	$data
	 * @param 	string 	$tablename
	 * @return 	array
	 */
	private static function insertTimestamp($data, $tablename) {
		global $GLOBALS;
		// Force creation of timestamp
		if (
				isset($GLOBALS['TCA'][$tablename]['ctrl']['tstamp'])
			&& !isset($data[$GLOBALS['TCA'][$tablename]['ctrl']['tstamp']])
		) {
			$data[$GLOBALS['TCA'][$tablename]['ctrl']['tstamp']] = $GLOBALS['EXEC_TIME'];
		}

		return $data;
	}

	/**
	 * Escaping and quoting values for SQL statements.
	 * Usage count/core: 100
	 *
	 * @param	string		Input string
	 * @param	string		Table name for which to quote string. Just enter the table that the field-value is selected from (and any DBAL will look up which handler to use and then how to quote the string!).
	 * @return	string		Output string; Wrapped in single quotes and quotes in the string (" / ') and \ will be backslashed (or otherwise based on DBAL handler)
	 * @see quoteStr()
	 */
	public static function fullQuoteStr($value, $tableAlias = '') {
		return $GLOBALS['TYPO3_DB']->fullQuoteStr($value, $tableAlias);
	}

	/**
	 * Make a SQL INSERT Statement
	 *
	 * @param 	string 	$tablename
	 * @param 	array 	$values
	 * @param 	int 	$debug = 0 		Set to 1 to debug sql-String
	 * @param 	array 	$options
	 * @return 	int 	UID of created record
	 */
	public static function doInsert($tablename, $values, $debug=0, array $options = array()) {
		if($options['eleminateNonTcaColumns']) $values = tx_mklib_util_TCA::eleminateNonTcaColumnsByTable($tablename, $values);
		$newUid = tx_rnbase_util_DB::doInsert(
						$tablename,
						self::insertCrdateAndTimestamp($values, $tablename),
						$debug
					);
		self::log('doInsert', $tablename, '1=1 AND `'.$tablename.'`.`uid`=\''.$newUid.'\'', $values);
		return $newUid;
	}

	/**
	 * Aktualisiert einen Datensatz
	 *
	 * @param 	string 	$tablename
	 * @param 	string 	$where
	 * @param 	array 	$values
	 * @param 	int 	$debug = 0 		Set to 1 to debug sql-String
	 * @param 	mixed 	$noQuoteFields 	Array or commaseparated string with fieldnames
	 * @param 	array 	$options
	 * @return 	int 	number of rows affected
	 */
	public static function doUpdate($tablename, $where, $values, $debug=0, $noQuoteFields = false, array $options = array()) {
		if($options['eleminateNonTcaColumns']) $values = tx_mklib_util_TCA::eleminateNonTcaColumnsByTable($tablename, $values);
		$res = parent::doUpdate(
						$tablename, $where,
						self::insertTimestamp($values, $tablename),
						$debug, $noQuoteFields
					);
		self::log('doUpdate', $tablename, $where, $values);
		return $res;
	}

	/**
	 * Löscht einen Datensatz
	 *
	 * @param 	string 		$tablename
	 * @param 	string 		$where
	 * @param 	boolean 	$debug
	 * @return 	int 		number of rows affected
	 */
	public static function doDelete($tablename, $where, $debug=0) {
		$res = parent::doDelete($tablename, $where, $debug);
		self::log('doDelete', $tablename, $where);
		return $res;
	}

	/**
	 * Führt ein SELECT aus
	 *
	 * @param 	string 		$what 		requested columns
	 * @param 	string 		$from 		either the name of on table or an array with index 0 the from clause
	 *              					and index 1 the requested tablename and optional index 2 a table alias to use.
	 * @param 	array 		$arr 		the options array
	 * @param 	boolean 	$debug = 0 	Set to 1 to debug sql-String
	 * @return 	array
	 */
	public static function doSelect($what, $from, $arr, $debug=0){
		return parent::doSelect($what, $from, $arr, $debug);
	}

	/**
	 * Make a plain SQL Query.
	 * Notice: The db resource is not closed by this method. The caller is in charge to do this!
	 *
	 * @TODO: logging integrieren!
	 *
	 * @param string $sqlQuery
	 * @param int $debug
	 * @return result pointer for SELECT, EXPLAIN, SHOW, DESCRIBE or boolean
	 */
	public static function doQuery($query, array $options = array()) {
		return parent::doQuery($query);
	}

	/**
	 * Läd die TCA für eine Tabelle.
	 * Optional kann geprüft werden, ob eine Spalte in der TCA für diese Tabelle existiert.
	 *
	 * @TODO: Änderung an der TCA durch fremde Extensions berücksichtigen.
	 * Vor dem aufruf muss tx_mklib_util_TCA::loadTcaAdditions(array('extkey')); aufgerufen werden.
	 *
	 * @param 	string 	$sTable
	 * @param 	string 	$sField		False if no field check is needed.
	 * @return 	boolean				Returns true if field exists.
	 * @deprecated Wird nicht mehr benötigt
	 */
	private static function loadTCA($sTable, $sField = false){
		return (!$sField || ($sField && is_array($GLOBALS['TCA'][$sTable]['columns'][$sField]['config'])))
					? true : false;
	}

	/* *** ************ *** *
	 * *** MM FUNCTIONS ***
	 * *** ************ *** */

	/**
	 * Prüft ob ein MM eintrag bereits existiert.
	 *
	 * @TODO: write tests
	 *
	 * @param 	string 	$sTable
	 * @param 	string 	$sField
	 * @param 	int 	$sLocalId
	 * @param 	int 	$iForeignId
	 * @return 	boolean
	 */
	public static function mmExists($sTable, $sField, $sLocalId, $iForeignId){
		if(!self::loadTCA($sTable)) { return false; }

		$sMmTable = self::mmGetTable($sTable, $sField);

		$where = implode(
					' AND ',
					self::mmGetData($sTable, $sField, $sLocalId, $iForeignId, true)
				);

		$options = array(
			'where' => $where,
			'enablefieldsoff' => true,
//			'debug' => 1,
		);

		tx_rnbase::load('tx_mklib_util_DB');
		$ret = self::doSelect('COUNT(*) as cnt', $sMmTable, $options);

		return count($ret) ? (intval($ret[0]['cnt']) > 0) : false;
	}

	/**
	 * Liefert alle Einträge zu einem Feld.
	 *
	 * @TODO: write tests
	 *
	 * @param 	string 	$sTable
	 * @param 	string 	$sField
	 * @param 	int 	$sLocalId
	 * @param 	int 	$iForeignId
	 * @return 	boolean
	 */
	public static function mmSelectForeign($sTable, $sField, $sLocalId, array $options = array()) {
		if(!self::loadTCA($sTable)) { return false; }

		$sMmTable = self::mmGetTable($sTable, $sField);
		$sForeignTable = self::mmGetTable($sTable, $sField, 'foreign_table');
		$where = implode(' AND ', self::mmGetData($sTable, $sField, $sLocalId, false, true) );
		$sJoin = $sForeignTable.' JOIN '.$sMmTable.' on '.$sMmTable.'.uid_foreign = '.$sForeignTable.'.uid';

		$options['where'] = (isset($options['where']) ? $options['where'].' AND ' : '') . $where;

		return self::doSelect(
					$sForeignTable.'.*',
					array($sJoin, $sForeignTable),
					$options
//					, 1
				);
	}

	/**
	 * Erstellt einen Eintrag in einer MM Tabelle.
	 *
	 * @TODO: write tests
	 *
	 * @param 	string 	$sTable
	 * @param 	string 	$sField
	 * @param 	int 	$sLocalId
	 * @param 	int 	$iForeignId
	 * @return 	boolean
	 */
	public static function mmCreate($sTable, $sField, $sLocalId, $iForeignId) {
		if(!self::loadTCA($sTable)) { return false; }

		// Der mm Eintrag existiert bereits
		if(self::mmExists($sTable, $sField, $sLocalId, $iForeignId)){
			return true;
		}

		$sMmTable = self::mmGetTable($sTable, $sField);
		$aData = self::mmGetData($sTable, $sField, $sLocalId, $iForeignId);
		self::doInsert($sMmTable, $aData/*, 1*/);
		return true; //$this->mmExists($sTable, $sField, $sLocalId, $iForeignId);
	}

	/**
	 * Liefert den Tabellenname für die MM Tabelle aus der TCA
	 *
	 * @TODO: write tests
	 *
	 * @param 	string 	$sTable
	 * @param 	string 	$sField
	 * @return 	string
	 */
	private static function mmGetTable($sTable, $sField, $sCF='MM') {
		if(!self::loadTCA($sTable)) { return false; }
		return $GLOBALS['TCA'][$sTable]['columns'][$sField]['config'][$sCF];
	}

	/**
	 * Erstellt einen Eintrag in einer MM Tabelle.
	 *
	 * @TODO: write tests
	 *
	 * @param 	string 	$sTable
	 * @param 	string 	$sField
	 * @param 	int 	$sLocalId
	 * @param 	int 	$iForeignId
	 * @param 	boolean	$bWhere
	 * @return 	array
	 */
	private static function mmGetData($sTable, $sField, $sLocalId = false, $iForeignId = false, $bWhere = false) {
		if(!self::loadTCA($sTable)) { return false; }

		$aFieldConfig = $GLOBALS['TCA'][$sTable]['columns'][$sField]['config'];

		$sMmTable = $aFieldConfig['MM'];

		// wenn opposite_field dann die uid felder vertauschen
		$sLocalField = isset($aFieldConfig['MM_opposite_field']) ? 'uid_foreign' : 'uid_local';
		$sForeignField = isset($aFieldConfig['MM_opposite_field']) ? 'uid_local' : 'uid_foreign';

		$aData = is_array($aFieldConfig['MM_match_fields']) ? $aFieldConfig['MM_match_fields'] : array();
		$aData = is_array($aFieldConfig['MM_insert_fields']) ? $aFieldConfig['MM_insert_fields'] : $aData;
		if($sLocalId) {
			$aData[$sLocalField] = $sLocalId;
		}
		if($iForeignId) {
			$aData[$sForeignField] = $iForeignId;
		}

		if($bWhere){
			// Anhand der Daten das WHERE aufbauen
			$where = array();
			foreach($aData as $sField => $sValue) {
				$where[] = $sMmTable.'.'.$sField.' = \''.$sValue.'\'';
			}
			return $where;
		}

		return $aData;
	}


	/* *** **************** *** *
	 * *** LOGGIN FUNCTIONS ***
	 * *** **************** *** */

	/**
	 * Is logging enabled?
	 *
	 * @param 	string 	$tablename
	 * @return 	boolean
	 */
	private static function isLog($tablename){
		if(self::$log == -1) {
			// erst die Extension Konfiguration fragen!
			tx_rnbase::load('tx_rnbase_configurations');
			self::$log = intval(tx_rnbase_configurations::getExtensionCfgValue('mklib', 'logDbHandler'));
			if(self::$log) {
				tx_rnbase::load('tx_rnbase_util_Logger');
				self::$log = tx_rnbase_util_Logger::isNoticeEnabled();
			}
		}
		if(self::$log){
			// ignore tables besorgen
			if(!is_array(self::$ignoreTables)){
				self::$ignoreTables = tx_rnbase_util_Strings::trimExplode(',',
									tx_rnbase_configurations::getExtensionCfgValue('mklib', 'logDbIgnoreTables'),
								true);
			}
			// tabelle loggen ?
			if(in_array($tablename,self::$ignoreTables)) {
				return false;
			}
		}
		return self::$log;
	}

	/**
	 * Logs DB changes
	 *
	 * @TODO: t3users log nutzen, wenn installiert! tx_t3users_util_ServiceRegistry::getLoggingService();
	 *
	 * @param 	string 	$msg
	 * @param 	string 	$tablename
	 * @param 	string 	$where
	 * @param 	mixed 	$values
	 */
	private static function log($msg, $tablename, $where=false, $values=false){
		if(!self::isLog($tablename)) return false;
		// else

		// daten sammeln
		$data = array();
		$data['fe_user'] = isset($GLOBALS['TSFE']->fe_user->user['uid']) ? $GLOBALS['TSFE']->fe_user->user['uid'] : 'none';
		$data['be_user'] = (array_key_exists('BE_USER', $GLOBALS) && is_object($GLOBALS['BE_USER'])) ? $GLOBALS['BE_USER']->user['uid'] : 'none';
		$data['tablename'] = $tablename;
		if($where) $data['where'] = $where;
		if($values) $data['values'] = $values;
		// backtrace Konfigurierbar machen?
		tx_rnbase::load('tx_mklib_util_Logger');
		$data['debug_backtrace'] = tx_mklib_util_Logger::getDebugBacktrace();

		// wurde auf hidden gesetzt?
		$disabled = tx_mklib_util_TCA::getEnableColumn($tablename, 'disabled', 'hidden');
		if($values && isset($values[$disabled]) && $values[$disabled]) $msg .= '->disabled';
		// wurde gelöscht?
		$delete = tx_mklib_util_TCA::getEnableColumn($tablename, 'delete', 'deleted');
		if($values && isset($values[$delete]) && $values[$delete]) $msg .= '->delete';

		// tabellenname ergänzen
		$msg .= '('.$tablename.')';

		tx_rnbase_util_Logger::notice($msg, 'mklib', $data);
		return true;
	}

	private function getBackTrace() {

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
		$affectedRows = 0;
		switch ($mode) {
			// Hide model
			case self::DELETION_MODE_HIDE:
				global $GLOBALS;
				// Set hidden field according to $TCA
				if (!isset($GLOBALS['TCA'][$table]['ctrl']['enablecolumns']['disabled'])) {
					throw new Exception(
						"tx_mklib_util_DB::delete(): Cannot hide records in table $table - no \$TCA entry found!"
					);
				}

				//else
				$data = array($GLOBALS['TCA'][$table]['ctrl']['enablecolumns']['disabled'] => 1);
				$affectedRows = static::doUpdate($table, $where, $data);
				break;

				// Soft-delete model
			case self::DELETION_MODE_SOFTDELETE:
				global $GLOBALS;
				// Set deleted field according to $TCA
				if (!isset($GLOBALS['TCA'][$table]['ctrl']['delete'])) {
					throw new Exception(
						"tx_mklib_util_DB::delete(): Cannot soft-delete records in table $table - no \$TCA entry found!"
					);
				}

				//else
				$data = array($GLOBALS['TCA'][$table]['ctrl']['delete'] => 1);
				$affectedRows = static::doUpdate($table, $where, $data);
				break;

				// Really hard-delete model
			case self::DELETION_MODE_REALLYDELETE:
				$affectedRows = static::doDelete($table, $where);
				break;

			default:
				throw new Exception("tx_mklib_util_DB::delete(): Unknown deletion mode ($mode)");

		}

		return $affectedRows;
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/util/class.tx_mklib_util_DB.php']) {
  include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/util/class.tx_mklib_util_DB.php']);
}
