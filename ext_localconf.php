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

if (TYPO3_MODE=='BE')    {
    // Setting up scripts that can be run from the cli_dispatch.phpsh script.
    $TYPO3_CONF_VARS['SC_OPTIONS']['GLOBAL']['cliKeys'][$_EXTKEY] = array('EXT:'.$_EXTKEY.'/cli/class.tx_mklib_cli_main.php','_CLI_'.$_EXTKEY);
}

require_once(t3lib_extMgm::extPath('mklib').'scheduler/ext_localconf.php');
require_once(t3lib_extMgm::extPath('mklib').'srv/ext_localconf.php');