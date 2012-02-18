<?php
/**
 *  Copyright notice
 *
 *  (c) 2011 das MedienKombinat <kontakt@das-medienkombinat.de>
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
require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');
tx_rnbase::load('tx_mklib_scheduler_GenericFieldProvider');

// t3lib_extMgm::addLLrefForTCAdescr('_MOD_tools_txschedulerM1', t3lib_extMgm::extPath($_EXTKEY).'scheduler/locallang.xml');

/**
 * Fügt Felder im scheduler task hinzu
 *
 * @package tx_mketernit
 * @subpackage tx_mketernit_scheduler
 * @author Michael Wagner <michael.wagner@das-medienkombinat.de>
 */
class tx_mklib_scheduler_SchedulerTaskFreezeDetectionFieldProvider
	extends tx_mklib_scheduler_GenericFieldProvider
	implements tx_scheduler_AdditionalFieldProvider {

	/**
	 *
	 * @return 	array
	 * @todo CSH einfügen
	 */
	protected function getAdditionalFieldConfig(){
		return array(
			'receiver' => array(
				'type' => 'input',
 				'label' => 'LLL:EXT:mklib/scheduler/locallang.xml:scheduler_SchedulerTaskFreezeDetection_field_receiver',
				'default' => $GLOBALS['BE_USER']->user['email'], // default is 7 days
				'eval' => 'email',
			),
			'threshold' => array(
				'type' => 'input',
 				'label' => 'LLL:EXT:mklib/scheduler/locallang.xml:scheduler_SchedulerTaskFreezeDetection_field_threshold',
				'default' => 90, // jeder task sollte nach 90 sekunden fertig sein
				'eval' => 'int,minThreshold',
			),
			'rememberAfter' => array(
				'type' => 'input',
 				'label' => 'LLL:EXT:mklib/scheduler/locallang.xml:scheduler_SchedulerTaskFreezeDetection_field_rememberAfter',
				'cshLabel' => 'LLL:EXT:mklib/scheduler/locallang.xml:scheduler_SchedulerTaskFreezeDetection_field_rememberAfter', // key aus der ssh locallang zu cshKey
				'default' => 3600, // nach 1 h erneut mail schicken
				'eval' => 'int',
			),
		);
	}

	/**
	 * der threshold sollte nicht kleiner als 10 sekunden sein. dsa
	 * prüfen wir hier
	 *
	 * @param  string $sPath
	 * @return 	mixed
	 */
	protected function minThreshold($iThreshold){
		return ($iThreshold < 10)
			? $GLOBALS['LANG']->sL('LLL:EXT:mklib/scheduler/locallang.xml:scheduler_SchedulerTaskFreezeDetection_field_threshold_eval_minThreshold')
			: true;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/scheduler/class.tx_mklib_scheduler_cleanupTempFilesFieldProvider.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/scheduler/class.tx_mklib_scheduler_cleanupTempFilesFieldProvider.php']);
}