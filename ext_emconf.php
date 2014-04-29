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
  'author' => 'das MedienKombinat GmbH',
  'author_email' => 'info@das-medienkombinat.de',
  'author_company' => 'das Medienkombinat GmbH',
  'shy' => '',
  'dependencies' => 'rn_base',
'version' => '0.9.41',
  'conflicts' => '',
  'priority' => '',
  'module' => '',
  'state' => 'beta',
  'internal' => '',
  'uploadfolder' => 0,
  'createDirs' => '',
  'modify_tables' => '',
  'clearCacheOnLoad' => 0,
  'lockType' => '',
  'constraints' => array(
    'depends' => array(
		'rn_base' => '0.12.2-',
    ),
    'conflicts' => array(
    ),
    'suggests' => array(
    	'xajax' => '',
    ),
  ),
);

?>