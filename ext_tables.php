<?php

/**
 * DB-Felder, die im BE bearbeitbar sind.
 * @package tx_mkmarketplace
 * @subpackage tx_mkmarketplace_
 */

/**
 * alle benötigten Klassen einbinden etc.
 */
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}
$_EXTKEY = 'mklib';

//TCAs einbinden
require tx_rnbase_util_Extensions::extPath($_EXTKEY).'tca/ext_tables.php';

// initalize 'context sensitive help' (csh)
require_once tx_rnbase_util_Extensions::extPath($_EXTKEY).'res/help/ext_csh.php';



////////////////////////////////
// Plugin anmelden
////////////////////////////////
// Einige Felder ausblenden
$TCA['tt_content']['types']['list']['subtypes_excludelist']['tx_mklib'] = 'layout,select_key,pages';

// Das tt_content-Feld pi_flexform einblenden
$TCA['tt_content']['types']['list']['subtypes_addlist']['tx_mklib'] = 'pi_flexform';

tx_rnbase_util_Extensions::addPiFlexFormValue('tx_mklib', 'FILE:EXT:'.$_EXTKEY.'/flexform_main.xml');

tx_rnbase_util_Extensions::addPlugin(
    array(
        'LLL:EXT:'.$_EXTKEY.'/locallang_db.php:plugin.mklib.label',
        'tx_mklib',
        tx_rnbase_util_Extensions::extRelPath($_EXTKEY) . 'ext_icon.gif',
    )
);

tx_rnbase_util_Extensions::addStaticFile($_EXTKEY, 'static/basic/', 'MK Lib - Basics');
tx_rnbase_util_Extensions::addStaticFile($_EXTKEY, 'static/development/', 'MK Lib - Development');
