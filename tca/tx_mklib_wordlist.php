<?php

/**
 * TCA.
 */
if (!defined('TYPO3_MODE')) {
    exit('Access denied.');
}

$TCA['tx_mklib_wordlist'] = [
  'ctrl' => $TCA['tx_mklib_wordlist']['ctrl'],
  'interface' => [
    'showRecordFieldList' => 'hidden,blacklisted,whitelisted,word',
  ],
  'feInterface' => $TCA['tx_mklib_wordlist']['feInterface'],
  'columns' => [
    'hidden' => [
      'exclude' => 1,
      'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
      'config' => [
        'type' => 'check',
        'default' => '0',
      ],
    ],
    'blacklisted' => [
      'exclude' => 1,
      'label' => 'LLL:EXT:mklib/locallang_db.xml:tx_mklib_wordlist.blacklisted',
      'config' => [
        'type' => 'check',
        'default' => '1',
      ],
    ],
    'whitelisted' => [
      'exclude' => 1,
      'label' => 'LLL:EXT:mklib/locallang_db.xml:tx_mklib_wordlist.whitelisted',
      'config' => [
        'type' => 'check',
        'default' => '0',
      ],
    ],
    'word' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:mklib/locallang_db.xml:tx_mklib_wordlist.word',
        'config' => [
            'type' => 'input',
            'size' => '30',
            'eval' => 'required',
        ],
    ],
  ],
  'types' => [
    '0' => ['showitem' => 'hidden;;1;;1-1-1,blacklisted,whitelisted,word'],
  ],
  'palettes' => [
    '1' => ['showitem' => ''],
  ],
];
