<?php

/**
 * benötigte Klassen einbinden.
 */

/**
 * @author Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 */
class tx_mklib_mod1_util_Helper
{
    /**
     * Die dazu das aktuelle item für eine Detailseite zu holen bzw dieses zurückzusetzen.
     * Dazu muss den Linker einfach folgendes für den action namen liefern: "show" + den eigentlichen key.
     *
     * Dann brauch man in der Detailansicht noch einen Button nach folgendem Schema:
     * $markerArray['###NEWSEARCHBTN###'] = $formTool->createSubmit('showHowTo[clear]', '###LABEL_BUTTON_BACK###');
     *
     * @param string                $key
     * @param tx_rnbase_mod_IModule $module
     *
     * @return Tx_Rnbase_Domain_Model_RecordInterface
     */
    public static function getCurrentItem($key, tx_rnbase_mod_IModule $module)
    {
        $itemid = 0;
        $data = tx_rnbase_parameters::getPostOrGetParameter('show'.$key);
        if ($data) {
            list($itemid) = each($data);
        }
        $dataKey = 'current'.$key;
        if ('clear' === $itemid) {
            return false;
        }
        // Daten mit Modul abgleichen
        $changed = $itemid ? [$dataKey => $itemid] : [];
        $data = Tx_Rnbase_Backend_Utility::getModuleData([$dataKey => ''], $changed, $module->getName());
        $itemid = $data[$dataKey];
        if (!$itemid) {
            return false;
        }
        $modelData = explode('|', $itemid);
        $item = tx_rnbase::makeInstance($modelData[0], $modelData[1]);

        if (!$item->isValid()) {
            $item = null; //auf null setzen damit die Suche wieder angezeigt wird
        }

        return $item;
    }
}
