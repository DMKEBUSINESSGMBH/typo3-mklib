<?php

/**
 * Laden der Configs für die Services.
 */

/*
 * alle benötigten Klassen einbinden etc.
 */
if (!defined('TYPO3_MODE')) {
    exit('Access denied.');
}

tx_rnbase_util_Extensions::addService(
    'mklib',
    'mklib' /* sv type */ ,
    'tx_mklib_srv_Finance' /* sv key */ ,
    [
    'title' => 'Finance services', 'description' => 'Service functions for handling finances',
    'subtype' => 'finance',
    'available' => true, 'priority' => 50, 'quality' => 50,
    'os' => '', 'exec' => '',
    'classFile' => tx_rnbase_util_Extensions::extPath('mklib').'srv/class.tx_mklib_srv_Finance.php',
    'className' => 'tx_mklib_srv_Finance',
    ]
);

tx_rnbase_util_Extensions::addService(
    'mklib',
    'mklib' /* sv type */ ,
    'tx_mklib_srv_StaticCountries' /* sv key */ ,
    [
    'title' => 'StaticCountries services', 'description' => 'Service functions for handling StaticCountries',
    'subtype' => 'staticCountries',
    'available' => true, 'priority' => 50, 'quality' => 50,
    'os' => '', 'exec' => '',
    'classFile' => tx_rnbase_util_Extensions::extPath('mklib').'srv/class.tx_mklib_srv_StaticCountries.php',
    'className' => 'tx_mklib_srv_StaticCountries',
    ]
);

tx_rnbase_util_Extensions::addService(
    'mklib',
    'mklib' /* sv type */ ,
    'tx_mklib_srv_StaticCountryZones' /* sv key */ ,
    [
    'title' => 'StaticCountryZones services', 'description' => 'Service functions for handling StaticCountryZones',
    'subtype' => 'staticCountryZones',
    'available' => true, 'priority' => 50, 'quality' => 50,
    'os' => '', 'exec' => '',
    'classFile' => tx_rnbase_util_Extensions::extPath('mklib').'srv/class.tx_mklib_srv_StaticCountryZones.php',
    'className' => 'tx_mklib_srv_StaticCountryZones',
    ]
);
