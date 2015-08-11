<?php
/**
 * lokale Config laden.
 * @package tx_mklib
 * @subpackage tx_mklib_util
 */

/**
 * alle benötigten Klassen einbinden etc.
 */
if (!defined ('TYPO3_MODE')) {
   die ('Access denied.');
}

$_EXTKEY = 'mklib';

if (TYPO3_MODE == 'BE' && !tx_rnbase_util_TYPO3::isTYPO62OrHigher()) {
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['cliKeys'][$_EXTKEY] = array('EXT:'.$_EXTKEY.'/cli/class.tx_mklib_cli_main.php','_CLI_'.$_EXTKEY);
}

require_once(t3lib_extMgm::extPath($_EXTKEY).'scheduler/ext_localconf.php');
require_once(t3lib_extMgm::extPath($_EXTKEY).'srv/ext_localconf.php');
require_once(t3lib_extMgm::extPath($_EXTKEY).'hooks/ext_localconf.php');

//das ist nur eine info für entwickler welcher basis exception code
//für diese extension verwendet wird. in diesem fall 400.
//also könnte ein valider exception code dieser extension 4001 sein
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['baseExceptionCode'] = 400;
define('ERROR_CODE_MKLIB', 400);
