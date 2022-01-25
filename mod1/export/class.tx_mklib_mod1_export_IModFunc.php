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
     * Returns an instance of \Sys25\RnBase\Backend\Module\IModule.
     *
     * @return \Sys25\RnBase\Backend\Module\IModule
     */
    public function getModule();

    /**
     * Liefert den Searcher des Module.
     *
     * @return tx_mklib_mod1_export_ISearcher
     */
    public function getSearcher();
}
