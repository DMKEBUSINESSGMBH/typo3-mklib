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
 */
class tx_mklib_filter_Sorter extends tx_rnbase_filter_BaseFilter {
	
	/**
	 * bei bedarf default wert überschreiben.
	 * 
	 * @var string
	 */
	protected $defaultSortBy = '';
	
	/**
	 * bei bedarf default wert überschreiben.
	 * 
	 * @var string
	 */
	protected $defaultSortOrder = 'asc';
	
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
		
		$sortBy = $this->getSortByFromParameters();
		
		if($sortBy && $this->sortByIsAllowed($sortBy)) {
			$sortOrder = $this->getSortOrderFromParameters();
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
	 * 
	 * @return string
	 */
	private function getSortByFromParameters() {
		$parameters = $this->getParameters();
		$sortBy = trim($parameters->get('sortBy'));
		return $sortBy ? $sortBy : $this->defaultSortBy;
	}
	
	/**
	 * @return string
	 */
	private function getSortOrderFromParameters() {
		$parameters = $this->getParameters();
		$sortOrder = $parameters->get('sortOrder');
		return $sortOrder ? $sortOrder : $this->defaultSortOrder;
	}
	
	/**
	 * @param string $sortOrder
	 * 
	 * @return string
	 */
	private function assureSortOrderIsValid($sortOrder) {
		return ($sortOrder == 'asc') ? 'asc' : 'desc';
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
		return in_array($sortField, $this->getAllowSortFields());
	}
	
	/**
	 * Beispiel TS config:
	 * myConfId.filter.sort.fields = title, name
	 * 
	 * @return array
	 */
	private function getAllowSortFields() {
		$confId = $this->getConfId().'sort.';
		$configurations = $this->getConfigurations();

		$sortFields = $configurations->get($confId.'fields');
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
			$template, $markerArray, $subpartArray, $wrappedSubpartArray, $formatter, $confId, $marker
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
	private function insertMarkersForSorting($template, &$markerArray, &$subpartArray, &$wrappedSubpartArray, &$formatter, $confId, $marker = 'FILTER') {
		$marker = 'SORT';
		$confId = $this->getConfId().'sort.';
		$configurations = $formatter->getConfigurations();
		
		// die felder für die sortierung stehen kommasepariert im ts
		$sortFields = $this->getAllowSortFields();

		if(!empty($sortFields)) {
			tx_rnbase::load('tx_rnbase_util_BaseMarker');
		  	$token = md5(microtime());
		  	$markOrders = array();
			foreach($sortFields as $field) {
				$isField = ($field == $this->getSortBy());
				// sortOrder ausgeben
				$markOrders[$field.'_order'] = $isField ? $this->sortOrder : '';

				$fieldMarker = $marker.'_'.strtoupper($field).'_LINK';
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
					$link->initByTS($configurations, $confId.'link.', $params);
					if($makeLink)
						$wrappedSubpartArray['###'.$fieldMarker.'###'] = explode($token, $link->makeTag());
					if($makeUrl)
						$markerArray['###'.$fieldMarker.'URL###'] = $link->makeUrl(false);
				}
			}
			// die sortOrders parsen
			$markOrders = $formatter->getItemMarkerArrayWrapped($markOrders, $confId, 0,$marker.'_', array_keys($markOrders));
			$markerArray = array_merge($markerArray, $markOrders);
		}
	}
}