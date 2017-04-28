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
tx_rnbase::load('Tx_Rnbase_Scheduler_FieldProvider');
/**
 * tx_mklib_scheduler_GenericFieldProvider
 *
 * @package         TYPO3
 * @subpackage      mklib
 * @author          Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 * @license         http://www.gnu.org/licenses/lgpl.html
 *                  GNU Lesser General Public License, version 3 or later
 */
abstract class tx_mklib_scheduler_GenericFieldProvider extends Tx_Rnbase_Scheduler_FieldProvider
{
    protected $taskInfo;
    protected $task;
    protected $schedulerModule;

    /**
     * Liefert die Konfiguration für zusätzliche Felder des Tasks.
     *
     * ACHTUNG!!!
     * Die Feldnamen sollten eindeutig und einzigartig sein aus folgendem Grund.
     * Damit das Anlegen eines Scheduler schneller geht, werden die addtionalFields
     * von allen vorhandenen Schedulern vorgeladen.
     * Dadurch kann es zu Überschreibungen kommen
     * wenn 2 Felder den gleichen Namen haben.
     * Ein gutes Beispiel dafür ist der EmailFieldProvider.
     *
     * @return  array
     */
    abstract protected function getAdditionalFieldConfig();
    /*{
     return array(
     'lifetime' => array(
     // sollte immer ein LLL sein,
     // da fehlermeldungen aus der ll geladen werden $label.'_required'
     'label' => 'LLL:EXT:mklib/locallang_db.xml:label_key',
     'type' => 'input'
     'cshKey' => '_MOD_tools_txschedulerM1',
     'cshLabel' => 'label_key_csh', // key aus der ssh locallang zu cshKey
     'default' => '', // wird genjutzt, wenn kein Wert angegeben wurde
     'eval' => 'trim',
     ),
     );
     }*/

    /**
     * Gets additional fields to render in the form to add/edit a task
     *
     * @param array &$taskInfo Values of the fields from the add/edit task form
     * @param Tx_Rnbase_Scheduler_Task $task The task object being edited. Null when adding a task!
     * @param tx_mklib_scheduler_Generic $schedulerModule Reference to the scheduler backend module
     * @return array
     */
    protected function _getAdditionalFields(array &$taskInfo, $task, $schedulerModule)
    {
        $this->taskInfo = &$taskInfo;
        $this->task = &$task;
        $this->schedulerModule = &$schedulerModule;

        $additionalFields = array();
        foreach ($this->getAdditionalFieldConfig() as $sKey => $aOptions) {
            // Initialize extra field value
            if (empty($taskInfo[$sKey])) {
                if ($schedulerModule->CMD == 'edit') {
                    // In case of edit, and editing a test task, set to internal value if not data was submitted already
                    $taskInfo[$sKey] = $task->getOption($sKey);
                } else /*if ($parentObject->CMD == 'add') */{
                    // In case of new task and if field is empty, set default.
                    $taskInfo[$sKey] = $aOptions['default'];
                }
            }

            // Write the code for the field
            $fieldID = 'task_'.$sKey;
            switch ($aOptions['type']) {
                case 'check':
                    $fieldCode = '<input type="checkbox" id="'.$fieldID.'" name="tx_scheduler['.$sKey.']" '.($taskInfo[$sKey] ? 'checked="checked"' : '').'>';
                    break;
                case 'select':
                    $fieldCode = '<select id="'.$fieldID.'" name="tx_scheduler['.$sKey.']">';
                    foreach ($aOptions['items'] as $value => $caption) {
                        $fieldCode .= '<option value="'.$value.'" '.($taskInfo[$sKey] == $value ? 'selected="selected"' : '').'>'.$caption.'</option>';
                    }
                    $fieldCode .= '</select>';
                    break;
                case 'input':
                default:
                    $fieldCode = '<input type="text" name="tx_scheduler['.$sKey.']" id="'.$fieldID.'" value="'.$taskInfo[$sKey].'" size="30" />';
                    break;
            }
            $additionalFields[$fieldID] = array(
                    'code' => $fieldCode,
                    'label' => $aOptions['label'] ? $aOptions['label'] : $sKey,
                    'cshKey' => $aOptions['cshKey'] ? $aOptions ['cshKey'] : 'tx_mklib_scheduler_cleanupTempFiles',
                    'cshLabel' => ($aOptions['cshLabel'] ? $aOptions['cshLabel'] : $sKey).'_csh'
            );
        }

        return $additionalFields;
    }

    /**
     * Validates the additional fields' values
     *
     * @param array $submittedData An array containing the data submitted by the add/edit task form
     * @param tx_scheduler_Module $schedulerModule Reference to the scheduler backend module
     * @return bool TRUE if validation was ok (or selected class is not relevant), FALSE otherwise
     */
    protected function _validateAdditionalFields(array &$submittedData, $schedulerModule)
    {
        $bError = false;
        foreach ($this->getAdditionalFieldConfig() as $sKey => $aOptions) {
            $mValue = & $submittedData[$sKey];
            // bei einer checkbox ist der value immer 'on'!
            if ($aOptions['type'] && $mValue === 'on') {
                $mValue = 1;
            }

            $bMessage = false;

            // Die Einzelnen validatoren anwenden.
            if (!$bMessage) {
                foreach (tx_rnbase_util_Strings::trimExplode(',', $aOptions['eval']) as $sEval) {
                    $sLabelKey = ($aOptions['label'] ? $aOptions['label'] : $sKey).'_eval_'.$sEval;
                    switch ($sEval) {
                        case 'required':
                            $bMessage = empty($mValue);
                            break;
                        case 'trim':
                            $mValue = trim($mValue);
                            break;
                        case 'int':
                            $bMessage = !is_numeric($mValue);
                            if (!$bMessage) {
                                $mValue = intval($mValue);
                            }
                            break;
                        case 'date':
                            $mValue = date($GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'], $mValue);
                            break;
                        case 'email':
                            // wir unterstützen kommaseparierte listen von email adressen
                            if (!empty($mValue)) {
                                $aEmails = explode(',', $mValue);
                                $bMessage = false;
                                foreach ($aEmails as $sEmail) {
                                    if (!tx_rnbase_util_Strings::validEmail($sEmail)) {
                                        $bMessage = true;
                                    }
                                }
                            }

                            break;
                        case 'folder':
                            tx_rnbase::load('tx_mklib_util_File');
                            $sPath = tx_mklib_util_File::getServerPath($mValue);
                            $bMessage = !@is_dir($sPath);
                            if (!$bMessage) {
                                $mValue = $sPath;
                            }
                            break;
                        case 'file':
                            tx_rnbase::load('tx_mklib_util_File');
                            $sPath = tx_mklib_util_File::getServerPath($mValue);
                            $bMessage = !@file_exists($sPath);
                            if (!$bMessage) {
                                $mValue = $sPath;
                            }
                            break;
                        case 'url':
                            $bMessage = !tx_rnbase_util_Network::isValidUrl($mValue);
                            break;
                        default:
                            // wir prüfen auf eine eigene validator methode in der Kindklasse.
                            // in eval muss validateLifetime stehen, damit folgende methode aufgerufen wird.
                            // protected function validateLifetime($mValue){ return true; }
                            // @TODO: clasname::method prüfen!?
                            if (method_exists($this, $sEval)) {
                                $ret = $this->$sEval($mValue, $submittedData);
                                if (is_string($ret)) {
                                    $bMessage = ($sMessage = $ret);
                                }
                            }
                    }

                    // wir generieren nur eine meldung pro feld!
                    if ($bMessage) {
                        break;
                    }
                }
            }

            // wurde eine fehlermeldung erzeugt?
            if ($bMessage) {
                $sMessage = $sMessage ? $sMessage : $GLOBALS['LANG']->sL($sLabelKey);
                $sMessage = $sMessage ? $sMessage : ucfirst($sKey) . ' has to eval ' . $sEval.'.';
                $flashMessageClass = tx_rnbase_util_Typo3Classes::getFlashMessageClass();
                $schedulerModule->addMessage($sMessage, $flashMessageClass::ERROR);
                $bError = true;
                continue;
            }
        }

        return !$bError;
    }

    /**
     * Takes care of saving the additional fields' values in the task's object
     *
     * @param array $submittedData An array containing the data submitted by the add/edit task form
     * @param tx_scheduler_Task $task Reference to the scheduler backend module
     * @return void
     */
    protected function _saveAdditionalFields(array $submittedData, Tx_Rnbase_Scheduler_Task $task)
    {
        foreach ($this->getAdditionalFieldConfig() as $sKey => $aOptions) {
            $task->setOption($sKey, $submittedData[$sKey]);
        }
    }
}
