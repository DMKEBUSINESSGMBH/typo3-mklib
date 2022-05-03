<?php
/**
 *  Copyright notice.
 *
 *  (c) 2011 DMK E-BUSINESS GmbH  <dev@dmk-ebusiness.de>
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

// \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('_MOD_tools_txschedulerM1', \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY).'scheduler/locallang.xlf');

/**
 * Fügt Felder im scheduler task hinzu.
 *
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_mklib_scheduler_cleanupTempFilesFieldProvider extends tx_mklib_scheduler_GenericFieldProvider
{
    /**
     * @return array
     */
    protected function getAdditionalFieldConfig()
    {
        return [
            'lifetime' => [
                'type' => 'input',
                'label' => 'LLL:EXT:mklib/scheduler/locallang.xlf:scheduler_cleanupTempFiles_field_lifetime',
                'default' => 604800, // default is 7 days
                'eval' => 'trim,int',
            ],
            'directorycheckdir' => [
                'type' => 'select',
                'label' => 'LLL:EXT:mklib/scheduler/locallang.xlf:scheduler_cleanupTempFiles_field_directorycheckdir',
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
                'label' => 'LLL:EXT:mklib/scheduler/locallang.xlf:scheduler_cleanupTempFiles_field_folder',
                'default' => '',
                'eval' => 'trim,folder,validateFolder',
            ],
            'filetypes' => [
                'type' => 'input',
                'label' => 'LLL:EXT:mklib/scheduler/locallang.xlf:scheduler_cleanupTempFiles_field_filetypes',
                'default' => '',
                'eval' => 'trim',
            ],
            'recursive' => [
                'type' => 'check',
                'label' => 'LLL:EXT:mklib/scheduler/locallang.xlf:scheduler_cleanupTempFiles_field_recursive',
                'default' => '',
                'eval' => '',
            ],
        ];
    }

    /**
     * Validiert den Pfad. Dieser muss zur sicherheit unter typo3temp oder uploads liegen!
     *
     * @param string $sPath
     *
     * @return mixed
     */
    protected function validateFolder($sPath, $submittedData)
    {
        $directoryCheckDir = isset($submittedData['directorycheckdir']) ? $submittedData['directorycheckdir'] : 'typo3temp';

        return (false === strpos($sPath, $directoryCheckDir)) ? $GLOBALS['LANG']->sL('LLL:EXT:mklib/scheduler/locallang.xlf:scheduler_cleanupTempFiles_field_folder_eval_'.$directoryCheckDir) : true;
    }
}
