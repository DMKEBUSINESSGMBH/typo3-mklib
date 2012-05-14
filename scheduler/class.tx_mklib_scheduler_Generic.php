<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 das MedienKombinat GmbH <kontakt@das-medienkombinat.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
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

require_once (t3lib_extMgm::extPath('rn_base', 'class.tx_rnbase.php'));
require_once (t3lib_extMgm::extPath('scheduler', 'class.tx_scheduler_task.php'));
tx_rnbase::load('tx_rnbase_configurations');
tx_rnbase::load('tx_rnbase_util_Logger');

/**
 *
 * @package tx_mketernit
 * @subpackage tx_mketernit_scheduler
 * @author Michael Wagner <michael.wagner@das-medienkombinat.de>
 */
abstract class tx_mklib_scheduler_Generic extends tx_scheduler_Task {

	/**
	 * die verschiedenen optionen vom field provider
	 *
	 * @var	array
	 */
	private $options = array();

	/**
	 * Extension key, used for devlog.
	 * @return 	string
	 */
	protected function getExtKey() {
		return 'mklib';
	}

	/**
	 * Function executed from the Scheduler.
	 *
	 * @return	void
	 */
	public function execute() {
		$success = true;

		try {
			// beispiel für das logging array.
// 			$devLog = array('message' => '', 'extKey' => 'mklib', 'dataVar' => FALSE);
// 			$devLog = array(
// 				tx_rnbase_util_Logger::LOGLEVEL_DEBUG => $devLog,
// 				tx_rnbase_util_Logger::LOGLEVEL_INFO => $devLog,
// 				tx_rnbase_util_Logger::LOGLEVEL_NOTICE => $devLog,
// 				tx_rnbase_util_Logger::LOGLEVEL_WARN => $devLog,
// 				tx_rnbase_util_Logger::LOGLEVEL_FATAL => $devLog
// 			);
			$devLog = array();
			$options = $this->getOptions();
			$message = $this->executeTask($options, $devLog);

			// devlog
			if (t3lib_extMgm::isLoaded('devlog')) {
				if(
					// infolog setzen, wenn devlog leer
					empty($devLog)
					// infolog setzen, wenn infolog gesetzt, aber keine message vorhanden ist
					|| (
							isset($devLog[tx_rnbase_util_Logger::LOGLEVEL_INFO])
							&& empty($devLog[tx_rnbase_util_Logger::LOGLEVEL_INFO]['message'])
						)
					)
					$devLog[tx_rnbase_util_Logger::LOGLEVEL_INFO]['message'] = $message;

				foreach ($devLog as $logLevel => $logData) {
					if (empty($logData['message'])) continue;
					t3lib_div::devLog(
							'['.get_class($this).']: '.$logData['message'],
							isset($logData['extKey']) ? $logData['extKey'] : $this->getExtKey(),
							$logLevel,
							isset($logData['dataVar']) ? $logData['dataVar'] : FALSE
						);
				}
			}
		} catch (Exception $exception) {
			if (tx_rnbase_util_Logger::isFatalEnabled())
				tx_rnbase_util_Logger::fatal('Task ['.get_class($this).'] failed. '.$exception->getMessage(), $this->getExtKey());
			// Exception Mail an die Entwicker senden
			if($mail = tx_rnbase_configurations::getExtensionCfgValue('rn_base', 'sendEmailOnException')) {
				$this->sendErrorMail($mail, $exception);
			}
			//Wir geben die Exception weiter, damit der Scheduler eine entsprechende Meldung ausgeben kann.
			throw $exception;
			$success = false;
		}

		return $success;
	}

	/**
	 *
	 * @param 	array 	$options
	 * @param 	array 	$devLog	Put some informations for the logging here.
	 * @return 	string
	 */
	abstract protected function executeTask(array $options, array &$devLog);

	/**
	 * This method returns the destination mail address as additional information
	 *
	 * @return	string	Information to display
	 */
	public function getAdditionalInformation($info='') {
		return $info.CRLF.' Options: '.t3lib_div::arrayToLogString($this->getOptions(), array(), 64);
		/* old code
		$options = array();
		foreach($this->getOptions() as $key => $value){
			$options [] = '\''.$key.'\' => \''.$value.'\'';
		}
		return $info.CRLF.' Options: '.implode(', ',$options);
		*/
	}

	/**
	 * Setzt eine Option
	 *
	 * @param 	string 	$key
	 * @param 	mixed 	$value
	 * @return 	mixed	Der gesetzte Wert.
	 */
	public function setOption($key, $value){
		return $this->options[$key] = $value;
	}
	/**
	 * Liefert eine Option.
	 *
	 * @param 	string 	$key
	 * @return 	mixed
	 */
	public function getOption($key){
		return $this->options[$key];
	}
	/**
	 * Setzt alle Otionen.
	 *
	 * @param 	array 	$values
	 * @return 	mixed 	Der gesetzte Wert.
	 */
	public function setOptions(array $values){
		return $this->options = $values;
	}
	/**
	 * Liefert alle Optionen
	 *
	 * @return 	array
	 */
	public function getOptions(){
		//wir brauchen per default ein array
		return is_array($this->options) ? $this->options : array();
	}

	protected function sendErrorMail($email, Exception $exception) {
		tx_rnbase::load('tx_rnbase_util_Misc');
		$options = array('ignoremaillock' => true);
		tx_rnbase_util_Misc::sendErrorMail($email, get_class($this), $exception, $options);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/scheduler/class.tx_mklib_scheduler_Generic.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/scheduler/class.tx_mklib_scheduler_Generic.php']);
}