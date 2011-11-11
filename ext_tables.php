<?php

/**
 * DB-Felder, die im BE bearbeitbar sind.
 * @package tx_mkmarketplace
 * @subpackage tx_mkmarketplace_
 */

/**
 * alle benötigten Klassen einbinden etc.
 */
if (!defined ('TYPO3_MODE')) {
  die ('Access denied.');
}
$_EXTKEY = 'mklib';

//TCAs einbinden
require t3lib_extMgm::extPath($_EXTKEY).'tca/ext_tables.php';

// initalize 'context sensitive help' (csh)
require_once t3lib_extMgm::extPath($_EXTKEY).'res/help/ext_csh.php';
?>