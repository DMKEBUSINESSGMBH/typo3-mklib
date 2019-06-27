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

// Nur für für tests wichtig.
$_EXTCONF = isset($_EXTCONF) ? $_EXTCONF : $_EXTCONF = $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['mklib'];
$_EXTKEY = isset($_EXTKEY) ? $_EXTKEY : 'mklib';
// Konfiguration umwandeln
$_EXTCONF = is_array($_EXTCONF) ? $_EXTCONF : unserialize($_EXTCONF);
