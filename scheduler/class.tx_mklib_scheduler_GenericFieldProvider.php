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
require_once t3lib_extMgm::extPath('scheduler', '/interfaces/interface.tx_scheduler_additionalfieldprovider.php');

/**
 * Fügt Felder im scheduler task hinzu
 *
 * @package tx_mketernit
 * @subpackage tx_mketernit_scheduler
 * @author Michael Wagner <michael.wagner@das-medienkombinat.de>
 */
abstract class tx_mklib_scheduler_GenericFieldProvider implements tx_scheduler_AdditionalFieldProvider {

	protected $taskInfo;
	protected $task;
	protected $schedulerModule;

	/**
	 * Liefert die Konfiguration für zusätzliche Felder des Tasks.
	 *
	 * ACHTUNG!!!
	 * Die Feldnamen sollten eindeutig und einzigartig sein aus folgendem Grund.
	 * Damit das Anlegen eines Scheduler schneller geht, werden die addtionalFields
	 * von allen vorhandenen Schedulern vorgeladen. Dadurch kann es zu Überschreibungen
	 * kommen wenn 2 Felder den gleichen Namen haben. Ein gutes Beispiel dafür ist der EmailFieldProvider.
	 *
	 * @return 	array
	 */
	abstract protected function getAdditionalFieldConfig();
// 	{
// 		return array(
// 			'lifetime' => array(
				// sollte immer ein LLL sein, da fehlermeldungen aus der ll geladen werden $label.'_required'
// 				'label' => 'LLL:EXT:mklib/locallang_db.xml:label_key', // Label für dieses Feld.
//				'type' => 'input'
// 				'cshKey' => '_MOD_tools_txschedulerM1',
// 				'cshLabel' => 'label_key_csh', // key aus der ssh locallang zu cshKey
// 				'default' => '', // wird genjutzt, wenn kein Wert angegeben wurde
// 				'eval' => 'trim',
// 			),
// 		);
// 	}

	/**
	 * This method is used to define new fields for adding or editing a task
	 *
	 * @param	array						$taskInfo: reference to the array containing the info used in the add/edit form
	 * @param	tx_scheduler_Task			$task: when editing, reference to the current task object. Null when adding.
	 * @param	tx_mklib_scheduler_Generic	$parentObject: reference to the calling object (Scheduler's BE module)
	 * @return	array						Array containg all the information pertaining to the additional fields
	 *										The array is multidimensional, keyed to the task class name and each field's id
	 *										For each field it provides an associative sub-array with the following:
	 *											['code']		=> The HTML code for the field
	 *											['label']		=> The label of the field (possibly localized)
	 *											['cshKey']		=> The CSH key for the field
	 *											['cshLabel']	=> The code of the CSH label
	 */
	public function getAdditionalFields(array &$taskInfo, $task, tx_scheduler_Module $schedulerModule) {
		$this->taskInfo =& $taskInfo;
		$this->task =& $task;
		$this->schedulerModule =& $schedulerModule;

		$additionalFields = array();
		foreach ($this->getAdditionalFieldConfig() as $sKey => $aOptions){
			// Initialize extra field value
			if (empty($taskInfo[$sKey])) {
				if ($schedulerModule->CMD == 'edit') {
					// In case of edit, and editing a test task, set to internal value if not data was submitted already
					$taskInfo[$sKey] = $task->getOption($sKey);
				}
				else/*if ($parentObject->CMD == 'add') */{
					// In case of new task and if field is empty, set default.
					$taskInfo[$sKey] = $aOptions['default'];
				}
			}

			// Write the code for the field
			$fieldID = 'task_'.$sKey;
			switch($aOptions['type']){
				case 'check':
					$fieldCode = '<input type="checkbox" id="'.$fieldID.'" name="tx_scheduler['.$sKey.']" '.($taskInfo[$sKey] ? 'checked="checked"': '').'>';
					break;
				case 'select':
					$fieldCode = '<select id="'.$fieldID.'" name="tx_scheduler['.$sKey.']">';
					foreach($aOptions['items'] as $value => $caption) {
						$fieldCode .= '<option value="'.$value.'" '.($taskInfo[$sKey] == $value ? 'selected="selected"': '').'>'.$caption.'</option>';
					}
					$fieldCode .= '</select>';
					break;
				case 'input':
				default:
					$fieldCode = '<input type="text" name="tx_scheduler['.$sKey.']" id="'.$fieldID.'" value="'.$taskInfo[$sKey].'" size="30" />';
					break;
			}
			$additionalFields[$fieldID] = array(
							'code'     => $fieldCode,
							'label'    => $aOptions['label'] ? $aOptions['label'] : $sKey,
							'cshKey'   => $aOptions['cshKey'] ? $aOptions ['cshKey'] : 'tx_mklib_scheduler_cleanupTempFiles',
							'cshLabel' => ($aOptions['cshLabel'] ? $aOptions['cshLabel'] : $sKey).'_csh'
			);
		}

		return $additionalFields;
	}

	/**
	 * This method checks any additional data that is relevant to the specific task
	 * If the task class is not relevant, the method is expected to return true
	 *
	 * @param	array					$submittedData: reference to the array containing the data submitted by the user
	 * @param	tx_scheduler_Module		$parentObject: reference to the calling object (Scheduler's BE module)
	 * @return	boolean					True if validation was ok (or selected class is not relevant), false otherwise
	 */
	public function validateAdditionalFields(array &$submittedData, tx_scheduler_Module $parentObject) {
		$bError = false;
		foreach ($this->getAdditionalFieldConfig() as $sKey => $aOptions) {
			$mValue = & $submittedData[$sKey];
			// bei einer checkbox ist der value immer 'on'!
			if( $aOptions['type'] && $mValue === 'on') {
				$mValue = 1;
			}

			$bMessage = false;

			// Die Einzelnen validatoren anwenden.
			if (!$bMessage) {
				foreach(t3lib_div::trimExplode(',', $aOptions['eval']) as $sEval) {
					$sLabelKey = ($aOptions['label'] ? $aOptions['label'] : $sKey).'_eval_'.$sEval;
					switch($sEval){
						case 'required':
							$bMessage = empty($mValue);
							break;
						case 'trim':
							$mValue = trim($mValue);
							break;
						case 'int':
							$bMessage = !is_numeric($mValue);
							if (!$bMessage) $mValue = intval($mValue);
							break;
						case 'date':
							$mValue = date($GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'], $mValue);
							break;
						case 'email':
							//wir unterstützen kommaseparierte listen von email andressen
							if(!empty($mValue)) {
								$aEmails = explode(',', $mValue);
								$bMessage = false;
								foreach ($aEmails as $sEmail) {
									if(!t3lib_div::validEmail($sEmail))
										$bMessage = true;
								}
							}

							break;
						case 'folder':
							tx_rnbase::load('tx_mklib_util_File');
							$sPath = tx_mklib_util_File::getServerPath($mValue);
							$bMessage = !@is_dir($sPath);
							if (!$bMessage) $mValue = $sPath;
							break;
						case 'url':
							$bMessage = !t3lib_div::isValidUrl($mValue);
							break;
						default:
							// wir prüfen auf eine eigene validator methode in der Kindklasse.
							// in eval muss validateLifetime stehen, damit folgende methode aufgerufen wird.
							// protected function validateLifetime($mValue){ return true; }
							// @TODO: clasname::method prüfen!?
							if(method_exists($this, $sEval)) {
								$ret = $this->$sEval($mValue, $submittedData);
								if (is_string($ret)) {
									$bMessage = ($sMessage = $ret);
								}
							}
					}

					// wir generieren nur eine meldung pro feld!
					if($bMessage) {
						continue;
					}
				}
			}

			// wurde eine fehlermeldung erzeugt?
			if($bMessage) {
				$sMessage = $sMessage ? $sMessage : $GLOBALS['LANG']->sL($sLabelKey);
				$sMessage = $sMessage ? $sMessage : ucfirst($sKey) . ' has to eval ' . $sEval.'.';
				$parentObject->addMessage($sMessage, t3lib_FlashMessage::ERROR);
				$bError = true;
				continue;
			}

		}

		return !$bError;
	}

	/**
	 * This method is used to save any additional input into the current task object
	 * if the task class matches
	 *
	 * @param	array				$submittedData: array containing the data submitted by the user
	 * @param	tx_scheduler_Task	$task: reference to the current task object
	 * @return	void
	 */
	public function saveAdditionalFields(array $submittedData, tx_scheduler_Task $task) {
		foreach ($this->getAdditionalFieldConfig() as $sKey => $aOptions) {
			$task->setOption($sKey, $submittedData[$sKey]);
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/scheduler/class.tx_mklib_scheduler_GenericFieldProvider.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/scheduler/class.tx_mklib_scheduler_GenericFieldProvider.php']);
}