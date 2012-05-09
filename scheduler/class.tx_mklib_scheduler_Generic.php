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
	 * Lifetime of e file.
	 *
	 * @var	array
	 */
	protected $options = array();
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
		$bSuccess = true;

		try {
			// beispiel für das logging array.
// 			$aDevLog = array('message' => '', 'extKey' => 'mklib', 'dataVar' => FALSE);
// 			$aDevLog = array(
// 				tx_rnbase_util_Logger::LOGLEVEL_DEBUG => $aDevLog,
// 				tx_rnbase_util_Logger::LOGLEVEL_INFO => $aDevLog,
// 				tx_rnbase_util_Logger::LOGLEVEL_NOTICE => $aDevLog,
// 				tx_rnbase_util_Logger::LOGLEVEL_WARN => $aDevLog,
// 				tx_rnbase_util_Logger::LOGLEVEL_FATAL => $aDevLog
// 			);
			$aDevLog = array();
			$aOptions = $this->getOptions();
			$sMessage = $this->executeTask($aOptions, $aDevLog);

			// devlog
			if (t3lib_extMgm::isLoaded('devlog')) {
				if(
					// infolog setzen, wenn devlog leer
					empty($aDevLog)
					// infolog setzen, wenn infolog gesetzt, aber keine message vorhanden ist
					|| (
							isset($aDevLog[tx_rnbase_util_Logger::LOGLEVEL_INFO])
							&& empty($aDevLog[tx_rnbase_util_Logger::LOGLEVEL_INFO]['message'])
						)
					)
					$aDevLog[tx_rnbase_util_Logger::LOGLEVEL_INFO]['message'] = $sMessage;

				foreach ($aDevLog as $logLevel => $logData) {
					if (empty($logData['message'])) continue;
					t3lib_div::devLog(
							$logData['message'],
							isset($logData['extKey']) ? $logData['extKey'] : $this->getExtKey(),
							$logLevel,
							isset($logData['dataVar']) ? $logData['dataVar'] : FALSE
						);
				}
			}
		} catch (Exception $oException) {
			if (tx_rnbase_util_Logger::isFatalEnabled())
				tx_rnbase_util_Logger::fatal('Task failed. '.$oException->getMessage(), 'mklib');
			// Exception Mail an die Entwicker senden
			if($sMail = tx_rnbase_configurations::getExtensionCfgValue('rn_base', 'sendEmailOnException')) {
				tx_rnbase::load('tx_rnbase_util_Misc');
				tx_rnbase_util_Misc::sendErrorMail($sMail, get_class($this), $oException);
			}
			//Wir geben die Exception weiter, damit der Scheduler eine entsprechende Meldung ausgeben kann.
			throw $oException;
			$bSuccess = false;
		}

		return $bSuccess;
	}

	/**
	 *
	 * @param 	array 	$aOptions
	 * @param 	array 	$aDevLog	Put some informations for the logging here.
	 * @return 	string
	 */
	abstract protected function executeTask(array $aOptions, array &$aDevLog);

	/**
	 * This method returns the destination mail address as additional information
	 *
	 * @return	string	Information to display
	 */
	public function getAdditionalInformation($sInfo='') {
		return $sInfo.CRLF.' Options: '.t3lib_div::arrayToLogString($this->getOptions(), array(), 64);
		/* old code
		$aOptions = array();
		foreach($this->getOptions() as $sKey => $mValue){
			$aOptions [] = '\''.$sKey.'\' => \''.$mValue.'\'';
		}
		return $sInfo.CRLF.' Options: '.implode(', ',$aOptions);
		*/
	}

	/**
	 * Setzt eine Option
	 *
	 * @param 	string 	$sKey
	 * @param 	mixed 	$mValue
	 * @return 	mixed	Der gesetzte Wert.
	 */
	public function setOption($sKey, $mValue){
		return $this->options[$sKey] = $mValue;
	}
	/**
	 * Liefert eine Option.
	 *
	 * @param 	string 	$sKey
	 * @return 	mixed
	 */
	public function getOption($sKey){
		return $this->options[$sKey];
	}
	/**
	 * Setzt alle Otionen.
	 *
	 * @param 	array 	$aValues
	 * @return 	mixed 	Der gesetzte Wert.
	 */
	public function setOptions(array $aValues){
		return $this->options = $aValues;
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

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/scheduler/class.tx_mklib_scheduler_Generic.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/scheduler/class.tx_mklib_scheduler_Generic.php']);
}