<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_util
 *  @author Hannes Bochmann
 *
 *  Copyright notice
 *
 *  (c) 2010 Hannes Bochmann <hannes.bochmann@das-medienkombinat.de>
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
 * benötigte Klassen einbinden
 */
require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');
require_once(PATH_typo3 . 'class.db_list.inc');
require_once(PATH_typo3 . 'class.db_list_extra.inc');

/**
 * Die Klasse ermöglicht direkt eine CSV Datei
 * mit den Boardmitteln von TYPO3 zu schreiben
 */
class tx_mklib_util_Csv extends localRecordList {
	
	/**
	 * Gibt die Csv Zeilen zurück, die gesetzt wurden
	 * @return array
	 */
	public function getCsvLines() {
		return $this->csvLines;
	}
	
	/**
	 * Schreibt die ganzen CSV Zeilen in eine Datei
	 * 
	 * @param string $sDir
	 * @param string $sPrefix
	 * @param array $aData
	 * @param string $sFileName	| gibt es einen festen dateinamen?
	 * 
	 * @return string | Name der Datei
	 */
	public function writeCsv($sDir, $sPrefix = '', $aData = array(), $sFileName = '') {
		if(empty($aData)) $aData = $this->getCsvLines();
		
		if(!$sFileName){
			if($sPrefix)$sPrefix = $sPrefix.'_';
			$sFileName=$sPrefix.date('dmy-Hi').'.csv';
		}
		$sCsvLines = implode(chr(13).chr(10),$aData);
		
		if(t3lib_div::writeFile($sDir.$sFileName,$sCsvLines))
			return $sFileName;
		else return false;
	}
	
	/**
	 * Adds input row of values to the internal csvLines array as a CSV formatted line
	 *
	 * @param	array		Array with values to be listed.
	 * @return	void
	 */
	public function setCsvRow($aCsvRow, $sDelim = ',', $sQuote = '"')	{
		$this->csvLines[] = t3lib_div::csvValues($aCsvRow,$sDelim ,$sQuote);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/util/class.tx_mklib_util_Csv.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/util/class.tx_mklib_util_Csv.php']);
}

?>