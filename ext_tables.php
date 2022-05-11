<?php

/**
 * DB-Felder, die im BE bearbeitbar sind.
 */

/*
 * alle benötigten Klassen einbinden etc.
 */
if (!defined('TYPO3')) {
    exit('Access denied.');
}
$_EXTKEY = 'mklib';

// //////////////////////////////
// Plugin anmelden
// //////////////////////////////
// Einige Felder ausblenden
$TCA['tt_content']['types']['list']['subtypes_excludelist']['tx_mklib'] = 'layout,select_key,pages';

// Das tt_content-Feld pi_flexform einblenden
$TCA['tt_content']['types']['list']['subtypes_addlist']['tx_mklib'] = 'pi_flexform';

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue('tx_mklib', 'FILE:EXT:mklib/flexform_main.xml');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(
    [
        'LLL:EXT:mklib/locallang_db.php:plugin.mklib.label',
        'tx_mklib',
        'EXT:mklib/ext_icon.gif',
    ],
    'list_type',
    'mklib'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'static/basic/', 'MK Lib - Basics');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'static/development/', 'MK Lib - Development');
