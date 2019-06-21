<?php

/**
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
interface tx_mklib_mod1_export_IModFunc
{
    /**
     * Liefert die ConfId für diese ModFunc.
     *
     * @return string
     */
    public function getConfId();

    /**
     * Returns an instance of tx_rnbase_mod_IModule.
     *
     * @return tx_rnbase_mod_IModule
     */
    public function getModule();

    /**
     * Liefert den Searcher des Module.
     *
     * @return tx_mklib_mod1_export_ISearcher
     */
    public function getSearcher();
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/mod1/export/class.tx_mklib_mod1_export_IModFunc.php']) {
    include_once $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/mod1/export/class.tx_mklib_mod1_export_IModFunc.php'];
}
