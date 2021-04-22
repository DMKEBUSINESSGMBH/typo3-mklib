<?php

/**
 * DB-Felder, die im BE bearbeitbar sind.
 */

/*
 * alle benötigten Klassen einbinden etc.
 */
if (!defined('TYPO3_MODE')) {
    exit('Access denied.');
}
$_EXTKEY = 'mklib';

////////////////////////////////
// Plugin anmelden
////////////////////////////////
// Einige Felder ausblenden
$TCA['tt_content']['types']['list']['subtypes_excludelist']['tx_mklib'] = 'layout,select_key,pages';

// Das tt_content-Feld pi_flexform einblenden
$TCA['tt_content']['types']['list']['subtypes_addlist']['tx_mklib'] = 'pi_flexform';

tx_rnbase_util_Extensions::addPiFlexFormValue('tx_mklib', 'FILE:EXT:mklib/flexform_main.xml');

tx_rnbase_util_Extensions::addPlugin(
    [
        'LLL:EXT:mklib/locallang_db.php:plugin.mklib.label',
        'tx_mklib',
        'EXT:mklib/ext_icon.gif',
    ],
    'list_type',
    'mklib'
);

tx_rnbase_util_Extensions::addStaticFile($_EXTKEY, 'static/basic/', 'MK Lib - Basics');
tx_rnbase_util_Extensions::addStaticFile($_EXTKEY, 'static/development/', 'MK Lib - Development');
