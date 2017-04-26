<?php
if (!defined ('TYPO3_MODE')) {
   die ('Access denied.');
}

// static_info_tables um PLZ regeln erweitern
if(tx_rnbase_util_Extensions::isLoaded('static_info_tables')) {
	tx_rnbase::load('tx_rnbase_util_TYPO3');
	tx_rnbase_util_Extensions::addTCAcolumns(
		'static_countries',
		array(
			'zipcode_rule' => array(
				'exclude' => '0',
				'label' => 'LLL:EXT:mklib/locallang_db.xml:static_countries.zipcode_rule',
				'config' => array (
					'type' => 'input',
					'size' => '1',
					'eval' => 'trim,int',
				)
			),
			'zipcode_length' => array(
				'exclude' => '0',
				'label' => 'LLL:EXT:mklib/locallang_db.xml:static_countries.zipcode_length',
				'config' => array (
					'type' => 'input',
					'size' => '2',
					'eval' => 'trim,int',
				)
			),
		),
		!tx_rnbase_util_TYPO3::isTYPO62OrHigher()
	);
	tx_rnbase_util_Extensions::addToAllTCAtypes('static_countries', 'zipcode_rule');
	tx_rnbase_util_Extensions::addToAllTCAtypes('static_countries', 'zipcode_length');
}
