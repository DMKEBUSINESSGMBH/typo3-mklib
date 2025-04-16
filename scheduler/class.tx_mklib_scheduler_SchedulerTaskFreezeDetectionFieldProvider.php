<?php

/*
 * Copyright notice
 *
 * (c) DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
 * All rights reserved
 *
 * This file is part of the "mklib" Extension for TYPO3 CMS.
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GNU Lesser General Public License can be found at
 * www.gnu.org/licenses/lgpl.html
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 */

// \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('_MOD_tools_txschedulerM1', \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY).'Resources/Private/Language/Scheduler/locallang.xlf');

/**
 * Fügt Felder im scheduler task hinzu.
 *
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_mklib_scheduler_SchedulerTaskFreezeDetectionFieldProvider extends tx_mklib_scheduler_GenericFieldProvider
{
    /**
     * @todo CSH einfügen
     */
    protected function getAdditionalFieldConfig(): array
    {
        return [
            'receiver' => [
                'type' => 'input',
                'label' => 'LLL:EXT:mklib/Resources/Private/Language/Scheduler/locallang.xlf:scheduler_SchedulerTaskFreezeDetection_field_receiver',
                'default' => $GLOBALS['BE_USER']->user['email'], // default is 7 days
                'eval' => 'required,email',
            ],
            'threshold' => [
                'type' => 'input',
                'label' => 'LLL:EXT:mklib/Resources/Private/Language/Scheduler/locallang.xlf:scheduler_SchedulerTaskFreezeDetection_field_threshold',
                'default' => 90, // jeder task sollte nach 90 sekunden fertig sein
                'eval' => 'int,minThreshold',
            ],
            'rememberAfter' => [
                'type' => 'input',
                'label' => 'LLL:EXT:mklib/Resources/Private/Language/Scheduler/locallang.xlf:scheduler_SchedulerTaskFreezeDetection_field_rememberAfter',
                'cshLabel' => 'LLL:EXT:mklib/Resources/Private/Language/Scheduler/locallang.xlf:scheduler_SchedulerTaskFreezeDetection_field_rememberAfter', // key aus der ssh locallang zu cshKey
                'default' => 3600, // nach 1 h erneut mail schicken
                'eval' => 'int',
            ],
        ];
    }

    /**
     * der threshold sollte nicht kleiner als 10 sekunden sein. dsa
     * prüfen wir hier.
     */
    protected function minThreshold($iThreshold)
    {
        return ($iThreshold < 10) ? $GLOBALS['LANG']->sL('LLL:EXT:mklib/Resources/Private/Language/Scheduler/locallang.xlf:scheduler_SchedulerTaskFreezeDetection_field_threshold_eval_minThreshold') : true;
    }
}
