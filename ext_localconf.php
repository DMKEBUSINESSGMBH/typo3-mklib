<?php
/**
 * lokale Config laden.
 */

/**
 * alle benötigten Klassen einbinden etc.
 */
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

$_EXTKEY = 'mklib';

if (!tx_rnbase_util_TYPO3::isTYPO90OrHigher()) {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['cliKeys'][$_EXTKEY] = array('EXT:'.$_EXTKEY.'/cli/class.tx_mklib_cli_main.php', '_CLI_'.$_EXTKEY);
}

require_once tx_rnbase_util_Extensions::extPath($_EXTKEY).'scheduler/ext_localconf.php';
require_once tx_rnbase_util_Extensions::extPath($_EXTKEY).'srv/ext_localconf.php';

//das ist nur eine info für entwickler welcher basis exception code
//für diese extension verwendet wird. in diesem fall 400.
//also könnte ein valider exception code dieser extension 4001 sein
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['baseExceptionCode'] = 400;

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals']['tx_mklib_tca_eval_priceDecimalSeperator'] =
    'tx_mklib_tca_eval_priceDecimalSeperator';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals']['tx_mklib_tca_eval_isoDate'] =
    'tx_mklib_tca_eval_isoDate';
