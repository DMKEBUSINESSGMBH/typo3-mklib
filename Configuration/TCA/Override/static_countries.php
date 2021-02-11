<?php

if (!defined('TYPO3_MODE')) {
    exit('Access denied.');
}

// static_info_tables um PLZ regeln erweitern
if (tx_rnbase_util_Extensions::isLoaded('static_info_tables')) {
    tx_rnbase_util_Extensions::addTCAcolumns(
        'static_countries',
        [
            'zipcode_rule' => [
                'exclude' => '0',
                'label' => 'LLL:EXT:mklib/locallang_db.xml:static_countries.zipcode_rule',
                'config' => [
                    'type' => 'input',
                    'size' => '1',
                    'eval' => 'trim,int',
                ],
            ],
            'zipcode_length' => [
                'exclude' => '0',
                'label' => 'LLL:EXT:mklib/locallang_db.xml:static_countries.zipcode_length',
                'config' => [
                    'type' => 'input',
                    'size' => '2',
                    'eval' => 'trim,int',
                ],
            ],
        ],
        false
    );
    tx_rnbase_util_Extensions::addToAllTCAtypes('static_countries', 'zipcode_rule');
    tx_rnbase_util_Extensions::addToAllTCAtypes('static_countries', 'zipcode_length');
}
