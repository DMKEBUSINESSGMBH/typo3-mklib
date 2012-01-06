<?php

/**
 * DB-Felder, die im BE bearbeitbar sind.
 * @package tx_mkmarketplace
 * @subpackage tx_mkmarketplace_
 */

/**
 * alle benötigten Klassen einbinden etc.
 */
if (!defined ('TYPO3_MODE')) {
  die ('Access denied.');
}
$_EXTKEY = 'mklib';

//TCAs einbinden
require t3lib_extMgm::extPath($_EXTKEY).'tca/ext_tables.php';

// initalize 'context sensitive help' (csh)
require_once t3lib_extMgm::extPath($_EXTKEY).'res/help/ext_csh.php';



////////////////////////////////
// Plugin anmelden
////////////////////////////////
// Einige Felder ausblenden
$TCA['tt_content']['types']['list']['subtypes_excludelist']['tx_mklib']='layout,select_key,pages';

// Das tt_content-Feld pi_flexform einblenden
$TCA['tt_content']['types']['list']['subtypes_addlist']['tx_mklib'] = 'pi_flexform';

t3lib_extMgm::addPiFlexFormValue('tx_mklib','FILE:EXT:'.$_EXTKEY.'/flexform_main.xml');

t3lib_extMgm::addPlugin(
	array(
		'LLL:EXT:'.$_EXTKEY.'/locallang_db.php:plugin.mklib.label',
		'tx_mklib',
		t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif',
	)
);

t3lib_extMgm::addStaticFile($_EXTKEY, 'static/basic/', 'MK Lib - Basics');
t3lib_extMgm::addStaticFile($_EXTKEY, 'static/development/', 'MK Lib - Development');