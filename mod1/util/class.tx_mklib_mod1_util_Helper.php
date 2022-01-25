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
     * @param \Sys25\RnBase\Backend\Module\IModule $module
     *
     * @return \Sys25\RnBase\Domain\Model\RecordInterface
     */
    public static function getCurrentItem($key, \Sys25\RnBase\Backend\Module\IModule $module)
    {
        $itemid = 0;
        $data = \Sys25\RnBase\Frontend\Request\Parameters::getPostOrGetParameter('show'.$key);
        if ($data) {
            list($itemid) = each($data);
        }
        $dataKey = 'current'.$key;
        if ('clear' === $itemid) {
            return false;
        }
        // Daten mit Modul abgleichen
        $changed = $itemid ? [$dataKey => $itemid] : [];
        $data = \Sys25\RnBase\Backend\Utility\BackendUtility::getModuleData([$dataKey => ''], $changed, $module->getName());
        $itemid = $data[$dataKey];
        if (!$itemid) {
            return false;
        }
        $modelData = explode('|', $itemid);
        $item = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance($modelData[0], $modelData[1]);

        if (!$item->isValid()) {
            $item = null; //auf null setzen damit die Suche wieder angezeigt wird
        }

        return $item;
    }
}
