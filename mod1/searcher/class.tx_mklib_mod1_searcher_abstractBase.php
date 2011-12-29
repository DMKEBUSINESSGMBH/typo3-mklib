<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 das MedienKombinat GmbH <kontakt@das-medienkombinat.de>
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
 ***************************************************************/
require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');


/**
 * Basisklasse für Suchfunktionen in BE-Modulen
 * 
 * @package tx_mklib
 * @subpackage tx_mklib_mod1
 * @author Michael Wagner <michael.wagner@das-medienkombinat.de>
 */
abstract class tx_mklib_mod1_searcher_abstractBase {
	
	/**
	 * Wurde die ll bereits geladen?
	 * @var boolean
	 */
	private static $llLoaded = false;
	/**
	 * Selector Klasse
	 * @var tx_rnbase_mod_IModule
	 */
	private $mod = null;
	/**
	 * Selector Klasse
	 * @var tx_mklib_mod1_util_Selector
	 */
	private $selector = null;
	/**
	 * Otions
	 * @var array
	 */
	protected $options = array();
	
	/**
	 * Current search term 
	 * @var 	string
	 */
	protected $currentSearchWord = '';
	
	/**
	 * Current hidden option 
	 * @var 	string
	 */
	protected $currentShowHidden = 1;
	
	
	/**
	 * Constructor
	 * 
	 * @param 	tx_rnbase_mod_IModule 	$mod
	 * @param 	array 					$options
	 */
	public function __construct(tx_rnbase_mod_IModule $mod, array $options = array()) {
		$this->init($mod, $options);
	}

	/**
	 * Init object
	 *
	 * @param 	tx_rnbase_mod_IModule 	$mod
	 * @param 	array 					$options
	 */
	protected function init(tx_rnbase_mod_IModule $mod, $options) {
		// locallang einlesen
		if(!self::$llLoaded) {
			$GLOBALS['LANG']->includeLLFile('EXT:mklib/mod1/locallang.xml');
			self::$llLoaded = true;
		}
		$this->setOptions($options);
		$this->mod = $mod;
	}
	/**
	 * Bietet die Möglichkeit die Optionen nach der Erstellung noch zu ändern
	 * @param array $options
	 */
	public function setOptions($options) {
		$this->options = $options;;
	}
	/**
	 * @return string
	 */
	protected function getSearcherId(){
		tx_rnbase::load('tx_mklib_util_String');
		$pageId = tx_mklib_util_String::toCamelCase(get_class($this));
		return $pageId;
	}
	
	/**
	 * Liefert den Service.
	 * 
	 * @return tx_mklib_srv_Base
	 */
	abstract protected function getService();
	
	/**
	 * Returns the complete search form
	 * @return 	string
	 */
	public function getSearchForm() {
		$data = $this->getFilterTableDataForSearchForm();
		
		$selector = $this->getSelector();
		$out = $selector->buildFilterTable($data);
		return $out;
	}
	
	/**
	 * Liefert die Daten für das Basis-Suchformular damit
	 * das Html gebaut werden kann
	 * @return array
	 */
	protected function getFilterTableDataForSearchForm() {
		$data = array();
		$options = array('pid' => $this->options['pid']);
		$selector = $this->getSelector();
		
		$this->currentSearchWord = $selector->showFreeTextSearchForm(
			$data['search'],
			$this->getSearcherId().'Search',
			$options
		);
		
		$this->currentShowHidden = $selector->showHiddenSelector(
			$data['hidden'],
			$options
		);
		
		if($updateButton = $this->getSearchButton())
			$data['updatebutton'] = array(
				'label' => '',
				'button'=> $updateButton
			);
				
		return $data;
	}
	
	/**
	 * Returns the search button
	 * @return 	string|false
	 */
	protected function getSearchButton() {
		$out = $this->getFormTool()->createSubmit(
					$this->getSearcherId().'Search',
					$GLOBALS['LANG']->getLL('label_button_update')
				);
		return $out;
	}

	/**
	 * Bildet die Resultliste mit Pager
	 * 
	 * @param tx_mklib_mod1_searcher_Base $callingClass
	 * @param object $srv
	 * @param array $fields
	 * @param array $options
	 * @return string
	 */
	public function getResultList() {
		$srv = $this->getService();
		/* @var $pager tx_rnbase_util_BEPager */
		$pager = tx_rnbase::makeInstance(
				'tx_rnbase_util_BEPager',
				$this->getSearcherId().'Pager',
				$this->getModule()->getName(),
				$pid = 0 //@TODO: die PageId solle noch konfigurierbar gemacht werden.
			);

		$fields = $options = array();
		$this->prepareFieldsAndOptions($fields, $options);
		
		//@todo tests schreiben
		//es könnte sein dass ein sorting gewählt wurde. dann wollen wir dieses
		//auch nutzen
		$this->prepareSorting($options);

		// Get counted data
		$cnt = $this->getCount($fields, $options);
		
		$pager->setListSize($cnt);
		$pager->setOptions($options);

		// Get data
		$items = $srv->search($fields, $options);
		$content = '';
		$this->showItems($content, $items);
		
		$pagerData = $pager->render();
		
		//der zusammengeführte Pager für die Ausgabe
		//nur wenn es auch Ergebnisse gibt. sonst reicht die noItemsFoundMsg
		$sPagerData = '';
		if($cnt)
			$sPagerData = $pagerData['limits'] . ' - ' .$pagerData['pages'];
			
		return array(
				'table' 	=> $content,
				'totalsize' => $cnt,
				'pager' 	=> '<div class="pager">' . $sPagerData .'</div>',
			);
	}
	/**
	 * Sortierung vorbereiten
	 * @param array $options
	 */
	private function prepareSorting(&$options) {
		$sortedCols = array();
		if(t3lib_div::_GET('sortField') && t3lib_div::_GET('sortRev')) {
			$sortedCols = array(t3lib_div::_GET('sortField') => t3lib_div::_GET('sortRev'));
			//wir setzen die daten noch für das Modul um bei einem seiten wechsel
			//weiterhin die richtige sortierung zu haben
			$this->getSelector()->setValueToModuleData(
				$this->getModule()->getName(),
				array($this->getSearcherId().'orderby' => $sortedCols));
		}elseif($aOrderByByModuleData = $this->getSelector()->getValueFromModuleData($this->getSearcherId().'orderby')){
			$sortedCols = $aOrderByByModuleData;
			//wenn die sortierung aus dem Modul kommt, müssen wir die Sortierung in
			//den $_GET Daten setzen damit die richtigen Pfeile angezeigt werden
			//siehe tx_rnbase_util_FormTool::createSortLink
			$aKeys = array_keys($aOrderByByModuleData);
			$_GET['sortField'] = $aKeys[0];
			$aValues = array_values($aOrderByByModuleData);
			$_GET['sortRev'] = $aValues[0];
		}

		if(!empty($sortedCols)) {
			$cols = $this->getDecoratorColumns($this->getDecorator($this->getModule()));

			foreach($sortedCols As $colLabel => $sortOrder) {
				$configuredCol = $cols[$colLabel];
				if(!$configuredCol || !array_key_exists('sortable', $configuredCol)) continue;

				// das Label in die notwendige SQL-Anweisung umwandeln. Normalerweise ein Spaltenname.
				$sortCol = $configuredCol['sortable'];
				// Wenn am Ende ein Punkt steht, muss die Spalte zusammengefügt werden.
				$sortCol = substr($sortCol, -1) === '.' ? $sortCol.$colLabel : $sortCol;
				$options['orderby'][$sortCol] = (strtolower($sortOrder)== 'asc' ? 'asc':'desc');
			}
		}
	}
	/**
	 * Kann von der Kindklasse überschrieben werden, um weitere Filter zu setzen.
	 * 
	 * @param 	array 	$fields
	 * @param 	array 	$options
	 */
	protected function prepareFieldsAndOptions(array &$fields, array &$options) {
		$options['distinct'] = 1;
//		$options['debug'] = true;
		
		if(!$this->currentShowHidden) { $options['enablefieldsfe'] = 1;}
		else { $options['enablefieldsbe'] = 1; }
		
		//die fields nun mit dem Suchbegriff und den Spalten, 
		//in denen gesucht werden soll, füllen
		tx_rnbase::load('tx_mklib_mod1_util_SearchBuilder');
		tx_mklib_mod1_util_SearchBuilder::buildFreeText($fields, $this->currentSearchWord, $this->getCols());
		
		// das muss die kindklasse auswerten (oder eigene methode?)
//		if($this->currentSearchWord){}
//		if(isset($this->options['pid'])){}
	}
	
	/**
	 * @deprecated Bitte getSearchColumns nutzen!
	 */
	protected function getCols() {
		return $this->getSearchColumns();
	}
	
	/**
	 * Liefert die Spalten, in denen gesucht werden soll
	 * @return array
	 */
	protected function getSearchColumns() {
		return array();
	}

	/**
	 * Start creation of result list.
	 * @param 	string 	$content
	 * @param 	array 	$items
	 * @return 	string
	 */
	protected function showItems(&$content, array $items) {
		if(count($items) === 0) {
			$content = $this->getNoItemsFoundMsg();
			return;//stop	
		}
		// else
		$aColumns = $this->getColumns( $this->getDecorator( $this->getModule() ) );
		tx_rnbase::load('tx_rnbase_mod_Tables');
		$arr = tx_rnbase_mod_Tables::prepareTable($items, $aColumns, $this->getFormTool(), $this->getOptions());
		$out = $this->getModule()->getDoc()->table($arr[0]);
		$content .= $out;
		return $out;
	}
	
	/**
	 * @return 	tx_mklib_mod1_decorator_Base
	 */
	protected function getDecorator(&$mod){
		return tx_rnbase::makeInstance('tx_mklib_mod1_decorator_Base', $mod);		
	}
	/**
	 * @deprecated bitte getDecoratorColumns nutzen
	 */
	protected function getColumns(&$oDecorator){
		return $this->getDecoratorColumns($oDecorator);
	}
	
	/**
	 * Liefert die Spalten für den Decorator.
	 * @param 	tx_mklib_mod1_decorator_Base 	$oDecorator
	 * @return 	array
	 */
	protected function getDecoratorColumns(&$oDecorator) {
		return array(
			'uid' => array(
				'title' => 'label_tableheader_uid',
				'decorator' => &$oDecorator,
			),
			'actions' => array(
				'title' => 'label_tableheader_actions',
				'decorator' => &$oDecorator,
			)
		);
	}
	
	/**
	 * Returns an instance of tx_mkhoga_beutil_Selector.
	 * Der Selector wird erst erzeugt, wenn er benötigt wird
	 * 
	 * @return 	tx_mklib_mod1_util_Selector
	 */
	protected function getSelector() {
		if(!$this->selector) {
			$this->selector = tx_rnbase::makeInstance('tx_mklib_mod1_util_Selector');
			$this->selector->init($this->getModule());
		}
		return $this->selector;
	}
	
	/**
	 * 
	 * @param array $fields
	 * @param array $options
	 */
	protected function getCount(array &$fields, array $options){
		// Get counted data
		$options['count'] = 1;
		return $this->getService()->search($fields, $options);
	}
	
	/**
	 * Returns an instance of tx_rnbase_mod_IModule
	 * 
	 * @return 	tx_rnbase_mod_IModule
	 */
	protected function getModule() {
		return $this->mod;
	}
	
	/**
	 * Returns an instance of tx_rnbase_mod_IModule
	 * 
	 * @return 	tx_rnbase_mod_IModule
	 */
	protected function getOptions() {
		return $this->options;
	}
	
	/**
	 * Returns an instance of tx_rnbase_mod_IModule
	 * 
	 * @return 	tx_rnbase_util_FormTool
	 */
	protected function getFormTool() {
		return $this->mod->getFormTool();
	}
	
	/**
	 * Returns the message in case no items could be found in showItems()
	 * 
	 * @return 	string
	 */
	protected function getNoItemsFoundMsg() {
		return '<p><strong>###LABEL_NO_'.strtoupper($this->getSearcherId()).'_FOUND###</strong></p><br/>';;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/mod1/searcher/class.tx_mklib_mod1_searcher_abstractBase.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/mod1/searcher/class.tx_mklib_mod1_searcher_abstractBase.php']);
}
