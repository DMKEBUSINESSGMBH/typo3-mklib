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

require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');

$GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tceforms.php']['getSingleFieldClass'][] ='EXT:' . $_EXTKEY . '/hooks/class.tx_mklib_hooks_t3lib_tceforms_getSingleFieldClass.php:tx_mklib_hooks_t3lib_tceforms_getSingleFieldClass';
