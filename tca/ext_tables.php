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
	$TCA['tx_mklib_wordlist'] = array (
	    'ctrl' => array (
	        'title'     => 'LLL:EXT:mklib/locallang_db.xml:tx_mklib_wordlist',
	        'label'     => 'word',
	    	'label_alt' => 'uid',
	    	'label_alt_force' => false,
	        'tstamp'    => 'tstamp',
	        'crdate'    => 'crdate',
	        'cruser_id' => 'cruser_id',
	        'default_sortby' => 'ORDER BY crdate',
	        'delete' => 'deleted',
	        'enablecolumns' => array (
	            'disabled' => 'hidden',
	        ),
	        'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca/tx_mklib_wordlist.php',
	        'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon/icon_tx_mklib_wordlist.gif',
	        'dividers2tabs'     => true,
	    ),
	);
}

// static_info_tables um PLZ regeln erweitern
if(t3lib_extMgm::isLoaded('static_info_tables')) {
	t3lib_div::loadTCA('static_countries');
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
	t3lib_extMgm::addTCAcolumns('static_countries', $tempColumns, 1);
	t3lib_extMgm::addToAllTCAtypes('static_countries', 'zipcode_rule');
	t3lib_extMgm::addToAllTCAtypes('static_countries', 'zipcode_length');
}
