<?php
/**
 *  @author Hannes Bochmann
 *
 *  Copyright notice
 *
 *  (c) 2014 Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
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


tx_rnbase::load('tx_mklib_scheduler_Generic');

/**
 *
 * @package tx_mklib
 * @subpackage tx_mklib_scheduler
 * @author Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 */
class tx_mklib_scheduler_DeleteFromDatabase extends tx_mklib_scheduler_Generic {

	/**
	 * @var array
	 */
	private $affectedRows = array();

	/**
	 * (non-PHPdoc)
	 * @see tx_mklib_scheduler_Generic::executeTask()
	 */
	protected function executeTask(array $options, array &$devLog) {
		$table = $options['table'];
		$where = $options['where'];
		$mode = $options['mode'];
		$databaseConnection = $this->getDatabaseConnection();

		$databaseConnection->doSelect(
			$this->getSelectFields(), $table,
			array(
				'where' => $where, 'enablefieldsoff' => true,
				'callback'	=> array($this, 'deleteRow')
			)
		);

		$devLog[tx_rnbase_util_Logger::LOGLEVEL_INFO] = array(
			'message' => 	count($this->affectedRows) . ' Datensätze wurden in ' .
							$table . ' mit der Bedingung ' .
							$where . ' und dem Modus ' . $mode . ' entfernt',
			'dataVar' => 	array('betroffene Datensätze' => $this->affectedRows)
		);
	}

	/**
	 * @return string
	 */
	private function getSelectFields() {
		$selectFields =
			$this->getOption('selectFields') ? $this->getOption('selectFields') : 'uid';

		if (strpos($this->getUidField(), $selectFields) === FALSE) {
			$selectFields .= ',' . $this->getUidField();
		}

		return $selectFields;
	}

	/**
	 * @return string
	 */
	protected function getDatabaseConnection() {
		return tx_rnbase::makeInstance('Tx_Mklib_Database_Connection');
	}

	/**
	 * @param array $row
	 */
	public function deleteRow(array $row) {
		$this->affectedRows[] = $row;
		$databaseConnection = $this->getDatabaseConnection();
		$uidField = $this->getUidField();
		$where = $uidField . ' = ' . $databaseConnection->fullQuoteStr($row[$uidField]);

		$databaseConnection->delete(
			$this->getOption('table'), $where, $this->getOption('mode')
		);
	}

	/**
	 * @return Ambigous <string, mixed, multitype:>
	 */
	private function getUidField() {
		return $this->getOption('uidField') ? $this->getOption('uidField') : 'uid';
	}

	/**
	 * This method returns the destination mail address as additional information
	 *
	 * @return	string	Information to display
	 */
	public function getAdditionalInformation($info = '') {
		return parent::getAdditionalInformation(
				$GLOBALS['LANG']->sL('LLL:EXT:mklib/scheduler/locallang.xml:scheduler_DeleteFromDatabase_taskinfo')
			);
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/scheduler/class.tx_mklib_scheduler_DeleteFromDatabase.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/scheduler/class.tx_mklib_scheduler_DeleteFromDatabase.php']);
}
