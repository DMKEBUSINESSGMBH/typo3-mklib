<?php

/**
 * Laden der Configs für die Services.
 */

/*
 * alle benötigten Klassen einbinden etc.
 */
if (!defined('TYPO3')) {
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
