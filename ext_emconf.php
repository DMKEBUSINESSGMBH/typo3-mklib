<?php
/**
 * Extension Manager/Repository config file for ext "mklib".
 * @package tx_mklib
 * @subpackage tx_mklib_
 */

########################################################################
# Extension Manager/Repository config file for ext "mklib".
#
# Auto generated 29-11-2010 17:40
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
  'title' => 'MK Lib',
  'description' => 'Utilities for extensions',
  'category' => 'misc',
  'author' => 'DMK E-BUSINESS GmbH',
  'author_email' => 'dev@dmk-ebusiness.de',
  'author_company' => 'DMK E-BUSINESS GmbH',
  'shy' => '',
  'dependencies' => 'rn_base',
'version' => '0.9.56',
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
  'constraints' => array(
    'depends' => array(
		'rn_base' => '0.12.2-',
		'typo3' => '4.3.0-6.2.99',
    ),
    'conflicts' => array(
    ),
    'suggests' => array(
    	'xajax' => '',
    ),
  ),
);

?>
