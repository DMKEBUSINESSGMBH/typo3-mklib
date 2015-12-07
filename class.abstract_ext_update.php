<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib
 *
 *  Copyright notice
 *
 *  (c) 2011 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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

tx_rnbase::load('tx_rnbase_util_Network');
/**
 * Class for updating the db
 *
 * @author	 Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 */
abstract class abstract_ext_update  {

	/**
	 *
	 * @var t3lib_cs
	 */
	private $csconv = false;

	/**
	 * Main function, returning the HTML content of the update module
	 *
	 * @return	string		HTML
	 */
	function main()	{
		$fieldsets = array();
		$fieldsets['Character encoding'] 	= $this->getDestEncodingSelect();
		$fieldsets['Update Static Info Tables']	= $this->handleUpdateStaticInfoTables();

		$content  = '';
		$content .= '<form action="'.htmlspecialchars(t3lib_div::linkThisScript()).'" method="post">';
		foreach($fieldsets as $legend => $fieldset) {
			$content .= '<fieldset>';
			if($legend && !is_numeric($legend)) {
				$content .=  '<legend><strong>&nbsp;'.$legend.'&nbsp;</strong></legend>';
			}
			$content .= $fieldset;
			$content .= '</fieldset>';
			$content .= '<p><br /></p>';
		}

		$content .= '<p><input type="submit" /></p>';
		$content .= '</form>';

		return $content;
	}

	/**
	 * Erzeugt die Selectbox für das encoding
	 * @return 	string
	 */
	private function getDestEncodingSelect(){
		require_once(tx_rnbase_util_Extensions::extPath('static_info_tables','class.tx_staticinfotables_encoding.php'));
		$content  = '';
		$content .= '<label>Destination character encoding:</label>';
		$content .= tx_staticinfotables_encoding::getEncodingSelect('dest_encoding', '', 'utf-8');
		$content .= '<p>(The character encoding must match the encoding of the existing tables data. By default this is UTF-8.)</p>';
		if($destEncoding = $this->getDestEncoding()) {
			$content .= '<p>Current encoding: '.htmlspecialchars($destEncoding).'</p>';
		}
		return $content;
	}

	/**
	 * @return string
	 */
	private function getDestEncoding(){
		return t3lib_div::_GP('dest_encoding');
	}

	/**
	 * @TODO prüfen ob der import bereits durchgeführt wurde.
	 *
	 * @return 	string
	 */
	private function handleUpdateStaticInfoTables(){
		$updateKey = $this->getStatementKey();

		$content  = '';
		$content .= $this->getInfoMsg();

		if (!tx_rnbase_util_Extensions::isLoaded('static_info_tables')) {
			$content .= '<p><strong>The extension static_info_tables needs to be installed first!</strong></p>';
		} else {
			if(t3lib_div::_GP($updateKey)) {
				if(($ret = $this->queryDB($updateKey)) === true) {
					$content .= $this->getSuccessMsg();
				} else {
					$content .= '<p><big><strong>'.$ret.'</strong></big></p>';
				}
			} else {
				$content .= '<p><br /><input type="checkbox" name="'.$updateKey.'" id="'.$updateKey.'" /> <label for="'.$updateKey.'">'.$this->getCheckboxLabel().'</label></p>';
			}
		}
		return $content;

		// export von der dsag
//		$sUpdate = 'UPDATE static_countries SET	zipcode_rule=\'%2$d\',	zipcode_length=\'%3$d\'	WHERE cn_iso_2=\'%1$s\';';
//		tx_rnbase::load('tx_rnbase_util_DB');
//		$aLand = tx_rnbase_util_DB::doSelect('*','land', array('enablefieldsoff'=>1,/*'debug'=>1,*/ 'where'=>'tx_dsagsap_iso_2 != \'\''));
//		$sSQL = ''; $aCountries = array();
//		foreach($aLand as $aRecord){
//			$aCountries[] = $aRecord['land'].' ('.$aRecord['tx_dsagsap_iso_2'].')';
//			$sSQL .= sprintf( $sUpdate, $aRecord['tx_dsagsap_iso_2'], $aRecord['prplz'], $aRecord['lnplz'] )."\n";
//		}
//		exit('<h1>Importierte Länder:</h1><ul><li>'.implode("</li>\r\n<li>", $aCountries).'</li></ul><h1>Import SQL für static_countries</h1><pre>'.$sSQL.'</pre>');

	}

	/**
	 * Liefert das Label für die Checkbox
	 * Enter description here ...
	 */
	protected function getCheckboxLabel() {
		return 'import static info tables';
	}

	private function queryDB($updateKey){

		$file = tx_rnbase_util_Extensions::extPath($this->getExtensionName(), $this->getSqlFileName());
		$fileContent = explode("\n", tx_rnbase_util_Network::getUrl($file));
		if(!$fileContent) {
			return $this->getSqlFileName().' not found! ('.$file.')';
		}

		$destEncoding = $this->getDestEncoding();
		$querys = array();
		$keyQuery = 0;
		foreach($fileContent as $line)	{
			$line=trim($line);
			// nach dem ende des update keys suchen
			if($keyQuery && t3lib_div::isFirstPartOfStr($line,'#'.$updateKey)) {
				$keyQuery = 2;
				break; // alle satements gefunden schleife nicht mehr durchlaufen
			}
			// nach dem anfang des update keys suchen
			if(!$keyQuery && t3lib_div::isFirstPartOfStr($line, '#'.$updateKey)) {
				$keyQuery = 1; // key gefunden, jetzt folgen die statements
				continue;
			}
			// der update key wurde noch nicht erreicht
			if(!$keyQuery) {
				continue;
			}
			if ($line && t3lib_div::isFirstPartOfStr($line, $this->getSqlMode())) {
				// ggf. das encoding ändern
				$querys[] = $this->getUpdateEncoded($line, $destEncoding);
			}
		}

		switch($keyQuery){
			case 0:
				return 'No '.strtolower($this->getSqlMode()).' key not found. ('.$updateKey.')';
			case 1:
				return 'End key not found. ('.$updateKey.')';
			case 2:
				// alles ok
		}

		if(count($querys)===0) {
			return 'No queries found. ('.$updateKey.')';
		}
		foreach($querys as $query) {
			$GLOBALS['TYPO3_DB']->admin_query($query);
		}
		return true;
	}

	/**
	 * Sollen Updates, Inserts ausgeführt werden?
	 * @return string
	 */
	protected function getSqlMode() {
		return 'UPDATE';
	}

	/**
	 * @return 	t3lib_cs
	 */
	private function getCharsetsConversion(){
		if(!$this->csconv) {
			$this->csconv = tx_rnbase::makeInstance('t3lib_cs');
		}
		return $this->csconv;
	}

	/**
	 * Convert the values of a SQL update statement to a different encoding than UTF-8.
	 *
	 * @param 	string $query Update statement like: UPDATE static_countries SET zipcode_rule='2', zipcode_length='5' WHERE cn_iso_2='DE';
	 * @param 	string $destEncoding Destination encoding
	 * @return 	string Converted update statement
	 */
	private function getUpdateEncoded($query, $destEncoding) {
		if (!($destEncoding==='utf-8')) {
			$queryElements = explode('WHERE', $query);
			$where = preg_replace('#;$#', '', trim($queryElements[1]));
			$queryElements = explode('SET', $queryElements[0]);
			$queryFields = $queryElements[1];

			$queryElements = tx_rnbase_util_Strings::trimExplode('UPDATE', $queryElements[0], 1);
			$table = $queryElements[0];

			$fields_values = array();
			$queryFieldsArray = tx_rnbase_util_Strings::trimExplode(',', $queryFields, 1);
			foreach ($queryFieldsArray as $fieldsSet) {
				$col = tx_rnbase_util_Strings::trimExplode('=', $fieldsSet, 1);
				$value = stripslashes(substr($col[1], 1, strlen($col[1])-2));
				$value = $this->getCharsetsConversion()->conv($value, 'utf-8', $destEncoding);
				$fields_values[$col[0]] = $value;
			}
			$query = $GLOBALS['TYPO3_DB']->UPDATEquery($table,$where,$fields_values);
		}
		return $query;
	}

	function access() {
		return TRUE;
	}

	/**
	 * Liefert den Namen der Datei, welche die Update Statements beinhaltet
	 * @return string
	 */
	protected function getSqlFileName() {
		return 'ext_tables_static_update.sql';
	}

	/**
	 * @return string
	 */
	protected function getStatementKey() {
		return 'importStaticInfoTables';
	}

	/**
	 * Liefert den Namen der Extension für die
	 * @return string
	 */
	abstract protected function getExtensionName();

	/**
	 * Liefert die Nachricht, was gemacht werden soll
	 * @return string
	 */
	abstract protected function getInfoMsg();

	/**
	 * Liefert die Nachricht für den Erfolgsfall
	 * @return string
	 */
	abstract protected function getSuccessMsg();
}

// Include extension?
if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/class.ext_update.php'])	{
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/class.ext_update.php']);
}
