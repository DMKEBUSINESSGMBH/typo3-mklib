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
class tx_mklib_scheduler_cleanupTempFilesFieldProvider extends tx_mklib_scheduler_GenericFieldProvider
{
    protected function getAdditionalFieldConfig(): array
    {
        return [
            'lifetime' => [
                'type' => 'input',
                'label' => 'LLL:EXT:mklib/Resources/Private/Language/Scheduler/locallang.xlf:scheduler_cleanupTempFiles_field_lifetime',
                'default' => 604800, // default is 7 days
                'eval' => 'trim,int',
            ],
            'directorycheckdir' => [
                'type' => 'select',
                'label' => 'LLL:EXT:mklib/Resources/Private/Language/Scheduler/locallang.xlf:scheduler_cleanupTempFiles_field_directorycheckdir',
                'items' => [
                    // $value => $caption
                    'typo3temp' => 'typo3temp',
                    'uploads' => 'uploads',
                    'fileadmin' => 'fileadmin',
                ],
                'default' => '',
                'eval' => 'required',
            ],
            'folder' => [
                'type' => 'input',
                'label' => 'LLL:EXT:mklib/Resources/Private/Language/Scheduler/locallang.xlf:scheduler_cleanupTempFiles_field_folder',
                'default' => '',
                'eval' => 'trim,folder,validateFolder',
            ],
            'filetypes' => [
                'type' => 'input',
                'label' => 'LLL:EXT:mklib/Resources/Private/Language/Scheduler/locallang.xlf:scheduler_cleanupTempFiles_field_filetypes',
                'default' => '',
                'eval' => 'trim',
            ],
            'recursive' => [
                'type' => 'check',
                'label' => 'LLL:EXT:mklib/Resources/Private/Language/Scheduler/locallang.xlf:scheduler_cleanupTempFiles_field_recursive',
                'default' => '',
                'eval' => '',
            ],
        ];
    }

    /**
     * Validiert den Pfad. Dieser muss zur sicherheit unter typo3temp oder uploads liegen!
     *
     * @param string $sPath
     */
    protected function validateFolder($sPath, array $submittedData)
    {
        $directoryCheckDir = $submittedData['directorycheckdir'] ?? 'typo3temp';

        return (str_contains($sPath, $directoryCheckDir)) ? true : $GLOBALS['LANG']->sL('LLL:EXT:mklib/Resources/Private/Language/Scheduler/locallang.xlf:scheduler_cleanupTempFiles_field_folder_eval_'.$directoryCheckDir);
    }
}
