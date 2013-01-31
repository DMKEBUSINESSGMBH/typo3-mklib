<?php
/**
 * 	@package TYPO3
 *  @subpackage tx_mkdifu
 *  @author Hannes Bochmann <hannes.bochmann@das-medienkombinat.de>
 *
 *  Copyright notice
 *
 *  (c) 2011 Michael Wagner <michael.wagner@das-medienkombinat.de>
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
tx_rnbase::load('tx_rnbase_filter_BaseFilter');

/**
 * BITTE INS WIKI SCHAUEN FÜR EINEN BEISPIEL TESTCASE, 
 * DER FÜR ABGELEITETE KLASSEN GESCHRIEBEN WERDEN SOLLTE!
 * 
 * @author Hannes Bochmann <hannes.bochmann@das-medienkombinat.de>
 * 
 * @todo default sortierung per TypoScript konfigurierbar machen
 * @todo mehrfach sortierung unertsützen?
 */
class tx_mklib_filter_Sorter extends tx_rnbase_filter_BaseFilter {
	
	/**
	 * @var string
	 */
	protected $sortConfId = 'sort.';
	
	/**
	 * @var string
	 */
	protected $allowedFieldsConfId = 'fields';
	
	/**
	 * @var string
	 */
	protected $sortLinkConfId = 'link.';	
	
	/**
	 * @var string
	 */
	protected $sortByParameterName = 'sortBy';
	
	/**
	 * @var string
	 */
	protected $sortOrderParameterName = 'sortOrder';
	
	/**
	 * @var string
	 */
	protected $markerPrefix = 'SORT';
	
	/**
	 * @var string
	 */
	private $sortBy;
	
	/**
	 * @var string
	 */
	private $sortOrder;
	
	
	/**
	 * @var null || boolean
	 */
	private $initiatedSorting = null;
	
	/**
	 * Beispiel TS config:
	 * myConfId.filter.sort.fields = title, name
	 * 
	 * setzt $this->sortBy und $this->sortOrder
	 * 
	 * @param 	tx_rnbase_IParameters 	$parameters
	 * 
	 * @return boolean
	 */
	protected function initSorting() {
		if(!is_null($this->initiatedSorting)){
			return $this->initiatedSorting;
		}
		
		$parameters = $this->getParameters();
		$sortBy = trim($parameters->get($this->sortByParameterName));
		
		if($sortBy && $this->sortByIsAllowed($sortBy)) {
			$sortOrder = $parameters->get($this->sortOrderParameterName);
			$sortOrder = $this->assureSortOrderIsValid($sortOrder);
			
			$this->sortBy = $sortBy;
			$this->sortOrder = $sortOrder;
			$this->initiatedSorting = true;
			
			return true;
		}
		//else
		
		$this->initiatedSorting = false;
		return false;
	}
	
	/**
	 * @param string $sortOrder
	 * 
	 * @return string
	 */
	private function assureSortOrderIsValid($sortOrder) {
		return ($sortOrder == 'desc') ? 'desc' : 'asc';
	}
	
	/**
	 * @return string
	 */
	protected function getSortBy() {
		return $this->sortBy;
	}
	
	/**
	 * @return string
	 */
	protected function getSortOrder() {
		return $this->sortOrder;
	}
	
	/**
	 * @param string $sortField
	 * 
	 * @return boolean
	 */
	private function sortByIsAllowed($sortField) {
		return in_array($sortField, $this->getAllowedSortFields());
	}
	
	/**
	 * Beispiel TS config:
	 * myConfId.filter.sort.fields = title, name
	 * 
	 * @return array
	 */
	private function getAllowedSortFields() {
		$confId = $this->getConfId().$this->sortConfId;
		$configurations = $this->getConfigurations();

		$sortFields = $configurations->get($confId.$this->allowedFieldsConfId);
		$sortFields = $sortFields ? t3lib_div::trimExplode(',', $sortFields, true) : array();
		
		return $sortFields;
	}
	
	/**
	 * @param string $template HTML template
	 * @param tx_rnbase_util_FormatUtil $formatter
	 * @param string $confId
	 * @param string $marker
	 * 
	 * @return string
	 */
	public function parseTemplate($template, &$formatter, $confId, $marker = 'FILTER') {
		$markerArray = $subpartArray  = $wrappedSubpartArray = array();

		$this->initSorting();
		$this->insertMarkersForSorting(
			$template, $markerArray, $subpartArray, $wrappedSubpartArray, $formatter, $confId
		);

		$template = tx_rnbase_util_Templates::substituteMarkerArrayCached($template, $markerArray, $subpartArray, $wrappedSubpartArray);
		
		return $template;
	}

	/**
	 * Beispiel TS config:
	 * myConfId.filter.sort.fields = title, name
	 * 
	 * Beispiel Template:
	 * ###SORT_TITLE_LINK###sortieren nach titel###SORT_TITLE_LINK###
	 * 
	 * @param string $template HTML template
	 * @param array $markerArray
	 * @param array $subpartArray
	 * @param array $wrappedSubpartArray
	 * @param tx_rnbase_util_FormatUtil $formatter
	 * @param string $confId
	 * @param string $marker
	 * 
	 * @return void
	 */
	private function insertMarkersForSorting($template, &$markerArray, &$subpartArray, &$wrappedSubpartArray, &$formatter, $confId) {
		$confId = $this->getConfId().$this->sortConfId;
		$configurations = $formatter->getConfigurations();
		
		$sortFields = $this->getAllowedSortFields();

		if(!empty($sortFields)) {
			tx_rnbase::load('tx_rnbase_util_BaseMarker');
		  	$token = md5(microtime());
		  	$markOrders = array();
			foreach($sortFields as $field) {
				$isField = ($field == $this->getSortBy());
				// sortOrder ausgeben
				$markOrders[$field.'_order'] = $isField ? $this->getSortOrder() : '';

				$fieldMarker = $this->markerPrefix.'_'.strtoupper($field).'_LINK';
				$makeLink = tx_rnbase_util_BaseMarker::containsMarker($template, $fieldMarker);
				$makeUrl = tx_rnbase_util_BaseMarker::containsMarker($template, $fieldMarker.'URL');
				// link generieren
				if($makeLink || $makeUrl) {
					// sortierungslinks ausgeben
					$params = array(
							'sortBy' => $field,
							'sortOrder' => $isField && $this->getSortOrder() == 'asc' ? 'desc' : 'asc',
						);
					$link = $configurations->createLink();
					$link->label($token);
					$link->initByTS(
						$configurations, 
						$confId.$this->sortLinkConfId, 
						$params
					);
					if($makeLink)
						$wrappedSubpartArray['###'.$fieldMarker.'###'] = explode($token, $link->makeTag());
					if($makeUrl)
						$markerArray['###'.$fieldMarker.'URL###'] = $link->makeUrl(false);
				}
			}
			// die sortOrders parsen
			$markOrders = $formatter->getItemMarkerArrayWrapped($markOrders, $confId, 0,$this->markerPrefix.'_', array_keys($markOrders));
			$markerArray = array_merge($markerArray, $markOrders);
		}
	}
}