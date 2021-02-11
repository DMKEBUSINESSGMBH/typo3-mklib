<?php
/**
 * @author Hannes Bochmann
 *
 *  Copyright notice
 *
 *  (c) 2010 Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 */

/**
 * Die Klasse ermöglicht direkt eine CSV Datei
 * mit den Boardmitteln von TYPO3 zu schreiben.
 */
class tx_mklib_util_Csv extends Tx_Rnbase_RecordList_DatabaseRecordList
{
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
     * @param string $sDir
     * @param string $sPrefix
     * @param array  $aData
     * @param string $sFileName | gibt es einen festen dateinamen?
     *
     * @return string | Name der Datei
     */
    public function writeCsv($sDir, $sPrefix = '', $aData = [], $sFileName = '')
    {
        if (empty($aData)) {
            $aData = $this->getCsvLines();
        }

        if (!$sFileName) {
            if ($sPrefix) {
                $sPrefix = $sPrefix.'_';
            }
            $sFileName = $sPrefix.date('dmy-Hi').'.csv';
        }
        $sCsvLines = implode(chr(13).chr(10), $aData);

        if (tx_rnbase_util_Files::writeFile($sDir.$sFileName, $sCsvLines)) {
            return $sFileName;
        } else {
            return false;
        }
    }

    /**
     * @param array  $csvRow
     * @param string $delimiter
     * @param string $quote
     */
    public function setCsvRow($csvRow, $delimiter = ',', $quote = '"')
    {
        if (tx_rnbase_util_TYPO3::isTYPO90OrHigher()) {
            $csvLine = \TYPO3\CMS\Core\Utility\CsvUtility::csvValues($csvRow, $delimiter, $quote);
        } else {
            $csvLine = \TYPO3\CMS\Core\Utility\GeneralUtility::csvValues($csvRow, $delimiter, $quote);
        }
        $this->csvLines[] = $csvLine;
    }
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/util/class.tx_mklib_util_Csv.php']) {
    include_once $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/util/class.tx_mklib_util_Csv.php'];
}
