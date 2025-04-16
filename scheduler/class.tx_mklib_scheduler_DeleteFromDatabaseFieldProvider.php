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

/**
 * Fügt Felder im scheduler task hinzu.
 *
 * @author Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 */
class tx_mklib_scheduler_DeleteFromDatabaseFieldProvider extends tx_mklib_scheduler_GenericFieldProvider
{
    /**
     * @todo CSH einfügen
     */
    protected function getAdditionalFieldConfig(): array
    {
        $twentyEightDaysInSeconds = 2419200;

        return [
            'table' => [
                'type' => 'input',
                'label' => 'LLL:EXT:mklib/Resources/Private/Language/Resources/Private/Language/Scheduler/locallang.xlf:scheduler_DeleteFromDatabase_field_table',
                'eval' => 'required,trim',
            ],
            'selectFields' => [
                'type' => 'input',
                'label' => 'LLL:EXT:mklib/Resources/Private/Language/Scheduler/locallang.xlf:scheduler_DeleteFromDatabase_field_selectFields',
                'eval' => 'trim',
                'default' => 'uid',
            ],
            'uidField' => [
                'type' => 'input',
                'label' => 'LLL:EXT:mklib/Resources/Private/Language/Scheduler/locallang.xlf:scheduler_DeleteFromDatabase_field_uid',
                'eval' => 'required,trim',
                'default' => 'uid',
            ],
            'where' => [
                'type' => 'input',
                'label' => 'LLL:EXT:mklib/Resources/Private/Language/Scheduler/locallang.xlf:scheduler_DeleteFromDatabase_field_where',
                'default' => sprintf('hidden = 1 AND tstamp < (UNIX_TIMESTAMP() - %d)', $twentyEightDaysInSeconds),
                'eval' => 'required,trim',
            ],
            'mode' => [
                'type' => 'select',
                'items' => [
                    Tx_Mklib_Database_Connection::DELETION_MODE_HIDE => $GLOBALS['LANG']->sL('LLL:EXT:mklib/Resources/Private/Language/Scheduler/locallang.xlf:scheduler_DeleteFromDatabase_field_mode_hidden'),
                    Tx_Mklib_Database_Connection::DELETION_MODE_SOFTDELETE => $GLOBALS['LANG']->sL('LLL:EXT:mklib/Resources/Private/Language/Scheduler/locallang.xlf:scheduler_DeleteFromDatabase_field_mode_delete'),
                    Tx_Mklib_Database_Connection::DELETION_MODE_REALLYDELETE => $GLOBALS['LANG']->sL('LLL:EXT:mklib/Resources/Private/Language/Scheduler/locallang.xlf:scheduler_DeleteFromDatabase_field_mode_delete_hard'),
                ],
                'label' => 'LLL:EXT:mklib/Resources/Private/Language/Scheduler/locallang.xlf:scheduler_DeleteFromDatabase_field_mode',
            ],
        ];
    }
}
