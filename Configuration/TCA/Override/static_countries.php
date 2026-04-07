<?php

declare(strict_types=1);

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

if (!defined('TYPO3')) {
    exit('Access denied.');
}

// static_info_tables um PLZ regeln erweitern
if (TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('static_info_tables')) {
    TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
        'static_countries',
        [
            'zipcode_rule' => [
                'exclude' => '0',
                'label' => 'LLL:EXT:mklib/Resources/Private/Language/locallang_db.xlf:static_countries.zipcode_rule',
                'config' => [
                    'type' => 'number',
                    'size' => '1',
                    'eval' => 'trim',
                ],
            ],
            'zipcode_length' => [
                'exclude' => '0',
                'label' => 'LLL:EXT:mklib/Resources/Private/Language/locallang_db.xlf:static_countries.zipcode_length',
                'config' => [
                    'type' => 'number',
                    'size' => '2',
                    'eval' => 'trim',
                ],
            ],
        ]
    );
    TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('static_countries', 'zipcode_rule');
    TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('static_countries', 'zipcode_length');
}
