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
