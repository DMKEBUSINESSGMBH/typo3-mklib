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
 * Die Klasse ermöglicht direkt eine CSV Datei
 * mit den Boardmitteln von TYPO3 zu schreiben.
 */
class tx_mklib_util_Csv
{
    /**
     * @var array
     */
    protected $csvLines;

    /**
     * Gibt die Csv Zeilen zurück, die gesetzt wurden.
     *
     * @return array
     */
    public function getCsvLines()
    {
        return $this->csvLines;
    }

    /**
     * Schreibt die ganzen CSV Zeilen in eine Datei.
     *
     * @param string $sPrefix
     * @param array  $aData
     * @param string $sFileName | gibt es einen festen dateinamen?
     *
     * @return string|Name der Datei
     */
    public function writeCsv(string $sDir, $sPrefix = '', $aData = [], $sFileName = '')
    {
        if (empty($aData)) {
            $aData = $this->getCsvLines();
        }

        if (!$sFileName) {
            if ($sPrefix) {
                $sPrefix .= '_';
            }

            $sFileName = $sPrefix.date('dmy-Hi').'.csv';
        }

        $sCsvLines = implode(chr(13).chr(10), $aData);

        if (Sys25\RnBase\Utility\Files::writeFile($sDir.$sFileName, $sCsvLines)) {
            return $sFileName;
        }

        return false;
    }

    public function setCsvRow(array $csvRow, string $delimiter = ',', string $quote = '"'): void
    {
        $this->csvLines[] = TYPO3\CMS\Core\Utility\CsvUtility::csvValues($csvRow, $delimiter, $quote);
    }
}
