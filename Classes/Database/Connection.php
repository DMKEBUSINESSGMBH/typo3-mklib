<?php
/**
 * @author Michael Wagner
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

/**
 * Beinhaltet Utility-Methoden für Datenbank handling.
 *
 * @author Michael Wagner
 */
class Tx_Mklib_Database_Connection extends Tx_Rnbase_Database_Connection
{
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
     * Is logging enabled? (protected für Tests).
     *
     * @var bool
     */
    protected $log = -1;

    /**
     * Dont log this tables.
     *
     * @var array
     */
    protected $ignoreTables = -1;

    /**
     * Insert crdate and timestamp into correct field (gotten from TCA).
     *
     * @param array  $data
     * @param string $tablename
     *
     * @return array
     */
    private function insertCrdateAndTimestamp($data, $tablename)
    {
        global $GLOBALS;
        // Force creation of timestamp
        if (isset($GLOBALS['TCA'][$tablename]['ctrl']['crdate'])
            && !isset($data[$GLOBALS['TCA'][$tablename]['ctrl']['crdate']])
        ) {
            $data[$GLOBALS['TCA'][$tablename]['ctrl']['crdate']] = $GLOBALS['EXEC_TIME'];
        }

        return $this->insertTimestamp($data, $tablename);
    }

    /**
     * Insert timestamp into correct field (gotten from TCA).
     *
     * @param array  $data
     * @param string $tablename
     *
     * @return array
     */
    private function insertTimestamp($data, $tablename)
    {
        global $GLOBALS;
        // Force creation of timestamp
        if (isset($GLOBALS['TCA'][$tablename]['ctrl']['tstamp'])
            && !isset($data[$GLOBALS['TCA'][$tablename]['ctrl']['tstamp']])
        ) {
            $data[$GLOBALS['TCA'][$tablename]['ctrl']['tstamp']] = $GLOBALS['EXEC_TIME'];
        }

        return $data;
    }

    /**
     * Make a SQL INSERT Statement.
     *
     * @param string $tablename
     * @param array  $values
     * @param int    $debug     = 0      Set to 1 to debug sql-String
     * @param array  $options
     *
     * @return int UID of created record
     */
    public function doInsert($tablename, $values, $debug = 0, array $options = [])
    {
        if ($options['eleminateNonTcaColumns']) {
            $values = tx_mklib_util_TCA::eleminateNonTcaColumnsByTable($tablename, $values);
        }
        $newUid = parent::doInsert(
            $tablename,
            $this->insertCrdateAndTimestamp($values, $tablename),
            $debug
        );
        $this->log('doInsert', $tablename, '1=1 AND `'.$tablename.'`.`uid`=\''.$newUid.'\'', $values);

        return $newUid;
    }

    /**
     * Aktualisiert einen Datensatz.
     *
     * @param string $tablename
     * @param string $where
     * @param array  $values
     * @param int    $debug         = 0      Set to 1 to debug sql-String
     * @param mixed  $noQuoteFields Array or commaseparated string with fieldnames
     * @param array  $options
     *
     * @return int number of rows affected
     */
    public function doUpdate($tablename, $where, $values, $debug = 0, $noQuoteFields = false, array $options = [])
    {
        if ($options['eleminateNonTcaColumns']) {
            $values = tx_mklib_util_TCA::eleminateNonTcaColumnsByTable($tablename, $values);
        }
        $res = parent::doUpdate(
            $tablename,
            $where,
            $this->insertTimestamp($values, $tablename),
            $debug,
            $noQuoteFields
        );
        $this->log('doUpdate', $tablename, $where, $values);

        return $res;
    }

    /**
     * Löscht einen Datensatz.
     *
     * @param string $tablename
     * @param string $where
     * @param bool   $debug
     *
     * @return int number of rows affected
     */
    public function doDelete($tablename, $where, $debug = 0)
    {
        $res = parent::doDelete($tablename, $where, $debug);
        $this->log('doDelete', $tablename, $where);

        return $res;
    }

    /**
     * Führt ein SELECT aus.
     *
     * @param string $what  requested columns
     * @param string $from  either the name of on table or an array with index 0 the from clause
     *                      and index 1 the requested tablename and optional index 2 a table alias to use
     * @param array  $arr   the options array
     * @param bool   $debug = 0  Set to 1 to debug sql-String
     *
     * @return array
     */
    public function doSelect($what, $from, $arr, $debug = 0)
    {
        return parent::doSelect($what, $from, $arr, $debug);
    }

    /**
     * Make a plain SQL Query.
     * Notice: The db resource is not closed by this method. The caller is in charge to do this!
     *
     * @TODO: logging integrieren!
     *
     * @param string $sqlQuery
     * @param int    $debug
     *
     * @return bool
     */
    public function doQuery($query, array $options = [])
    {
        return parent::doQuery($query);
    }

    /* *** ************ *** *
     * *** MM FUNCTIONS ***
     * *** ************ *** */

    /**
     * Prüft ob ein MM eintrag bereits existiert.
     *
     * @TODO: write tests
     *
     * @param string $sTable
     * @param string $sField
     * @param int    $sLocalId
     * @param int    $iForeignId
     *
     * @return bool
     */
    public function mmExists($sTable, $sField, $sLocalId, $iForeignId)
    {
        $sMmTable = $this->mmGetTable($sTable, $sField);

        $where = implode(
            ' AND ',
            $this->mmGetData($sTable, $sField, $sLocalId, $iForeignId, true)
        );

        $options = [
            'where' => $where,
            'enablefieldsoff' => true,
        ];

        $ret = $this->doSelect('COUNT(*) as cnt', $sMmTable, $options);

        return count($ret) ? (intval($ret[0]['cnt']) > 0) : false;
    }

    /**
     * Liefert alle Einträge zu einem Feld.
     *
     * @TODO: write tests
     *
     * @param string $sTable
     * @param string $sField
     * @param int    $sLocalId
     * @param int    $iForeignId
     *
     * @return bool
     */
    public function mmSelectForeign($sTable, $sField, $sLocalId, array $options = [])
    {
        $sMmTable = $this->mmGetTable($sTable, $sField);
        $sForeignTable = $this->mmGetTable($sTable, $sField, 'foreign_table');
        $where = implode(' AND ', $this->mmGetData($sTable, $sField, $sLocalId, false, true));
        $sJoin = $sForeignTable.' JOIN '.$sMmTable.' on '.$sMmTable.'.uid_foreign = '.$sForeignTable.'.uid';

        $options['where'] = (isset($options['where']) ? $options['where'].' AND ' : '').$where;

        return $this->doSelect(
            $sForeignTable.'.*',
            [$sJoin, $sForeignTable],
            $options
        );
    }

    /**
     * Erstellt einen Eintrag in einer MM Tabelle.
     *
     * @TODO: write tests
     *
     * @param string $sTable
     * @param string $sField
     * @param int    $sLocalId
     * @param int    $iForeignId
     *
     * @return bool
     */
    public function mmCreate($sTable, $sField, $sLocalId, $iForeignId)
    {
        // Der mm Eintrag existiert bereits
        if ($this->mmExists($sTable, $sField, $sLocalId, $iForeignId)) {
            return true;
        }

        $sMmTable = $this->mmGetTable($sTable, $sField);
        $aData = $this->mmGetData($sTable, $sField, $sLocalId, $iForeignId);
        $this->doInsert($sMmTable, $aData/*, 1*/);

        return true; //$this->mmExists($sTable, $sField, $sLocalId, $iForeignId);
    }

    /**
     * Liefert den Tabellenname für die MM Tabelle aus der TCA.
     *
     * @TODO: write tests
     *
     * @param string $sTable
     * @param string $sField
     *
     * @return string
     */
    private function mmGetTable($sTable, $sField, $sCF = 'MM')
    {
        return $GLOBALS['TCA'][$sTable]['columns'][$sField]['config'][$sCF];
    }

    /**
     * Erstellt einen Eintrag in einer MM Tabelle.
     *
     * @TODO: write tests
     *
     * @param string $sTable
     * @param string $sField
     * @param int    $sLocalId
     * @param int    $iForeignId
     * @param bool   $bWhere
     *
     * @return array
     */
    private function mmGetData($sTable, $sField, $sLocalId = false, $iForeignId = false, $bWhere = false)
    {
        $aFieldConfig = $GLOBALS['TCA'][$sTable]['columns'][$sField]['config'];

        $sMmTable = $aFieldConfig['MM'];

        // wenn opposite_field dann die uid felder vertauschen
        $sLocalField = isset($aFieldConfig['MM_opposite_field']) ? 'uid_foreign' : 'uid_local';
        $sForeignField = isset($aFieldConfig['MM_opposite_field']) ? 'uid_local' : 'uid_foreign';

        $aData = is_array($aFieldConfig['MM_match_fields']) ? $aFieldConfig['MM_match_fields'] : [];
        $aData = is_array($aFieldConfig['MM_insert_fields']) ? $aFieldConfig['MM_insert_fields'] : $aData;
        if ($sLocalId) {
            $aData[$sLocalField] = $sLocalId;
        }
        if ($iForeignId) {
            $aData[$sForeignField] = $iForeignId;
        }

        if ($bWhere) {
            // Anhand der Daten das WHERE aufbauen
            $where = [];
            foreach ($aData as $sField => $sValue) {
                $where[] = $sMmTable.'.'.$sField.' = '.Tx_Rnbase_Database_Connection::getInstance()->fullQuoteStr($sValue, $sMmTable);
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
     * @param string $tablename
     *
     * @return bool
     */
    private function isLog($tablename)
    {
        if (-1 == $this->log) {
            // erst die Extension Konfiguration fragen!
            $this->log = intval(tx_rnbase_configurations::getExtensionCfgValue('mklib', 'logDbHandler'));
            if ($this->log) {
                $this->log = tx_rnbase_util_Logger::isNoticeEnabled();
            }
        }
        if ($this->log) {
            // ignore tables besorgen
            if (!is_array($this->ignoreTables)) {
                $this->ignoreTables = tx_rnbase_util_Strings::trimExplode(
                    ',',
                    tx_rnbase_configurations::getExtensionCfgValue('mklib', 'logDbIgnoreTables'),
                    true
                );
            }
            // tabelle loggen ?
            if (in_array($tablename, $this->ignoreTables)) {
                return false;
            }
        }

        return $this->log;
    }

    /**
     * Logs DB changes.
     *
     * @TODO: t3users log nutzen, wenn installiert! tx_t3users_util_ServiceRegistry::getLoggingService();
     *
     * @param string $msg
     * @param string $tablename
     * @param string $where
     * @param mixed  $values
     */
    private function log($msg, $tablename, $where = false, $values = false)
    {
        if (!$this->isLog($tablename)) {
            return false;
        }
        // else

        // daten sammeln
        $data = [];
        $data['fe_user'] = isset($GLOBALS['TSFE']->fe_user->user['uid']) ? $GLOBALS['TSFE']->fe_user->user['uid'] : 'none';
        $data['be_user'] = (array_key_exists('BE_USER', $GLOBALS) && is_object($GLOBALS['BE_USER'])) ? $GLOBALS['BE_USER']->user['uid'] : 'none';
        $data['tablename'] = $tablename;
        if ($where) {
            $data['where'] = $where;
        }
        if ($values) {
            $data['values'] = $values;
        }
        // backtrace Konfigurierbar machen?
        $data['debug_backtrace'] = tx_mklib_util_Logger::getDebugBacktrace();

        // wurde auf hidden gesetzt?
        $disabled = tx_mklib_util_TCA::getEnableColumn($tablename, 'disabled', 'hidden');
        if ($values && isset($values[$disabled]) && $values[$disabled]) {
            $msg .= '->disabled';
        }
        // wurde gelöscht?
        $delete = tx_mklib_util_TCA::getEnableColumn($tablename, 'delete', 'deleted');
        if ($values && isset($values[$delete]) && $values[$delete]) {
            $msg .= '->delete';
        }

        // tabellenname ergänzen
        $msg .= '('.$tablename.')';

        tx_rnbase_util_Logger::notice($msg, 'mklib', $data);

        return true;
    }

    /**
     * Wrapper for actual deletion.
     *
     * Delete records according to given ready-constructed "where" condition and deletion mode
     *
     * @TODO: use tx_mklib_util_TCA::getEnableColumn to get enablecolumns!
     *
     * @param string $table
     * @param string $where Ready-to-use where condition containing uid restriction
     * @param int    $mode  @see $this->handleDelete()
     *
     * @return int anzahl der betroffenen zeilen
     */
    public function delete($table, $where, $mode)
    {
        switch ($mode) {
            // Hide model
            case self::DELETION_MODE_HIDE:
                global $GLOBALS;
                // Set hidden field according to $TCA
                if (!isset($GLOBALS['TCA'][$table]['ctrl']['enablecolumns']['disabled'])) {
                    throw new Exception("Tx_Mklib_Database_Connection::delete(): Cannot hide records in table $table - no \$TCA entry found!");
                }

                //else
                $data = [$GLOBALS['TCA'][$table]['ctrl']['enablecolumns']['disabled'] => 1];
                $affectedRows = $this->doUpdate($table, $where, $data);
                break;

                // Soft-delete model
            case self::DELETION_MODE_SOFTDELETE:
                global $GLOBALS;
                // Set deleted field according to $TCA
                if (!isset($GLOBALS['TCA'][$table]['ctrl']['delete'])) {
                    throw new Exception("Tx_Mklib_Database_Connection::delete(): Cannot soft-delete records in table $table - no \$TCA entry found!");
                }

                //else
                $data = [$GLOBALS['TCA'][$table]['ctrl']['delete'] => 1];
                $affectedRows = $this->doUpdate($table, $where, $data);
                break;

                // Really hard-delete model
            case self::DELETION_MODE_REALLYDELETE:
                $affectedRows = $this->doDelete($table, $where);
                break;

            default:
                throw new Exception("Tx_Mklib_Database_Connection::delete(): Unknown deletion mode ($mode)");
        }

        return $affectedRows;
    }
}
