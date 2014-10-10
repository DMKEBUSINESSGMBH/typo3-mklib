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

require_once t3lib_extMgm::extPath('rn_base', 'class.tx_rnbase.php');
if (!class_exists('tx_scheduler_Task')) {
	require_once t3lib_extMgm::extPath('scheduler', 'class.tx_scheduler_task.php');
}
tx_rnbase::load('tx_rnbase_configurations');
tx_rnbase::load('tx_rnbase_util_Logger');

/**
 * generic abstract scheduler
 *
 * @package tx_mklib
 * @subpackage tx_mklib_scheduler
 * @author Michael Wagner <michael.wagner@das-medienkombinat.de>
 */
abstract class tx_mklib_scheduler_Generic extends tx_scheduler_Task {

	/**
	 * The DateTime Object with the last run time
	 *
	 * @var	DateTime
	 */
	protected $lastRun = FALSE;

	/**
	 * die verschiedenen optionen vom field provider
	 *
	 * @var	array
	 */
	private $options = array();

	/**
	 * Extension key, used for devlog.
	 *
	 * @return 	string
	 */
	protected function getExtKey() {
		return 'mklib';
	}

	/**
	 * Function executed from the Scheduler.
	 *
	 * @return boolean	Returns true on successful execution, false on error
	 */
	public function execute() {
		/* beispiel für das logging array.
		$devLog = array('message' => '', 'extKey' => 'mklib', 'dataVar' => FALSE);
		$devLog = array(
			tx_rnbase_util_Logger::LOGLEVEL_DEBUG => $devLog,
			tx_rnbase_util_Logger::LOGLEVEL_INFO => $devLog,
			tx_rnbase_util_Logger::LOGLEVEL_NOTICE => $devLog,
			tx_rnbase_util_Logger::LOGLEVEL_WARN => $devLog,
			tx_rnbase_util_Logger::LOGLEVEL_FATAL => $devLog
		);
		*/
		$devLog = array();
		$options = $this->getOptions();

		tx_rnbase_util_Logger::info(
			'[' . get_class($this) . ']: Scheduler starts', $this->getExtKey()
		);

		try {
			$message = $this->executeTask($options, $devLog);

			$this->setLastRunTime();

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
							'[' . get_class($this) . ']: ' . $logData['message'],
							isset($logData['extKey']) ? $logData['extKey'] : $this->getExtKey(),
							$logLevel,
							isset($logData['dataVar']) ? $logData['dataVar'] : FALSE
						);
				}
			}
		} catch (Exception $exception) {
			$dataVar = array(
				'errorcode' => $exception->getCode(),
				'errormsg' => $exception->getMessage(),
				'trace' => $exception->getTraceAsString(),
				'options' => $options,
				// bisherige logs mitgeben
				'devlog' => $devLog,
			);
			if ($exception instanceof tx_rnbase_util_Exception) {
				$dataVar['exception_data'] = $exception->getAdditional(FALSE);
			}
			if (tx_rnbase_util_Logger::isFatalEnabled())
				tx_rnbase_util_Logger::fatal(
					'Task [' . get_class($this) . '] failed.' .
						' Error(' . $exception->getCode() . '):' .
						$exception->getMessage(),
					$this->getExtKey(), $dataVar
				);
			// Exception Mail an die Entwicker senden
			$mail = tx_rnbase_configurations::getExtensionCfgValue(
				'rn_base', 'sendEmailOnException'
			);
			if(!empty($mail)) {
				$this->sendErrorMail(
					$mail,
					// Wir erstellen eine weitere Exception mit zusätzlichen Daten.
					tx_rnbase::makeInstance(
						'tx_rnbase_util_Exception',
						get_class($exception) . ': ' . $exception->getMessage(),
						$exception->getCode(), $dataVar, $exception
					)
				);
			}
			// Wir geben die Exception weiter,
			// damit der Scheduler eine entsprechende Meldung ausgeben kann.
			throw $exception;
		}

		tx_rnbase_util_Logger::info(
			'[' . get_class($this) . ']: Scheduler ends successful ',
			$this->getExtKey()
		);

		return true;
	}

	/**
	 * This is the main method that is called when a task is executed
	 * It MUST be implemented by all classes inheriting from this one
	 * Note that there is no error handling, errors and failures are expected
	 * to be handled and logged by the client implementations.
	 * Should return true on successful execution, false on error.
	 *
	 * @param array $options
	 * @param array &$devLog Put some informations for the logging here.
	 * @return string
	 */
	abstract protected function executeTask(array $options, array &$devLog);

	/**
	 * Liefert die im Scheduler gesetzten Optionen.
	 *
	 * @param string $info
	 * @return	string	Information to display
	 */
	public function getAdditionalInformation($info = '') {
		$info .= CRLF . ' Options: ';
		$info .= t3lib_div::arrayToLogString($this->getOptions(), array(), 64);
		return $info;
	}

	/**
	 * Setzt eine Option
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return mixed Der gesetzte Wert.
	 */
	public function setOption($key, $value) {
		return $this->options[$key] = $value;
	}

	/**
	 * Liefert eine Option.
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function getOption($key) {
		return $this->options[$key];
	}
	/**
	 * Setzt alle Otionen.
	 *
	 * @param array $values
	 * @return mixed Der gesetzte Wert.
	 */
	public function setOptions(array $values) {
		return $this->options = $values;
	}
	/**
	 * Liefert alle Optionen
	 *
	 * @return 	array
	 */
	public function getOptions() {
		// wir brauchen per default ein array
		return is_array($this->options) ? $this->options : array();
	}

	/**
	 * gets the last run time
	 *
	 * @return DateTime|null
	 */
	protected function getLastRunTime()
	{
		if ($this->lastRun === FALSE) {
			$options = array();
			$options['enablefieldsoff'] = 1;
			$options['where'] = 'uid=' . (int) $this->getTaskUid();
			$options['limit'] = 1;
			try {
				$ret = @tx_rnbase_util_DB::doSelect(
					'tx_mklib_lastrun', 'tx_scheduler_task', $options
				);
			} catch (Exception $e) {
				$ret = NULL;
			}
			$this->lastRun = (
					empty($ret)
					|| empty($ret[0]['tx_mklib_lastrun'])
					|| $ret[0]['tx_mklib_lastrun'] === '0000-00-00 00:00:00'
				) ? NULL : new DateTime($ret[0]['tx_mklib_lastrun']);
		}
		return $this->lastRun;
	}

	/**
	 * updates the lastrun time with the current time
	 *
	 * @return integer
	 */
	protected function setLastRunTime()
	{
		try {
			$lastRun = new DateTime();
			$return = @tx_rnbase_util_DB::doUpdate(
				'tx_scheduler_task',
				'uid=' . (int) $this->getTaskUid(),
				array(
					'tx_mklib_lastrun' => $lastRun->format('Y-m-d H:i:s')
				)
			);
		} catch (Exception $e) {
			$return = 0;
		}
		return $return;
	}


	/**
	 * sends a exception mail
	 *
	 * @param string $email
	 * @param Exception $exception
	 * @return void
	 */
	protected function sendErrorMail($email, Exception $exception) {
		tx_rnbase::load('tx_rnbase_util_Misc');
		$options = array('ignoremaillock' => true);
		tx_rnbase_util_Misc::sendErrorMail($email, get_class($this), $exception, $options);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/scheduler/class.tx_mklib_scheduler_Generic.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/scheduler/class.tx_mklib_scheduler_Generic.php']);
}