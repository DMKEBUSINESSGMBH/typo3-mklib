<?php

/**
 * Laden der Configs für die Services.
 * @package tx_mklib
 * @subpackage tx_mklib_srv
 */

/**
 * alle benötigten Klassen einbinden etc.
 */
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}


tx_rnbase::load('tx_mklib_util_ServiceRegistry');
tx_rnbase::load('tx_rnbase_util_SearchBase');


tx_rnbase_util_Extensions::addService(
    $_EXTKEY,
    'mklib' /* sv type */,
    'tx_mklib_srv_Wordlist' /* sv key */,
    array(
    'title' => 'Wordlist services', 'description' => 'Service functions for handling wordlists',
    'subtype' => 'wordlist',
    'available' => true, 'priority' => 50, 'quality' => 50,
    'os' => '', 'exec' => '',
    'classFile' => tx_rnbase_util_Extensions::extPath($_EXTKEY).'srv/class.tx_mklib_srv_Wordlist.php',
    'className' => 'tx_mklib_srv_Wordlist',
    )
);

tx_rnbase_util_Extensions::addService(
    $_EXTKEY,
    'mklib' /* sv type */,
    'tx_mklib_srv_Finance' /* sv key */,
    array(
    'title' => 'Finance services', 'description' => 'Service functions for handling finances',
    'subtype' => 'finance',
    'available' => true, 'priority' => 50, 'quality' => 50,
    'os' => '', 'exec' => '',
    'classFile' => tx_rnbase_util_Extensions::extPath($_EXTKEY).'srv/class.tx_mklib_srv_Finance.php',
    'className' => 'tx_mklib_srv_Finance',
    )
);

tx_rnbase_util_Extensions::addService(
    $_EXTKEY,
    'mklib' /* sv type */,
    'tx_mklib_srv_StaticCountries' /* sv key */,
    array(
    'title' => 'StaticCountries services', 'description' => 'Service functions for handling StaticCountries',
    'subtype' => 'staticCountries',
    'available' => true, 'priority' => 50, 'quality' => 50,
    'os' => '', 'exec' => '',
    'classFile' => tx_rnbase_util_Extensions::extPath($_EXTKEY).'srv/class.tx_mklib_srv_StaticCountries.php',
    'className' => 'tx_mklib_srv_StaticCountries',
    )
);

tx_rnbase_util_Extensions::addService(
    $_EXTKEY,
    'mklib' /* sv type */,
    'tx_mklib_srv_StaticCountryZones' /* sv key */,
    array(
    'title' => 'StaticCountryZones services', 'description' => 'Service functions for handling StaticCountryZones',
    'subtype' => 'staticCountryZones',
    'available' => true, 'priority' => 50, 'quality' => 50,
    'os' => '', 'exec' => '',
    'classFile' => tx_rnbase_util_Extensions::extPath($_EXTKEY).'srv/class.tx_mklib_srv_StaticCountryZones.php',
    'className' => 'tx_mklib_srv_StaticCountryZones',
    )
);
