<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "mklib".
 *
 * Auto generated 09-12-2014 15:57
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/
$EM_CONF['mklib'] = [
    'title' => 'MK Lib',
    'description' => 'Utilities for extensions',
    'category' => 'misc',
    'author' => 'DMK E-BUSINESS GmbH',
    'author_email' => 'dev@dmk-ebusiness.de',
    'author_company' => 'DMK E-BUSINESS GmbH',
    'shy' => '',
    'dependencies' => 'rn_base',
    'version' => '12.0.0',
    'conflicts' => '',
    'priority' => '',
    'module' => '',
    'state' => 'stable',
    'internal' => '',
    'uploadfolder' => 0,
    'createDirs' => '',
    'modify_tables' => '',
    'clearCacheOnLoad' => 0,
    'lockType' => '',
    'constraints' => [
        'depends' => [
            'rn_base' => '1.17.0-',
            'typo3' => '11.5.7-12.4.99',
        ],
        'conflicts' => [],
    ],
];
