<?php
/**
 * lokale Config laden.
 * @package tx_mklib
 * @subpackage tx_mklib_tca
 */

/**
 * alle benötigten Klassen einbinden etc.
 */
if (!defined ('TYPO3_MODE')) {
   die ('Access denied.');
}

// Nur für für tests wichtig.
$_EXTCONF = isset($_EXTCONF) ? $_EXTCONF : $_EXTCONF = $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['mklib'];
$_EXTKEY = isset($_EXTKEY) ? $_EXTKEY : 'mklib';
// Konfiguration umwandeln
$_EXTCONF = is_array($_EXTCONF) ? $_EXTCONF : unserialize($_EXTCONF);

// tca integrieren für tx_mklib_wordlist einbinden, wenn gesetzt.
if(is_array($_EXTCONF) && array_key_exists('tableWordlist', $_EXTCONF) && intval($_EXTCONF['tableWordlist'])) {
	tx_rnbase::load('tx_mklib_srv_Wordlist');
	$TCA['tx_mklib_wordlist'] = tx_mklib_srv_Wordlist::getTca();
}

// static_info_tables um PLZ regeln erweitern
if(tx_rnbase_util_Extensions::isLoaded('static_info_tables')) {
	$tempColumns = array(
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
		);
	tx_rnbase_util_Extensions::addTCAcolumns('static_countries', $tempColumns, 1);
	tx_rnbase_util_Extensions::addToAllTCAtypes('static_countries', 'zipcode_rule');
	tx_rnbase_util_Extensions::addToAllTCAtypes('static_countries', 'zipcode_length');
}
