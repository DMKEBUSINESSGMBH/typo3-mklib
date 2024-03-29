<?php

/**
 * @author Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 */
class tx_mklib_model_StaticCountryZone extends Sys25\RnBase\Domain\Model\BaseModel
{
    /**
     * @var array
     */
    private static $instances = [];

    /**
     * @param mixed $rowOrUid
     *
     * @return tx_mklib_model_StaticCountryZone
     */
    public static function getInstance($rowOrUid = null)
    {
        // Instanzieren, wenn nicht im Cache oder ein Record übergeben wurde.
        if (is_array($rowOrUid) || !isset(self::$instances[$rowOrUid])) {
            $item = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_mklib_model_StaticCountryZone', $rowOrUid);
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
        return 'static_country_zones';
    }
}
