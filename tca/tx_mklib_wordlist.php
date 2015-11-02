<?php

/**
 * TCA.
 * @package tx_mklib
 * @subpackage tx_mklib_tca
 */

if (!defined ('TYPO3_MODE')) {
  die ('Access denied.');
}

$TCA['tx_mklib_wordlist'] = Array (
  'ctrl' => $TCA['tx_mklib_wordlist']['ctrl'],
  'interface' => Array (
    'showRecordFieldList' => 'hidden,blacklisted,whitelisted,word'
  ),
  'feInterface' => $TCA['tx_mklib_branches']['feInterface'],
  'columns' => Array (
    'hidden' => array (
      'exclude' => 1,
      'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
      'config'  => array (
        'type'    => 'check',
        'default' => '0'
      )
    ),
    'blacklisted' => array (
      'exclude' => 1,
      'label' => 'LLL:EXT:mklib/locallang_db.xml:tx_mklib_wordlist.blacklisted',
      'config'  => array (
        'type'    => 'check',
        'default' => '1'
      )
    ),
    'whitelisted' => array (
      'exclude' => 1,
      'label' => 'LLL:EXT:mklib/locallang_db.xml:tx_mklib_wordlist.whitelisted',
      'config'  => array (
        'type'    => 'check',
        'default' => '0'
      )
    ),
    'word' => array (
		'exclude' => 1,
        'label' => 'LLL:EXT:mklib/locallang_db.xml:tx_mklib_wordlist.word',
        'config' => array (
        	'type' => 'input',
            'size' => '30',
            'eval' => 'required',
		)
	),
  ),
  'types' => Array (
    '0' => array('showitem' => 'hidden;;1;;1-1-1,blacklisted,whitelisted,word')
  ),
  'palettes' => Array (
    '1' => array('showitem' => '')
  )
);
