<?php
/**
 * lokale Config laden.
 * @package tx_mklib
 * @subpackage tx_mklib_tca
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


if (!tx_rnbase_util_TYPO3::isTYPO60OrHigher()) {
    require tx_rnbase_util_Extensions::extPath(
        'mksearch',
        'Configuration/TCA/Override/static_countries.php'
    );
}
