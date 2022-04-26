<?php
/**
 * lokale Config laden.
 */

/*
 * alle benötigten Klassen einbinden etc.
 */
if (!defined('TYPO3_MODE')) {
    exit('Access denied.');
}

$_EXTKEY = 'mklib';

require_once \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY).'scheduler/ext_localconf.php';
require_once \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY).'srv/ext_localconf.php';

// das ist nur eine info für entwickler welcher basis exception code
// für diese extension verwendet wird. in diesem fall 400.
// also könnte ein valider exception code dieser extension 4001 sein
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['baseExceptionCode'] = 400;

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals']['tx_mklib_tca_eval_priceDecimalSeperator'] =
    'tx_mklib_tca_eval_priceDecimalSeperator';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals']['tx_mklib_tca_eval_isoDate'] =
    'tx_mklib_tca_eval_isoDate';
