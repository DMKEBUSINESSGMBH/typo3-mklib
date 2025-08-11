<?php

/*
 * Copyright notice
 *
 * (c) DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
 * All rights reserved
 *
 * This file is part of the "mklib" Extension for TYPO3 CMS.
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GNU Lesser General Public License can be found at
 * www.gnu.org/licenses/lgpl.html
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
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
     * @return Sys25\RnBase\Domain\Model\RecordInterface
     */
    public static function getCurrentItem(string $key, Sys25\RnBase\Backend\Module\IModule $module): false|object|null
    {
        $itemid = 0;
        $data = Sys25\RnBase\Frontend\Request\Parameters::getPostOrGetParameter('show'.$key);
        if ($data) {
            $itemid = current($data);
        }

        $dataKey = 'current'.$key;
        if ('clear' === $itemid) {
            return false;
        }

        // Daten mit Modul abgleichen
        $changed = $itemid ? [$dataKey => $itemid] : [];
        $data = Sys25\RnBase\Backend\Utility\BackendUtility::getModuleData([$dataKey => ''], $changed, $module->getName());
        $itemid = $data[$dataKey];
        if (!$itemid) {
            return false;
        }

        $modelData = explode('|', (string) $itemid);
        $item = TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance($modelData[0], $modelData[1]);

        if (!$item->isValid()) {
            $item = null; // auf null setzen damit die Suche wieder angezeigt wird
        }

        return $item;
    }
}
