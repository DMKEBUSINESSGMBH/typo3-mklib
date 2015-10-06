<?php
/**
 *  Copyright notice
 *
 *  (c) 2015 DMK E-BUSINESS GmbH  <dev@dmk-ebusiness.de>
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

if (!interface_exists('tx_scheduler_AdditionalFieldProvider')) {
	require_once t3lib_extMgm::extPath(
		'scheduler', '/interfaces/interface.tx_scheduler_additionalfieldprovider.php'
	);
}
tx_rnbase::load('tx_mklib_scheduler_AbstractFieldProviderBase');

/**
 * Fügt Felder im scheduler task hinzu
 *
 * @package TYPO3
 * @subpackage tx_mklib
 * @author Michael Wagner
 */
abstract class tx_mklib_scheduler_AbstractFieldProvider45
	extends tx_mklib_scheduler_AbstractFieldProviderBase
	implements tx_scheduler_AdditionalFieldProvider
{

	/**
	 * Gets additional fields to render in the form to add/edit a task
	 *
	 * @param array &$taskInfo Values of the fields from the add/edit task form
	 * @param tx_scheduler_Task $task The task object being edited. Null when adding a task!
	 * @param tx_mklib_scheduler_Generic $schedulerModule Reference to the scheduler backend module
	 * @return array A two dimensional array, array('Identifier' => array('fieldId' => array('code' => '', 'label' => '', 'cshKey' => '', 'cshLabel' => ''))
	 */
	public function getAdditionalFields(array &$taskInfo, $task, tx_scheduler_Module $schedulerModule) {
		return $this->_getAdditionalFields($taskInfo, $task, $schedulerModule);
	}

	/**
	 * Validates the additional fields' values
	 *
	 * @param array $submittedData An array containing the data submitted by the add/edit task form
	 * @param tx_scheduler_Module $schedulerModule Reference to the scheduler backend module
	 * @return boolean TRUE if validation was ok (or selected class is not relevant), FALSE otherwise
	 */
	public function validateAdditionalFields(array &$submittedData, tx_scheduler_Module $parentObject) {
		return $this->_validateAdditionalFields($submittedData, $parentObject);
	}

	/**
	 * Takes care of saving the additional fields' values in the task's object
	 *
	 * @param array $submittedData An array containing the data submitted by the add/edit task form
	 * @param tx_scheduler_Task $task Reference to the scheduler backend module
	 * @return void
	 */
	public function saveAdditionalFields(array $submittedData, tx_scheduler_Task $task) {
		return $this->_saveAdditionalFields($submittedData, $task);
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/scheduler/class.tx_mklib_scheduler_AbstractFieldProvider45.php'])	{
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/scheduler/class.tx_mklib_scheduler_AbstractFieldProvider45.php']);
}
