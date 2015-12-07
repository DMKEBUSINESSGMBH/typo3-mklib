<?php
/**
 * lokale Config laden.
 * @package tx_mkkvbb
 * @subpackage tx_mkkvbb_hooks
 */

/**
 * alle benötigten Klassen einbinden etc.
 */
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

require_once tx_rnbase_util_Extensions::extPath('rn_base', 'class.tx_rnbase.php');

// http://forge.typo3.org/issues/24925 wurden nach 3 Jahren in Typo3 6.1.8 Implementiert
tx_rnbase::load('tx_rnbase_util_TYPO3');
if (!tx_rnbase_util_TYPO3::isTYPO3VersionOrHigher(6001008)) {
	$GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tceforms.php']['getSingleFieldClass']['mklib']
		= 'EXT:' . $_EXTKEY . '/hooks/class.tx_mklib_hooks_t3lib_tceforms_getSingleFieldClass.php:tx_mklib_hooks_t3lib_tceforms_getSingleFieldClass';
}
