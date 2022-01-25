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

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'mklib',
    'mklib' /* sv type */ ,
    'tx_mklib_srv_Finance' /* sv key */ ,
    [
    'title' => 'Finance services', 'description' => 'Service functions for handling finances',
    'subtype' => 'finance',
    'available' => true, 'priority' => 50, 'quality' => 50,
    'os' => '', 'exec' => '',
    'classFile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('mklib').'srv/class.tx_mklib_srv_Finance.php',
    'className' => 'tx_mklib_srv_Finance',
    ]
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'mklib',
    'mklib' /* sv type */ ,
    'tx_mklib_srv_StaticCountries' /* sv key */ ,
    [
    'title' => 'StaticCountries services', 'description' => 'Service functions for handling StaticCountries',
    'subtype' => 'staticCountries',
    'available' => true, 'priority' => 50, 'quality' => 50,
    'os' => '', 'exec' => '',
    'classFile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('mklib').'srv/class.tx_mklib_srv_StaticCountries.php',
    'className' => 'tx_mklib_srv_StaticCountries',
    ]
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'mklib',
    'mklib' /* sv type */ ,
    'tx_mklib_srv_StaticCountryZones' /* sv key */ ,
    [
    'title' => 'StaticCountryZones services', 'description' => 'Service functions for handling StaticCountryZones',
    'subtype' => 'staticCountryZones',
    'available' => true, 'priority' => 50, 'quality' => 50,
    'os' => '', 'exec' => '',
    'classFile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('mklib').'srv/class.tx_mklib_srv_StaticCountryZones.php',
    'className' => 'tx_mklib_srv_StaticCountryZones',
    ]
);
