<?php

/**
 * TCA.
 */
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

$TCA['tx_mklib_wordlist'] = array(
  'ctrl' => $TCA['tx_mklib_wordlist']['ctrl'],
  'interface' => array(
    'showRecordFieldList' => 'hidden,blacklisted,whitelisted,word',
  ),
  'feInterface' => $TCA['tx_mklib_wordlist']['feInterface'],
  'columns' => array(
    'hidden' => array(
      'exclude' => 1,
      'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
      'config' => array(
        'type' => 'check',
        'default' => '0',
      ),
    ),
    'blacklisted' => array(
      'exclude' => 1,
      'label' => 'LLL:EXT:mklib/locallang_db.xml:tx_mklib_wordlist.blacklisted',
      'config' => array(
        'type' => 'check',
        'default' => '1',
      ),
    ),
    'whitelisted' => array(
      'exclude' => 1,
      'label' => 'LLL:EXT:mklib/locallang_db.xml:tx_mklib_wordlist.whitelisted',
      'config' => array(
        'type' => 'check',
        'default' => '0',
      ),
    ),
    'word' => array(
        'exclude' => 1,
        'label' => 'LLL:EXT:mklib/locallang_db.xml:tx_mklib_wordlist.word',
        'config' => array(
            'type' => 'input',
            'size' => '30',
            'eval' => 'required',
        ),
    ),
  ),
  'types' => array(
    '0' => array('showitem' => 'hidden;;1;;1-1-1,blacklisted,whitelisted,word'),
  ),
  'palettes' => array(
    '1' => array('showitem' => ''),
  ),
);
