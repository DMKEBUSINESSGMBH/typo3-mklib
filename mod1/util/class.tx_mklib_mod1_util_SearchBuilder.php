<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_mod1
 *  @author Hannes Bochmann
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
 * benötigte Klassen einbinden
 */
require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');

/**
 * Hilfsklasse für Suchen im BE
 */
class tx_mklib_mod1_util_SearchBuilder {
	/**
	 * Suche nach einem Freitext bei der Wordlist-Suche. Wird ein leerer String
	 * übergeben, dann wird nicht gesucht. 
	 *
	 * @param array $fields
	 * @param string $searchword
	 */
	public static function buildFeUserFreeText(&$fields, $searchword) {
		$result = false;
	  	if(strlen(trim($searchword))) {
	   		$joined['value'] = trim($searchword);
	   		$joined['cols'] = array('FEUSER.uid', 'FEUSER.LAST_NAME', 'FEUSER.FIRST_NAME', 'FEUSER.username', 'FEUSER.email');
	   		$joined['operator'] = OP_LIKE;
	   		$fields[SEARCH_FIELD_JOINED][] = $joined;
	   		$result = true;
	  	} 
	  	return $result;
	}

	
	/**
	 * Build a wildcard query. Support for phrases:
	 * "bad ar" will be turned into "+field:bad* +field:ar*"
	 * Note: the following signs will be ignored: ,.&*+-%/
	 * @param string $term
	 * @param string $fieldName
	 * @param boolean $leadingWC force leading wildcard query
	 * @return string
	 */
	public static function makeWildcardTerm($term, $fieldName='', $leadingWC=false) {
		$term = $term ? mb_strtolower(trim($term), 'UTF-8') : '*';

//		$pattern = '/[\s%,.&*+-\/]+/';
		//wir brauchen 3 backslashes (\\\) um einen einfachen zu entwerten.
		//der erste entwertet den zweiten für die hochkommas. der zweite
		//entwertet den dritten für regex.
		//ansonsten sind das alle Zeichen, die in Solr nicht auftauchen 
		//dürfen da sie zur such-syntax gehören
		//genau dürfen nicht auftauchen: + - & | ! ( ) { } [ ] ^ " ~ * ? : \
		//außerdem nehmen wir folgende raus um die Suche zu verfeinern:
		//, . / # '
		//eigentlich sollte dies aber ebenfalls durch Solr filter realisiert
		//werden
		$pattern = '/[\s%,.&*+-\/\'!?#()\[\]\{\}"^|:\\\~]+/';
		$arr = preg_split($pattern, $term);

		$terms = array();
		$field = $fieldName ? $fieldName.':' : '';
		foreach($arr As $term) {
			// einen leeren string ignorieren
			if(empty($term)) continue;
				// @FIXME: warum Hochkommas um den string?
				// es handelt sich um ein einzelnes wort!
				// bei buhl musste dies wieder entfernt werden, da es mit hochkommas nicht funktionierte.
				$terms[] = '+' .$field.( $leadingWC ? '*' : '' ).'"'.$term.'"*';
		}
		return implode(' ', $terms);
	}
	
	/**
	 * Returns the complete search form
	 * enthält Suchefeld und Dropdown für versteckte Items
	 * 
	 * @param string $funcId
	 * @param string $currentSearchWord
	 * @param string $currentShowHidden
	 * @param object $selector
	 * @param string $out
	 * @deprecated tx_mklib_mod1_searcher_abstractBase nutzen
	 * @return string
	 */
	public static function getSearchForm($funcId, &$currentSearchWord, &$currentShowHidden, $selector, array &$data = array()) {
		$currentSearchWord = $selector->showFreeTextSearchForm($data, $funcId, 'search'.$funcId, $GLOBALS['LANG']->getLL('label_button_search'));
		$currentShowHidden = $selector->showHiddenSelector($data, array('id'=>'showhidden'.$funcId));
		$out = '';
		$out .= '<div>Suche: ' . $data['field']. $data['selector'] . $data['misc'] . $data['button'] .'</div>';
		return $out;
	}
	
	/**
	 * Fügt allgemeine Spalten ein
	 * 
	 * @param array $columns
	 * @param tx_mklib_mod1_decorator_Base $decorator
	 * @deprecated tx_mklib_mod1_searcher_abstractBase nutzen
	 */
	public static function addMiscColumns(&$columns,tx_mklib_mod1_decorator_Base $decorator) {
		$columns['actions'] = array('title' => 'label_tableheader_actions', 'decorator' => $decorator, 'width' => 90);
	}
	
	/**
	 * Suche nach einem Freitext. Wird ein leerer String
	 * übergeben, dann wird nicht gesucht. 
	 *
	 * @param array $fields
	 * @param string $searchword
	 * @param array $cols
	 */
	public static function buildFreeText(&$fields, $searchword, array $cols = array()) {
		$result = false;
	  	if(strlen(trim($searchword))) {
	   		$joined['value'] = trim($searchword);
	   		$joined['cols'] = $cols;
	   		$joined['operator'] = OP_LIKE;
	   		$fields[SEARCH_FIELD_JOINED][] = $joined;
	   		$result = true;
	  	}  
	  	return $result;
	}
	
	/**
	 * Bildet die Resultliste mit Pageer
	 * 
	 * @param tx_mklib_mod1_searcher_Base $callingClass
	 * @param object $srv
	 * @param array $fields
	 * @param array $options
	 * @deprecated tx_mklib_mod1_searcher_abstractBase nutzen
	 * @return string
	 */
	public static function getResultList(tx_mklib_mod1_searcher_Base $callingClass, $srv, array &$fields = array(), array &$options = array()) {
		$funcId = $callingClass->getFuncId();
		$pager = tx_rnbase::makeInstance('tx_rnbase_util_BEPager', $funcId.'Pager', $callingClass->getModule()->getName(), 0);

		$options['distinct'] = 1;
		$callingClass->prepareFieldsAndOptions($fields, $options);
			
		// Get counted data
		$cnt = $callingClass->getCount($srv, $fields, $options);
		
		$pager->setListSize($cnt);
		$pager->setOptions($options);

		// Get data
//		$options['debug'] = true;
		$items = $srv->search($fields, $options);
		$ret = array();
		$content = '';
		$callingClass->showItems($content, $items);
		$ret['table'] = $content;
		$ret['totalsize'] = $cnt;
		$pagerData = $pager->render();
		$ret['pager'] .= '<div class="pager">' . $pagerData['limits'] . ' - ' .$pagerData['pages'] .'</div>';
		return $ret;
	}
	
	/**
	 * Start creation of result list
	 * 
	 * @param string $content
	 * @param array $items
	 * @param tx_mklib_mod1_decorator_Base $decorator
	 * @param tx_mklib_mod1_searcher_Base $callingClass
	 * @param bool $bAddMiscColumns
	 * @deprecated tx_mklib_mod1_searcher_abstractBase nutzen
	 */
	public static function showItems(&$content, $items, tx_mklib_mod1_decorator_Base $decorator, tx_mklib_mod1_searcher_Base $callingClass, $columns, $bAddMiscColumns = true) {
		$funcId = $callingClass->getFuncId();
		//allgmeine Spalten hinzufügen
		if($bAddMiscColumns)
			self::addMiscColumns($columns,$decorator);

		if($items) {
			tx_rnbase::load('tx_rnbase_mod_Tables');
			$arr = tx_rnbase_mod_Tables::prepareTable($items, $columns, $callingClass->getFormTool(), $callingClass->getOptions());
			$out = $callingClass->getModule()->getDoc()->table($arr[0]);
		}
		else {
	  		$out = '<p><strong>###LABEL_NO_'.strtoupper($funcId).'_FOUND###</strong></p><br/>';
		}
		$content .= $out;
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/util/class.tx_mklib_mod1_util_SearchBuilder.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/util/class.tx_mklib_mod1_util_SearchBuilder.php']);
}
?>