<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_util
 *
 *  Copyright notice
 *
 *  (c) 2013 das MedienKombinat GmbH <kontakt@das-medienkombinat.de>
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

require_once t3lib_extMgm::extPath('rn_base', 'class.tx_rnbase.php');
tx_rnbase::load('tx_rnbase_util_ListBuilder');

/**
 * Der Listbuilder erzeugt die ausgabe für den export.
 *
 * @author Michael Wagner <michael.wagner@das-medienkombinat.de>
 */
class tx_mklib_util_list_Builder
	extends tx_rnbase_util_ListBuilder {

	// die ist leider private und muss überschrieben werden
	private $callbacks = array();

	/**
	 * @var tx_mklib_util_list_output_Interface
	 */
	private $output = NULL;
	
	
	public function __construct($data = array(), $outputHandler = null) {
		
		$this->setOutputHandler($outputHandler, $data);
		if (!$this->output instanceof tx_mklib_util_list_output_Interface) {
			$this->setOutputHandler('tx_mklib_util_list_output_File', $data);
		}
		$info = (is_array($data) && array_key_exists('info', $data)) ? $data['info'] : null;
		parent::__construct($info);
	}
	
	/**
	* Load the output handler
	*
	* @param string $outputHandlerd
	* @param array $data
	* @return bool $success
	*/
	public function setOutputHandler($outputHandler, $data = array()) {
		if (!is_string($outputHandler)) {
			return false;
		}
		$outputHandler = tx_rnbase::makeInstance($outputHandler, $data);
		if (!$outputHandler instanceof tx_mklib_util_list_output_Interface) {
			throw new Exception('Passed output Handler is not valid');
		}
		$this->output = $outputHandler;
		return true;
	}
	
	/**
	 * Add a visitor callback. It is called for each item before rendering
	 * @param array $callback
	 */
	public function addVisitor(array $callback) {
		$this->callbacks[] = $callback;
	}
	
	public function renderEach(
		tx_rnbase_util_IListProvider $provider,
		$viewData, $template,
		$markerClassname, $confId,
		$marker, $formatter,
		$markerParams = null
	) {

		$viewData = is_object($viewData) ? $viewData : new ArrayObject();

		$outerMarker = $this->getOuterMarker($marker, $template);

		// wir teilen das Template, da der erste teil direkt ausgegeben werden muss!
		list($header, $footer) = $this->getWrapForSubpart($template, $outerMarker.'S');

		$this->handleOutput($header);
		
		/* @var $listMarker tx_mklib_util_list_Marker */
		$listMarker = tx_rnbase::makeInstance(
			'tx_mklib_util_list_Marker',
			$this->info->getListMarkerInfo(), $this->output
		);

		$templateList = t3lib_parsehtml::getSubpart($template, '###'.$outerMarker.'S###');
		list($listHeader, $listFooter) = $this->getWrapForSubpart($templateList, $marker);
		$templateEntry = t3lib_parsehtml::getSubpart($templateList, '###'.$marker.'###');

		$this->handleOutput($listHeader);

		$listMarker->addVisitors($this->callbacks);
		$ret = $listMarker->renderEach($provider, $templateEntry, $markerClassname,
				$confId, $marker, $formatter, $markerParams, $offset);
		
		$this->handleOutput($listFooter);
		$this->handleOutput($footer);

		return true;
	}

	protected function getWrapForSubpart($template, $marker, $required = true) {
		// wir teilen das Template, da der erste teil direkt ausgegeben werden muss!
		$token = md5(time()).md5(get_class());
		$wrap = tx_rnbase_util_Templates::substituteSubpart(
				$template, '###'.$marker.'###', $token, 0);
		$wrap = explode($token, $wrap);

		if ($required && count($wrap) != 2) {
			// es ist etwas schiefgelaufen, wir sollten immer 2 teile haben
			// einmal header und einmal footer
			throw new Exception('Marker '.$marker.' not fount in Template', 1361171589);
		}

		return $wrap;
	}

	function render(&$dataArr, $viewData, $template, $markerClassname, $confId, $marker, $formatter, $markerParams = null) {
		$out = parent::render($dataArr, $viewData, $template, $markerClassname, $confId, $marker, $formatter);
		$this->handleOutput($out);
	}
	
	/**
	 * Handle output
	 * @param string $out
	 * @return bool
	 */
	private function handleOutput($out) {
		if ($this->output instanceof tx_mklib_util_list_output_Interface) {
			$this->output->handleOutput($out);
			return true;
		}
		return false;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/util/list/class.tx_mklib_util_list_Builder.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/util/list/class.tx_mklib_util_list_Builder.php']);
}
