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
	 * Function executed from the Scheduler.
	 *
	 * @return	void
	 */
	public function execute() {
		$bSuccess = true;

		try {
			$aOptions = $this->getOptions();
			$sMessage = $this->executeTask($aOptions);
			tx_rnbase_util_Logger::info($sMessage, 'mklib');
		} catch (Exception $oException) {
			tx_rnbase_util_Logger::fatal('Task failed', 'mklib', array('Exception' => $oException->getMessage()));
			// Da die Exception gefangen wird, würden die Entwickler keine Mail bekommen
			// also machen wir das manuell
			if($sMail = tx_rnbase_configurations::getExtensionCfgValue('rn_base', 'sendEmailOnException')) {
				tx_rnbase::load('tx_rnbase_util_Misc');
				//die Mail soll immer geschickt werden
				tx_rnbase_util_Misc::sendErrorMail($sMail, get_class($this), $oException, array('ignoremaillock' => true));
			}
			$bSuccess = false;
		}
			
		return $bSuccess;
	}
	
	/**
	 *
	 *
	 * @param 	array 	$options
	 * @return 	string
	 */
	abstract protected function executeTask(array $options);
	
	/**
	 * This method returns the destination mail address as additional information
	 *
	 * @return	string	Information to display
	 */
	public function getAdditionalInformation() {
		return 'Generic task. Child class has to override getAdditionalInformation()';
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