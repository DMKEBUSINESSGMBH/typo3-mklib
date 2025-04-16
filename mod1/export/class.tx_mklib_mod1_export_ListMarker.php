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
 * Base class for Markers.
 *
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_mklib_mod1_export_ListMarker extends Sys25\RnBase\Frontend\Marker\ListMarker
{
    /**
     * Callback function for next item.
     *
     * @param object $data
     */
    public function renderNext($data): void
    {
        $data->setProperty('roll', $this->rowRollCnt);
        $data->setProperty('line', $this->i); // Marker für aktuelle Zeilenummer
        $data->setProperty('totalline', $this->i + $this->totalLineStart + $this->offset); // Marker für aktuelle Zeilenummer der Gesamtliste
        $this->handleVisitors($data);
        $part = $this->entryMarker->parseTemplate($this->getInfo()->getTemplate($data), $data, $this->getFormatter(), $this->confId, $this->marker);

        tx_mklib_mod1_export_Util::doOutPut($part);

        $this->rowRollCnt = ($this->rowRollCnt >= $this->rowRoll) ? 0 : $this->rowRollCnt + 1;
        ++$this->i;
    }

    /**
     * Call all visitors for an item.
     *
     * @param object $data
     */
    private function handleVisitors($data): void
    {
        if (!is_array($this->getVisitors())) {
            return;
        }

        foreach ($this->getVisitors() as $visitor) {
            call_user_func($visitor, $data);
        }
    }

    /**
     * Render an array of objects.
     *
     * @param array                                   $dataArr
     * @param string                                  $template
     * @param string                                  $markerClassname
     * @param string                                  $confId
     * @param string                                  $marker
     * @param Sys25\RnBase\Frontend\Marker\FormatUtil $formatter
     * @param int                                     $offset
     *
     * @return array
     */
    public function render($dataArr, $template, $markerClassname, $confId, $marker, &$formatter, $markerParams = false, $offset = 0): string
    {
        $out = parent::render($dataArr, $template, $markerClassname, $confId, $marker, $formatter, $markerParams, $offset);
        tx_mklib_mod1_export_Util::doOutPut($out);

        return '';
    }
}
