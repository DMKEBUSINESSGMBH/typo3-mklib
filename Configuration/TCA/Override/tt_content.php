<?php

if (!defined('TYPO3')) {
    exit('Access denied.');
}

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['tx_mklib'] = 'pi_flexform';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['tx_mklib'] = 'layout,select_key,pages';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue('tx_mklib', 'FILE:EXT:mklib/flexform_main.xml');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(
    [
        'LLL:EXT:mklib/locallang_db.xlf:plugin.mklib.label',
        'tx_mklib',
        'EXT:mklib/ext_icon.gif',
    ],
    'list_type',
    'mklib'
);
