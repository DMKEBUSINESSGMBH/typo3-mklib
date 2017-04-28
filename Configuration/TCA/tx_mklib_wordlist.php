<?php

if (! defined('TYPO3_MODE')) {
    die('Access denied.');
}

return array(
    'ctrl' => array(
        'title' => 'LLL:EXT:mklib/locallang_db.xml:tx_mklib_wordlist',
        'label' => 'word',
        'label_alt' => 'uid',
        'label_alt_force' => false,
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'default_sortby' => 'ORDER BY crdate',
        'delete' => 'deleted',
        'enablecolumns' => array(
            'disabled' => 'hidden'
        ),
        'iconfile' => 'EXT:mklib/icon/icon_tx_mklib_wordlist.gif',
        'dividers2tabs' => true
    ),
    'interface' => array(
        'showRecordFieldList' => 'hidden,blacklisted,whitelisted,word'
    ),
    'feInterface' => $TCA['tx_mklib_wordlist']['feInterface'],
    'columns' => array(
        'hidden' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
            'config' => array(
                'type' => 'check',
                'default' => '0'
            )
        ),
        'blacklisted' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:mklib/locallang_db.xml:tx_mklib_wordlist.blacklisted',
            'config' => array(
                'type' => 'check',
                'default' => '1'
            )
        ),
        'whitelisted' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:mklib/locallang_db.xml:tx_mklib_wordlist.whitelisted',
            'config' => array(
                'type' => 'check',
                'default' => '0'
            )
        ),
        'word' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:mklib/locallang_db.xml:tx_mklib_wordlist.word',
            'config' => array(
                'type' => 'input',
                'size' => '30',
                'eval' => 'required'
            )
        )
    ),
    'types' => array(
        '0' => array(
            'showitem' => 'hidden;;1;;1-1-1,blacklisted,whitelisted,word'
        )
    ),
    'palettes' => array(
        '1' => array(
            'showitem' => ''
        )
    )
);
