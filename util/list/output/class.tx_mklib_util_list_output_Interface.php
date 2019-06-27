<?php

/**
 * Interface für Ausgaben des Listbuilders.
 *
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
interface tx_mklib_util_list_output_Interface
{
    public function handleOutput();
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/util/list/output/class.tx_mklib_util_list_output_Interface.php']) {
    include_once $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/util/list/output/class.tx_mklib_util_list_output_Interface.php'];
}
