<?php

if (!defined('TYPO3_MODE')) {
    exit('Access denied.');
}

// static_info_tables um PLZ regeln erweitern
if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('static_info_tables')) {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
        'static_countries',
        [
            'zipcode_rule' => [
                'exclude' => '0',
                'label' => 'LLL:EXT:mklib/locallang_db.xlf:static_countries.zipcode_rule',
                'config' => [
                    'type' => 'input',
                    'size' => '1',
                    'eval' => 'trim,int',
                ],
            ],
            'zipcode_length' => [
                'exclude' => '0',
                'label' => 'LLL:EXT:mklib/locallang_db.xlf:static_countries.zipcode_length',
                'config' => [
                    'type' => 'input',
                    'size' => '2',
                    'eval' => 'trim,int',
                ],
            ],
        ],
        false
    );
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('static_countries', 'zipcode_rule');
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('static_countries', 'zipcode_length');
}
