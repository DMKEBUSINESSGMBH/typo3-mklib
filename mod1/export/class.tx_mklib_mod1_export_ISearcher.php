<?php

/**
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
interface tx_mklib_mod1_export_ISearcher
{
    /**
     * Liefert den List-Provider,
     * welcher die Ausgabe der einzelnen Datensätze generiert
     * und an den Listbuilder übergeben wird.
     *
     * @return \Sys25\RnBase\Frontend\Marker\IListProvider
     */
    public function getInitialisedListProvider();
}
