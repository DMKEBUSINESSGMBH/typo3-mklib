<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_action
 *
 *  Copyright notice
 *
 *  (c) 2014 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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
tx_rnbase::load('tx_rnbase_action_BaseIOC');
tx_rnbase::load('tx_rnbase_filter_BaseFilter');

/**
 * Controller
 * Generische Klasse für List Views
 *
 * @package tx_mklib
 * @subpackage tx_mklib_action
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
abstract class tx_mklib_action_AbstractList
	extends tx_rnbase_action_BaseIOC {

	/**
	 * do the magic!
	 *
	 * @param tx_rnbase_parameters $parameters
	 * @param tx_rnbase_configurations $configurations
	 * @param ArrayObject $viewData
	 *
	 * @return string error msg or null
	 */
	public function handleRequest(&$parameters,&$configurations, &$viewData)
	{
		$items = $this->getItems();
		$viewData->offsetSet('items', is_array($items) ? array_values($items) : array());
		$viewData->offsetSet('searched', $items !== FALSE);

		return;
	}

	/**
	 * searches for the items to show in list
	 *
	 * @throws RuntimeException
	 * @return array|FALSE
	 */
	protected function getItems()
	{
		// get the repo
		$repo = $this->getRepository();

		// check the repo interface
		if (!$repo instanceof tx_mklib_interface_Repository) {
			throw new RuntimeException(
				'the repository "' . get_class($repo) . '" ' .
				'has to implement the interface "tx_mklib_interface_Repository"!',
				intval(ERROR_CODE_MKLIB . '1')
			);
		}

		// create filter
		$filter = tx_rnbase_filter_BaseFilter::createFilter(
			$this->getParameters(),
			$this->getConfigurations(),
			$this->getViewData(),
			$this->getConfId() . 'filter.'
		);

		$fields = $options = array();
		// let the filter fill the fields end options
		if (
			$this->prepareFieldsAndOptions($fields, $options)
			&& $filter->init($fields, $options)
		) {
			// we search for the items
			$items = $repo->search($fields, $options);
		}
		else {
			// it was not carried out search
			return FALSE;
		}

		return empty($items) ? array() : $items;
	}

	/**
	 *
	 * @param array &$fields
	 * @param array &$options
	 * @return boolean
	 */
	protected function prepareFieldsAndOptions(
		array &$fields,
		array &$options
	) {
		return TRUE;
	}

	/**
	 * Gibt den Name der zugehörigen View-Klasse zurück
	 *
	 * @return string
	 */
	public function getViewClassName()
	{
		return 'tx_rnbase_view_List';
	}


	/**
	 * Liefert den Templatenamen.
	 * Darüber wird per Konvention auch auf ein per TS konfiguriertes
	 * HTML-Template geprüft und die ConfId gebildet
	 *
	 * @return string
	 */
	// abstract protected function getTemplateName();

	/**
	 * Liefert die Service Klasse, welche das Suchen übernimmt
	 *
	 * @return tx_mklib_interface_Repository
	 */
	abstract protected function getRepository();

}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/action/class.tx_mklib_action_ListBase.php'])	{
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/action/class.tx_mklib_action_ListBase.php']);
}
