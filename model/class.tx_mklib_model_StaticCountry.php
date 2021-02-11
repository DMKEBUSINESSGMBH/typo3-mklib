<?php

/**
 * Model eins Landes.
 *
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_mklib_model_StaticCountry extends tx_rnbase_model_base implements tx_mklib_interface_IZipCountry
{
    private static $instances = [];

    /**
     * @TODO: prüfen, ob die felder zipcode_rule, zipcode_length existieren!!!
     *
     * @param mixed $rowOrUid
     *
     * @return tx_mklib_model_StaticCountry
     */
    public static function getInstance($rowOrUid = null)
    {
        // Instanzieren, wenn nicht im Cache oder ein Record übergeben wurde.
        if (is_array($rowOrUid) || !isset(self::$instances[$rowOrUid])) {
            $item = tx_rnbase::makeInstance('tx_mklib_model_StaticCountry', $rowOrUid);
            // Nur das erzeugte Model zurückgeben
            if (is_array($rowOrUid)) {
                return $item;
            }
            // else, Model Cachen, wenn eine uid übergeben wurde
            self::$instances[$rowOrUid] = $item;
        }

        return self::$instances[$rowOrUid];
    }

    /**
     * Liefert den Namen der Tabelle.
     *
     * @return Tabellenname als String
     */
    public function getTableName()
    {
        return 'static_countries';
    }

    /**
     * Liefert den ISO2 Code des Landes. DE,CZ,PL usw.
     *
     * @return string
     */
    public function getISO2()
    {
        return $this->record['cn_iso_2'];
    }

    /**
     * ID der Regel für die PLZ-Validierung.
     *
     * @return int 1-9
     */
    public function getZipRule()
    {
        return intval($this->record['zipcode_rule']);
    }

    /**
     * Erlaubte Anzahl Zeichen der PLZ.
     *
     * @return int
     */
    public function getZipLength()
    {
        return intval($this->record['zipcode_length']);
    }

    /**
     * @return string
     */
    public function getGermanShortName()
    {
        return $this->record['cn_short_de'];
    }

    /**
     * @return int
     */
    public function getIsoNumber()
    {
        return $this->record['cn_iso_nr'];
    }
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/model/class.tx_mklib_model_StaticCountry.php']) {
    include_once $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/model/class.tx_mklib_model_StaticCountry.php'];
}
