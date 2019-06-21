<?php

/**
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
interface tx_mklib_mod1_export_IInjectHandler
{
    /**
     * Iniziert den Exporthandler.
     *
     * @param tx_mklib_mod1_export_Handler $handler
     */
    public function setExportHandler(tx_mklib_mod1_export_Handler $handler);
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/mod1/export/class.tx_mklib_mod1_export_ISearcher.php']) {
    include_once $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/mod1/export/class.tx_mklib_mod1_export_ISearcher.php'];
}
