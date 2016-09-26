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
$EM_CONF [$_EXTKEY] = array (
	'title' => 'MK Lib',
	'description' => 'Utilities for extensions',
	'category' => 'misc',
	'author' => 'DMK E-BUSINESS GmbH',
	'author_email' => 'dev@dmk-ebusiness.de',
	'author_company' => 'DMK E-BUSINESS GmbH',
	'shy' => '',
	'dependencies' => 'rn_base',
	'version' => '2.0.10',
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
	'constraints' => array (
		'depends' => array (
			'rn_base' => '1.0.4-',
			'typo3' => '4.5.0-7.6.99',
			'scheduler' => '1.0.0-7.6.99'
		),
		'conflicts' => array (),
		'suggests' => array (
			'xajax' => ''
		)
	),
	'suggests' => array (),
);
